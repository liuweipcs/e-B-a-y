<?php Yii::app()->clientscript->scriptMap['jquery.js'] = false; ?>
<style>
    span.error{position: static}
    .chosen-container-single .chosen-single span {height: 100px}
</style>
<div class="pageContent">
<?php
$form = $this->beginWidget('ActiveForm',array(
    'id'                     =>'listauthsadd',
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
                <label style="width: 60px">用户:</label>
                <?php echo CHtml::dropDownList('listauths[uid]',
                    '',
                    UebModel::model('User')->getAllname(),
                    array('class'=>'required list-auth-select','style'=>'width:150px')
                );?>
            </div>
            <div class="row" style="margin-top: 10px;">
                <label style="width: 60px">所属上级:</label>
                <?php echo CHtml::dropDownList('listauths[pid]',
                    '',
                    UebModel::model('User')->getAllname(),
                    array('class'=>'required list-auth-select','style'=>'width:150px')
                );?>
            </div>
            <div class="row" style="margin-top: 10px;">
                <label style="width: 60px">权限等级:</label>
                <?php echo CHtml::dropDownList('listauths[auth_type]', '', array(
                    '1' => '一级',
                    '2' => '二级',
                )) ?>
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
    $(".list-auth-select").chosen();
</script>