<?php
/* @var $this YibaihelpercategoryController */
/* @var $data YibaihelperCategory */
?>

<div class="view">

	<b><?php echo CHtml::encode($data->getAttributeLabel('id')); ?>:</b>
	<?php echo CHtml::link(CHtml::encode($data->id), array('view', 'id'=>$data->id)); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('name')); ?>:</b>
	<?php echo CHtml::encode($data->name); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('short_name')); ?>:</b>
	<?php echo CHtml::encode($data->short_name); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('add_time')); ?>:</b>
	<?php echo CHtml::encode($data->add_time); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('add_user')); ?>:</b>
	<?php echo CHtml::encode($data->add_user); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('is_use')); ?>:</b>
	<?php echo CHtml::encode($data->is_use); ?>
	<br />


</div>