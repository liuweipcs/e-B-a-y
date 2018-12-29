<?php Yii::app()->clientscript->scriptMap['jquery.js'] = false; ?>
<div class="pageContent">
    <?php
    $form = $this->beginWidget('ActiveForm', array(
        'id' => 'menuForm',
        'enableAjaxValidation' => false,
        'enableClientValidation' => true,
        //'focus' => array($model, 'cn_name'),
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
            <?php echo $form->label($model, 'en_name'); ?>
            <?php echo $form->textField($model, 'en_name', array( 'size' => 38,'disabled'=>'disabled')); ?>
            <?php echo $form->error($model, 'en_name'); ?>
        </div>
        <div class="row">
            <?php echo $form->label($model, 'cn_name'); ?>
            <?php echo $form->textField($model, 'cn_name', array( 'size' => 38,'disabled'=>'disabled')); ?>
            <?php echo $form->error($model, 'cn_name'); ?>
        </div>
        <div class="row">
            <?php echo $form->label($model, 'continent'); ?>
            <?php echo $form->textField($model, 'continent', array( 'size' => 38,'disabled'=>'disabled')); ?>
            <?php echo $form->error($model, 'continent'); ?>
        </div>
        <div class="row">
            <?php echo $form->labelEx($model, 'ebay_code'); ?>
            <?php echo $form->textField($model, 'ebay_code', array( 'size' => 38)); ?>
            <?php echo $form->error($model, 'ebay_code'); ?>
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