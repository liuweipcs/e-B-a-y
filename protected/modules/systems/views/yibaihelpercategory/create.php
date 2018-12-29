<?php
/* @var $this YibaihelpercategoryController */
/* @var $model YibaihelperCategory */

$this->breadcrumbs=array(
	'Yibaihelper Categories'=>array('index'),
	'Create',
);

$this->menu=array(
	array('label'=>'List YibaihelperCategory', 'url'=>array('index')),
	array('label'=>'Manage YibaihelperCategory', 'url'=>array('admin')),
);
?>

<h1>Create YibaihelperCategory</h1>

<?php $this->renderPartial('_form', array('model'=>$model)); ?>