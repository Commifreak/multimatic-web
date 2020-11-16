<?php

$this->title                   = 'Resend Verification Mail';
$this->params['breadcrumbs'][] = $this->title;


if (isset($sent)) {
    echo '<div class="alert alert-success">' . Yii::t('site', 'Verification sent (but only if your email was valid)!') . '</div>';
}

?>

<h1><?= Yii::t('site', 'Your verification mail did not arrived?') ?></h1>
<p><?= Yii::t('site', 'I believe you, that sucks, because you were already looking forward to access mmWeb, right?') ?></p>
<p><?= Yii::t('site', 'If you enter your e-mail address right in the field below, I will try my best to get it right by sending it again! 😘') ?></p>
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