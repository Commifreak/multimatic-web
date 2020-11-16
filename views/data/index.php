<?php

use app\models\Data;
use app\models\Facility;
use app\models\User;
use kartik\widgets\SwitchInput;
use yii\bootstrap\Collapse;
use yii\helpers\Url;
use yii\web\View;

$this->title                   = 'Dashboard';
$this->params['breadcrumbs'][] = $this->title;

/* @var $this yii\web\View */

$harvesting_status = SwitchInput::widget([
    'id'            => 'global_harvesting_status',
    'name'          => 'global_harvesting_status',
    'value'         => Yii::$app->user->identity->harvester_status,
    'pluginOptions' => [
        'onColor'  => 'success',
        'offColor' => 'danger',
        'onText'   => Yii::t('data', 'Enabled'),
        'offText'  => Yii::t('data', 'Disabled'),
    ],
    'pluginEvents'  => [
        'switchChange.bootstrapSwitch' => 'function() { toggleHarvesterStatus(); }'
    ]
]);

$ajax_url = Url::to(['/user/toggle-harvester-status']);
$error    = Yii::t('data', 'Sorry, I was not able to change the status!');
$js       = <<<JS
function toggleHarvesterStatus() {
    $.ajax({
    type: 'POST',
    url: '{$ajax_url}',
    data: {
        status: $('#global_harvesting_status').is(':checked')
    }
    }).fail(function(data) {
        alert('{$error}');
    }).done(function() {
        window.location.reload();
    })
}
JS;

$this->registerJs($js, View::POS_END);


?>

<h1>Dashboard</h1>
<p class="alert alert-info"><?= Yii::t('data', '<b>Welcome to the dashboard!</b><br />Here you can control your data collection and tune some settings - and view your data ;)') ?></p>

<h3><?= Yii::t('data', 'Global harvesting status') . ' <small class="text-muted">For your whole account 😉</small><span class="pull-right">' . $harvesting_status . '</span>' ?></h3>
<br/>
<div>
    <?php


    $facilities = Yii::$app->user->identity->facilities;

    if (empty($facilities)) {
        $harvester_dis_warn = '';
        if (Yii::$app->user->identity->harvester_status == User::HARVESTER_DISABLED) {
            $harvester_dis_warn = Yii::t('data', '<br /><b>Please enable the global harvester if you want to see any of your facilities ;)</b>');
        }
        echo '<div class="alert alert-warning">' . Yii::t('site', 'Our harvester did not find any facility (yet)! Either you have to wait some minutes (new account) or there is an issue with your account.') . $harvester_dis_warn . '</div>';
    } else {
        $items = [];
        foreach ($facilities as $facility) {
            /* @var $facility Facility */

            $txt_last_sync    = Yii::t('data', 'Last sync:');
            $txt_newest_entry = Yii::t('data', 'Newest data entry:');
            $txt_fw           = Yii::t('data', 'Firmware');
            $txt_network      = Yii::t('data', 'Network Info:');

            $last_sync    = empty($facility->last_sync) ? Yii::t('data', 'Never') : Yii::$app->formatter->asDatetime($facility->last_sync);
            $network_info = implode(', ', array_values(unserialize($facility->network_info)));
            $data_entry   = Data::find()->where(['fid' => $facility->id])->orderBy(['time' => SORT_DESC])->one();
            $newest_entry = $data_entry ? Yii::$app->formatter->asDatetime($data_entry->time) : '<i>' . Yii::t('data', 'No data available (yet)') . '</i>';

            $content = <<<HTML
<div class="row">
<div class="col-md-2"><b>{$txt_last_sync}</b></div><div class="col-md-3">{$last_sync}</div>
</div>
<div class="row">
<div class="col-md-2"><b>{$txt_newest_entry}</b></div><div class="col-md-3">{$newest_entry}</div>
</div>
<div class="row">
<div class="col-md-2"><b>{$txt_fw}</b></div><div class="col-md-3">{$facility->firmware}</div>
</div>
<div class="row">
<div class="col-md-2"><b>{$txt_network}</b></div><div class="col-md-5">{$network_info}</div>
</div>
HTML;

            $boxStatus = '';

            if ($facility->isOnline) {
                $boxStatus .= '<span class="label label-success">ONLINE</span>';
            }
            if ($facility->isOffline) {
                $boxStatus .= '<span class="label label-danger">OFFLINE</span>';
            }
            if ($facility->updatePending) {
                $boxStatus .= '<span class="label label-warning">UPDATE_PENDING</span>';
            }

            if (empty($boxStatus)) {
                $boxStatus = '<span class="label label-default">UNKNOWN</span>';
            }


            array_push($items, [
                'label'   => $facility->name . ' <small class="text-muted">' . $facility->fid . '</small><a href="' . Url::to(['data/chart', 'fid' => $facility->id]) . '"><span class="glyphicon glyphicon-signal"></span></a><span class="pull-right">' . $boxStatus . '</span>',
                'content' => $content
            ]);

        }

        echo Collapse::widget([
            'encodeLabels' => false,
            'items'        => $items
        ]);
    }
    ?>
</div>
