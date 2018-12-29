<div class="panelBar">
    <ul class="toolBar">
        <li>
            <a class="add" href="javascript:void(0);" onclick="$.refreshConfigCache('<?php echo PurchaseSetting::PARA_TYPE;?>');" >
                <span><?php echo Yii::t('system', 'Refresh Cache')?></span>
            </a>
        </li>          
    </ul>
</div>

<?php 
Yii::app()->clientscript->scriptMap['jquery.js'] = false; 
$form = $this->beginWidget('ActiveForm', array(
    'id' => 'purchasesetForm',
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
        'class' => 'pageForm ',
    )
));
?>
<style type="text/css">
.row label {width:140px;line-height:24px;}
.con {float:left;padding-top:4px;}
.con_tips {float:left;padding-left:6px;padding-top:6px;}
.btn_help  {width:20px;height:20px;}
.errorMessage  {width:auto;}
</style>
<div class="pageFormContent" layoutH="90">
	<div class="bg14 pdtb2 dot">
            <strong><?php echo Yii::t('purchases', 'Ready for the rule set');?></strong>           
    </div>
    <div class="dot7 pd5" style="height:220px;">
        <div class="row">
            <?php echo $form->labelEx($model, 'default_warehouse'); ?>
            <?php echo $form->dropDownList($model, 'default_warehouse',$warehouseList,array('empty' => Yii::t('system','Please Select'),'class'=>'type')); ?>
            <?php echo $form->error($model, 'default_warehouse'); ?>
        </div>
    	<div class="row">
            <?php echo $form->labelEx($model, 'purchasing_delivery'); ?>
            <div class="con">
            <?php echo $form->textField($model, 'purchasing_delivery', array( 'size' => 6,'inc_sub_size' => 1)); ?>
            <div class="con_tips"><?php echo Yii::t('purchases','Days');?> [采购至到货的天数]</div>
            </div>
            <?php echo $form->error($model, 'purchasing_delivery'); ?>
        </div>
        <div class="row" >
            <?php echo $form->labelEx($model, 'mandatory_purchasing_delivery'); ?>
            <div class="con">
                <?php var_dump() ?>
                <?=$form->CheckBox($model, 'mandatory_purchasing_delivery',array('value'=>1,'style'=>'float:left;' ));?>

            <div class="con_tips"><?php echo Yii::t('system','Yes');?></div>
			</div>
			<?php echo $form->error($model, 'mandatory_purchasing_delivery'); ?>          
        </div>
        <div class="row">
            <?php echo $form->labelEx($model, 'purchasing_cycle'); ?>
            <div class="con">
            <?php echo $form->textField($model, 'purchasing_cycle', array( 'size' => 6,'inc_sub_size' => 1)); ?>
            <div class="con_tips"><?php echo Yii::t('purchases','Days');?> [一次购买多少天的产品]</div>
            </div>
            <?php echo $form->error($model, 'purchasing_cycle'); ?>          
        </div>
        <div class="row" style="border-bottom:1px dashed #ccc;overflow:hidden;margin-bottom:8px;">
            <?php echo $form->labelEx($model, 'mandatory_purchasing_cycle'); ?>
            <div class="con">
            <?php echo $form->CheckBox($model, 'mandatory_purchasing_cycle',array('value'=>1,'style'=>'float:left;')); ?>
            <div class="con_tips"><?php echo Yii::t('system','Yes');?></div>
            </div>
			<?php echo $form->error($model, 'mandatory_purchasing_cycle'); ?>          
        </div>
        <div class="row">
            <?php echo $form->labelEx($model, 'formula_average_daily_sales'); ?>
            <div class="con">
            <?php echo $form->textField($model, 'formula_average_daily_sales', array( 'size' => 35)); ?>
            <div class="con_tips"><?php echo Yii::t('purchases','The formula of example');?></div>
            <!--  <a title="查看帮助" class="btn_help" href="#help_bar">帮助</a>-->
            </div>
            <?php echo $form->error($model, 'formula_average_daily_sales'); ?>          
        </div>
        <div class="row">
            <?php echo $form->labelEx($model, 'purchasing_prepare_time'); ?>
            <div class="con">
	            <?php echo $form->textField($model, 'purchasing_prepare_time', array( 'size' => 6,'inc_sub_size' => 1)); ?>
	            <div class="con_tips"><?php echo Yii::t('purchases','Days');?></div>
            </div>
            <?php echo $form->error($model, 'purchasing_prepare_time'); ?>          
        </div>
        <!--<div class="row">
            < ?php echo $form->labelEx($model, 'purchasing_prepare_safe_cycle'); ?>
            <div class="con">
	            < ?php echo $form->textField($model, 'purchasing_prepare_safe_cycle', array( 'size' => 6,'inc_sub_size' => 1)); ?>
	            <div class="con_tips">< ?php echo Yii::t('purchases','Days');?></div>
            </div>
            < ?php echo $form->error($model, 'purchasing_prepare_safe_cycle'); ?>          
        </div>
          
        <div class="row">
            < ?php echo $form->labelEx($model, 'prompt_quantity'); ?>
            <div class="con">
	            < ?php echo $form->textField($model, 'prompt_quantity', array( 'size' => 6,'inc_sub_size' => 1)); ?>
	            <div class="con_tips"> (= < ?php echo Yii::t('purchases','purchasing cycle (Global)');?> * < ?php echo Yii::t('purchases','Average daily sales');?>)</div>
            </div>
            < ?php echo $form->error($model, 'prompt_quantity'); ?>          
        </div>
        
        
        
        <div class="row">
            < ?php echo $form->labelEx($model, 'inventory_alert'); ?>
            <div class="con">
	            < ?php echo $form->textField($model, 'inventory_alert', array( 'size' => 6,'inc_sub_size' => 1)); ?>
	            <div class="con_tips"> = (< ?php echo Yii::t('purchases','Purchasing Delivery (Global)');?>+< ?php echo Yii::t('purchases','Purchasing time to prepare');?>) * 
	            < ?php echo Yii::t('purchases','Average daily sales');?></div>
            </div>
            < ?php echo $form->error($model, 'inventory_alert'); ?>
        </div>-->

    </div>
    <br/>
    <div class="bg14 pdtb2 dot">
            <strong><?php echo Yii::t('purchases', 'Audit rules set');?></strong>
    </div>
    <div class="dot7 pd5" style="height:120px;">
       <div class="row">
            <?php echo $form->labelEx($model, 'total_prices'); ?>
            <div class="con">
	            <?php echo $form->textField($model, 'total_prices', array( 'size' => 6,'inc_sub_size' => 1000)); ?>
            </div>
            <?php echo $form->error($model, 'total_prices'); ?>          
        </div>
        <div class="row">
            <?php echo $form->labelEx($model, 'purchase_maximum'); ?>
            <div class="con">
	            <?php echo $form->textField($model, 'purchase_maximum', array( 'size' => 6,'inc_sub_size' => 100)); ?>
            </div>
            <?php echo $form->error($model, 'purchase_maximum'); ?>
        </div>
        <div class="row">
            <?php echo $form->labelEx($model, 'price_float_range'); ?>
            <div class="con">
	            <?php echo $form->textField($model, 'price_float_range', array( 'size' => 6,'inc_sub_size' => 0.1)); ?><div class="con_tips"><?php echo Yii::t('purchases', 'Compared to the last');?></div>
            </div>
            <?php echo $form->error($model, 'price_float_range'); ?>          
        </div>
        <div class="row">
            <?php echo $form->labelEx($model, 'quantity_prompted_ratio'); ?>
            <div class="con">
	            <?php echo $form->textField($model, 'quantity_prompted_ratio', array( 'size' => 6,'inc_sub_size' => 0.1)); ?>
            </div>
            <?php echo $form->error($model, 'quantity_prompted_ratio'); ?>          
        </div>
        
        
 	</div>
 	<br/>
 	<div class="bg14 pdtb2 dot">
            <strong><?php echo Yii::t('purchases', 'Other set');?></strong>
    </div>
    <div class="dot7 pd5" style="height:55px;">
       <div class="row">
            <?php echo $form->labelEx($model, 'least_purchase'); ?>
            <div class="con">
	            <?php echo $form->textField($model, 'least_purchase', array( 'size' => 6,'inc_sub_size' => 1)); ?>
            </div>
            <?php echo $form->error($model, 'least_purchase'); ?>          
        </div>
        <div class="row">
            <?php echo $form->labelEx($model, 'optimal_inquiry_days'); ?>
            <div class="con">
	            <?php echo $form->textField($model, 'optimal_inquiry_days', array( 'size' => 6,'inc_sub_size' => 1)); ?><div class="con_tips"><?php echo Yii::t('purchases','Days');?></div>
            </div>
            <?php echo $form->error($model, 'optimal_inquiry_days'); ?>          
        </div>
 	</div>
    <br/>
    
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
            <div class="button"><div class="buttonContent"><button type="button" class="close" ><?php echo Yii::t('system', 'Cancel')?></button></div></div>
        </li>
    </ul>
</div>
<script type="text/javascript">
$(document).ready(function(){
    $(".type").change(function(){
         var type = $(".type").val();

         $.ajax({
                type:"GET",
                url:"<?=Yii::app()->createUrl('/systems/purchaseset/load')?>",
                data:{type:type},
                dataType:"json",
                success:function(data){
                    if(data[0]){

                          $('#PurchaseSetting_purchasing_delivery').val(data[0].config_value);
                          $('#PurchaseSetting_purchasing_cycle').val(data[1].config_value);
                          $('#PurchaseSetting_formula_average_daily_sales').val(data[5].config_value);

                          if(data[2].config_value == 1)
                          {
                            $('#PurchaseSetting_mandatory_purchasing_delivery').attr("checked",'true');
                          }
                          if (data[4].config_value == 1)
                          {
                            $('#PurchaseSetting_mandatory_purchasing_cycle').attr('checked','true');
                          }
                          $('#PurchaseSetting_purchasing_prepare_time').val(data[6].config_value);
                          $('#PurchaseSetting_total_prices').val(data[7].config_value);
                          $('#PurchaseSetting_purchase_maximum').val(data[8].config_value);
                          $('#PurchaseSetting_price_float_range').val(data[9].config_value);
                          $('#PurchaseSetting_quantity_prompted_ratio').val(data[10].config_value);
                          $('#PurchaseSetting_least_purchase').val(data[11].config_value);
                          $('#PurchaseSetting_optimal_inquiry_days').val(data[12].config_value);

                    } else {
                       $("input").val(' ');


                    }


                },

               });
    });

});








</script>
<?php $this->endWidget(); ?>




