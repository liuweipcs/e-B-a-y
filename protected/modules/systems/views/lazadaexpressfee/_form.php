<?php Yii::app()->clientscript->scriptMap['jquery.js'] = false; ?>
<div class="pageContent"> 
    <?php
    $form = $this->beginWidget('ActiveForm', array(
        'id' => 'lazadaexpressfeeForm',
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
		<?php echo $form->labelEx($model,'weight'); ?>
		<?php echo $form->textField($model,'weight'); ?>
		<?php echo $form->error($model,'weight'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'my_price'); ?>
		<?php echo $form->textField($model,'my_price',array('size'=>10,'maxlength'=>10)); ?>
		<?php echo $form->error($model,'my_price'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'ph_price'); ?>
		<?php echo $form->textField($model,'ph_price',array('size'=>10,'maxlength'=>10)); ?>
		<?php echo $form->error($model,'ph_price'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'th_price'); ?>
		<?php echo $form->textField($model,'th_price',array('size'=>10,'maxlength'=>10)); ?>
		<?php echo $form->error($model,'th_price'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'id_price'); ?>
		<?php echo $form->textField($model,'id_price',array('size'=>10,'maxlength'=>10)); ?>
		<?php echo $form->error($model,'id_price'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'sg_price'); ?>
		<?php echo $form->textField($model,'sg_price',array('size'=>10,'maxlength'=>10)); ?>
		<?php echo $form->error($model,'sg_price'); ?>
	</div>
	<div class="row">
		<?php echo $form->labelEx($model,'vn_price'); ?>
		<?php echo $form->textField($model,'vn_price',array('size'=>10,'maxlength'=>10)); ?>
		<?php echo $form->error($model,'vn_price'); ?>
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