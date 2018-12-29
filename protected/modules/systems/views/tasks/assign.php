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
                    <li class="selected"><a href="#"><span><?php echo Yii::t('logistics', '分配任务')?></span></a></li>
                </ul>
            </div>
        </div>

        <div class="tabsContent" style="height:100%;">
            <div class="pageFormContent" layoutH="156" style="border:1px solid #ccc">
                <style>
                    table,table tr th, table tr td { border:1px solid #ccc;line-height: 25px;}
                    table {border-collapse: collapse;border-collapse: collapse;  }
                    .h1{
                        color: red;font-size: 12px;border: 1px solid #ccc;margin: 10px 0px;padding: 10px 0px;

                    }
                </style>
                <h1 class="h1">任务指派给</h1>
                <table  width="100%"  >
                    <tr>
                        <th>责任人</th>
                        <th>积分</th>
                        <th>备注</th>
                    </tr>
                    <?php
                    $as  =UebModel::model('user') ->queryPairs('id,user_full_name',"department_id = ".$_GET['gid']);
                    //$b=0;
                    foreach($as as $k=>$v){?>
                    <tr>
                        <td>
                            <input id="TasksAssign_task_assign_id_<?=$k?>" value="<?=$k?>" <?php /*if($b==0){ echo 'checked';}*/?> type="checkbox" name="TasksAssign[task_assign_id][]"><?=$v?>
                        </td>
                        <td>
                            <input  name="TasksAssign[integral][<?=$k?>]" id="TasksAssign_integral" type="text" class="textInput" style="background: none;">
                        </td>
                        <td>
                            <textarea cols="50" rows="1" name="TasksAssign[task_remarks][<?=$k?>]" id="TasksAssign_task_remarks" class="textInput" style="background: none;"></textarea>
                        </td>

                    </tr>
                    <?php
                        //$b++;
                    }?>
                </table>

                <div class="row">
                    <?php echo $form->labelEx($model, 'task_name'); ?>
                    <?php echo Tasks::getTaskId($_GET['id'])['task_name']; ?>
                    <?php echo $form->error($model, 'task_name'); ?>
                </div>
                <div class="row">
                    <?php echo $form->labelEx($model, 'task_content'); ?>
                    <?php echo Tasks::getTaskId($_GET['id'])['task_content']; ?>
                    <?php echo $form->error($model, 'task_content'); ?>
                </div>
                <div class="row">
                    <?php echo $form->labelEx($model, '要求完成时间'); ?>
                    <?php echo date('Y-m-d H:i:s',Tasks::getTaskId($_GET['id'])['task_claim_time']); ?>
                    <?php echo $form->error($model, 'task_claim_time'); ?>
                </div>
                <div class="row">
                    <?php echo $form->labelEx($model, '承诺上线时间'); ?>
                    <?php echo $form->textField($model, 'task_promise_time', array( 'size' => 30,'datefmt'=>'yyyy-MM-dd HH:mm:ss','class'=>'date textInput','value'=>date('Y-m-d H:i:s',$model->task_promise_time ? $model->task_promise_time:time()))); ?>
                    <?php echo $form->error($model, 'task_promise_time'); ?>
                </div>
                <?php echo CHtml::hiddenField('TasksAssign[gourp_id]', $_GET['gid']);?>
                <?php echo CHtml::hiddenField('TasksAssign[task_id]', $_GET['id']);?>
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