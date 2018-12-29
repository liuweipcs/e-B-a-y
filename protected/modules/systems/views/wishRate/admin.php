<?php
/* @var $this WishRateController */
/* @var $model WishRate */

$this->breadcrumbs=array(
	'Wish Rates'=>array('index'),
	'Manage',
);

$this->menu=array(
	array('label'=>'List WishRate', 'url'=>array('index')),
	array('label'=>'Create WishRate', 'url'=>array('create')),
);

Yii::app()->clientScript->registerScript('search', "
$('.search-button').click(function(){
	$('.search-form').toggle();
	return false;
});
$('.search-form form').submit(function(){
	$('#wish-rate-grid').yiiGridView('update', {
		data: $(this).serialize()
	});
	return false;
});
");
?>

<h1>Manage Wish Rates</h1>

<p>
You may optionally enter a comparison operator (<b>&lt;</b>, <b>&lt;=</b>, <b>&gt;</b>, <b>&gt;=</b>, <b>&lt;&gt;</b>
or <b>=</b>) at the beginning of each of your search values to specify how the comparison should be done.
</p>

<?php echo CHtml::link('Advanced Search','#',array('class'=>'search-button')); ?>
<div class="search-form" style="display:none">
<?php $this->renderPartial('_search',array(
	'model'=>$model,
)); ?>
</div><!-- search-form -->

<?php $this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'wish-rate-grid',
	'dataProvider'=>$model->search(),
	'filter'=>$model,
	'columns'=>array(
		'id',
		'wish_id',
		'start_price',
		'top_price',
		'basic_rate',
		'mini_rate',
		/*
		'float_rate',
		'ship_fee',
		'status',
		*/
		array(
			'class'=>'CButtonColumn',
		),
	),
)); ?>
