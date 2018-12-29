<?php
/* @var $this WishRateController */
/* @var $model WishRate */
/* @var $form CActiveForm */
?>

<div class="wide form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'action'=>Yii::app()->createUrl($this->route),
	'method'=>'get',
)); ?>

	<div class="row">
		<?php echo $form->label($model,'id'); ?>
		<?php echo $form->textField($model,'id'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'wish_id'); ?>
		<?php echo $form->textField($model,'wish_id'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'start_price'); ?>
		<?php echo $form->textField($model,'start_price'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'top_price'); ?>
		<?php echo $form->textField($model,'top_price'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'basic_rate'); ?>
		<?php echo $form->textField($model,'basic_rate',array('size'=>5,'maxlength'=>5)); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'mini_rate'); ?>
		<?php echo $form->textField($model,'mini_rate',array('size'=>5,'maxlength'=>5)); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'float_rate'); ?>
		<?php echo $form->textField($model,'float_rate',array('size'=>5,'maxlength'=>5)); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'ship_fee'); ?>
		<?php echo $form->textField($model,'ship_fee'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'status'); ?>
		<?php echo $form->textField($model,'status'); ?>
	</div>

	<div class="row buttons">
		<?php echo CHtml::submitButton('Search'); ?>
	</div>

<?php $this->endWidget(); ?>

</div><!-- search-form -->