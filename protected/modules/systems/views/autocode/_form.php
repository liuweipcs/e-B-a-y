<?php Yii::app()->clientscript->scriptMap['jquery.js'] = false; ?>
<div class="pageContent"> 
    <?php
    $form = $this->beginWidget('ActiveForm', array(
        'id' => 'menuForm',
        'enableAjaxValidation' => false,
        'enableClientValidation' => true,
    	'focus' => array($model, 'code_type'),
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
            <?php echo $form->labelEx($model, 'code_type'); ?>                 
            <?php echo $form->textField($model, 'code_type', array('size' => 38)); ?>
            <?php echo $form->error($model, 'code_type'); ?>          
        </div>  
        <div class="row">
            <?php echo $form->labelEx($model, 'code_prefix'); ?>
            <?php echo $form->textField($model, 'code_prefix', array('size' => 38)); ?>
            <?php echo $form->error($model, 'codee_prefix'); ?>          
        </div>
        <div class="row">
            <?php echo $form->labelEx($model, 'code_suffix'); ?>
            <?php echo $form->textField($model, 'code_suffix', array('size' => 38)); ?>
            <?php echo $form->error($model, 'code_suffix'); ?>
        </div>
        <div class="row">
            <?php echo $form->labelEx($model, 'code_format'); ?>
            <?php echo $form->textField($model, 'code_format', array('size' => 38)); ?>
            <?php echo $form->error($model, 'code_format'); ?>
        </div>
        <div class="row">
            <?php echo $form->labelEx($model, 'code_min_num'); ?>
            <?php echo $form->textField($model, 'code_min_num', array('size' => 38,'inc_sub_size' => 1)); ?>
            <?php echo $form->error($model, 'code_min_num'); ?>
        </div>
        <div class="row">
            <?php echo $form->labelEx($model, 'code_max_num'); ?>
            <?php echo $form->textField($model, 'code_max_num', array('size' => 38,'inc_sub_size' => 1)); ?>
            <?php echo $form->error($model, 'code_max_num'); ?>
        </div>        
        <div class="row">
            <?php echo $form->labelEx($model, 'code_fix_length'); ?>
            <?php echo $form->textField($model, 'code_fix_length', array('size' => 38,'inc_sub_size' => 1)); ?>
            <?php echo $form->error($model, 'code_fix_length'); ?>
        </div>
        <div class="row">
            <?php echo $form->labelEx($model, 'code_increate_type'); ?>                 
            <?php echo $form->dropDownList($model, 'code_increate_type', VHelper::getIncreateTypeConfig()); ?>
            <?php echo $form->error($model, 'code_increate_type'); ?>          
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


