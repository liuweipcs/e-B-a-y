<?php
/**
 * District Fill Text Based On AutoComplete
 * @author :Gordon
 * @date   :2013-11-26
 */
Yii::app()->clientScript->registerCssFile(Yii::app()->baseUrl.'/css/jquery-ui.custom.css', 'screen');
Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl.'/js/custom/autocomplete.js'); 
//Get All countries
$countryList = UebModel::model('country')->getCountryList();
?>
<script type="text/javascript">
	$(function(){
		var availableTags = new Array();
		<?php foreach($countryList as $item):?>
		availableTags.push("<?php echo $item;?>");
		<?php endforeach;?>
		$("input[name='<?php echo $name.'[country]';?>']").autocomplete({
			source : availableTags
		});	
	});

// 	function districtHint(val,type){
// 		$.ajax({
// 			'type' : 'post',
// 			'url'  : '',
// 		});
// 	}
</script>
<style>
a.ui-corner-all{text-align:left;}
</style>
<?php 
if(isset($data)){
	foreach($data as $key=>$item){
		echo CHtml::textField($name.'['.$key.']', $item, array(
			'class'		=> 'district-text',
			'style'     => 'width:150px;margin-right:5px;',
		));	
	}
}

// echo CHtml::textField('Warehouse[state]', isset($state) ? $state : '', array(
// 		'class'		=> 'state-text',
// 		'style'     => 'width:150px;margin-right:5px;',
// ));
// echo CHtml::textField('Warehouse[city]', isset($city) ? $city : '', array(
// 		'class'		=> 'city-text',
// 		'style'     => 'width:150px;',
// ));