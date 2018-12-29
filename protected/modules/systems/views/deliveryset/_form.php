<?php Yii::app()->clientscript->scriptMap['jquery.js'] = false; ?>
<div class="pageContent">
    <?php
    $form = $this->beginWidget('ActiveForm', array(
        'id' => 'wishForm',
        'enableAjaxValidation' => false,
        'enableClientValidation' => true,
    	'clientOptions' => array(
            'validateOnSubmit' => true,
            'validateOnChange' => true,
            'validateOnType' => false,
            'afterValidate' => 'js:afterValidate',
        ),
        'action' => Yii::app()->createUrl($this->route,array('id'=>$model->id)),
        'htmlOptions' => array(
            'class' => 'pageForm',
        )
    ));
    ?>   
    <div class="pageFormContent" layoutH="56" >
        <div class="row">
            <?php echo $form->labelEx($model, 'platform_name'); ?>
            <?php echo $form->textField($model, 'platform_name', array('style'=>'width:450px;')); ?>
            <?php echo $form->error($model, 'platform_name'); ?>
        </div>
        <div class="row">
            <?php echo $form->labelEx($model, 'platform_code'); ?>
            <?php echo $form->dropDownList($model,'platform_code', UebModel::model('Platform')->getPlatformList(),array('empty'=>Yii::t('system','Please Select'),'options'=>array($model->platform_code=>array('selected'=>'selected'))));  ?>
            <?php echo $form->error($model, 'platform_code'); ?>
        </div>
        <div class="row">
            <?php echo $form->labelEx($model, 'delivery_time'); ?>
            <?php echo $form->textField($model, 'delivery_time', array('style'=>'width:450px;')); ?> 天
            <?php echo $form->error($model, 'delivery_time'); ?>
        </div>
        <div class="row OrderDeliveryTime_site" style="display: none">
            <?php echo $form->labelEx($model, 'site'); ?>
            <?php echo $form->dropDownList($model,'site',UebModel::model('EbaySites')->GetListAll(),array('empty'=>Yii::t('system','Please Select'),'options'=>array($model->site=>array('selected'=>'selected'))));  ?>
            <?php echo $form->error($model, 'site'); ?>
        </div>
        <div class="row">
            <?php echo $form->labelEx($model, 'return_time'); ?>
            <?php echo $form->textField($model, 'return_time', array('style'=>'width:450px;')); ?> 天
            <?php echo $form->error($model, 'return_time'); ?>
        </div>
        <div class="row">
            <?php echo $form->labelEx($model, 'execution_type'); ?>
            <?php echo $form->dropDownList($model, 'execution_type',array(1=>'在设置时间内可同步',2=>'到设置时间时才可同步') , array('empty'=>Yii::t('system','Please Select'),'options'=>array($model->platform_code=>array('selected'=>'selected')))); ?>
            <?php echo $form->error($model, 'execution_type'); ?>
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
                <div class="button"><div class="buttonContent"><button type="button" class="close"><?php echo Yii::t('system', 'Cancel') ?></button></div></div>
            </li>
        </ul>
    </div>
    <?php $this->endWidget(); ?>
</div>
<script type="text/javascript">
    $("#OrderDeliveryTime_platform_code").change(function(){
        var platform = $(this).val();
        $.post("/systems/deliveryset/getsite",{'platform':platform},function(result){
            var obj = eval('('+result+')');
            var trs = '';
            if(obj.status == 1){
                $('.OrderDeliveryTime_site').css("display","block");
                $.each(obj.data,function(n,value) {
                    trs += '<option value="'+n+'">' + value+'</option>';
                });
                $('#OrderDeliveryTime_site').html('');
                $('#OrderDeliveryTime_site').append(trs);
            }else {
                $('#OrderDeliveryTime_site').html('');
                $('.OrderDeliveryTime_site').css("display","none");
            }
        });
    });
</script>


