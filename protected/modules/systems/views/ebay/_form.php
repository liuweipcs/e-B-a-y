<?php Yii::app()->clientscript->scriptMap['jquery.js'] = false; ?>
<style>
    #Ebay_platform br {
        display: none;
    }
    #EbayAccountMapGroup_group_id {
        display: inline-block;
        border: 1px solid #B8D0D6;
        margin: 2px 0px;
        padding: 2px 10px 2px 2px;
    }
</style>
<div class="pageContent"> 
    <?php
    $form = $this->beginWidget('ActiveForm', array(
        'id' => 'ebayForm',
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
        	<?php echo $form->labelEx($model, 'user_name'); ?>                 
            <?php echo $form->textField($model, 'user_name', array('size'=>50)); ?>
            <?php echo $form->error($model, 'user_name'); ?>    
        </div>  
        <div class="row">
            <?php echo $form->labelEx($model, 'store_name'); ?>
            <?php echo $form->textField($model, 'store_name', array('size' => 50)); ?>
            <?php echo $form->error($model, 'store_name'); ?>
        </div>
        <div class="row">
            <?php echo $form->labelEx($model, 'image_host'); ?>
            <?php echo $form->textField($model, 'image_host', array('size' => 50)); ?>
            <?php echo $form->error($model, 'image_host'); ?>
        </div>
        <div class="row">
            <?php echo $form->labelEx($model, 'short_name'); ?>
            <?php echo $form->textField($model, 'short_name', array('size' => 50)); ?>
            <?php echo $form->error($model, 'short_name'); ?>
        </div>
        <div class="row">
            <?php echo $form->labelEx($model, 'user_token'); ?>
            <?php echo $form->textArea($model, 'user_token', array('style' => 'width:410px;height:100px;')); ?>
            <?php echo $form->error($model, 'user_token'); ?>
        </div> 
        <div class="row">
            <?php echo $form->labelEx($model, 'user_token_endtime'); ?>
			<?php echo $form->textField($model, 'user_token_statrtime', array( 'size' => 20,'datefmt'=>'yyyy-MM-dd','class'=>'date textInput','value'=>$user_token_statrtime)); ?> <?php echo $form->textField($model, 'user_token_endtime', array( 'size' => 20,'datefmt'=>'yyyy-MM-dd','class'=>'date textInput','value'=>$user_token_endtime)); ?>
            <?php echo $form->error($model, 'user_token_endtime'); ?>
        </div>
        <div class="row">
            <?php echo $form->labelEx($model, 'platform'); ?>
            <?php echo $form->checkBoxList($model, 'platform', array('ebay'=>'ebay国内仓','ebayout'=>'ebay海外仓')); ?>
            <?php echo $form->error($model, 'platform'); ?>
        </div>
        <div class="row">
            <?php echo $form->labelEx($model, 'status'); ?>
            <?php echo $form->dropDownList($model, 'status', UebModel::model('Ebay')->getEbayAccountStatus(),array('empty'=>Yii::t('system','Please Select'))); ?>
            <?php echo $form->error($model, 'status'); ?>
        </div>
        <div class="row">
            <?php echo $form->labelEx($model, 'is_lock'); ?>
            <?php echo $form->dropDownList($model, 'is_lock', UebModel::model('Ebay')->getEbayAccountLock()); ?>
            <?php echo $form->error($model, 'is_lock'); ?>
        </div>
        <div class="row">
            <?php echo $form->labelEx($accountMapGroupModel, 'group_id'); ?>
            <?php echo $form->checkBoxList($accountMapGroupModel, 'group_id', array_column(VHelper::selectAsArray('EbayAccountGroup','id,name','',true,''),'name','id')); ?>
            <?php echo $form->error($accountMapGroupModel, 'group_id'); ?>
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


