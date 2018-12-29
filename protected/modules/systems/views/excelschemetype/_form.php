<?php Yii::app()->clientscript->scriptMap['jquery.js'] = false; ?>
<div class="pageContent"> 
    <?php
    $form = $this->beginWidget('ActiveForm', array(
        'id' => 'menuForm',
        'enableAjaxValidation' => false,
        'enableClientValidation' => true,
    	'focus' => array($model, 'type_name'),
        'clientOptions' => array(
            'validateOnSubmit' => true,
            'validateOnChange' => true,
            'validateOnType' => false,
            'afterValidate' => 'js:afterValidate',
        	'additionValidate' => 'js:checkData',
        ),
        'action' => Yii::app()->createUrl($this->route, array('id' => $model->id)),
        'htmlOptions' => array(
            'class' => 'pageForm',
        )
    ));
    ?>   
    <div class="pageFormContent" layoutH="56">  
        <div class="row">
            <?php echo $form->labelEx($model, 'type_name'); ?>
            <?php echo $form->textField($model, 'type_name', array( 'size' => 38));  ?>
            <?php echo $form->error($model, 'type_name'); ?>          
        </div>
       
        <div class="row">
           <?php echo $form->labelEx($model, 'schema_table_name'); ?>
           <p align=left>
    			<br/>
			    <a id="btnAddTr" onclick="addDataBaseTableTr(this);" href="javascript:void(0);">
			        <?php echo Yii::t('system', 'Add dataBase table tr')?>
			    </a>
			</p>
		   <div id="checkDiv" class="errorMessage" style='margin-left:-300px;float:left;display:none;height:20px;width:150px;'></div>
           <table id="tableData" class="dataintable" width="550" cellspacing="1" cellpadding="3" border="0" align="left">
            <?php $num = 0;?>
			<?php if (! empty($model->schema_table_paris)):?>
			<?php foreach ($model->schema_table_paris as $key => $val):
					$num++;
			?>
           		<tr>
           			<td>
			            <?php echo CHtml::dropDownList('dataBaseName['.$num.']', $key, Configuration::getDbNamesConfig(), array('onchange' => 'getDataBaseTables(this,'.$num.');')); ?>
        				<span>
        				<?php echo CHtml::listBox('dataBaseTableName['.$num.'][]', '', UebModel::model('ExcelSchemeType')->getSchemaTableNameArr($key), array(
										'data-placeholder'     => Yii::t('system', 'Please Select'),
										'class'                => 'chosen-select',
										'style'                => 'width:350px;',
										'multiple'             => 'multiple',
										'options'              => $val,
									)
							  );
        				?>
        				</span>
        				<a onclick="deleteTr(this);" href="javascript:void(0);" align="right"><?php echo Yii::t('system', 'Delete')?></a>
        			</td>
        		</tr>
        	<?php endforeach;?>
			<?php else:?>
				<tr>
           			<td>
			            <?php echo CHtml::dropDownList('dataBaseName[0]', '', Configuration::getDbNamesConfig(), array('onchange' => 'getDataBaseTables(this,0);')); ?>
        				<span>
        				<?php echo CHtml::listBox('dataBaseTableName[0][]', '', UebModel::model('ExcelSchemeType')->getSchemaTableNameArr('db'), array(
										'data-placeholder'     => Yii::t('system', 'Please Select'),
										'class'                => 'chosen-select',
										'style'                => 'width:350px;',
										'multiple'             => 'multiple',
										'options'              => '',
									)
							   );
        				?>
        				</span>
        				<a onclick="deleteTr(this);" href="javascript:void(0);" align="right"><?php echo Yii::t('system', 'Delete')?></a>
        			</td>
        		</tr>
			<?php endif;?>
        	</table>
        	
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
$('.chosen-select').chosen({});

var getDataBaseTables = function(obj,id){
	var $p = navTab.getCurrentPanel();
	$.ajax({
	       type: "post",
	       url: "/systems/ExcelSchemeType/getDataBaseTables",
	       data: {dbkey: obj.value,
                  id: id},
	       async: false,   
	       dataType:'html',
	       success: function(data) {
	          if ( data ) {                
	        	  $(obj,$p).next().html(data);
	          }
	       }
	});  
}

var index = parseInt('<?php echo $num;?>');

var addDataBaseTableTr = function(obj){
	index++;
	var $p = navTab.getCurrentPanel();
	if($('select[name^="dataBaseName"]').length >= 5){
		alertMsg.warn($.regional.excel.msg.dataBaseIsFullDonotAddAgain);
		return false;
	}
	var dataBaseName = [];
	$("select[id^='dataBaseName_']").each(function(k,v){
				dataBaseName[k] = $(v).val();
		});
			
	$.ajax({
	       type: "post",
	       url: "/systems/ExcelSchemeType/getDataBaseTr",
	       data: {index: index,
	    	      dataBaseName: dataBaseName},
	       async: false,   
	       dataType: 'html',
	       success: function(data) {
	          if ( data ) {               
	        	  $('#tableData', $.pdialog.getCurrent()).append(data);
	          }
	       }
	   });
}

var deleteTr = function(obj){
	var $p = navTab.getCurrentPanel();
	 $(obj, $p).parent().parent().remove();
}

var checkData = function(){
	var $p = navTab.getCurrentPanel();
	var empty = false;
	$('select[name^="dataBaseTableName"]', $p).each(function(i) { 
		if($(this).val()==null){
			empty = true;
		}
	})
	if(empty){
		$('#checkDiv', $p).show();
		$('#checkDiv', $p).html($.regional.excel.msg.dataBaseTableCannotBeEmpty);
		return false;
	}else{
		$('#checkDiv', $p).hide();
	 	return true;
	}
}
</script>
