<?php
/* @var $this WishRateController */
/* @var $model WishRate */

$this->breadcrumbs=array(
	'Wish Rates'=>array('index'),
	$model->id,
);

$this->menu=array(
	array('label'=>'List WishRate', 'url'=>array('index')),
	array('label'=>'Create WishRate', 'url'=>array('create')),
	array('label'=>'Update WishRate', 'url'=>array('update', 'id'=>$model->id)),
	array('label'=>'Delete WishRate', 'url'=>'#', 'linkOptions'=>array('submit'=>array('delete','id'=>$model->id),'confirm'=>'Are you sure you want to delete this item?')),
	array('label'=>'Manage WishRate', 'url'=>array('admin')),
);
?>

<h1>View WishRate #<?php echo $model->id; ?></h1>

<?php $this->widget('zii.widgets.CDetailView', array(
	'data'=>$model,
	'attributes'=>array(
		'id',
		'wish_id',
		'start_price',
		'top_price',
		'basic_rate',
		'mini_rate',
		'float_rate',
		'ship_fee',
		'status',
	),
)); ?>
