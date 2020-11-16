<?php

use yii\helpers\Html;


/* @var $this yii\web\View */

$this->title                   = Yii::t('site', 'Contact');
$this->params['breadcrumbs'][] = $this->title;

?>
<h1>Contact</h1>
<p>You can contact me by clicking <?= Html::a('Here', ['site/contact', 'do' => 1], ['class' => 'btn btn-primary']) ?>
    .</p>