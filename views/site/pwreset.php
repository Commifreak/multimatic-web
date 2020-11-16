<?php

use app\models\User;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title                   = 'Password reset';
$this->params['breadcrumbs'][] = $this->title;

/* @var $user User */


if (isset($sent)) {
    echo '<div class="alert alert-success">' . Yii::t('site', 'Password reset link sent (but only if your email was valid)!') . '</div>';
}

?>

<h1><?= Yii::t('site', 'You forgot your password?') ?></h1>

<?php
if (!$user):
    ?>
    <p><?= Yii::t('site', 'Dont you use a password manager who takes care of your password..?!') ?></p>
    <p><?= Yii::t('site', 'Anyway, I will help you! Please enter your registered email in the field below and I will send you a reset link.') ?></p>
    <div class="row">
        <div class="col-md-6">
            <form method="post">
                <?= yii\helpers\Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>
                <input type="email" name="email" id="email" placeholder="<?= Yii::t('site', 'Your email') ?>"
                       class="form-control"/>
        </div>
    </div>
    <br/><br/>
    <div class="row">
        <div class="col-md-6">
            <input type="submit" class="btn btn-primary" value="<?= Yii::t('site', 'Send') ?>"/>
            </form>
        </div>
    </div>
<?php else: ?>
    <p><?= Yii::t('site', 'Hey, you are back :)') ?></p>
    <p><?= Yii::t('site', 'Please type in your new password.') ?></p>

    <?php
    $form                  = ActiveForm::begin([
        'id'                   => 'pwResetForm',
        'enableAjaxValidation' => true
    ]);
    $user->password        = '';
    $user->password_repeat = '';
    $user->scenario        = 'pwreset';
    echo $form->field($user, 'password')->passwordInput();
    echo $form->field($user, 'password_repeat')->passwordInput();

    echo Html::submitButton(Yii::t('site', 'Submit'), ['class' => 'btn btn-primary']);

    ActiveForm::end();
    ?>

<?php endif; ?>
