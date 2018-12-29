<style type="text/css">
<!-- 
	.chosen-single span{padding-top:6px;}
-->
</style>

<?php
/*
 * Region drop list group
 */
$modelName = get_class($model);

$province_id = isset($fieldName[0]) && !empty($fieldName[0]) ? $fieldName[0] : 'province_id';
$city_id 		= isset($fieldName[1]) && !empty($fieldName[1]) ? $fieldName[1] : 'city_id';
$region_id		= isset($fieldName[2]) && !empty($fieldName[2]) ? $fieldName[2] : 'region_id';

if(isset($model->payment_platform_id)){
	//$provincePairs = UebModel::model('Region')->getPairsByParentId(1);
	$provincePairs = UebModel::model('Bank')->getProvinceByPaymentPlatformId($model->payment_platform_id,'bank_province_id');
}

//$provincePairs = UebModel::model('region')->getPairsByParentId(1);
if ( isset($model->$province_id) ) {
    $cityPairs = UebModel::model('region')->getPairsByParentId($model->$province_id);
}
if ( isset($model->$city_id) ) {
    $countyPairs = UebModel::model('region')->getPairsByParentId($model->$city_id);
}
// echo CHtml::dropDownList($province_id, isset($region_bank[0]) ? $region_bank[0] : '', $provincePairs, array(
//     'empty' => Yii::t('system', 'Please Select'),
// 	'style'                => 'width:100px;',
// 	'onChange'   => "regionOnChange(this.value,'".$city_id."');",
// ));
// echo CHtml::dropDownList($city_id, isset($region_bank[1]) ? $region_bank[1] : '', isset($cityPairs) ? $cityPairs : array(), array(
//     'empty' => Yii::t('system', 'Please Select'),
// 	'style'                => 'width:100px;',
// 	'onChange'   => "regionOnChange(this.value,'".$region_id."');",
// ));
// echo CHtml::dropDownList($region_id, isset($region_bank[2]) ? $region_bank[2] : '', isset($countyPairs) ? $countyPairs : array(), array(
//    	'empty'      	=> Yii::t('system', 'Please Select'),
// 	'style'         => 'width:100px;',
//    	'onChange'   	=> "$('#".$changeid."').val(this.value);",
// ));

if($model->financialstatus){
	echo $form->dropDownList($model, $province_id, $provincePairs, array( 
	'disabled'=>'true',
	'empty' 			=> Yii::t('system', 'Please Select'),
	'style'               	=> 'width:100px;',
	'onChange'   	=> "bankregionOnChange(this.value,'".$modelName.'_'.$city_id."');",
	));

	echo $form->dropDownList($model, $city_id, isset($cityPairs) ? $cityPairs : array(), array( 
	    'disabled'=>'true',
		'empty' => Yii::t('system', 'Please Select'),
		'style'                => 'width:100px;',
		'onChange'   => "bankregionOnChange(this.value,'".$modelName.'_'.$region_id."');",
	));
	$param = array( 
	    'disabled'=>'true',
		'empty' => Yii::t('system', 'Please Select'),
		'style'                => 'width:100px;',
		//'onChange'   	=> "$('#".$changeid."').val(this.value);",
		'onChange'   => "bankregionOnChange(this.value,'no');",
	);
	if($show_bank_name){
		//$param['onChange'] = "getBank(this.value,'".$modelName.'_'.$region_id."');";
	}
	echo $form->dropDownList($model, $region_id, isset($countyPairs) ? $countyPairs : array(), $param);
}else{
	echo $form->dropDownList($model, $province_id, $provincePairs, array( 
	'empty' 			=> Yii::t('system', 'Please Select'),
	'style'               	=> 'width:100px;',
	'onChange'   	=> "bankregionOnChange(this.value,'".$modelName.'_'.$city_id."');",
	));

	echo $form->dropDownList($model, $city_id, isset($cityPairs) ? $cityPairs : array(), array( 
		'empty' => Yii::t('system', 'Please Select'),
		'style'                => 'width:100px;',
		'onChange'   => "bankregionOnChange(this.value,'".$modelName.'_'.$region_id."');",
	));
	$param = array( 
		'empty' => Yii::t('system', 'Please Select'),
		'style'                => 'width:100px;',
		//'onChange'   	=> "$('#".$changeid."').val(this.value);",
		'onChange'   => "bankregionOnChange(this.value,'no');",
	);
	if($show_bank_name){
		//$param['onChange'] = "getBank(this.value,'".$modelName.'_'.$region_id."');";
	}
	echo $form->dropDownList($model, $region_id, isset($countyPairs) ? $countyPairs : array(), $param);
}


?>

<script type="text/javascript">
function getBank(areaId){
	var pay_platform_id = $("#<?php echo $modelName.'_payment_platform_id'; ?>  option:selected").val();
	getBankList(pay_platform_id,0,0,areaId);
}

function bankregionOnChange(parent_id,cur_id=''){
	var pay_platform_id = $("#<?php echo $modelName.'_payment_platform_id'; ?>  option:selected").val();
	if(cur_id !='no'){
		$.ajax({
			type: "get",
			url: "/systems/region/getInfoByParentId/",
			data: {'parent_id':parent_id},
			dataType:'json',
			success: function(data) {
				var provinceObj 	= $("#<?php echo $modelName.'_'.$province_id;?>");
				var cityObj 			= $("#<?php echo $modelName.'_'.$city_id;?>");
				var regionObj 		= $("#<?php echo $modelName.'_'.$region_id;?>");
				var curObj = $("#"+cur_id);
				//curObj.parent().children().remove('div');
				regionObj.empty();
				if(cur_id=='<?php echo $modelName.'_'.$city_id;?>'){
					cityObj.empty();
					$("<option value=''><?php echo Yii::t('system', 'Please Select');?></option>").appendTo("#<?php echo $modelName.'_'.$region_id;?>");
				}
				$.each(data,function(id,item){
					var option=new Option(item.name,item.id);
					if(cur_id=='<?php echo $modelName.'_'.$city_id;?>'){
						cityObj.get(0).options.add(option);
					}else{
						regionObj.get(0).options.add(option);
					}
				});
				if(cur_id=='<?php echo $modelName.'_'.$city_id;?>'){
					//getBankList(pay_platform_id,parent_id,0,0);
				}else if(cur_id=='<?php echo $modelName.'_'.$region_id;?>'){
					//getBankList(pay_platform_id,0,parent_id,0);
				}else{
					
				}
			}
		});
	}else{
		//getBankList(pay_platform_id,0,0,parent_id);
	}
	
}
</script>
