<?php Yii::app()->clientscript->scriptMap['jquery.js'] = false; ?>
<div class="pageContent"> 
    <?php
    $form = $this->beginWidget('ActiveForm', array(
        'id' => 'menuForm',
        'enableAjaxValidation' => false,
        'enableClientValidation' => true,
    	'focus' => array($model, 'column_type'),
        'clientOptions' => array(
            'validateOnSubmit' => true,
            'validateOnChange' => true,
            'validateOnType' => false,
            'afterValidate' => 'js:afterValidate',
        	'additionValidate' => 'js:checkData',
        ),
        'action' => Yii::app()->createUrl($this->route, array( 'id' => $model->id)),
        'htmlOptions' => array(
            'class' => 'pageForm',
        )
    ));
    ?>   
    <div class="pageFormContent" layoutH="56">  
        <div class="row" style="display:<?php if(!isset($_GET['id'])):?>block;<?php else:?>none;<?php endif;?>">
            <?php echo $form->labelEx($model, 'column_type'); ?>
            <?php echo $form->dropDownList($model, 'column_type', UebModel::model('ExcelSchemeType')->queryPairs('id,type_name'), array('empty'=>Yii::t('system', 'Please Select'),'onchange' => 'getTableNames(this);')); ?>
            <?php echo $form->error($model, 'column_type'); ?>          
        </div>
           
        <div class="row">
            <?php echo $form->labelEx($model, 'column_title'); ?>
            <?php echo $form->textField($model, 'column_title', array( 'size' => 38)); ?>
            <?php echo $form->error($model, 'column_title'); ?>          
        </div>       
        <div class="row">
            <?php echo $form->labelEx($model, 'column_field'); ?>
            <div id="column_field_div">
            <?php if (! empty($model->column_type)):?>
	            <table id="column_field_table" class="dataintable" width="450" cellspacing="1" cellpadding="3" border="0" align="left">
				       <tr>
				           <td width="80">
							   	<?php echo Yii::t('system', 'Schema Table Name');?>
						   </td>
						   <td>
							   <?php echo CHtml::dropDownList('tableName', $model->table_name, UebModel::model('excelSchemeTypeMap')->getTableNamesByTypeId($model->column_type), array('disabled'=>isset($_GET['id'])?true:false,'onchange' => 'getColunmField(this);')); ?>
				           </td>
				       </tr>
				       <tr>
				       	   <td width="80">
				       	   		<?php echo Yii::t('system', 'Column field');?>
				       	   </td>
				       	   <td>
				        		<span id="columnSpan">
				        			<?php echo CHtml::dropDownList('columnField', $model->column_field, MHelper::getColumnsPairsByTableName($model->table_name),array('disabled'=>isset($_GET['id'])?true:false)); ?> 
				        		</span>
				           </td>
				       </tr>
				</table>
				<?php else:?>
				<?php echo '';?>
				<?php endif;?>
	        </div>
	        <div id="checkDiv" class="errorMessage" style='margin-right:30px;margin-top:-80px;float:right;height:20px;width:150px;display:none;'></div> 
        </div>
        <div class="row">
            <?php echo $form->labelEx($model, Yii::t('system', 'Other Column Field')); ?>
        	<p align=left>
    			<br/>
			    <a id="btnAddTr" onclick="addOtherTableTr(this);" href="javascript:void(0);">
			        <?php echo Yii::t('system', 'Add')?>
			    </a>
			</p>
			<div id="other_column_field_div">
			   <table id="other_column_field_table" class="dataintable" width="450" cellspacing="1" cellpadding="3" border="0" align="left">
					   <?php $key = 0;?>
					   <?php if (! empty($model->table_column_paris)):?>
					   <?php foreach ($model->table_column_paris as $key => $val):?>
					   		 	<tr height="30">
								   <td width="80">
											<?php echo Yii::t('system', 'Schema Table Name');?>: 
									   <?php echo CHtml::dropDownList('otherTableName['.$key.']', $val['map_table_name'], UebModel::model('ExcelSchemeTypeMap')->getTableNamesByTypeId($model->column_type), array('onchange' => 'getOtherColunmField(this,'.$key.');')); ?>	   
								   </td>
								   <td width="80">
								       <?php echo Yii::t('system', 'Column field');?>: <br>
								       <span id="columnSpan">
								        	<?php echo CHtml::dropDownList('otherColumnField['.$key.'][]', $val['map_field_name'], MHelper::getColumnsPairsByTableName($val['map_table_name'])); ?> 
								       </span>
								   </td>
								   <td width="20">
								   		<a onclick="deleteOtherTable(this);" href="javascript:void(0);" align="right"><?php echo Yii::t('system', 'Delete')?></a>
								   </td>
								</tr>
					   <?php endforeach;?>
					   <?php else:?>
					   <?php echo '';?>
					   <?php endif;?>
			   </table>
        	</div>
        </div>        
    </div>
    <div class="formBar">
        <ul>              
            <li>
                <div class="buttonActive">
                    <div class="buttonContent">                        
                        <button type="submit"><?php echo Yii::t('system', 'Save') ?></button>                     
                    </div>
                </div>
            </li>
            <li>
                <div class="button"><div class="buttonContent"><button type="button" class="close"><?php echo Yii::t('system', 'Cancel') ?></button></div></div>
            </li>
        </ul>
    </div>
    <?php $this->endWidget(); ?>
</div>
<script type="text/javascript">

var getTableNames = function (obj){
	$.ajax({
	       type: "post",
	       url: "/systems/ExcelDefaultColumn/getTableNames",
	       data: {typeId: obj.value},
	       async: false,   
	       dataType:'html',
	       success: function(data) {
	          if ( data ) {                
	        	  $('#column_field_div').html(data);
	        	  $('#other_column_field_table').html('');
	          }
	       }
	}); 
}

var checkData = function (){
	var tableName = $('#tableName option:selected').val();
	var columnField = $('#columnField option:selected').val();

	if(tableName==null || columnField==null){
		$("#checkDiv").show();
		$("#checkDiv").html('数据表或字段名不能为空');
		return false;
	}else{
		$("#checkDiv").hide();
		return true;
	}	
}

var index = parseInt('<?php echo $key;?>');
var addOtherTableTr = function (obj){
	var typeId = $('#ExcelDefaultColumn_column_type option:selected').val();
	index++;
	$.ajax({
	       type: "post",
	       url: "/systems/ExcelDefaultColumn/getOtherTableTr",
	       data: {index: index,
	    	      typeId: typeId},
	       async: false,   
	       dataType:'html',
	       success: function(data) {
	          if ( data ) {                
	        	  $('#other_column_field_table').append(data);
	          }
	       }
	}); 
	
}

var getColunmField = function(obj){
	$.ajax({
	       type: "post",
	       url: "/systems/ExcelDefaultColumn/getColumnFields",
	       data: {tableName: obj.value},
	       async: false,   
	       dataType:'html',
	       success: function(data) {
	          if ( data ) {                
	        	  $('#columnSpan').html(data);
	          }
	       }
	}); 
}

var getOtherColunmField = function (obj,index){
	$.ajax({
	       type: "post",
	       url: "/systems/ExcelDefaultColumn/getOtherColunmField",
	       data: {tableName: obj.value,
	    	   	  index: index},
	       async: false,   
	       dataType:'html',
	       success: function(data) {
	          if ( data ) {                
	        	  $(obj).parent().next().find('span').html(data);
	          }
	       }
	}); 
}

var deleteOtherTable = function (obj){
	$(obj).parent().parent().remove();
}
</script>
