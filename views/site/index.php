<?php

/* @var $this yii\web\View */

$this->title = Yii::t('site', 'Welcome!');

use yii\helpers\Url;

?>
<div class="site-index">

    <div class="container">
        <div class="jumbotron">
            <h1><?= Yii::t('site', 'Welcome to Multimatic-Web!') ?></h1>
            <p><?= Yii::t('site', 'With this online tool, you can simply collect historical data from your Vaillant HVAC!<sup>*</sup>') ?></p>
            <small class="text-muted"><sup>*</sup>Requires VR900/920</small>
            <br/><br/>
            <a href="<?= Url::to(['site/register']) ?>"
               class="btn btn-lg btn-primary"><?= Yii::t('site', 'Register') ?></a><br/><br/><br/>
            <small class="text-muted"><?= Yii::t('site', 'Already registered? Login in the right corner!') ?></small>
        </div>
    </div>
</div>