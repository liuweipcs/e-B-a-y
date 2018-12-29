<?php Yii::app()->clientscript->scriptMap['jquery.js'] = false; ?>
<div class="panelBar">
    <ul class="toolBar">
        <li>
            <a class="add" href="javascript::void(0);" onclick="$.refreshConfigCache('global');" >
                <span><?php echo Yii::t('system', 'Refresh Cache')?></span>
            </a>
        </li>          
    </ul>
</div>
<h2 class="contentTitle"><?php echo Yii::t('system', 'Global Setting');?></h2>
<div class="pageContent"> 
    <?php
    $form = $this->beginWidget('CActiveForm', array(
        'id' => 'menuForm',
        'enableAjaxValidation' => false,  
        'enableClientValidation' => true,
        'clientOptions' => array(
            'validateOnSubmit' => true,
            'validateOnChange' => true,
            'validateOnType' => false,
            'afterValidate'=>'js:afterValidate',
        ),
        'action' => Yii::app()->createUrl($this->route), 
        'htmlOptions' => array(        
            'class' => 'pageForm',         
           )
    ));
    ?>   
    <div class="pageFormContent" layoutH="125"> 
        <div class="row">
            <?php echo $form->labelEx($model, 'timezone'); ?>
            <?php echo $form->textField($model, 'timezone', array( 'size' => 20)); ?>
            <?php echo $form->error($model, 'timezone'); ?>          
        </div>   
        <div class="row">
            <?php echo $form->labelEx($model, 'profileTimingLimit'); ?>
            <?php echo $form->textField($model, 'profileTimingLimit', array( 'size' => 5)); ?>
            <?php echo $form->error($model, 'profileTimingLimit'); ?>          
        </div>
        <div class="row">
            <?php echo $form->labelEx($model, 'companyName'); ?>
            <?php echo $form->textField($model, 'companyName', array( 'size' => 40)); ?>
            <?php echo $form->error($model, 'companyName'); ?>          
        </div>
        <div class="row">
            <?php echo $form->labelEx($model, 'companyAddress'); ?>
            <?php echo $form->textField($model, 'companyAddress', array( 'size' => 55)); ?>
            <?php echo $form->error($model, 'companyAddress'); ?>          
        </div>
        <div class="row">
            <?php echo $form->labelEx($model, 'companyTel'); ?>
            <?php echo $form->textField($model, 'companyTel', array( 'size' => 40)); ?>
            <?php echo $form->error($model, 'companyTel'); ?>          
        </div>
        <div class="row">
            <?php echo $form->labelEx($model, 'companyFax'); ?>
            <?php echo $form->textField($model, 'companyFax', array( 'size' => 40)); ?>
            <?php echo $form->error($model, 'companyFax'); ?>          
        </div>
    </div>
    <div class="formBar">
        <ul>              
            <li>
                <div class="buttonActive">
                    <div class="buttonContent">                                                                     
                        <button type="submit"><?php echo Yii::t('system', 'Save')?></button>                     
                    </div>
                </div>
            </li>
            <li>
                <div class="button"><div class="buttonContent"><button type="reset"><?php echo Yii::t('system', 'Reset')?></button></div></div>
            </li>
        </ul>
    </div>
    <?php $this->endWidget(); ?>
</div>


