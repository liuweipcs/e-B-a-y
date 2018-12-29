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
        	<?php echo $form->labelEx($model, 'account'); ?>                 
            <?php echo $form->textField($model, 'account', array('style'=>'width:450px;')); ?>
            <?php echo $form->error($model, 'account'); ?>    
        </div>  
        <div class="row">
            <?php echo $form->labelEx($model, 'short_name'); ?>
            <?php echo $form->textField($model, 'short_name', array('style'=>'width:450px;')); ?>
            <?php echo $form->error($model, 'short_name'); ?>
        </div>
        <div class="row">
            <?php echo $form->labelEx($model, 'member_id'); ?>
            <?php echo $form->textField($model, 'member_id', array('style'=>'width:450px;')); ?>
            <?php echo $form->error($model, 'member_id'); ?>
        </div>

		<div class="row">
            <?php echo $form->labelEx($model, 'app_key'); ?>
            <?php echo $form->textField($model, 'app_key', array('style'=>'width:450px;')); ?>
            <?php echo $form->error($model, 'app_key'); ?>
        </div>
        <div class="row">
            <?php echo $form->labelEx($model, 'secret_key'); ?>
            <?php echo $form->textField($model, 'secret_key', array('style'=>'width:450px;')); ?>
            <?php echo $form->error($model, 'secret_key'); ?>
        </div>
        <div class="row">
            <?php echo $form->labelEx($model, 'email'); ?>
            <?php echo $form->textField($model, 'email', array('style'=>'width:450px;')); ?>
            <?php echo $form->error($model, 'email'); ?>
        </div>
        <div class="row">
            <?php echo $form->labelEx($model, 'access_token'); ?>
            <?php echo $form->textArea($model, 'access_token', array('style'=>'width:450px;height:60px;')); ?>
            <?php echo $form->error($model, 'access_token'); ?>
        </div>
        <div class="row">
            <?php echo $form->labelEx($model, 'refresh_token'); ?>
            <?php echo $form->textArea($model, 'refresh_token', array('style'=>'width:450px;height:60px;')); ?>
            <?php echo $form->error($model, 'refresh_token'); ?>
        </div>

        <div class="row">
            <?php echo $form->labelEx($model, 'redirect_uri'); ?>
            <?php echo $form->textArea($model, 'redirect_uri', array('style'=>'width:450px;height:60px;','value'=>"http://".$_SERVER['SERVER_NAME'].'/systems/alibaba/getcode/account/'.$model->id)); ?>
            <?php echo $form->error($model, 'redirect_uri'); ?>
        </div>

        <div class="row">
            <?php echo $form->labelEx($model, 'status'); ?>
            <?php echo $form->dropDownList($model, 'status', UebModel::model('AlibabaAccount')->getAlibabaAccountStatus(), array('empty'=>Yii::t('system','Please Select'))); ?>
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


