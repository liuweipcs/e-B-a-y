<?php Yii::app()->clientscript->scriptMap['jquery.js'] = false; ?>
<div class="pageContent"> 
    <?php
    $form = $this->beginWidget('ActiveForm', array(
        'id' => 'walmartForm',
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
        	<?php echo $form->labelEx($model, 'account_name'); ?>
            <?php echo $form->textField($model, 'account_name', array('style'=>'width:520px;')); ?>
            <?php echo $form->error($model, 'account_name'); ?>
        </div>
        <div class="row">
            <?php echo $form->labelEx($model, 'short_name'); ?>
            <?php echo $form->textField($model, 'short_name', array('style'=>'width:520px;')); ?>
            <?php echo $form->error($model, 'short_name'); ?>
        </div>
		<div class="row">
            <?php echo $form->labelEx($model, 'consumer_id'); ?>
            <?php echo $form->textField($model, 'consumer_id', array('style'=>'width:520px;')); ?>
            <?php echo $form->error($model, 'consumer_id'); ?>
        </div>
		<div class="row">
            <?php echo $form->labelEx($model, 'private_key'); ?>
            <?php echo $form->textField($model, 'private_key', array('style'=>'width:520px;')); ?>
            <?php echo $form->error($model, 'private_key'); ?>
        </div>
        <div class="row">
            <?php echo $form->labelEx($model, 'channel_type'); ?>
            <?php echo $form->textArea($model, 'channel_type', array('style' => 'width:520px;height:40px;')); ?>
            <?php echo $form->error($model, 'channel_type'); ?>
        </div>
        <div class="row">
            <?php echo $form->labelEx($model, '配送中心(DSV填此项)'); ?>
            <?php echo $form->textField($model, 'ship_node', array('style'=>'width:520px;')); ?>
            <?php echo $form->error($model, 'ship_node'); ?>
        </div>
        <div class="row">
            <?php echo $form->labelEx($model, Yii::t('system', '分组')); ?>
            <?php echo $form->dropDownList($model, 'group_id', UebModel::model('WalmartStoreGroup')->getList(), array('options'=>array($model->group_id=>array('selected'=>'selected')),'empty'=>Yii::t('system','Please Select'))); ?>
            <?php echo $form->error($model, 'group_id'); ?>
        </div>

        <div class="row">
            <?php echo $form->labelEx($model, 'status'); ?>
            <?php echo $form->dropDownList($model, 'status', UebModel::model('WalmartAccount')->getWalmartAccountStatus(),array('empty'=>Yii::t('system','Please Select'))); ?>
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

