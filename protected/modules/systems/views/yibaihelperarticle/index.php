<?php
/* @var $this YibaihelperarticleController */
/* @var $dataProvider CActiveDataProvider */

$this->breadcrumbs=array(
	'Yibaihelper Articles',
);

$this->menu=array(
	array('label'=>'Create YibaihelperArticle', 'url'=>array('create')),
	array('label'=>'Manage YibaihelperArticle', 'url'=>array('admin')),
);
?>

<h1>Yibaihelper Articles</h1>

<?php $this->widget('zii.widgets.CListView', array(
	'dataProvider'=>$dataProvider,
	'itemView'=>'_view',
)); ?>
