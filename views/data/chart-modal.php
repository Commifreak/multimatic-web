<?php

use app\models\Chart;
use app\models\Data;
use app\models\Facility;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;

/* @var $facility Facility */
/* @var $model Chart */

$form = ActiveForm::begin([
    'id'                   => 'createChartForm',
    'enableAjaxValidation' => true
]);

$txtClrForField = Yii::t('data', 'Color for field');


echo $form->field($model, 'dataTypes')->widget(Select2::class, [
    'data'         => ArrayHelper::map(Data::find()->where(['fid' => $facility->id])->groupBy('type')->all(), 'type', 'niceName'),
    'options'      => [
        'multiple' => true
    ],
    'pluginEvents' => [
        'change' => 'function() {
        console.log($("#chart-datatypes").val());
        
        $(\'[id^="div_"]\').each(function() {
            var realId = $(this).attr("id").split("__");
            console.log(realId);
            if(!$("#chart-datatypes").val().includes(realId[1])) {
            console.log("removing");
                $(this).remove();
            }
        });
        
        
        // new elems
        $.each($("#chart-datatypes").val(), function(index, value) {
            if($(\'[id^="color_\'+value+\'"]\').length === 0) {
            console.log("adding");
                $("#createChartForm").append(\'<div id="div__\'+value+\'" class="form-group"><label>' . $txtClrForField . ' \'+value+\' </label><input type="color" name="Chart[colors][\'+value+\']" id="color_\'+value+\'" class="form-control" /></div>\');
            }
        });
        
        }'
    ]
]);

echo $form->field($model, 'view')->widget(Select2::class, [
    'data' => [Chart::VIEW_DAY => Chart::VIEW_DAY, Chart::VIEW_WEEK => Chart::VIEW_WEEK, Chart::VIEW_MONTH => Chart::VIEW_MONTH, Chart::VIEW_YEAR => Chart::VIEW_YEAR],
]);

if (!empty($model->colors)) {
    foreach ($model->colors as $field => $color) {
        $data       = new Data();
        $data->type = $field;
        $data->fid  = $model->fid;
        echo '<div id="div__' . $field . '" class="form-group">
    <label>' . Yii::t('data', 'Color for field {field}', ['field' => $data->niceNameWithUnit]) . '</label>
    <input type="color" name="Chart[colors][' . $field . ']" value="' . $color . '" id="color_' . $field . '" class="form-control" />
</div>';
    }
}

ActiveForm::end();


?>