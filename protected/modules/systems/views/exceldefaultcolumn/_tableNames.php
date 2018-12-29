<table id="column_field_table" class="dataintable" width="450" cellspacing="1" cellpadding="3" border="0" align="left">
       <tr>
           <td width="80">
			   	<?php echo Yii::t('system', 'Schema Table Name');?>: 
		   </td>
		   <td>
			   <?php echo CHtml::dropDownList('tableName', '', $tableNames, array('onchange' => 'getColunmField(this);')); ?>
           </td>
       <tr>
       	   <td width="80">
       	   		<?php echo Yii::t('system', 'Column field');?>: 
       	   </td>
       	   <td>
        		<span id="columnSpan">
        			<?php echo CHtml::dropDownList('columnField', '', MHelper::getColumnsPairsByTableName(current($tableNames))); ?>
        		</span>
           </td>
       </tr>
</table>

