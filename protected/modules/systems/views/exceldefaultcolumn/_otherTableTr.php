<tr height="30">
   <td width="80">
			<?php echo Yii::t('system', 'Schema Table Name');?>: 
	   <?php echo CHtml::dropDownList('otherTableName['.$index.']', '', $tableName, array('onchange' => 'getOtherColunmField(this,'.$index.');')); ?>
   </td>
   <td width="80">
       <?php echo Yii::t('system', 'Column field');?>: <br>
       <span id="columnSpan">
        	<?php echo CHtml::dropDownList('otherColumnField['.$index.'][]', '', MHelper::getColumnsPairsByTableName(current($tableName))); ?> 
       </span>
   </td>
   <td width="20">
   		<a onclick="deleteOtherTable(this);" href="javascript:void(0);" align="right"><?php echo Yii::t('system', 'Delete')?></a>
   </td>
</tr>

