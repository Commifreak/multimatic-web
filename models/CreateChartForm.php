<?php

namespace app\models;

use yii\base\Model;


class CreateChartForm extends Model
{
    public $types;


    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['types'], 'required'],
        ];
    }

}
