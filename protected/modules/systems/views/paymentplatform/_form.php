<?php Yii::app()->clientscript->scriptMap['jquery.js'] = false; ?>
<div class="pageContent"> 
    <?php
    $form = $this->beginWidget('ActiveForm', array(
        'id' => 'paymentplatForm',
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
        	<?php echo $form->labelEx($model, 'payment_platform_code'); ?>                 
            <?php echo $form->textField($model, 'payment_platform_code', array('size'=>20)); ?>
            <?php echo $form->error($model, 'payment_platform_code'); ?>    
        </div>  
        <div class="row">
            <?php echo $form->labelEx($model, 'payment_platform_name'); ?>
            <?php echo $form->textField($model, 'payment_platform_name', array('size' => 40)); ?>
            <?php echo $form->error($model, 'payment_platform_name'); ?>
        </div>
        <div class="row">
            <?php echo $form->labelEx($model, 'platform_type'); ?>
            <?php echo $form->dropDownList($model, 'platform_type', UebModel::model('PaymentPlatform')->getPlatformType(),array('empty'=>Yii::t('system','Please Select'))); ?>
            <?php echo $form->error($model, 'platform_type'); ?>
        </div>
        <div class="row">
            <?php echo $form->labelEx($model, 'sort'); ?>
            <?php echo $form->textField($model, 'sort', array('size' => 6)); ?>
            <?php echo $form->error($model, 'sort'); ?>
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


