<?php
/* @var $this YibaihelpercategoryController */
/* @var $model YibaihelperCategory */

$this->breadcrumbs=array(
	'Yibaihelper Categories'=>array('index'),
	$model->name,
);

$this->menu=array(
	array('label'=>'List YibaihelperCategory', 'url'=>array('index')),
	array('label'=>'Create YibaihelperCategory', 'url'=>array('create')),
	array('label'=>'Update YibaihelperCategory', 'url'=>array('update', 'id'=>$model->id)),
	array('label'=>'Delete YibaihelperCategory', 'url'=>'#', 'linkOptions'=>array('submit'=>array('delete','id'=>$model->id),'confirm'=>'Are you sure you want to delete this item?')),
	array('label'=>'Manage YibaihelperCategory', 'url'=>array('admin')),
);
?>

<h1>View YibaihelperCategory #<?php echo $model->id; ?></h1>

<?php $this->widget('zii.widgets.CDetailView', array(
	'data'=>$model,
	'attributes'=>array(
		'id',
		'name',
		'short_name',
		'add_time',
		'add_user',
		'is_use',
	),
)); ?>
