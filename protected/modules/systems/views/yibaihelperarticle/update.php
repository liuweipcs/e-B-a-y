<?php
/* @var $this YibaihelperarticleController */
/* @var $model YibaihelperArticle */

$this->breadcrumbs=array(
	'Yibaihelper Articles'=>array('index'),
	$model->title=>array('view','id'=>$model->id),
	'Update',
);

$this->menu=array(
	array('label'=>'List YibaihelperArticle', 'url'=>array('index')),
	array('label'=>'Create YibaihelperArticle', 'url'=>array('create')),
	array('label'=>'View YibaihelperArticle', 'url'=>array('view', 'id'=>$model->id)),
	array('label'=>'Manage YibaihelperArticle', 'url'=>array('admin')),
);
?>

<h1>Update YibaihelperArticle <?php echo $model->id; ?></h1>

<?php $this->renderPartial('_form', array('model'=>$model)); ?>