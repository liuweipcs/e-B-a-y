<?php

Yii::app()->clientscript->scriptMap['jquery.js'] = false;
$form = $this->beginWidget('ActiveForm', array(
    'id' => 'dashBoardForm',
    'enableAjaxValidation' => false,
    'enableClientValidation' => true,
    'focus' => array($model, 'task_name'),
    'clientOptions' => array(
        'validateOnSubmit' => true,
        'validateOnChange' => true,
        'validateOnType' => false,
        'afterValidate'=>'js:afterValidate',
    ),
    'action' => Yii::app()->createUrl($this->route.'/id/'.$model->id),
    'htmlOptions' => array(
        'class' => 'pageForm',
    )
));

?>
<style>
    <!--
    label.alt{width: 128px; opacity: 1;height:20px;margin:0;line-height:22px;}
    -->
</style>
<div class="pageContent">
    <div class="tabs">
        <div class="tabsHeader">
            <div class="tabsHeaderContent">
                <ul>
                    <li class="selected"><a href="#"><span><?php echo Yii::t('logistics', '任务延期')?></span></a></li>
                </ul>
            </div>
        </div>

        <div class="tabsContent" style="height:100%;">
            <div class="pageFormContent" layoutH="156" style="border:1px solid #B8D0D6">
                <div class="row">
                    <?php echo $form->labelEx($model, 'task_promise_time'); ?>
                    <?php echo $form->textField($model, 'task_promise_time', array( 'size' => 30,'datefmt'=>'yyyy-MM-dd HH:mm:ss','class'=>'date textInput','value'=>date('Y-m-d H:i:s',$model->task_promise_time ? $model->task_promise_time:time()))); ?>
                    <?php echo $form->error($model, 'task_promise_time'); ?>
                </div>
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