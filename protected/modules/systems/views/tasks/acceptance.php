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
                    <li class="selected"><a href="#"><span><?php echo Yii::t('logistics', '测试验收 ')?></span></a></li>
                </ul>
            </div>
        </div>

        <div class="tabsContent" style="height:90%">
            <div class="pageFormContent" layoutH="156" style="border:1px solid #B8D0D6">

                    <div class="row">
                        <?php echo $form->labelEx($model, 'acceptance_content'); ?>
                        <?php echo $form->textArea($model, 'acceptance_content', array('cols' => '55','rows'=> '8')); ?>
                        <?php echo $form->error($model, 'acceptance_content'); ?>


                    </div>


                <style>
                    table,table tr th, table tr td { border:1px solid #ccc;line-height: 25px;}
                    table {border-collapse: collapse;  }
                    .h1{
                        color: red;font-size: 12px;padding: 10px 0px;
                        clear: both;
                        margin-bottom: 10px;
                    }
                </style>
                <h1 class="h1">历史验收</h1>
                <table  width="100%"  >
                    <tr>
                        <th>验收人</th>
                        <th>验收详细</th>
                        <th>验收时间</th>
                    </tr>
                    <?php
                    if($md){
                        foreach($md as $v){?>
                            <tr>
                                <td><?=Tasks::getUsers($v['acceptance_id'])?></td>
                                <td><?=$v['acceptance_content']?></td>
                                <td><?=date('Y-m-d H:i:s',$v['acceptance_time'])?></td>
                            </tr>
                        <?php }} ?>
                </table>

            </div>


            <?php echo CHtml::hiddenField('TasksAcceptance[task_id]',$id);?>
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