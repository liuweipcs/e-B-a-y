<?php

Yii::app()->clientscript->scriptMap['jquery.js'] = false;
$form = $this->beginWidget('ActiveForm', array(
    'id' => 'dashBoardForm',
    'enableAjaxValidation' => false,
    'enableClientValidation' => true,
    'focus' => array($model, 'task_remarks'),
    'clientOptions' => array(
        'validateOnSubmit' => true,
        'validateOnChange' => true,
        'validateOnType' => false,
        'afterValidate'=>'js:afterValidate',
    ),
    'action' => Yii::app()->createUrl($this->route.'/id/'.$_GET['id']),
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
                    <li class="selected"><a href="#"><span><?php echo Yii::t('logistics', '完成任务')?></span></a></li>
                </ul>
            </div>
        </div>

        <div class="tabsContent" style="height:100%;">
            <div class="pageFormContent" layoutH="156" style="border:1px solid #B8D0D6">
                <div class="row">
                    <?php echo $form->labelEx($model, '任务名'); ?><?=$models->task_name?>
                </div>
                <div class="row">

                    <?php echo $form->labelEx($model, '质量评分'); ?>
                    <label><input name="TasksIntegral[integral]" type="radio" value="100" checked/>赞美 </label>
                    <label><input name="TasksIntegral[integral]" type="radio" value="90" />表扬 </label>
                    <label><input name="TasksIntegral[integral]" type="radio" value="80" />鼓励 </label>


                </div>
                <div class="row">
                    <?php echo $form->labelEx($model, 'note'); ?>
                    <?php echo $form->textArea($model, 'note', array('cols' => '55','rows'=> '8')); ?>
                    <?php echo $form->error($model, 'note'); ?>


                </div>
                <p style="color: red">赞美:100分,表扬:90分,鼓励:80分</p>
            </div>

        </div>
        <?php echo CHtml::hiddenField('TasksIntegral[task_id]',$task_id);?>
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