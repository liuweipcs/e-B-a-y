<style type="text/css" >
    .sale_account_list{margin-top: -1px;margin-left: 130px;border: 1px solid #c0a2bb;min-height: 80px; max-height:180px;width: 530px;overflow: auto;}
.sale_account_list span{margin:3px; margin-left:8px;}
    .addlogistics4{margin-top: -1px;margin-left: 130px;border: 1px solid #c0a2bb;max-height:200px;min-height:50px;width:250px;overflow: auto;}
.addlogistics4 input{width: 140px;margin: 1px;height:13px;}
    .chosen-container-single .chosen-single{height:20px; padding-top: 6px;}
</style>
<?php
Yii::app()->clientscript->scriptMap['jquery.js'] = false; ?>
<div class="pageContent ">
    <?php
    $form = $this->beginWidget('ActiveForm', array(
        'id' => 'LogisticssetaccountAddForm',
        'enableAjaxValidation' => false,
        'enableClientValidation' => true,
        'clientOptions' => array(
            'validateOnSubmit' => true,
            'validateOnChange' => true,
            'validateOnType' => false,
            'afterValidate'=>'js:afterValidate',
        ),
        'action' => Yii::app()->createUrl($this->route),
        'htmlOptions' => array(
            'class' => 'pageForm',
        )
    ));
    ?>
    <div class="pageFormContent" layoutH="56">

        <div class="row">
            <?php echo $form->labelEx($model,'name');?>
            <?php echo $form->textField($model, 'name', array( 'style' => 'width:220px;',)); ?>
            <?php echo $form->error($model,'name');?>
        </div>

        <div class="row">
            <?php echo $form->labelEx($model,'type');?>
            <?php echo $form->dropDownList($model, 'type',array('0'=>'请选择','1'=>'重检订单','2'=>'设置正常','3'=>'其它')); ?>
            <?php echo $form->error($model,'type');?>
        </div>

        <div class="row">
            <?php echo $form->labelEx($model,'detail');?>
            <?php echo $form->textArea($model, 'detail', array( 'style' => 'width:220px;',)); ?>
            <?php echo $form->error($model,'detail');?>
        </div>

    </div>
    <div class="formBar">
        <ul>
            <li>
                <div class="buttonActive">
                    <div class="buttonContent">
                        <button type="submit" ><?php echo Yii::t('system', 'Save')?></button>
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

<script >
    //$(function(){
//        $("#TrackingLogistics_code").chosen();
//        $(".chosen-container-single").css('width','226px');
//        $(".chosen-container .chosen-results").css('max_height','160px');
//
//        $("#TrackingLogistics_code").change(function(event) {
////            var value=$(this).find("option:selected").text();
////            $("#TrackingLogistics_ship_name").val(value);
//        });
//        $("#TrackingLogistics_code").change();
//
////        $("#LogisticsPrice_m_id").change(function(event) {
////            var valuem_id=$(this).find("option:selected").text();
////            $("#LogisticsPrice_area_name").val(valuem_id);
////        });
////        // $("#LogisticsPrice_m_id").change();
//
//    });

</script>
