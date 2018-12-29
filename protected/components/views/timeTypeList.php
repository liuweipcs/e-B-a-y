<?php
/*
 * time type drop list group
 */

$type = UebModel::model('ExcelSchemeColumn')->getGroupDateType();

$day_type = @$_REQUEST['is_group'][$db_name.'.'.$table_name][$column_field][day_type]
			? @$_REQUEST['is_group'][$db_name.'.'.$table_name][$column_field][day_type]
			: $default_date;

echo CHtml::dropDownList('is_group['.$db_name.'.'.$table_name.']['.$column_field.'][day_type]', 
		$day_type, $type, array(
    'empty' 		=> Yii::t('system', 'Please Select'),
	'style'			=> 'width:70px;',
));

?>


