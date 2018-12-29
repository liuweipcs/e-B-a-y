<?php Yii::app()->clientscript->scriptMap['jquery.js'] = false; ?>
<style>
    span.error{position: static}
</style>
<div class="pageContent">
<?php
$form = $this->beginWidget('ActiveForm',array(
    'id'                     =>'amazonroleedit',
    'enableAjaxValidation'   =>false,
    'enableClientValidation' =>false,
    'clientOptions'          => array(
        'validateOnSubmit' => true,
        'validateOnChange' => true,
        'validateOnType'   => false,
        'afterValidate'    =>'js:afterValidate',
    ),
    'htmlOptions'=>array(
        'class'    =>'pageForm',
        'onsubmit' =>'return validateCallback(this,dialogAjaxDone)',
    )
));

?>
    <div class="pageFormContent" layoutH="58">
        <div>
            <div class="row">
                <label style="width: 50px">用户:</label>
                <?php echo CHtml::dropDownList(
                        'user',
                        $model->user_id,
                       
                        UebModel::model('User')->getAllname()
                            ,
                        array('class'=>'required','class'=>'country-select')
                    );?>
            </div>
            <div class="row">
                <label style="width: 50px">上级:</label>
                <?php echo CHtml::dropDownList('parent', $model->parent_id,UebModel::model('User')->getAllname(), array('class'=>'required','class'=>'country-select')) ?>
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

<script type="text/javascript">
    $(".country-select").chosen();
</script>
