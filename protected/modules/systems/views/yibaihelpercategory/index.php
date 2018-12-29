<?php
/* @var $this YibaihelpercategoryController */
/* @var $dataProvider CActiveDataProvider */

$this->breadcrumbs=array(
	'Yibaihelper Categories',
);

$this->menu=array(
	array('label'=>'Create YibaihelperCategory', 'url'=>array('create')),
	array('label'=>'Manage YibaihelperCategory', 'url'=>array('admin')),
);
?>

<h1>Yibaihelper Categories</h1>

<?php $this->widget('zii.widgets.CListView', array(
	'dataProvider'=>$dataProvider,
	'itemView'=>'_view',
)); ?>
