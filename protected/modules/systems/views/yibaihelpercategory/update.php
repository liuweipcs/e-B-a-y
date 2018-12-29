<?php
/* @var $this YibaihelpercategoryController */
/* @var $model YibaihelperCategory */

$this->breadcrumbs=array(
	'Yibaihelper Categories'=>array('index'),
	$model->name=>array('view','id'=>$model->id),
	'Update',
);

$this->menu=array(
	array('label'=>'List YibaihelperCategory', 'url'=>array('index')),
	array('label'=>'Create YibaihelperCategory', 'url'=>array('create')),
	array('label'=>'View YibaihelperCategory', 'url'=>array('view', 'id'=>$model->id)),
	array('label'=>'Manage YibaihelperCategory', 'url'=>array('admin')),
);
?>

<h1>Update YibaihelperCategory <?php echo $model->id; ?></h1>

<?php $this->renderPartial('_form', array('model'=>$model)); ?>