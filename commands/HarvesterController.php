<?php


namespace app\commands;

use app\components\VaillantAPI;
use app\models\Data;
use app\models\Facility;
use app\models\Name;
use app\models\User;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\ArrayHelper;


class HarvesterController extends Controller
{

    public function actionCronjob()
    {

        \Yii::info('Starting new harvesting round :)', 'harvester');

        $users = User::findAll(['harvester_status' => User::HARVESTER_ENABLED, 'status' => User::STATUS_ACTIVE]);


        foreach ($users as $user) {

            \Yii::info('Processing User #' . $user->id . ' (' . $user->email . ')', 'harvester');


            $va = new VaillantAPI($user->v_username, $user->v_password);

            // \Yii::error("Healthcheck failed!\n\n".print_r($va->getLastCurlError())."\n\n\n".print_r($va->getLastCurlInfo()), 'harvester');
            if (!$va->healthCheckPassed) {
                $errMsg = "The healthcheck failed!\n\n" . print_r($va->getLastCurlError(), true) . "\n\n\n" . print_r($va->getLastCurlInfo(), true);
                \Yii::error($errMsg, 'harvester');
                if (!\Yii::$app->cache->get('hc_admin_mailed')) {

                    \Yii::$app->cache->set('hc_admin_mailed', true, 900);

                    // Mail admin
                    \Yii::$app->mailer->compose()
                        ->setTo([\Yii::$app->params['adminEmail'] => \Yii::$app->params['senderName']])
                        ->setFrom([\Yii::$app->params['senderEmail'] => \Yii::$app->params['senderName']])
                        ->setSubject('Harvester error')
                        ->setTextBody($errMsg)
                        ->send();
                }
                return ExitCode::UNAVAILABLE;
            }

            $facilities = $va->getFacilities();

            if (!$facilities) {
                \Yii::error('We either have aconnection problem or username/password is wrong!', 'harvester');
                if ($va->getLastHttpCode() === 401) {
                    \Yii::$app->mailer->compose()
                        ->setTo([$user->email => $user->first_name . ' ' . $user->last_name])
                        ->setFrom([\Yii::$app->params['senderEmail'] => \Yii::$app->params['senderName']])
                        ->setReplyTo([\Yii::$app->params['adminEmail'] => \Yii::$app->params['senderName']])
                        ->setSubject('Vaillant credentials issue')
                        ->setTextBody("Hi :)\n\nIt seems, that your Vaillant credentials are no longer valid - at least I just got an authorization error from them.\n\nPlease dont hit me, but I disabled your account harvester for now. Please check your username/password and enable it again.")
                        ->send();

                    $user->harvester_status = User::HARVESTER_DISABLED;
                    $user->save(false);
                    \Yii::warning('Skipping this user - 401, harvester disabled now..', 'harvester');
                    continue;
                }
                exit;
            }

            $knownFacilities = ArrayHelper::getColumn($facilities, 'serialNumber');

            foreach (Facility::find()->where(['and', ['uid' => $user->id], ['not in', 'fid', $knownFacilities], ['status' => Facility::STATUS_ACTIVE]])->all() as $facility) {
                /* @var $facility Facility */
                \Yii::warning('Got Facility (#' . $facility->id . '), which is not for this user! Disabling it and mail the user.', 'harvester');
                $facility->status = Facility::STATUS_INACTIVE;
                $facility->uid    = null;
                $facility->save(false);

                \Yii::$app->mailer->compose()
                    ->setTo([$user->email => $user->first_name . ' ' . $user->last_name])
                    ->setFrom([\Yii::$app->params['senderEmail'] => \Yii::$app->params['senderName']])
                    ->setReplyTo([\Yii::$app->params['adminEmail'] => \Yii::$app->params['senderName']])
                    ->setSubject('Facility disabled')
                    ->setTextBody("Hi :)\n\nIve disabled your facility with the serial number " . $facility->fid . ", because it seems not to connected to your Vaillant account anymore. I also removed the link between your user account and that facility. If you are no longer owner of this facility, you dont have any right to view its data.\n\nIf this is a mistake, reply to this mail.\n\nSorry - anyway: Best wishes and regards,")
                    ->send();
            }


            foreach ($facilities as $facility) {
                \Yii::info('Processing facility ' . $facility->serialNumber, 'harvester');
                /* @var $facility \stdClass */
                $dbFac = Facility::findOne(['fid' => $facility->serialNumber]);
                if (!$dbFac) {
                    \Yii::warning('Got Facility (#' . $facility->serialNumber . '), which is NEW for this user! Adding it and mail the user.', 'harvester');

                    $dbFac      = new Facility();
                    $dbFac->uid = $user->id;
                    $dbFac->fid = $facility->serialNumber;
                    if ($dbFac->save()) {
                        \Yii::$app->mailer->compose()
                            ->setTo([$user->email => $user->first_name . ' ' . $user->last_name])
                            ->setFrom([\Yii::$app->params['senderEmail'] => \Yii::$app->params['senderName']])
                            ->setReplyTo([\Yii::$app->params['adminEmail'] => \Yii::$app->params['senderName']])
                            ->setSubject('Facility added')
                            ->setTextBody("Hi :)\n\nI have found a new facility with the serial number " . $facility->serialNumber . " which was not yet connected to your user account (I mean the user account in MultimaticWeb). Just wanted you to know that it is now available in your account.\n\nRegards from the system! ❤️")
                            ->send();
                    }
                }

                $va->setCurrentFacility($facility->serialNumber);


                // Check F/W
                if (!empty($dbFac->firmware) && $dbFac->firmware != $facility->firmwareVersion) {
                    // Send user a mail, that the F/W was updated.
                    \Yii::$app->mailer->compose()
                        ->setTo([$user->email => $user->first_name . ' ' . $user->last_name])
                        ->setFrom([\Yii::$app->params['senderEmail'] => \Yii::$app->params['senderName']])
                        ->setReplyTo([\Yii::$app->params['adminEmail'] => \Yii::$app->params['senderName']])
                        ->setSubject('Firmware updated!')
                        ->setTextBody("Hi :)\n\nYour Internet box with serial " . $facility->serialNumber . " got an update from " . $dbFac->firmware . " -> " . $facility->firmwareVersion . ". I just wanted to inform you about that.\n\nRegards from the system! ❤️")
                        ->send();
                }
                // Update some core data.
                $dbFac->name         = $facility->name;
                $dbFac->network_info = serialize((array)$facility->networkInformation);
                $dbFac->firmware     = $facility->firmwareVersion;
                $dbFac->box_status   = serialize($va->getBoxStatus());
                $dbFac->last_sync    = time();
                $dbFac->save(false);

                //print_r(unserialize($dbFac->box_status));

                // Collect data
                if ($dbFac->getIsOffline()) {
                    \Yii::warning('Skipping this facility, it seems to be offline:', 'harvester');
                    \Yii::warning(unserialize($dbFac->box_status), 'harvester');
                    continue;
                }

                // Store Systemcontrol.
                $sc = $va->getSystemControl();


                // Cache Meta info for later.
                $meta = $va->getApiMeta();


                if (empty($meta) || !property_exists($meta, 'resourceState')) {
                    \Yii::error('Caching Meta: Resource state missing!', 'harvester');
                    continue;
                }
                $syncStates = [];
                foreach ($meta->resourceState as $rs) {
                    $syncStates[str_replace('/' . $va->lastGeneratedCommand, '', $rs->link->resourceLink)] = [
                        'state'     => $rs->state,
                        'timestamp' => property_exists($rs, 'timestamp') ? $rs->timestamp : false
                    ];
                }

                print_r($syncStates);

                // Get zones.
                if (property_exists($sc, 'zones')) {
                    \Yii::info('Processing Zones.', 'harvester');
                    foreach ($sc->zones as $zone) {
                        \Yii::info('Processing Zone ' . $zone->_id, 'harvester');

                        if (property_exists($zone, 'configuration')) {
                            \Yii::info('Processing Zone main config', 'harvester');
                            $name = Name::findOne(['fid' => $dbFac->id, 'name' => $zone->_id]);

                            if (!$name) {
                                $name            = new Name();
                                $name->fid       = $dbFac->id;
                                $name->name      = $zone->_id;
                                $name->nice_name = $zone->configuration->name;
                            } else {
                                if ($name->nice_name != $zone->configuration->name) {
                                    $name->nice_name = $zone->configuration->name;
                                }
                            }
                            $name->save();
                        }

                        if (property_exists($zone, 'configuration') && array_key_exists('/zones/' . $zone->_id . '/' . strtolower($zone->configuration->active_function) . '/configuration', $syncStates) && $syncStates['/zones/' . $zone->_id . '/' . strtolower($zone->configuration->active_function) . '/configuration']['state'] == 'SYNCED') {
                            if (property_exists($zone, strtolower($zone->configuration->active_function))) {
                                $zoneData = [
                                    'enabled'       => $zone->configuration->enabled,
                                    'setback_temp'  => $zone->{strtolower($zone->configuration->active_function)}->configuration->setback_temperature,
                                    'setpoint_temp' => $zone->{strtolower($zone->configuration->active_function)}->configuration->setpoint_temperature,
                                ];
                            }
                        } else {
                            \Yii::warning('Not processing detailed zone configuration, due to its not in synced state', 'harvester');
                        }

                        // Write Zone info
                        if (isset($zoneData) && isset($syncStates['/zones/' . $zone->_id . '/' . strtolower($zone->configuration->active_function) . '/configuration'])) {
                            $time = $syncStates['/zones/' . $zone->_id . '/' . strtolower($zone->configuration->active_function) . '/configuration']['timestamp'];
                            if (Data::findOne(['fid' => $dbFac->id, 'type' => $zone->_id, 'time' => $time])) {
                                \Yii::warning('Ignoring this data, already in the database. (same timestamp)', 'harvester');
                            } else {
                                $data        = new Data();
                                $data->fid   = $dbFac->id;
                                $data->type  = $zone->_id;
                                $data->value = serialize($zoneData);
                                $data->time  = $time;
                                $data->save();
                            }
                        } else {
                            \Yii::warning('Not saving zoneData, cause it does not exist.', 'harvester');
                        }
                    }
                    unset($zone);
                }


                // Other data.
                if (property_exists($sc, 'status')) {
                    if ($syncStates['/status']['state'] == 'SYNCED') {
                        if (Data::findOne(['fid' => $dbFac->id, 'type' => 'outside_temp', 'time' => $syncStates['/status']['timestamp']])) {
                            \Yii::warning('Not saving outside temp, because its not newer', 'harvester');
                        } else {
                            $data        = new Data();
                            $data->fid   = $dbFac->id;
                            $data->type  = 'outside_temp';
                            $data->value = (string)$sc->status->outside_temperature;
                            $data->time  = $syncStates['/status']['timestamp'];
                            $data->save();
                        }
                    } else {
                        \Yii::warning('Not storing outside temp, because its outdated!', 'harvester');
                    }
                }


                // Domestic Hot Water
                if (property_exists($sc, 'dhw')) {
                    foreach ($sc->dhw as $dhw) {
                        \Yii::info('Processing DHW: ' . $dhw->_id, 'harvester');
                        if (array_key_exists('/dhw/' . $dhw->_id . '/hotwater/configuration', $syncStates) && $syncStates['/dhw/' . $dhw->_id . '/hotwater/configuration']['state'] == 'SYNCED') {
                            if (property_exists($dhw, 'hotwater') && property_exists($dhw->hotwater, 'configuration')) {
                                $dhwData = [
                                    'temp_setpoint' => $dhw->hotwater->configuration->temperature_setpoint,
                                ];
                            }
                        } else {
                            \Yii::warning('Not processing detailed dhw configuration, due to its not in synced state', 'harvester');
                        }

                        // Write DHW info
                        if (isset($dhwData)) {
                            $time = $syncStates['/dhw/' . $dhw->_id . '/hotwater/configuration']['timestamp'];
                            if (Data::findOne(['fid' => $dbFac->id, 'type' => $dhw->_id, 'time' => $time])) {
                                \Yii::warning('Ignoring this data, already in the database. (same timestamp)', 'harvester');
                            } else {
                                $data        = new Data();
                                $data->fid   = $dbFac->id;
                                $data->type  = $dhw->_id;
                                $data->value = serialize($dhwData);
                                $data->time  = $time;
                                $data->save();
                            }
                        } else {
                            \Yii::warning('Not saving dhwData, cause it does not exist.', 'harvester');
                        }
                    }
                    unset($dhw);
                }


                // Reports + new Meta Cache for syncStates


                $lr = $va->getLiveReport();

                $meta = $va->getApiMeta();


                if (empty($meta) || !property_exists($meta, 'resourceState')) {
                    \Yii::error('Caching Meta: Resource state missing!', 'harvester');
                    continue;
                }
                $syncStates = [];
                foreach ($meta->resourceState as $rs) {
                    $syncStates[str_replace('/' . $va->lastGeneratedCommand, '', $rs->link->resourceLink)] = [
                        'state'     => $rs->state,
                        'timestamp' => property_exists($rs, 'timestamp') ? $rs->timestamp : false
                    ];
                }

                if (property_exists($lr, 'devices')) {
                    foreach ($lr->devices as $device) {
                        \Yii::info('Processing device: ' . $device->_id, 'harvester');
                        /**
                         * $name = Name::findOne(['fid' => $dbFac->id, 'name' => $device->_id]);
                         *
                         * if (!$name) {
                         * $name = new Name();
                         * $name->fid = $dbFac->id;
                         * $name->name = $device->_id;
                         * $name->nice_name = $device->name;
                         * } else {
                         * if ($name->nice_name != $device->name) {
                         * $name->nice_name = $device->name;
                         * }
                         * }
                         * $name->save();
                         **/


                        if (property_exists($device, 'reports')) {
                            foreach ($device->reports as $report) {
                                \Yii::info('Processing LiveReport: ' . $report->_id, 'harvester');
                                $name = Name::findOne(['fid' => $dbFac->id, 'name' => $report->_id]);

                                if (!$name) {
                                    $name            = new Name();
                                    $name->fid       = $dbFac->id;
                                    $name->name      = $report->_id;
                                    $name->nice_name = $report->name;
                                    $name->unit      = $report->unit;
                                } else {
                                    if ($name->nice_name != $report->name || $name->unit != $report->unit) {
                                        $name->nice_name = $report->name;
                                        $name->unit      = $report->unit;
                                    }
                                }
                                $name->save();


                                // Processing data..
                                if ($syncStates['/devices/' . $device->_id . '/reports/' . $report->_id]['state'] == 'SYNCED') {
                                    $time = $syncStates['/devices/' . $device->_id . '/reports/' . $report->_id]['timestamp'];
                                    if (Data::findOne(['fid' => $dbFac->id, 'type' => $report->_id, 'time' => $time])) {
                                        \Yii::warning('Ignoring Device: ' . $device->_id . ' -> report: ' . $report->_id . ' as its already in db', 'harvester');
                                    } else {
                                        $data        = new Data();
                                        $data->fid   = $dbFac->id;
                                        $data->type  = $report->_id;
                                        $data->value = (string)$report->value;
                                        $data->time  = $time;
                                        $data->save();
                                    }
                                } else {
                                    \Yii::warning('Not processing this report: Not synced', 'harvester');
                                }
                            }
                        }
                    }
                }


            }

        }

        return ExitCode::OK;
    }
}
