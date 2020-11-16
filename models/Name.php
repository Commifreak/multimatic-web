<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * Class User
 * @property integer $id
 * @property integer $fid
 * @property string $name
 * @property string $nice_name
 * @property string $unit
 *
 * @property Facility $facility
 *
 */
class Name extends ActiveRecord
{


    public static function tableName()
    {
        return 'name';
    }

    public function rules()
    {
        return [
            [['id', 'fid'], 'integer'],
            [['name', 'nice_name', 'unit'], 'string'],
        ];
    }

    public function attributeLabels()
    {
        return [];
    }

    public function getFacility()
    {
        return $this->hasOne(Facility::class, ['id' => 'fid']);
    }


}
