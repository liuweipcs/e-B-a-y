<style type="text/css">
<!-- 
	.chosen-single span{padding-top:6px;}
-->
</style>

<?php
/*
 * Region drop list group
 */
	$provincePairs = UebModel::model('region')->getPairsByParentId(1);
	if ( isset($region[0]) ) {
			$cityPairs = UebModel::model('region')->getPairsByParentId($region[0]);
	}
	if ( isset($region[1]) ) {
			$countyPairs = UebModel::model('region')->getPairsByParentId($region[1]);
	}
/*
echo CHtml::listBox('province_id', isset($region[0]) ? $region[0] : '', $provincePairs, array(
		'data-placeholder'     => Yii::t('system', 'Please Select'),
		'class'                => 'chosen-select',
		'style'                => 'width:100px;padding-top:10px;',
		'ajax'  => array(
				'type'      => 'post',
				'url'       => '/systems/region/select',
				'update'    => '#city_id',
				'data'      => array('parent_id' => "js:this.value")
		),
// 		'onChange'   => "$('#city_id').show();",
		
		//                  'multiple'             => 'multiple',
//                     'options'              => $model->sku,
));

echo CHtml::listBox('city_id', isset($region[1]) ? $region[1] : '', isset($cityPairs) ? $cityPairs : array(), array(
		'data-placeholder'     => Yii::t('system', 'Please Select'),
		'class'                => 'chosen-select',
		'style'                => 'width:100px;padding-top:10px;',
		'ajax' => array(
				'type'      => 'post',
				'url'       => '/systems/region/select',
				'update'    => '#region_id',
				'data'      => array('parent_id' => "js:this.value")
		)
		//                  'multiple'             => 'multiple',
//                     'options'              => $model->sku,
));
echo CHtml::listBox('region_id', isset($region[2]) ? $region[2] : '', isset($countyPairs) ? $countyPairs : array(), array(
		'data-placeholder'     => Yii::t('system', 'Please Select'),
		'class'                => 'chosen-select',
		'style'                => 'width:100px;padding-top:10px;',
		'onChange'   => "$('#Provider_provider_region_id').val(this.value);",
		//                  'multiple'             => 'multiple',
//                     'options'              => $model->sku,
));
*/
	echo CHtml::dropDownList('province_id', isset($region[0]) ? $region[0] : '', isset($provincePairs) ?$provincePairs:array(), array(
	     'empty'       => Yii::t('system', 'Please Select'),
		 'class'       =>'chosen-select',
		 'style'       => 'width:100px;',
		 'onChange'    => "regionOnChange(this.value,'city_id');",
	));
	
	
	echo CHtml::dropDownList('city_id', isset($region[1]) ? $region[1] : '',  isset($cityPairs)  ?  $cityPairs : array(), array(
	    'empty'       => Yii::t('system', 'Please Select'),
		'class'       =>'chosen-select-city',
		'style'       => 'width:100px;',
		'onChange'    => "regionOnChange(this.value,'region_id');",
    ));
	echo CHtml::dropDownList('region_id', isset($region[2]) ? $region[2] : '', isset($countyPairs) ? $countyPairs : array(), array(
	   	'empty'       => Yii::t('system', 'Please Select'),
		'class'		  =>'chosen-select-region',
		'style'       => 'width:100px;',
	   	'onChange'    => "$('#".$changeid."').val(this.value);",
	));
?>

<script type="text/javascript">
$(".chosen-select").chosen();
$(".chosen-select-city").chosen();
$('.chosen-select-region',$.pdialog.getCurrent()).chosen({});

function regionOnChange(parent_id,cur_id){
	$('#<?php echo $changeid;?>').val(parent_id);
	$.ajax({
		type: "get",
		url: "/systems/region/getInfoByParentId/",
		data: {'parent_id':parent_id},
		dataType:'json',
		success: function(data) {
			var provinceObj = $("#province_id");
			var cityObj = $("#city_id");
			var regionObj = $("#region_id");
			var curObj = $("#"+cur_id);
			curObj.parent().children().remove('div');
			regionObj.empty();
			if(cur_id=='city_id'){
				cityObj.empty();
				$("<option value=''><?php echo Yii::t('system', 'Please Select');?></option>").appendTo("#region_id");
			}
			
//          curObj.removeClass();
			provinceObj.removeClass();
			cityObj.removeClass();
			regionObj.removeClass();
			provinceObj.chosen('destroy');
			cityObj.chosen('destroy');
			regionObj.chosen('destroy');
			$.each(data,function(id,item){
				var option=new Option(item.name,item.id);
				console.log('bbb');
				if(cur_id=='city_id'){
					cityObj.get(0).options.add(option);
				}else{
					regionObj.get(0).options.add(option);
				}
			});
			curObj.addClass("chosen-select");
			provinceObj.chosen();
			cityObj.chosen();
			regionObj.chosen();
//          $(".chosen-select-city").empty().append(data).trigger("liszt:updated");
		}
	});
}
</script>
