<?php
/* @var $this WishRateController */
/* @var $data WishRate */
?>

<div class="view">

	<b><?php echo CHtml::encode($data->getAttributeLabel('id')); ?>:</b>
	<?php echo CHtml::link(CHtml::encode($data->id), array('view', 'id'=>$data->id)); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('wish_id')); ?>:</b>
	<?php echo CHtml::encode($data->wish_id); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('start_price')); ?>:</b>
	<?php echo CHtml::encode($data->start_price); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('top_price')); ?>:</b>
	<?php echo CHtml::encode($data->top_price); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('basic_rate')); ?>:</b>
	<?php echo CHtml::encode($data->basic_rate); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('mini_rate')); ?>:</b>
	<?php echo CHtml::encode($data->mini_rate); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('float_rate')); ?>:</b>
	<?php echo CHtml::encode($data->float_rate); ?>
	<br />

	<?php /*
	<b><?php echo CHtml::encode($data->getAttributeLabel('ship_fee')); ?>:</b>
	<?php echo CHtml::encode($data->ship_fee); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('status')); ?>:</b>
	<?php echo CHtml::encode($data->status); ?>
	<br />

	*/ ?>

</div>