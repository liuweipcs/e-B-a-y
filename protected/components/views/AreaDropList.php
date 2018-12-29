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

$model->$province_id = isset($region[0]) ? $region[0]  : '' ;
$model->$city_id = isset($region[1]) ? $region[1]  : '' ;
$model->$region_id = isset($region[2]) ? $region[2]  : '' ;

$provincePairs = UebModel::model('region')->getPairsByParentId(1);
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


echo $form->dropDownList($model, $province_id, $provincePairs, array( 
	'empty' 			=> Yii::t('system', 'Please Select'),
	'style'               	=> 'width:100px;',
	'onChange'   	=> "areaOnChange(this.value,'".$modelName.'_'.$city_id."');",
));

echo $form->dropDownList($model, $city_id, isset($cityPairs) ? $cityPairs : array(), array( 
	'empty' => Yii::t('system', 'Please Select'),
	'style'                => 'width:100px;',
	'onChange'   => "areaOnChange(this.value,'".$modelName.'_'.$region_id."');",
));
$param = array( 
	'empty' => Yii::t('system', 'Please Select'),
	'style'                => 'width:100px;',
	//'onChange'   	=> "$('#".$changeid."').val(this.value);",
);
if(isset($changeid)){
	$param['onChange'] = "$('#".$changeid."').val(this.value);";
}
echo $form->dropDownList($model, $region_id, isset($countyPairs) ? $countyPairs : array(), $param);

?>

<script type="text/javascript">

function areaOnChange(parent_id,cur_id){
	$('#<?php echo $changeid;?>').val(parent_id);
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
			
//          curObj.removeClass();
// 			provinceObj.removeClass();
// 			cityObj.removeClass();
// 			regionObj.removeClass();

			$.each(data,function(id,item){
				var option=new Option(item.name,item.id);
				if(cur_id=='<?php echo $modelName.'_'.$city_id;?>'){
					cityObj.get(0).options.add(option);
				}else{
					regionObj.get(0).options.add(option);
				}
			});
		}
	});
}
</script>
