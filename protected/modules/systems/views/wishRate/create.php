<?php
/* @var $this WishRateController */
/* @var $model WishRate */

$this->breadcrumbs=array(
	'Wish Rates'=>array('index'),
	'Create',
);

$this->menu=array(
	array('label'=>'List WishRate', 'url'=>array('index')),
	array('label'=>'Manage WishRate', 'url'=>array('admin')),
);
?>

<h1>Create WishRate</h1>

<?php $this->renderPartial('_form', array('model'=>$model)); ?>