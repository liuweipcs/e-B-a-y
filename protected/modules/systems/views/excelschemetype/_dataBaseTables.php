<?php

echo CHtml::listBox('dataBaseTableName['.$id.'][]', '', $tableNames, array(
		'data-placeholder'     => Yii::t('system', 'Please Select'),
		'class'                => 'chosen-select',
		'style'                => 'width:350px;',
		'multiple'             => 'multiple',
		'options'              => '',
	)
);
?>
<script>
	$('.chosen-select').chosen({});
</script>