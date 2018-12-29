<?php
/* @var $this YibaihelperarticleController */
/* @var $model YibaihelperArticle */

$this->breadcrumbs=array(
	'Yibaihelper'=>array('index'),
	'Create',
);

$this->menu=array(
	// array('label'=>'List YibaihelperNew', 'url'=>array('index')),
	// array('label'=>'Manage YibaihelperNew', 'url'=>array('index')),
);
?>

<h1></h1>

<?php $this->renderPartial('_form', array('model'=>$model)); ?>