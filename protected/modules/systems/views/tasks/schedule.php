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
                    <li class="selected"><a href="#"><span><?php echo Yii::t('logistics', '更新进度 ')?></span></a></li>
                </ul>
            </div>
        </div>

        <div class="tabsContent" style="height:100%;">
            <div class="pageFormContent" layoutH="156" style="border:1px solid #B8D0D6">
                <div class="row">
                    <?php echo $form->labelEx($model, 'schedule'); ?>
                    <?php echo $form->dropDownList($model, 'schedule', ['10'=>'10%','20'=>'20%','30'=>'30%','40'=>'40%','50'=>'50%','60'=>'60%','70'=>'70%','80'=>'80%','90'=>'90%','100'=>'100%']); ?>
                    <?php echo $form->error($model, 'schedule'); ?>


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