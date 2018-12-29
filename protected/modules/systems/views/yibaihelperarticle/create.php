<?php
/* @var $this YibaihelperarticleController */
/* @var $model YibaihelperArticle */

$this->breadcrumbs=array(
	'Yibaihelper Articles'=>array('index'),
	'Create',
);

$this->menu=array(
	array('label'=>'List YibaihelperArticle', 'url'=>array('index')),
	array('label'=>'Manage YibaihelperArticle', 'url'=>array('admin')),
);
?>

<h1>Create YibaihelperArticle</h1>

<?php $this->renderPartial('_form', array('model'=>$model)); ?>
