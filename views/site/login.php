<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */

/* @var $model app\models\LoginForm */

use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;

$this->title                   = 'Login';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-login">

    <?php
    if (Yii::$app->request->get('as', false) == 1) {
        echo '<div class="alert alert-success">' . Yii::t('site', 'Verification successful! You are now able to log in!') . '</div>';
    }
    if (Yii::$app->request->get('as', false) == 2) {
        echo '<div class="alert alert-success">' . Yii::t('site', 'Your new password is now in place!') . '</div>';
    }
    ?>
    <h1><?= Html::encode($this->title) ?></h1>

    <p>Please fill out the following fields to login:</p>

    <?php $form = ActiveForm::begin([
        'id'          => 'login-form',
        'layout'      => 'horizontal',
        'fieldConfig' => [
            'template'     => "{label}\n<div class=\"col-lg-3\">{input}</div>\n<div class=\"col-lg-8\">{error}</div>",
            'labelOptions' => ['class' => 'col-lg-1 control-label'],
        ],
    ]); ?>

    <?= $form->field($model, 'email')->textInput(['type' => 'email', 'autofocus' => true]) ?>

    <?= $form->field($model, 'password')->passwordInput() ?>

    <?= $form->field($model, 'rememberMe')->checkbox([
        'template' => "<div class=\"col-lg-offset-1 col-lg-3\">{input} {label}</div>\n<div class=\"col-lg-8\">{error}</div>",
    ]) ?>
    <div class="col-md-offset-1">
        <small><a href="<?= Url::to(['site/resend-verification']) ?>"><?= Yii::t('site', 'My verification mail did not arrived!') ?></a></small><br/>
        <small><a href="<?= Url::to(['site/pwreset']) ?>"><?= Yii::t('site', 'I forgot my password!') ?></a></small><br/>
    </div>

    <div class="form-group">
        <div class="col-lg-offset-1 col-lg-11">
            <?= Html::submitButton('Login', ['class' => 'btn btn-primary', 'name' => 'login-button']) ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>
</div>
