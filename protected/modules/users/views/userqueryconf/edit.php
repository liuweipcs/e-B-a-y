<?php Yii::app()->clientscript->scriptMap['jquery.js'] = false; ?>
<style>
    span.error{position: static}
</style>
<div class="pageContent">
    <?php
    $form = $this->beginWidget('ActiveForm',array(
        'id'                     =>'userconfedit',
        'enableAjaxValidation'   =>false,
        'enableClientValidation' =>false,
        'clientOptions'          => array(
            'validateOnSubmit' => true,
            'validateOnChange' => true,
            'validateOnType'   => false,
            'afterValidate'    =>'js:afterValidate',
        ),
        //'action'=>Yii::app()->createUrl('/products/amazonbrand/edit'),
        'htmlOptions'=>array(
            'class'    =>'pageForm',
            'onsubmit' =>'return validateCallback(this,dialogAjaxDone)',
        )
    ));
    ?>
    <div class="pageFormContent" layoutH="58">
        <div>
            <?php echo CHtml::hiddenField('userconf[id]', $model->id) ?>
            <div class="row">
                <label style="width: 60px">部门名称:</label>
                <?php echo CHtml::textField('userconf[department_name]',
                    $model->department_name,
                    array('class'=>'required','style'=>'width:150px')
                );?>
            </div>
            <div class="row">
                <label style="width: 60px">部门分组ID:</label>
                <?php echo CHtml::textArea('userconf[department_id]', $model->department_id, array('class'=>'required','style'=>'height:360px;width:200px')) ?>
            </div>
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
                <div class="button">
                    <div class="buttonContent">
                        <button type="button" class="close"><?php echo Yii::t('system', 'Cancel')?></button>
                    </div>
                </div>
            </li>
        </ul>
    </div>
    <?php $this->endWidget()?>
</div>