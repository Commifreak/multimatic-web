<?php

namespace app\models;

use DateTime;
use yii\db\ActiveRecord;

/**
 * Class User
 * @property integer $id
 * @property integer $fid
 * @property string $type
 * @property string $value
 * @property integer $time
 *
 * @property Facility $facility
 *
 */
class Data extends ActiveRecord
{


    public static function tableName()
    {
        return 'data';
    }

    /**
     * @param $chart Chart
     * @param $data Data
     */
    public static function generateChartData($chart, $move = false)
    {

        $date = new DateTime();

        $scopeData = self::getDateTimeModifiers($chart->view, $move, $date);

        $data = Data::find()->where(['fid' => $chart->fid, 'type' => $chart->dataTypes]);

        //print_r($scopeData);

        $start = $date->modify($scopeData['start'])->format('U');
        $end   = $date->modify($scopeData['end'])->format('U');

        $data->andWhere(['between', 'time', $start, $end]);
        $datas = $data->orderBy(['time' => SORT_ASC, 'type' => SORT_ASC])->all();

        // mean for the day
        $curPeriod = false;
        $dt        = new DateTime();
        $labels    = [];
        $chartData = [];
        //$partialChartData = [];
        $dayData                  = [];
        $curType                  = false;
        $typeNameTranslationTable = [];
        foreach ($datas as $data) {
            /* @var $data Data */

            $dt->setTimestamp($data->time);
            if ($curPeriod != $dt->format($scopeData['arrayIndex'])) {
                $curPeriod = $dt->format($scopeData['arrayIndex']);
                array_push($labels, $dt->format($scopeData['niceValueInsteadArrayIndex']));
                //$dayData[$data->type][$curDay] = [];
            }
            $dayData[$data->type][$curPeriod][]    = $data->value;
            $typeNameTranslationTable[$data->type] = $data->niceNameWithUnit;
        }

        //print_r($dayData);

        foreach ($dayData as $type => $days) {
            $values = [];
            foreach ($days as $day) {
                $avg = number_format(array_sum($day) / count($day), 1);
                array_push($values, $avg);
            }
            $color       = !empty($chart->colors) && array_key_exists($type, $chart->colors) && !empty($chart->colors[$type]) ? $chart->colors[$type] : false;
            $chartData[] = [
                'label'                     => $typeNameTranslationTable[$type],
                //'backgroundColor' => !empty($chart->colors) && array_key_exists($type, $chart->colors) && !empty($chart->colors[$type]) ? $chart->colors[$type] : null,
                'backgroundColor'           => $color ? $color . '1A' : '',
                'borderColor'               => $color ? $color : '',
                'pointBackgroundColor'      => $color ? $color : '',
                'pointBorderColor'          => "#fff",
                'pointHoverBackgroundColor' => "#fff",
                'pointHoverBorderColor'     => $color ? $color : '',
                //'data' => [65, 40],
                'data'                      => $values,
                'fill'                      => false
            ];
        }


        return [$labels, $chartData];


    }

    /**
     * @param $type string
     * @param bool $move
     * @param $date DateTime
     * @return array
     */
    public static function getDateTimeModifiers($type, $move = false, &$date)
    {
        switch ($type) {
            case Chart::VIEW_DAY:
                if ($move && $move !== 0) {
                    $date->modify($move . ' days');
                }
                return ['start' => 'today', 'end' => 'tomorrow', 'arrayIndex' => 'G', 'niceValueInsteadArrayIndex' => 'G'];
                break;
            case Chart::VIEW_WEEK:
                if ($move && $move !== 0) {
                    $date->modify($move . ' weeks');
                }
                return ['start' => 'monday this week', 'end' => 'monday next week', 'arrayIndex' => 'N', 'niceValueInsteadArrayIndex' => 'l'];
                break;
            case Chart::VIEW_MONTH:
                if ($move && $move !== 0) {
                    $date->modify($move . ' months');
                }
                return ['start' => 'first day of this month 00:00:00', 'end' => 'first day of next month 00:00:00', 'arrayIndex' => 'j', 'niceValueInsteadArrayIndex' => 'j D'];
                break;
            case Chart::VIEW_YEAR:
                if ($move && $move !== 0) {
                    $date->modify($move . ' years');
                }
                return ['start' => 'first day of january this year 00:00:00', 'end' => 'first day of january next year 00:00:00', 'arrayIndex' => 'n', 'niceValueInsteadArrayIndex' => 'M'];
                break;
        }
    }

    public function rules()
    {
        return [
            [['id', 'fid'], 'integer'],
            [['type', 'value'], 'string'],
        ];
    }

    public function attributeLabels()
    {
        return [];
    }

    public function getNiceName()
    {
        if ($this->checkSpecialNames()) {
            return $this->checkSpecialNames();
        }
        $nn = Name::findOne(['fid' => $this->fid, 'name' => $this->type]);
        return $nn ? $nn->nice_name : $this->type;
    }

    public function checkSpecialNames()
    {
        switch ($this->type) {
            case 'outside_temp':
                return \Yii::t('data', 'Outside Temperature');
                break;
            default:
                return false;
        }
    }

    public function getNiceNameWithUnit()
    {
        if ($this->checkSpecialNames()) {
            return $this->checkSpecialNames();
        }
        $nn = Name::findOne(['fid' => $this->fid, 'name' => $this->type]);
        return $nn ? $nn->nice_name . ' ' . $nn->unit : $this->type;
    }

    public function getFacility()
    {
        return $this->hasOne(Facility::class, ['id' => 'fid']);
    }
}
