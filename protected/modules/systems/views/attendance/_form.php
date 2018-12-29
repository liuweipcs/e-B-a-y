<?php Yii::app()->clientscript->scriptMap['jquery.js'] = false; ?>
<div class="pageContent"> 
    <?php
    $form = $this->beginWidget('ActiveForm', array(
        'id' => 'attendanceForm',
        'enableAjaxValidation' => false,
        'enableClientValidation' => true,
    	'clientOptions' => array(
            'validateOnSubmit' => true,
            'validateOnChange' => true,
            'validateOnType' => false,
            'afterValidate' => 'js:afterValidate',
        ),
        'action' => Yii::app()->createUrl($this->route,array('id'=>$model->rule_id)),
        'htmlOptions' => array(
            'class' => 'pageForm',
        )
    ));
    ?>   
    <div class="pageFormContent" layoutH="56">

        <div class="row">
            <?php echo $form->labelEx($model, 'type'); ?>
            <?php echo $form->dropDownList($model, 'type', UebModel::model('AttendanceRule')->getAttendanceRule(), array('empty'=>Yii::t('system','Please Select'))); ?>
            <?php echo $form->error($model, 'type'); ?>
        </div>
        <div class="row">
            <?php echo $form->labelEx($model, 'status'); ?>
            <?php echo $form->dropDownList($model, 'status', UebModel::model('AttendanceRule')->getAttendanceRuleStatus(), array('empty'=>Yii::t('system','Please Select'))); ?>
            <?php echo $form->error($model, 'status'); ?>
        </div>
		<div class="row">
            <?php echo $form->labelEx($model, 'check_time'); ?>
            <?php echo $form->textField($model, 'check_time', array('style'=>'width:450px;','placeholder'=>"例如:9:00")); ?>
            <?php echo $form->error($model, 'check_time'); ?>
        </div>
        <div class="row">
            <?php echo $form->labelEx($model, 'checkout_time'); ?>
            <?php echo $form->textField($model, 'checkout_time', array('style'=>'width:450px;', 'placeholder'=>"例如:18:00")); ?>
            <?php echo $form->error($model, 'checkout_time'); ?>
        </div>


        <div class="row">
            <?php echo $form->labelEx($model, 'rule_content'); ?>
            <?php echo $form->textArea($model, 'rule_content' ,array('cols'=>72,'rows'=>10)); ?>
            <?php echo $form->error($model, 'rule_content'); ?>
        </div>


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
            <li>
                <div class="button"><div class="buttonContent"><button type="button" class="close"><?php echo Yii::t('system', 'Cancel') ?></button></div></div>
            </li>
        </ul>
    </div>
    <?php $this->endWidget(); ?>
</div>


