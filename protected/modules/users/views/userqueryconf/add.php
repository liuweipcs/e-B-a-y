<?php Yii::app()->clientscript->scriptMap['jquery.js'] = false; ?>
<style>
    span.error{position: static}
</style>
<div class="pageContent">
<?php
$form = $this->beginWidget('ActiveForm',array(
    'id'                     =>'userconfadd',
    'enableAjaxValidation'   =>false,
    'enableClientValidation' =>false,
    'clientOptions'          => array(
        'validateOnSubmit' => true,
        'validateOnChange' => true,
        'validateOnType'   => false,
        'afterValidate'    =>'js:afterValidate',
    ),
    //'action'=>Yii::app()->createUrl('/products/amazonbrand/add'),
    'htmlOptions'=>array(
        'class'    =>'pageForm',
        'onsubmit' =>'return validateCallback(this,dialogAjaxDone)',
    )
));
?>
    <div class="pageFormContent" layoutH="58">
        <div>
            <div class="row">
                <label style="width: 60px">部门名称:</label>
                <?php echo CHtml::textField('userconf[department_name]', '',
                    array('class'=>'required','placeholder'=>'部门名称')
                    );?>
            </div>
            <div class="row">
                <label style="width: 60px">部门分组ID:</label>
                <?php echo CHtml::textArea('userconf[department_id]', '', array('style'=>'height:360px;width:200px;resize:none', 'class'=>'required','placeholder'=>'id之间请用英文逗号隔开')) ?>
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
