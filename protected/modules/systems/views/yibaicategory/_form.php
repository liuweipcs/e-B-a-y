<?php Yii::app()->clientscript->scriptMap['jquery.js'] = false; ?>
<div class="pageContent"> 
    <?php
    $form = $this->beginWidget('ActiveForm', array(
        'id' => 'yibaicategoryForm',
        'enableAjaxValidation' => false,
        'enableClientValidation' => true,
    	'clientOptions' => array(
            'validateOnSubmit' => true,
            'validateOnChange' => true,
            'validateOnType' => false,
            'afterValidate' => 'js:afterValidate',
        ),
        'action' => Yii::app()->createUrl($this->route,array('id'=>$model->id)),
        'htmlOptions' => array(
            'class' => 'pageForm',
        )
    ));
    ?>   
    <div class="pageFormContent" layoutH="56">  
    <div class="row">
		<?php echo $form->labelEx($model,'name'); ?>
		<?php echo $form->textField($model,'name'); ?>
		<?php echo $form->error($model,'name'); ?>
	</div>

	<div class="row">
		<?php echo CHtml::label('简称'); ?>
		<?php echo $form->textField($model,'short_name'); ?>
		<?php echo $form->error($model,'short_name'); ?>
	</div>

	<div class="row">
		<?php echo CHtml::label('父分类');?>
		<?php echo $form->dropDownList($model,'parent_id',array('0'=>'请选择')+(UebModel::model('HelperCategory')->getTopCategory())); ?>
		<?php echo $form->error($model,'parent_id'); ?>
	</div>
	<div class="row">
		<?php echo $form->labelEx($model,'department_id'); ?>
		<?php echo $form->dropDownList($model,'department_id',array('0'=>'请选择')+(UebModel::model('Department')->queryPairs('id,department_name',array('department_status'=>1,'department_level'=>1)))); ?>
		<?php echo $form->error($model,'department_id'); ?>
	</div>
	<div class="row">
		<?php echo CHtml::label('是否开放'); ?>
		<?php echo $form->dropDownList($model,'is_open',array(0=>'开放',1=>'不开放')); ?>
		<?php echo $form->error($model,'is_open'); ?>
	</div>
	<div class="row buttons">
		 <div class="buttonActive">
                    <div class="buttonContent">                        
                        <button type="submit"><?php echo Yii::t('system', 'Save') ?></button>                     
                    </div>
          </div>
	</div>
    </div>
    <?php $this->endWidget(); ?>
</div>