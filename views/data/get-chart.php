<?php

use app\models\Chart;
use app\models\Data;
use dosamigos\chartjs\ChartJs;
use yii\widgets\Pjax;

/* @var $chart Chart */
/* @var $move string */


list($labels, $chartData) = Data::generateChartData($chart, $move);
// The Chart!
Pjax::begin([
    'id'            => 'pjaxChartHistory_' . $chart->id,
    'clientOptions' => [
        'push' => false,
    ]
]);
echo ChartJs::widget([
    'type'          => 'line',
    'options'       => [
        'id' => 'chart_' . $chart->id,
    ],
    'clientOptions' => [
        'scales' => [
            'xAxes' => [[
                            'display'    => true,
                            'scaleLabel' => [
                                'display'     => true,
                                'labelString' => Yii::t('chart', ucfirst($chart->view))
                            ],
                        ]],
        ],
    ],
    // 'options' => [
    //    'height' => 400,
    //   'width' => 400
    //],
    'data'          => [
        'labels'   => $labels,
        'datasets' => $chartData
        /**'datasets' => [
         * [
         * 'label' => "My First dataset",
         * 'backgroundColor' => "rgba(179,181,198,0.2)",
         * 'borderColor' => "rgba(179,181,198,1)",
         * 'pointBackgroundColor' => "rgba(179,181,198,1)",
         * 'pointBorderColor' => "#fff",
         * 'pointHoverBackgroundColor' => "#fff",
         * 'pointHoverBorderColor' => "rgba(179,181,198,1)",
         * 'data' => [65, 40],
         * 'fill' => false
         * ],
         * [
         * 'label' => "My Second dataset",
         * 'backgroundColor' => "rgba(255,99,132,0.2)",
         * 'borderColor' => "rgba(255,99,132,1)",
         * 'pointBackgroundColor' => "rgba(255,99,132,1)",
         * 'pointBorderColor' => "#fff",
         * 'pointHoverBackgroundColor' => "#fff",
         * 'pointHoverBorderColor' => "rgba(255,99,132,1)",
         * 'data' => [28, 48, 40, 19, 96, 27, 100]
         * ]
         * ]**/
    ]
]);
Pjax::end();
//echo '<br />';