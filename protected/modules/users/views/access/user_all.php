<?php
	//echo CHtml::dropDownList('Productrole[user_id]', '', $arr, array('onChange'=>'aa(this.options[this.options.selectedIndex].text,this.options[this.options.selectedIndex].value)') );
	//echo '<span id="error_user" style="color:#f00;padding-left:3px;"></span>';
	//echo CHtml::hiddenField('Productrole[user_name]','');

if($arr){
foreach($arr as $key=>$val):
echo '<li style="border-bottom:1px dashed #ccc;line-height:180%;list-style-type:none;">';
echo CHtml::link($val,'javascript:',array('onclick'=>"$.bringBack({user_name:'".$val."', role_code:'".$arr_role['role_code']."', user_id:'".$key."', user_result:'".$arr_role['role_name'].' -> '.$val."'})"));
echo '</li>';
endforeach;
}else{
	echo Yii::t('products','Under this role has not been assigned user');
}
?>
