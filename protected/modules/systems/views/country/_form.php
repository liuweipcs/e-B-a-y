<?php Yii::app()->clientscript->scriptMap['jquery.js'] = false; ?>
<div class="pageContent"> 
    <?php
    $form = $this->beginWidget('ActiveForm', array(
        'id' => 'menuForm',
        'enableAjaxValidation' => false,
        'enableClientValidation' => true,
    	'focus' => array($model, 'cn_name'),
        'clientOptions' => array(
            'validateOnSubmit' => true,
            'validateOnChange' => true,
            'validateOnType' => false,
            'afterValidate' => 'js:afterValidate',
        ),
        'action' => Yii::app()->createUrl($this->route) . '/id/' . $model->id,
        'htmlOptions' => array(
            'class' => 'pageForm',
        )
    ));
    ?>   
    <div class="pageFormContent" layoutH="56">  
        <div class="row">
            <?php echo $form->labelEx($model, 'cn_name'); ?>
            <?php echo $form->textField($model, 'cn_name', array( 'size' => 38)); ?>
            <?php echo $form->error($model, 'cn_name'); ?>          
        </div>           
        <div class="row">
            <?php echo $form->labelEx($model, 'en_name'); ?>
            <?php echo $form->textField($model, 'en_name', array( 'size' => 38)); ?>
            <?php echo $form->error($model, 'en_name'); ?>          
        </div>
        <div class="row">
            <?php echo $form->labelEx($model, 'en_abbr'); ?>
            <?php echo $form->textField($model, 'en_abbr', array( 'size' => 38)); ?>
            <?php echo $form->error($model, 'en_abbr'); ?>
        </div>       
        <div class="row">
            <?php echo $form->labelEx($model, 'continent'); ?>
            <?php echo $form->textField($model, 'continent', array( 'size' => 38)); ?>
            <?php echo $form->error($model, 'continent'); ?>
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


