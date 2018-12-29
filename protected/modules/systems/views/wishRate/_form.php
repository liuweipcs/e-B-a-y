<?php Yii::app()->clientscript->scriptMap['jquery.js'] = false; ?>
<div class="pageContent">
<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'wish-rate-form',
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
)); ?>
<div class="pageFormContent" layoutH="56"> 
	<div class="row">
		<?php echo $form->labelEx($model,'wish_id'); ?>
		<?php echo $form->dropDownList($model,'wish_id',UebModel::model('WishAccount')->getAccountListSelect()); ?>
		<?php echo $form->error($model,'wish_id'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'start_price'); ?>
		<?php echo $form->textField($model,'start_price'); ?>
		<?php echo $form->error($model,'start_price'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'top_price'); ?>
		<?php echo $form->textField($model,'top_price'); ?>
		<?php echo $form->error($model,'top_price'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'basic_rate'); ?>
		<?php echo $form->textField($model,'basic_rate',array('size'=>5,'maxlength'=>5)); ?>
		<?php echo $form->error($model,'basic_rate'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'mini_rate'); ?>
		<?php echo $form->textField($model,'mini_rate',array('size'=>5,'maxlength'=>5)); ?>
		<?php echo $form->error($model,'mini_rate'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'float_rate'); ?>
		<?php echo $form->textField($model,'float_rate',array('size'=>5,'maxlength'=>5)); ?>
		<?php echo $form->error($model,'float_rate'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'ship_fee'); ?>
		<?php echo $form->textField($model,'ship_fee'); ?>
		<?php echo $form->error($model,'ship_fee'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'status'); ?>
		<?php echo $form->dropDownList($model,'status',array('禁用','启用')); ?>
		<?php echo $form->error($model,'status'); ?>
	</div>

<div class="formBar">
<ul>
 <li>
                <div class="buttonActive">
                    <div class="buttonContent">                        
                        <button type="submit"><?php echo Yii::t('system', 'Save') ?></button>                     
                    </div>
                </div>
            </li>
            </ul>
            </div>
<?php $this->endWidget(); ?>
</div>
</div><!-- form -->