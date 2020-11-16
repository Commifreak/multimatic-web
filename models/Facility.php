<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * Class User
 * @property integer $id
 * @property integer $uid
 * @property string $fid Facility Name/Number
 * @property string $name
 * @property string $network_info
 * @property string $firmware
 * @property string $box_status
 * @property integer $last_sync As timestamp
 * @property integer $status
 *
 * @property User $user
 *
 */
class Facility extends ActiveRecord
{

    const STATUS_ACTIVE = 10;
    const STATUS_INACTIVE = 99;

    public static function tableName()
    {
        return 'facility';
    }

    public function rules()
    {
        return [
            [['id', 'uid', 'last_sync'], 'integer'],
            [['fid', 'name', 'network_info', 'firmware', 'box_status'], 'string'],
            ['fid', 'required'],
            ['fid', 'unique'],
            [['status'], 'default', 'value' => self::STATUS_ACTIVE],
        ];
    }

    public function attributeLabels()
    {
        return [
            'v_username' => \Yii::t('site', 'Vaillant Username'),
            'v_password' => \Yii::t('site', 'Vaillant Password'),
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'uid']);
    }

    public function getData()
    {
        return $this->hasMany(Data::class, ['fid' => 'id']);
    }

    public function getCharts()
    {
        return $this->hasMany(Chart::class, ['fid' => 'id']);
    }

    public function getIsOffline()
    {
        $data = unserialize($this->box_status);
        if (!empty($this->box_status) && is_object($data)) {
            return $data->onlineStatus->status == 'OFFLINE' && (empty($data->firmwareUpdateStatus) || $data->firmwareUpdateStatus->status == 'UPDATE_NOT_PENDING');
        }
        return false;
    }

    public function getIsOnline()
    {
        $data = unserialize($this->box_status);
        if (!empty($this->box_status) && is_object($data)) {
            return $data->onlineStatus->status == 'ONLINE' && (empty($data->firmwareUpdateStatus) || $data->firmwareUpdateStatus->status == 'UPDATE_NOT_PENDING');
        }
        return false;
    }

    public function getUpdatePending()
    {
        $data = unserialize($this->box_status);
        if (!empty($this->box_status) && is_object($data)) {
            return !empty($data->firmwareUpdateStatus) && $data->firmwareUpdateStatus->status == 'UPDATE_PENDING';
        }
        return false;
    }

}
