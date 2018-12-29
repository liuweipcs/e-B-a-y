<?php Yii::app()->clientscript->scriptMap['jquery.js'] = false; ?>
<div class="pageContent"> 
    <?php
    $form = $this->beginWidget('ActiveForm', array(
        'id' => 'wishForm',
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
            <?php echo $form->labelEx($model, 'email'); ?>
            <?php echo $form->textField($model, 'email', array('style'=>'width:450px;')); ?>
            <?php echo $form->error($model, 'email'); ?>
        </div>
        <div class="row">
            <?php echo $form->labelEx($model, 'password'); ?>
            <?php echo $form->textField($model, 'password', array('style'=>'width:450px;')); ?>
            <?php echo $form->error($model, 'password'); ?>
        </div>
        <div class="row">
            <?php echo $form->labelEx($model, 'access_token'); ?>
            <?php echo $form->textArea($model, 'access_token', array('style'=>'width:450px;height:60px;')); ?>
            <?php echo $form->error($model, 'access_token'); ?>
        </div>


        <div class="row">
            <?php echo $form->labelEx($model, 'status'); ?>
            <?php echo $form->dropDownList($model, 'status', UebModel::model('AliexpressAccount')->getAliexpressAccountStatus(), array('empty'=>Yii::t('system','Please Select'))); ?>
            <?php echo $form->error($model, 'status'); ?>
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


