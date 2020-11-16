<?php

$this->title                   = 'Dashboard';
$this->params['breadcrumbs'][] = $this->title;

/* @var $this yii\web\View */

use app\models\Chart;
use app\models\Facility;
use yii\bootstrap\Modal;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\Pjax;

?>

<h1><?= Yii::t('data', 'Charts') ?></h1>
<div class="row">
    <div class="col-md-12">
        <p class="alert alert-info"><?= Yii::t('data', 'On this page, you can view all your data with the help of charts :)') ?></p>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <?php

        $f = Facility::findOne($fid);

        if (empty($f->charts)) {
            echo '<p class="alert alert-warning">' . Yii::t('data', 'No charts created, yet. Go and create one :)') . '</p>';
        }

        $urlCreate = Url::to(['data/create-chart', 'fid' => $f->id]);
        $urlEdit   = Url::to(['data/edit-chart']);

        $urlPjaxMoveChart = Url::to(['/data/get-chart']);

        $txtSure   = Yii::t('data', 'Are your sure you want to delete this chart?');
        $urlDelete = Url::to(['data/delete-chart']);

        $showEventCreate = <<<JS
 function() {
    $.ajax({
        url: '{$urlCreate}',
        method: 'GET'
    }).done(function(data) {
        $('#createChartModal').find('.modal-body').html(data);
    }).fail(function() {
        alert('Something went wrong while loading content!');
    });
 }
JS;

        $js = <<<JS

chartMoves = [];

 function editChart(id) {
    $.ajax({
        url: '{$urlEdit}?id='+id,
        method: 'GET'
    }).done(function(data) {
        $('#editChartModal').find('.modal-body').html(data);
    }).fail(function() {
        alert('Something went wrong while loading content!');
    });
 }
 
  function deleteChart(id) {
    
    var cfn = confirm('{$txtSure}');
    
    if(!cfn) {
        return false;
    }
    
    $.ajax({
        url: '{$urlDelete}?id='+id,
        method: 'GET'
    }).done(function(data) {
        window.location.reload();
    }).fail(function() {
        alert('Something went wrong while loading content!');
    });
 }
 
  function moveChart(type, id) {
     if(typeof chartMoves[id] == 'undefined') {
         chartMoves[id] = 0;
     }
     if(type == 'tw') {
        chartMoves[id]++;
     } else {
         chartMoves[id]--;
     }
     $.pjax.reload({push: false, replace: false, url: '{$urlPjaxMoveChart}?move='+chartMoves[id]+'&id='+id, container: '#pjaxChartHistory_'+id, timeout: 10000});
 }
 
 function initCharts() {
     if($(toLoadCharts).length === 0) {
         return true;
     }
     
     $(toLoadCharts).each(function(index, value) {
         $.pjax.reload({push: false, replace: false, url: '{$urlPjaxMoveChart}?id='+value, container: '#pjaxChartHistory_'+value, timeout: 10000}).done(function() {
           console.log("done loading chart");
           // https://stackoverflow.com/questions/3596089/how-to-remove-specific-value-from-array-using-jquery
            toLoadCharts = jQuery.grep(toLoadCharts, function(remvalue) {
              return remvalue != value;
            });
            console.log('New list:');
            console.log(toLoadCharts);
            initCharts();
         });
     });
 }
JS;

        $this->registerJs($js, View::POS_END);


        echo Modal::widget([
            'id'           => 'createChartModal',
            'size'         => Modal::SIZE_LARGE,
            'header'       => Yii::t('data', 'Create Chart'),
            'footer'       => '<a href="javascript:void(0);" data-dismiss="modal" class="btn btn-default">' . Yii::t('data', 'Close') . '</a> <a href="javascript:void(0);" onclick="$(\'#createChartForm\').submit();" class="btn btn-primary">' . Yii::t('data', 'Create') . '</a>',
            'clientEvents' => [
                'show.bs.modal' => $showEventCreate
            ]
        ]);

        echo Modal::widget([
            'id'     => 'editChartModal',
            'size'   => Modal::SIZE_LARGE,
            'header' => Yii::t('data', 'Edit Chart'),
            'footer' => '<a href="javascript:void(0);" data-dismiss="modal" class="btn btn-default">' . Yii::t('data', 'Close') . '</a> <a href="javascript:void(0);" onclick="$(\'#createChartForm\').submit();" class="btn btn-primary">' . Yii::t('data', 'Save') . '</a>',
        ]);

        echo Html::tag('a', Yii::t('data', 'Create chart'), ['class' => 'btn btn-success pull-right', 'href' => 'javascript:void(0);', 'data-toggle' => 'modal', 'data-target' => '#createChartModal']);

        ?>
    </div>
</div>
<?php
$curCol       = 0;
$chartsToLoad = [];
foreach ($f->charts as $chart) {
    /* @var $chart Chart */
    $curCol++;

    array_push($chartsToLoad, $chart->id);

    if ($curCol === 1) {
        echo '<div class="row" style="padding-top: 30px;">';
    }

    echo '<div class="col-md-6">';

    // The Chart!
    Pjax::begin([
        'id'              => 'pjaxChartHistory_' . $chart->id,
        'enablePushState' => false,
    ]);
    ?>
    <div>Loading...</div>
    <?php
    Pjax::end();
    //echo '<br />';


    echo '<div style="text-align: center;"><a class="btn btn-default btn-sm" onclick="moveChart(\'bw\', ' . $chart->id . ')"><span class="glyphicon glyphicon-chevron-left"></span></a>&nbsp;&nbsp;&nbsp;&nbsp;<a class="btn btn-xs btn-warning" data-toggle="modal" data-target="#editChartModal" onclick="editChart(' . $chart->id . ')"><span class="glyphicon glyphicon-cog"></span></a> <a class="btn btn-xs btn-danger" onclick="deleteChart(' . $chart->id . ')"><span class="glyphicon glyphicon-trash"></span></a>&nbsp;&nbsp;&nbsp;&nbsp;<a class="btn btn-default btn-sm" onclick="moveChart(\'tw\', ' . $chart->id . ')"><span class="glyphicon glyphicon-chevron-right"></span></a></div>';

    echo '</div>';
    if ($curCol === 2) {
        $curCol = 0;
        echo '</div>';
    }
}

if ($curCol < 2) {
    // Fix missing div
    echo '</div>';
}

$chartsToLoad = implode(', ', $chartsToLoad);

$jsOnload = <<<JS
toLoadCharts = [{$chartsToLoad}];

initCharts();
JS;
$this->registerJs($jsOnload, View::POS_LOAD);

?>
