<style type="text/css">
<!-- 
	.chosen-single span{padding-top:6px;}
-->
</style>
<?php
/**
 * bank Region drop list group
 */
$modelName = get_class($model);
$platfornType = UebModel::model('PaymentPlatform')->getPlatformType();
$paymentplatformList = array();//付款平台列表
$bank_list = array();//支行列表
$display = 'display:none;';
if(isset($model->payment_platform_id)){
	$paymentplatformList = UebModel::model('PaymentPlatform')->getPaymentPlatformListByType($model->platform_type);
	$display = '';
	
	$region_id = isset($model->bank_province_id) && !empty($model->bank_province_id) ? array('field_name'=>'bank_province_id','value'=>$model->bank_province_id) 	: array();
	$region_id = isset($model->bank_city_id) && !empty($model->bank_city_id) 	? array('field_name'=>'bank_city_id','value'=>$model->bank_city_id) : $region_id;
	$region_id = isset($model->bank_area_id) && !empty($model->bank_area_id) ? array('field_name'=>'bank_area_id','value'=>$model->bank_area_id) : $region_id;
	$bank_list = UebModel::model('Bank')->getBankByPaymentPlatformId($model->payment_platform_id,$region_id);
}


?>
<?php if($model->financialstatus):?>
<div class="row" style="margin-top:8px;">
	<?php echo $form->labelEx($model, 'payment_platform_id'); ?>
	<?php 
	echo $form->dropDownList($model, 'platform_type', $platfornType, array(
	        'disabled'=>'true',
			'empty' 			=> Yii::t('system', 'Please Select'),
			'style'               	=> 'width:100px;',
			'onChange'   	=> "platformOnChange(this.value,'".$modelName."_payment_platform_id');",
	));
	echo $form->dropDownList($model, 'payment_platform_id', $paymentplatformList, array(
			'disabled'=>'true',
			'empty' 			=> Yii::t('system', 'Please Select'),
			//'style'               	=> 'width:100px;',
			//'onChange'   	=> "platformOnChange(this.value,'".$modelName."_payment_platform_id');",
			'onChange'   	=> "bankOnChange(this.value,'".$modelName.'_'.$fieldName[0]."','bank_province_id');",
	));
	?>
	<?php echo $form->error($model, 'payment_platform_id'); ?>
</div>
<?php else:?>
<div class="row" style="margin-top:8px;">
	<?php echo $form->labelEx($model, 'payment_platform_id'); ?>
	<?php 
	echo $form->dropDownList($model, 'platform_type', $platfornType, array(
			'empty' 			=> Yii::t('system', 'Please Select'),
			'style'               	=> 'width:100px;',
			'onChange'   	=> "platformOnChange(this.value,'".$modelName."_payment_platform_id');",
	));
	echo $form->dropDownList($model, 'payment_platform_id', $paymentplatformList, array(
			'empty' 			=> Yii::t('system', 'Please Select'),
			//'style'               	=> 'width:100px;',
			//'onChange'   	=> "platformOnChange(this.value,'".$modelName."_payment_platform_id');",
			'onChange'   	=> "bankOnChange(this.value,'".$modelName.'_'.$fieldName[0]."','bank_province_id');",
	));
	?>
	<?php echo $form->error($model, 'payment_platform_id'); ?>
</div>
<?php endif;?>
<div class="row" style="margin-top:8px;<?php echo $display;?>" id="bank_area">
	<?php echo $form->labelEx($model, 'provider_bank_region_id'); ?> 
	<?php
         $param = array( 
				'region_bank' 	=> isset($region_bank) ? $region_bank : array(),
				'fieldName'		=> array('provider_bank_province','provider_bank_city','provider_bank_area'),
				'form'				=> $form,
				'model'				=> $model,
				//'show_bank_name'		=>true
		);
       echo $this->renderPartial('application.components.views.BankAreaDropList',$param); ?>
</div>
<div class="row" style="margin-top:8px;<?php echo $display;?>" id="bank_name">
	<?php 
	// echo $form->labelEx($model, 'provider_bank_name');
	// echo $form->dropDownList($model, 'provider_bank_name', $bank_list ? $bank_list : array(), array(
			// 'empty' 			=> Yii::t('system', 'Please Select'),
			// 'style'               	=> 'width:100px;',
	// ));
	// echo $form->error($model, 'provider_bank_name');
	?> 
	
	<?php echo $form->labelEx($model, 'provider_bank_nametext'); ?>  
    	
	<?php
	if($model->financialstatus){
		echo $form->textField($model, 'provider_bank_nametext', array( 'readonly'=>'true','size' => 38));
	}else{
	      echo $form->textField($model, 'provider_bank_nametext', array( 'size' => 38));
	}
	?>
	<?php echo $form->error($model, 'provider_bank_nametext'); ?>   
</div>
<script type="text/javascript">
var provinceObj 	= $("#<?php echo $modelName.'_'.$province_id;?>");
var cityObj 			= $("#<?php echo $modelName.'_'.$city_id;?>");
var regionObj 		= $("#<?php echo $modelName.'_'.$region_id;?>");
var platform_type = $("#<?php echo $modelName.'_platform_type'; ?>  option:selected").val();
if(platform_type == 0){
	$('#bank_area').hide();
	$('#bank_name').hide();
}
function bankOnChange(pay_platform_id,to_id,region_id){
	var platform_type = $("#<?php echo $modelName.'_platform_type'; ?>  option:selected").val();
	if(platform_type == 1 && pay_platform_id !=''){
		$('#bank_area').show();
		$('#bank_name').show();
	}else{
		$('#bank_area').hide();
		$('#bank_name').hide();
		return false;
	}
// 	var provinceId 	= $("#< ?php echo $modelName.'_'.$param['fieldName'][0]; ?>  option:selected").val();
// 	var cityId 			= $("#< ?php echo $modelName.'_'.$param['fieldName'][1]; ?>  option:selected").val();
// 	var areaId 		= $("#< ?php echo $modelName.'_'.$param['fieldName'][2]; ?>  option:selected").val();
	$.ajax({
		type: "get",
		url: "/systems/bank/getprovince", 
		data: {'payment_platform_id':'gys_pay_platform_id'},
		dataType:'json',
		success: function(data) {
			var curObj = $("#"+to_id);
			curObj.empty();
			$("<option value=''><?php echo Yii::t('system', 'Please Select');?></option>").appendTo("#"+to_id);
			$.each(data,function(id,item){
				var option=new Option(item,id);
				curObj.get(0).options.add(option);
			});
			//Provider_provider_bank_city
			document.getElementById('Provider_provider_bank_city').selectedIndex = 0;
			document.getElementById('Provider_provider_bank_area').selectedIndex = 0;
			// getBankList(pay_platform_id,0,0,0);
		}
	});
}
// function getBankList(pay_platform_id,provinceId=0,cityId=0,areaId=0){
	// $.ajax({
		// type: "get",
		// url: "/systems/bank/getbank",
		// data: {'payment_platform_id':pay_platform_id,'bank_province_id':provinceId,'bank_city_id':cityId,'bank_area_id':areaId},
		// dataType:'json',
		// success: function(data) {
			// var curObj = $("#<?php echo $modelName;?>provider_bank_nametext");
			// curObj.empty();
			// $("<option value=''><?php echo Yii::t('system', 'Please Select');?></option>").appendTo("#<?php echo $modelName;?>provider_bank_nametext");
			// $.each(data,function(id,item){
				// var option=new Option(item,id);
				// curObj.get(0).options.add(option);
			// });
			
		// }
	// });
// }

function platformOnChange(platform_type,cur_id){
	$.ajax({
		type: "get",
		url: "/systems/paymentplatform/getplatformname",
		data: {'platform_type':platform_type},
		dataType:'json',
		success: function(data) {
			
			var curObj = $("#"+cur_id);
			regionObj.empty();
			curObj.empty();
			$("<option value=''><?php echo Yii::t('system', 'Please Select');?></option>").appendTo("#"+cur_id);
//          curObj.removeClass();
// 			provinceObj.removeClass();
// 			cityObj.removeClass();
// 			regionObj.removeClass();
			$.each(data,function(id,item){
				var option=new Option(item,id);
				curObj.get(0).options.add(option);
			});
		}
	});
	if(platform_type == 0){
		$('#bank_area').hide();
		$('#bank_name').hide();
		return false;
	}
	
}


</script>
