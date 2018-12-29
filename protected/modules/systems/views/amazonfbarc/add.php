<?php Yii::app()->clientscript->scriptMap['jquery.js'] = false; ?>
<style>
    span.error{position: static}
    .chosen-container-single .chosen-single span {height: 100px}
    .chosen-container .chosen-results {max-height: 200px}
    #pdata_group_id_chosen{width: 100px !important;}
</style>
<div class="pageContent">
<?php
$form = $this->beginWidget('ActiveForm',array(
    'id'                     =>'amazonfbarc-add',
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
                <label style="width: 60px">补货日期:</label>
                <div style="margin-bottom: 10px;">
                    <button class="batch-fabrc" role="all" type="button">全选/反选</button>
                </div>
                <div id="item-fabrc">
                    <fieldset style="margin: 5px 10px;">
                        <?php echo CHtml::checkBoxList('pdata[weeks]','',
                                    AmazonFbarc::getWeeks(),
                                    [
                                        'class'=>'required',
                                        'container' => 'div',
                                        'template'  => '<div style="width:80px;float:left;padding: 8px">{input}{label}</div>',
                                        'separator' => ''
                                    ]
                                )
                        ?>
                    </fieldset>
                </div>
            </div>
            <div class="row" style="margin-top: 10px;">
                <label style="width: 60px">小组:</label>
                <?php echo CHtml::dropDownList('pdata[group_id]',
                    '',
                    UebModel::model('Amazon')->getAmazonAccountGroup(),
                    array('class'=>'required fbarc-select','style'=>'height:100px')
                );?>
            </div>
            <!--<div class="row" style="margin-top: 10px;">
                <label style="width: 60px">状态:</label>
                <?php /*echo CHtml::dropDownList('pdata[state]', '', array(
                    '1' => '启用',
                    '2' => '禁用',
                )) */?>
            </div>-->
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
    $(".fbarc-select").chosen();
    $('.batch-fabrc').click(function(){
        var checkeds=$('#item-fabrc').find(':checkbox').attr('checked');

        if(checkeds=='checked'){
            $('#item-fabrc').find(':checkbox').attr('checked',false);
        }else{
            $('#item-fabrc').find(':checkbox').attr('checked',true);
        }
    });
</script>