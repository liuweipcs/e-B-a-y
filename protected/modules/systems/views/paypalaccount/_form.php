
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
            'afterValidate'=>'js:afterValidate',
        ),
        'action' => Yii::app()->createUrl($this->route,array('id'=>$model->id)),
        'htmlOptions' => array(        
            'class' => 'pageForm',         
        )
    ));
    ?>   
    <div class="pageFormContent" layoutH="56"> 
        <div class="pd5" style="height:150px;">
            <div class="row">
                <?php echo $form->labelEx($model, 'email'); ?>
                <?php echo $form->textField($model, 'email',array('empty'=>Yii::t('system','Email Can not empty'),'style'=>'width:300px')); ?>
                <?php echo $form->error($model, 'email'); ?>
            </div>
            <div class="row">
                <?php echo $form->labelEx($model, 'api_user_name'); ?>
                <?php echo $form->textField($model, 'api_user_name',array('empty'=>Yii::t('system','Api user name Can not empty'),'style'=>'width:300px')); ?>
                <?php echo $form->error($model, 'api_user_name'); ?>
            </div>
            <div class="row">
                <?php echo $form->labelEx($model, 'api_password'); ?>
                <?php echo $form->passwordField($model, 'api_password',array('empty'=>Yii::t('system','Api password Can not empty'),'style'=>'width:300px',)); ?>
                <?php echo $form->error($model, 'api_password'); ?>
            </div>
            <div class="row">
                <?php echo $form->labelEx($model, 'api_signature'); ?>
                <?php echo $form->textField($model, 'api_signature',array('empty'=>Yii::t('system','Api signature Can not empty'),'style'=>'width:300px')); ?>
                <?php echo $form->error($model, 'api_signature'); ?>
            </div>
            <div class="row">
                <?php echo $form->labelEx($model, 'group_id'); ?>
                <?php echo $form->dropDownList($model, 'group_id',$groupArr,array('style'=>'width:150px')); ?>
                <?php echo $form->error($model, 'group_id',array('style'=>'float:right;')); ?> 
            </div> 
            <div class="row">
            <?php echo $form->labelEx($model, 'platform_code'); ?>
            <?php echo $form->dropDownList($model, 'platform_code', UebModel::model('Platform')->getPlatformList(),array('empty'=>Yii::t('system','Please Select'),'style'=>'width:150px')); ?>
            <?php echo $form->error($model, 'platform_code'); ?>
        	</div>
            <div class="row">
                <?php echo $form->labelEx($model, 'amount_start'); ?>
                <?php echo $form->textField($model, 'amount_start', array('style'=>'width:300px')); ?>
                <?php echo $form->error($model, 'amount_start',array('style'=>'float:right;')); ?> 
            </div>
            <div class="row">
                <?php echo $form->labelEx($model, 'amount_end'); ?>
                <?php echo $form->textField($model, 'amount_end', array('style'=>'width:300px')); ?>
                <?php echo $form->error($model, 'amount_end',array('style'=>'float:right;')); ?>
            </div>
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
                <div class="button"><div class="buttonContent"><button type="button" class="close"><?php echo Yii::t('system', 'Cancel')?></button></div></div>
            </li>
        </ul>
    </div>
    <?php $this->endWidget(); ?>
</div>

