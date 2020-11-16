<?php

$this->title                   = 'Registration';
$this->params['breadcrumbs'][] = $this->title;

use yii\helpers\Html;
use yii\widgets\ActiveForm;

?>

<h1><?= Yii::t('site', 'Register') ?> <small><?= Yii::t('site', 'Welcome, M8!') ?></small></h1>

<div class="row">
    <div class="col-md-12">
        <p><?= Yii::t('site', 'In order to register, you\'ll need to verify as an active Vaillant MultiMATIC-App user.') ?>
            <br/>
            <?= Yii::t('site', 'You can easily do this by putting you app credentials into the fields below.') ?></p>
        <p>
            <span class="label label-warning"><?= Yii::t('site', 'Wait, what?') ?></span> <?= Yii::t('site', 'You dont have an Vaillant account? Then I\'ll bet you dont have a VR900/VR920 either!') ?>
            <a href="https://www.vaillant.de/heizung/produkte/internetmodul-vr-920-63168.html" target="_blank"
               class="btn btn-xs btn-primary"><?= Yii::t('site', 'Get one') ?></a></p>
    </div>
</div>
<h3><?= Yii::t('site', 'Getting started!') ?></h3>
<div class="row">
    <div class="col-md-12">
        <?php
        $form = ActiveForm::begin([
            'id'                   => 'registerForm',
            'enableAjaxValidation' => true
        ]);

        echo $form->field($model, 'v_username')->textInput();
        echo $form->field($model, 'v_password')->passwordInput();

        echo Html::tag('h3', Yii::t('site', 'Personal data'));

        echo $form->field($model, 'first_name')->textInput();
        echo $form->field($model, 'last_name')->textInput();
        echo $form->field($model, 'email')->textInput(['type' => 'email']);
        echo $form->field($model, 'password')->passwordInput()->hint(Yii::t('site', 'Yea, you\'re not forced to use the Vaillant password ;)'));
        echo $form->field($model, 'password_repeat')->passwordInput();

        echo Html::submitButton(Yii::t('site', 'Submit'), ['class' => 'btn btn-primary']);

        ActiveForm::end();
        ?>
    </div>
</div>