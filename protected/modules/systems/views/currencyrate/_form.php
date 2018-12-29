<?php Yii::app()->clientscript->scriptMap['jquery.js'] = false; ?>
<div class="pageContent"> 
    <?php
    $form = $this->beginWidget('ActiveForm', array(
        'id' => 'menuForm',
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
        	<?php echo $form->labelEx($model, 'from_currency_code'); ?>                 
            <?php echo $form->dropDownList($model, 'from_currency_code', UebModel::model('Currency')->getCurrencyList()); ?>
            <?php echo $form->error($model, 'from_currency_code'); ?>    
        </div>  
        <div class="row">
        	<?php echo $form->labelEx($model, 'to_currency_code'); ?>                 
            <?php echo $form->dropDownList($model, 'to_currency_code', UebModel::model('Currency')->getCurrencyList()); ?>
            <?php echo $form->error($model, 'to_currency_code'); ?>
        </div>
        <div class="row">
            <?php echo $form->labelEx($model, 'rate'); ?>
            <?php echo $form->textField($model, 'rate', array('size' => 20)); ?>
            <?php echo $form->error($model, 'rate'); ?>
        </div>
        <div class="row">
            <?php echo $form->labelEx($model, 'type'); ?>                 
            <?php echo $form->dropDownList($model, 'type', UebModel::model('CurrencyRate')->rateTypeList()); ?>
            <?php echo $form->error($model, 'type'); ?>
        </div>
        <div class="row">
            <?php echo $form->labelEx($model, 'note'); ?>
            <?php echo $form->textArea($model, 'note', array('style' => 'width:350px;height:80px;')); ?>
            <?php echo $form->error($model, 'note'); ?>
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


