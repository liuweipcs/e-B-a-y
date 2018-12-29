<?php
/* @var $this YibaihelperarticleController */
/* @var $model YibaihelperArticle */

$this->breadcrumbs=array(
	'Yibaihelper Articles'=>array('index'),
	$model->title,
);

$this->menu=array(
	array('label'=>'List YibaihelperArticle', 'url'=>array('index')),
	array('label'=>'Create YibaihelperArticle', 'url'=>array('create')),
	array('label'=>'Update YibaihelperArticle', 'url'=>array('update', 'id'=>$model->id)),
	array('label'=>'Delete YibaihelperArticle', 'url'=>'#', 'linkOptions'=>array('submit'=>array('delete','id'=>$model->id),'confirm'=>'Are you sure you want to delete this item?')),
	array('label'=>'Manage YibaihelperArticle', 'url'=>array('admin')),
);
?>

<h1>View YibaihelperArticle <?php echo $model->title; ?></h1>
<?php $this->widget('zii.widgets.CDetailView', array(
	'data'=>$model,
	'attributes'=>array(
		array(
			'name'=> 'category_id',
			'value'=>$model->category->name,
			'htmlOptions' 	=> array('style' => 'width:600px',),
		),
		'title',
		array(
			'name'=> 'content',
			'type' => 'html',
			'htmlOptions' 	=> array('style' => 'width:600px',),
		),
		'add_time',
		array(
			'name'=> 'add_user',
			'value'=>$model->user->user_full_name,
			'htmlOptions' 	=> array('style' => 'width:600px',),
		),
	),
)); ?>
