<?php
/* @var $this YibaihelperNewController */
/* @var $model YibaihelperNew */

$this->breadcrumbs=array(
	'Yibaihelper New'=>array('index'),
	$model->title=>array('view','id'=>$model->id),
	'Update',
);

$this->menu=array(
	array('label'=>'List YibaihelperNew', 'url'=>array('index')),
	array('label'=>'Create YibaihelperNew', 'url'=>array('create')),
	array('label'=>'View YibaihelperNew', 'url'=>array('view', 'id'=>$model->id)),
	array('label'=>'Manage YibaihelperNew', 'url'=>array('index')),
);
?>

<h1></h1>

<?php $this->renderPartial('_form', array('model'=>$model)); ?>