<?php 
$baseUrl = Yii::app()->request->baseUrl;
Yii::app()->clientScript->registerCssFile($baseUrl.'/themes/default/style.css', 'screen');
Yii::app()->clientScript->registerCssFile($baseUrl.'/css/pda.css', 'screen');
?>
