<tr>
    <td>
		<?php echo CHtml::dropDownList('dataBaseName['.$index.']', '', $dataBaseArr, array('onchange' => 'getDataBaseTables(this,'.$index.');')); ?>
        <span>
        <?php echo CHtml::listBox('dataBaseTableName['.$index.'][]', '', UebModel::model('ExcelSchemeType')->getSchemaTableNameArr(current(array_flip($dataBaseArr))), array(
										'data-placeholder'     => Yii::t('system', 'Please Select'),
										'class'                => 'chosen-select',
										'style'                => 'width:350px;',
										'multiple'             => 'multiple',
										'options'              => '',
								 )
			  );
        ?>
        </span>
        <a onclick="deleteTr(this);" href="javascript:void(0);"><?php echo Yii::t('system', 'Delete')?></a>
    </td>
</tr>
<script>
	$('.chosen-select').chosen({});
</script>