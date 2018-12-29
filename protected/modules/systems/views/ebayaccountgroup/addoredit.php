<?php Yii::app()->clientscript->scriptMap['jquery.js'] = false; ?>
<script>
    $(function(){
        $("img").lazyload({
            effect : "fadeIn"
        });
    });
</script>
<div class="pageContent">
    <?php
    if(empty($model->id))
        $actionUrl = Yii::app()->createUrl($this->route);
    else
        $actionUrl = Yii::app()->createUrl($this->route,array('id'=>$model->id));
    $form = $this->beginWidget('ActiveForm', array(
        'id' => 'EbayAccountGroupForm',
        'enableAjaxValidation' => false,
        'enableClientValidation' => true,
        'clientOptions' => array(
            'validateOnSubmit' => true,
            'validateOnChange' => true,
            'validateOnType' => false,
            'afterValidate'=>'js:afterValidate',
        ),
        'action' => $actionUrl,
        'htmlOptions' => array(
            'class' => 'pageForm',
        )
    ));
    ?>
    <div class="pageFormContent" layoutH="56">
        <div class="pd5" style="height:150px;">
            <div class="row">
                <?php echo $form->labelEx($model,'name');?>
                <?php echo $form->textField($model,'name',array('style'=>'width:300px')); ?>
                <?php echo $form->error($model,'name');?>
            </div>
            <div class="row">
                <?php echo $form->labelEx($model, 'reference'); ?>
                <?php echo $form->textArea($model, 'reference',array('style'=>'width:525px;height:142px')); ?>
                <?php echo $form->error($model, 'reference'); ?>
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