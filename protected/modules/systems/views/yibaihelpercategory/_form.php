<?php
/* @var $this YibaihelpercategoryController */
/* @var $model YibaihelperCategory */
/* @var $form CActiveForm */
?>

<div class="form">
    <?php
    $form = $this->beginWidget('ActiveForm',array(
        'id'=>'lazadaProductTaskForm',
        'enableAjaxValidation'=>false,
        'enableClientValidation'=>false,
        'clientOptions' => array(
            'validateOnSubmit' => true,
            'validateOnChange' => true,
            'validateOnType' => false,
            'afterValidate'=>'js:afterValidate',
        ),
        'action'=>Yii::app()->createUrl($this->route,array("id"=>$model->id)),
        'htmlOptions'=>array(
            'class'=>'pageForm',
            'onsubmit'=>'return validateCallback(this,dialogAjaxDone)',
        )
    ));
    ?>

<?php /*$form=$this->beginWidget('CActiveForm', array(
	'id'=>'yibaihelper-category-form',
	// Please note: When you enable ajax validation, make sure the corresponding
	// controller action is handling ajax validation correctly.
	// There is a call to performAjaxValidation() commented in generated controller code.
	// See class documentation of CActiveForm for details on this.
	'enableAjaxValidation'=>false,
)); */?>

	<!--<p class="note">Fields with <span class="required">*</span> are required.</p>-->

	<?php echo $form->errorSummary($model); ?>
	<div class="row">
		<?php echo $form->labelEx($model,'name'); ?>
		<?php echo $form->textField($model,'name',array('size'=>60,'maxlength'=>100)); ?>
		<?php echo $form->error($model,'name'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'short_name'); ?>
		<?php echo $form->textField($model,'short_name',array('size'=>20,'maxlength'=>20)); ?>
		<?php echo $form->error($model,'short_name'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'add_time'); ?>
		<?php echo $form->textField($model,'add_time'); ?>
		<?php echo $form->error($model,'add_time'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'add_user'); ?>
		<?php echo $form->textField($model,'add_user'); ?>
		<?php echo $form->error($model,'add_user'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'is_use'); ?>
		<?php echo $form->textField($model,'is_use'); ?>
		<?php echo $form->error($model,'is_use'); ?>
	</div>

	<div class="row buttons">
		<?php echo CHtml::submitButton($model->isNewRecord ? 'Create' : 'Save'); ?>
	</div>

<?php $this->endWidget(); ?>

</div><!-- form -->
