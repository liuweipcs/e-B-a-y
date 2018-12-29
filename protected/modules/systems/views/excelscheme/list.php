<?php Yii::app()->clientscript->scriptMap['jquery.js'] = false; 

$form = $this->beginWidget('ActiveForm', array(
    'id' => 'ColumnForm',
    'enableAjaxValidation' => false,  
    'enableClientValidation' => true,
));
?>
<div class="bg-white ofa h500 tbox">
    <div class="mb7 ">
       <div class="bg12 dot2 pdtb3 text-align-right">
          <span class="dbl left fcb"><?php echo Yii::t('system', 'Fixed bar')?></span>
          <a id="fistAddOne" onclick="createNewRow(this)" class="dbl right tdu" href="javascript:void(0)">添加一列</a>&nbsp;
       </div>
       <div id="fixedColumnDiv">
            <span class="tx-cen" xtype="gridpanel" id="fixedColumnTable">
                <div class="tbox"> 
                  <form action="" method="post" name="ColumnForm" id="ColumnForm">
                        <input type="hidden" name="schemeId" id="schemeId" value="<?php echo $schemeId;?>"/>
                  		<input type="hidden" name="schemeType" id="schemeType" value="<?php echo $schemeType;?>"/>
                        <input type="hidden" name="schemeName" id="schemeName" value=""/>
                    <div class="dataintable" style="width:100%;padding:30px 0px;">
                    	<div id="is_report_div" style="float:left;">
                    		<?php //echo CHtml::checkBox('is_report', $is_report?true:false, array('onclick'=>'checkReport(this)'));?>
                    		<?php echo CHtml::checkBox('is_report', $is_report?true:false);?>
                    		 <?php echo Yii::t('system', 'Is to report')?>
                    	</div>
                    	<div id="mainTable" style="float:left;padding-left:15px;display:<?php echo $is_report?'block;':'block;'?>">
                        选择主表:<?php echo CHtml::dropDownList('main_table', $main_model, UebModel::model('ExcelSchemeTypeMap')->getTableNamesByTypeId($schemeType), array('empty' => Yii::t('system', 'Select main model'))); ?>
                        </div>
                    </div>
                    <table class="dataintable" style="width:100%;">
                   
                        <thead class="bg5">     
                            <tr>
                                <th style="text-align: center; width: 50px;"><?php echo Yii::t('system', 'Order')?></th>    
                                <th style="text-align: center; width: 100px;"><?php echo Yii::t('system', 'Column title')?></th>    
                                <th style="text-align: center" class="hide"><?php echo Yii::t('system', 'Column title')?></th>    
                                <th style="text-align: center" class="hide"><?php echo Yii::t('system', 'Data resource')?></th>    
                                <th style="text-align: center" class="hide"><?php echo Yii::t('system', 'Fetch type')?></th>    
                                <th style="text-align: center" class="hide"><?php echo Yii::t('system', 'Fetch Column')?></th>    
                                <th style="text-align: center; width: 200px;"><?php echo Yii::t('system', 'Fetch data')?></th>  
                                <th style="text-align: center; width: 200px;"><?php echo Yii::t('system', 'Column expression')?></th> 
                                <th style="text-align: center; width: 150px;"><?php echo Yii::t('system', 'Report condition')?></th> 
                                <th style="text-align: center; width: 150px;"><?php echo Yii::t('system', 'Report values')?></th>
                                <th style="text-align: center; width: 150px;"><?php echo Yii::t('system', 'Report calculate')?></th>  
                                <th style="text-align: center; width: 50px;"><?php echo Yii::t('system', 'Column is longer')?></th>   
                                <th style="text-align: center; width: 80px;"><?php echo Yii::t('system', 'Operation')?></th>   
                            </tr>
                        </thead> 
<?php $key = 1;?>
<?php if (! empty($data)):?>
<?php foreach ($data as $key => $val):
		$key++;?>
                        <tr class="bg4" rowuuid="1381" onclick="changeTrColor();" onmouseover="mOver(this);" onmouseout="mOut(this);">     
                            <td>  
                                <div title="1" style="padding:3px 5px;white-space:nowrap;text-overflow:ellipsis;-o-text-overflow:ellipsis;overflow:hidden;width:50px;" class="colAlign-center"><input id="num_<?php echo $val['column_order']?>" class="titleSpan noBorder" type="number" name="titleSpan[<?php echo $val['column_order']?>]" value="<?php echo $val['column_order']?>"/></div>    
                            </td>    
                            <td>  <div title="" style="padding:3px 5px;white-space:nowrap;text-overflow:ellipsis;-o-text-overflow:ellipsis;overflow:hidden;width:150px;" class="colAlign-left">
                           
                            <input type="text"  name="inputTitle[<?php echo $val['column_order']?>]" id="inputTitle_<?php echo $val['column_order']?>" value="<?php echo $val['column_title']?>">
                            
                            </div>  
                            </td>    
                            <td>  
                                <div title="" style="padding:3px 5px;white-space:nowrap;text-overflow:ellipsis;-o-text-overflow:ellipsis;overflow:hidden;width:250px;" class="colAlign-left">
	                               
										<?php if(!empty($val['table_name'])){
	                                    		$hiddenValue = $val['column_field'].'+'.$val['table_name'].'+'.$val['column_id'];
	                                 	   }else{
												$hiddenValue = $val['column_field'];
										   }
										  //print_r($data);
										   if(is_numeric($val['column_field'])){
												$row = CHtml::textField('', $val['column_field'], array('class' => 'w120','onblur' => 'valueBlur(this);'));
												$fixedSelected = 'selected="true"';
												$spanValue = $val['column_field'];
											}else if(!empty($val['column_expression'])){
												$row = '';
												$expreSelected = 'selected="true"';
												$spanValue = Yii::t('system', 'Column expression');
											}else if($val['column_field']==''){
												$row = '';
												$emptySelected = 'selected="true"';
												$spanValue = Yii::t('system', 'Keep empty');
											}else{
												$systemData = UebModel::model('ExcelDefaultColumn')->getColumnFieldsByColumnType($schemeType);
												//print_r($systemData);
												//exit;
												$row = CHtml::dropDownList('', $hiddenValue, $systemData, array('empty' => Yii::t('system', 'Please Select'), 'class'=>'np t_combo_box','onchange'=>'valueChange(this,this.options[this.options.selectedIndex].text,this.options[this.options.selectedIndex].value);'));
												$dataSelected = 'selected="true"';
												$spanValue = $systemData[$hiddenValue];
											}
                                    ?>
                                    <input id="value_<?php echo $val['column_order']?>"  name="valueSpan[<?php echo $val['column_order']?>]" class="valueSpan hide noBorder"  value="<?php echo $hiddenValue?>" type="hidden">
                                    <span id="spanShow_<?php echo $val['column_order']?>" onclick="spanValueClick(id);"><?php echo $spanValue?></span>
                                    <span class=" mr3 hide">
                                  
	                                    <select id="select_<?php echo $val['column_order']?>" name="" class="np t_combo_box " onchange="selectDataChange(this,id);">
		                                    <option id="" value="systemData" <?php echo isset($dataSelected) ? $dataSelected : '';?>><?php echo Yii::t('system', 'System data')?></option>
		                                    <option id="" value="fixedValue" <?php echo isset($fixedSelected) ? $fixedSelected : '';?>><?php echo Yii::t('system', 'Fixed value')?></option>
		                                    <option id="" value="empty" <?php echo isset($emptySelected) ? $emptySelected : '';?>><?php echo Yii::t('system', 'Keep empty')?></option>
		                                    <option id="" value="empty" <?php echo isset($expreSelected) ? $expreSelected : '';?>><?php echo Yii::t('system', 'Column expression')?></option>
	                                    </select>
                                    </span>
                                    <span id="selectSpan_<?php echo $val['column_order']?>" class="hide">
                                    	<?php echo $row;?>
                                    </span>
                                </div>  
                            </td>    
                            <td>
                            	<textarea id="expression_<?php echo $val['column_order']?>" class="textInput" cols="30"  rows="" name="expressionText[<?php echo $val['column_order']?>]"  ><?php echo $val['column_expression']?></textarea>
                            </td> 
                            <td>
                            	
                            	<input id="condition_<?php echo $key?>" value="1" type="checkbox" onclick="checkCondition(this,<?php echo $key?>)" name="condition[<?php echo $key?>]" <?php echo $val['is_condition']=='1' ? 'checked= checked' : '' ;?>/>
                            	<?php echo Yii::t('system', 'Is condition for report')?>
                            	
                            	<div id="data_type_div_<?php echo $key?>" style="display:<?php echo $val['is_condition']=='1'?'block':'none'; ?>;margin:8px 3px">
                            		<?php echo CHtml::dropDownList('data_type['.$key.']', $val['data_type'], $model->getDataType(),array('onchange'=>'isDefaultDate(this)')); ?> 
                            	</div>
                            	<div style="display:<?php echo $val['data_type'] == ExcelSchemeColumn::_DATETIME ? 'block' : 'none';?>;margin:8px 3px">
                            		<?php echo CHtml::dropDownList('default_date['.$key.']', $val['default_date'], $model->getDefaultDate()); ?>
								</div>
                            	<div style="display:<?php echo ($val['data_type'] == ExcelSchemeColumn::_CHECKBOX || $val['data_type'] == ExcelSchemeColumn::_SELECT) ? 'block' : 'none';?>;margin:8px 3px">
                            		<?php echo Yii::t('system', 'Init Value'); ?>: <?php echo CHtml::textField('default_value['.$key.']', $val['default_value'], array('size'=>'10')); ?>
								</div>
                            </td> 
                            <td>
                            	<input id="filed_<?php echo $key?>" value="1" type="checkbox" name="filed[<?php echo $key?>]" <?php echo $val['is_value']=='1' ? 'checked= checked' : '' ;?>/>
                            	<?php echo Yii::t('system', 'Is filed for report')?>
                            </td> 
                            <td id="calculate_<?php echo $key;?>">
                            	<?php foreach ($model->getCalculateType() as $k=>$v):?>
                            	<?php 
                            		$disabled[$k] = true;
                            		$checked[$k] = in_array($k,explode(",", $val['calculate_type']))?true:false;
                            		if(in_array(ExcelSchemeColumn::_IS_GROUP,explode(",", $val['calculate_type']))){
                            			$disabled[ExcelSchemeColumn::_IS_GROUP] = false;
                            			$disabled[ExcelSchemeColumn::_IS_HAVING] = true;
                            		}elseif($val['calculate_type']){
                            			$disabled[$k] = false;
                            			$disabled[ExcelSchemeColumn::_IS_GROUP] = true;
                            			$disabled[ExcelSchemeColumn::_IS_HAVING] = false;
                            		}else{
                            			$disabled[$k] = false;
                            			$disabled[ExcelSchemeColumn::_IS_HAVING] = true;
                            		}
                            	?>
                            	<?php echo CHtml::checkBox("calculate[$key][]", $checked[$k], array('value' =>$k,'onclick'=>"checkCalculate(this,$key)",'disabled'=>$disabled[$k],'id' =>'calculate_'.$k.'_'.$key)) . $v;?>
                            	<br />
                            	<?php endforeach;?>
                            	
                            </td>
                            <td>
                            	<input id="longer_<?php echo $val['column_order']?>" type="checkbox" name="longerInput[<?php echo $val['column_order']?>]" <?php echo $val['column_is_longer']=='1' ? 'checked= checked' : '' ;?>/>
                            </td>
                            <td style="text-align: right;">
	                            <span class="t181 vem mr3" title="<?php echo Yii::t('commom', 'Upgrade')?>" onclick="moveUp(this);"></span>
								<span class="t182 vem mr3" title="<?php echo Yii::t('commom', 'Degradation')?>" onclick="moveDown(this);"></span>
                                <span class="t15 vem mr10" title="<?php echo Yii::t('commom', 'Delete')?>" onclick="deleteFixedColumnArr(this);"></span>
                            </td>   
                        </tr>
<?php endforeach;?>
<?php else:?>
 						<tr class="bg4" rowuuid="1381" onclick="changeTrColor();" onmouseover="mOver(this);" onmouseout="mOut(this);">     
                            <td>  
                                <div title="1" style="padding:3px 5px;white-space:nowrap;text-overflow:ellipsis;-o-text-overflow:ellipsis;overflow:hidden;width:50px;" class="colAlign-center"><input id="num_1" class="titleSpan noBorder" type="number" name="titleSpan[1]" value="1"/></div>  
                            </td>    
                            <td>  
	                            <div title="" style="padding:3px 5px;white-space:nowrap;text-overflow:ellipsis;-o-text-overflow:ellipsis;overflow:hidden;width:150px;" class="colAlign-left">
	                         
	                            <input type="text"  name="inputTitle[1]" id="inputTitle_1" >
	                            
	                            </div>  
                            </td>         
                            <td>  
                                <div title="" style="padding:3px 5px;white-space:nowrap;text-overflow:ellipsis;-o-text-overflow:ellipsis;overflow:hidden;width:250px;" class="colAlign-left">
                                    
                                    <input id="value_1"  name="valueSpan[1]" class="valueSpan hide noBorder"  value="" type="hidden">
                                    <span id="spanShow_1" onclick="spanValueClick(id);"></span>
                                    <span class=" mr3">
                                    <select id="select_1" name="" class="np t_combo_box " onchange="selectDataChange(this,id);">
	                                    <option id="" value="systemData"><?php echo Yii::t('system', 'System data')?></option>
	                                    <option id="" value="fixedValue"><?php echo Yii::t('system', 'Fixed value')?></option>
	                                    <option id="" value="empty" selected="true"><?php echo Yii::t('system', 'Keep empty')?></option>
	                                    <option id="" value="empty"><?php echo Yii::t('system', 'Column expression')?></option>
                                    </select>
                                    </span>
                                    <span id="selectSpan_1"></span>
                                </div>  
                            </td>    
                            <td>
                            	<textarea id="expression_1" class="textInput" cols="30"  rows="" name="expressionText[1]"  ></textarea>
                            </td>
                            
                            <td>
                            	
                            	<input id="condition_1" value="1" type="checkbox" onclick="checkCondition(this,1)" name="condition[1]" />
                            	<?php echo Yii::t('system', 'Is condition for report')?>
                            	
                            	<div id="data_type_div_1" style="display:none;margin:8px 3px">
                            		<?php echo CHtml::dropDownList('data_type[1]', 0, $model->getDataType(),array('onchange'=>'isDefaultDate(this)')); ?> 
                            	</div>
                            	<div style="display:none;margin:8px 3px">
                            		<?php echo CHtml::dropDownList('default_date[1]', 0, $model->getDefaultDate()); ?>
                            	</div>
                            </td> 
                            <td>
                            	<input id="filed_1" value="1" type="checkbox" name="filed[1]" />
                            	<?php echo Yii::t('system', 'Is filed for report')?>
                            </td> 
                            <td id="calculate_1">
                            	<?php foreach ($model->getCalculateType() as $k=>$v):?>
                            	<?php if($k == ExcelSchemeColumn::_IS_HAVING):$disabled = true;else :$disabled = false;endif;?>
                            	<?php echo CHtml::checkBox("calculate[1][]", 0, array('value' =>$k,'disabled'=>$disabled,'onclick'=>"checkCalculate(this,1)",'id' =>'calculate_'.$k.'_1')) . $v;?>
                            	<br />
                            	<?php endforeach;?>
                            </td>
                            
                            <td>
                            	<input id="longer_1" type="checkbox" name="longerInput[1]" />
                            </td>
                            <td style="text-align: right;">
	                            <span class="t181 vem mr3" title="<?php echo Yii::t('commom', 'Upgrade')?>" onclick="moveUp(this);"></span>
								<span class="t182 vem mr3" title="<?php echo Yii::t('commom', 'Degradation')?>" onclick="moveDown(this);"></span>
                                <span class="t15 vem mr10" title="<?php echo Yii::t('commom', 'Delete')?>" onclick="deleteFixedColumnArr(this);"></span>
                            </td>   
                        </tr>
<?php endif;?>
                    </table> 
                   </form>
                </div>                      
            </span>
        </div>
        <div class="clear"></div>
    </div>
   
 </div>
 <?php $this->endWidget(); ?>
 <script>
var inputTitleBlur = function(obj){
	var $p = navTab.getCurrentPanel();
	var inputId = obj.id;
	var index = inputId.substr(-1,1);
	var inputText = $(obj).val();
	if($(obj).val()==''){
		alertMsg.error($.regional.excel.msg.columnTitleCannotBeEmpty);
		return false;
	}else{
		var bgColorRGB = getBackgroundColor(obj);
		var colorCode = bgColorRGB.colorHex();
		$(obj).css({"background-Color": colorCode,
					'border':'0 solid #99BBE8'});
	}
}

function checkCondition(obj,k){
	if(obj.checked==true){
		$("#data_type_div_"+k).show();
		$("td[id='calculate_"+k+"'] input[value!='<?php echo ExcelSchemeColumn::_IS_GROUP;?>']").attr("disabled",true);
	}else{
		$("#data_type_div_"+k).hide();
		$("td[id='calculate_"+k+"'] input").attr("disabled",false);
	}
}

var inputTitleClick = function(obj){
	$(obj).css({"background-Color":'white',
				'border':'1px solid #99BBE8'});
}

var mOver = function(obj){
	$(obj).addClass('tboxHover');
}

var mOut = function(obj){
	$(obj).removeClass('tboxHover');
}

var $p = navTab.getCurrentPanel();
var changeTrColor = function(){
    $('#fixedColumnTable tr', $p).each(function(i) {     
        $(this).click(function(e) {                
            $('.tboxSelect', $p).attr( 'class', 'bg4');                
            $(this).attr('class', 'tboxSelect');              
        });           
    });
}

var getBackgroundColor = function(obj) {
	var objParent = $(obj).parent();
    var parentColor = objParent.css("background-Color");
    if(parentColor=='transparent'){
    	return getBackgroundColor(objParent);
    }else{
    	var bgColor = parentColor.colorHex();
    	return bgColor;
    }
}

var selectDataChange = function(obj,id){
	var $p = navTab.getCurrentPanel();
	var schemeType = $('#type_name'+' option:selected', $p).val();
	//var index = id.substr(-1,1);
	var index = id.replace('select_', '');
	$.ajax({
	       type: "post",
	       url: "/systems/excelscheme/getSelectDataByValue",
	       data: {value: obj.value,
	    	      columnType: schemeType},
	       async: false,   
	       dataType:'json',
	       success: function(data) {
	          if ( data.status ) {                
	        	  $('#selectSpan_'+index, $p).html(data.content);
	          }
	       }
	   });  
}

var spanValueClick = function(id){
	var $p = navTab.getCurrentPanel();
	$('#'+id, $p).hide();
	$('#'+id, $p).html('');
	$('#'+id, $p).next().removeClass('hide');
	$('#'+id, $p).next().show();
	$('#'+id, $p).next().next().removeClass('hide');
	$('#'+id, $p).next().next().show();	
}

var valueChange = function(obj,text,value){
	$(obj).parent().hide();
	$(obj).parent().prev().hide();
	$(obj).parent().prev().prev().show();
	$(obj).parent().prev().prev().html(text);
	$(obj).parent().prev().prev().prev().val(value);
}

var valueBlur = function(obj){
	var value = (obj.value=='') ? '<?php echo Yii::t('system', 'Fixed Value Is Empty');?>' : obj.value;
	$(obj).parent().hide();
	$(obj).parent().prev().hide();
	$(obj).parent().prev().prev().show();
	$(obj).parent().prev().prev().html(value);
	$(obj).parent().prev().prev().prev().val(value);
}

var deleteFixedColumnArr = function(obj){
	var curr = $(obj).parent().parent();
	var next = curr.nextAll();
	var pt;
	$(obj).parent().parent().remove();
	index1--;
	//更改删除行以下行下标值
	next.each(function(i,k){
		 pt = $(k).find('input :first').attr('id').substr(-1,1) - 1;
		 changePoint($(k),pt);
	});
}

var index1 = parseInt('<?php echo $key?>');
var createNewRow = function(obj) {
	if($("#leftListTable :radio:checked").size() < 1) {
		alertMsg.error('<?php echo Yii::t('system', 'Please Select Templet'); ?>');
		return false;
	}
	var $p = navTab.getCurrentPanel();
   index1++;
   if($('#fixedColumnDiv :text', $p).val()==''){
	   alertMsg.warn($.regional.excel.msg.pleaseFinishFirst);
		return false;
   }
   $.ajax({
       type: "post",
       url: "/systems/excelscheme/getFixedColumnAttr",
       data: {index: index1},
       async: false,   
       dataType: 'html',
       success: function(data) {
          if ( data ) {                  
        	  $('#fixedColumnDiv', $p).find('table').append(data);
        	  $('#fixedColumnDiv tr:last :text:first', $p).focus();
          }
       }
   });               
}  

var moveUp = function (obj) {  
    var current=$(obj).parent().parent();  
    var prev=current.prev();  
    if(current.index()>=1) {  
        var point = current.find('input :first').attr('id').replace('num_','');
        var newPoint = point - 1;
      	//当前行改下标值
        changeCurrentObj(obj, newPoint);
      	//上一行改下标值
        changeObjData(obj, point, 'prevObj');
        current.insertBefore(prev);
    }  
}  

var moveDown = function (obj) {  
    var current=$(obj).parent().parent();  
    var next=current.next();
    if(next.length > 0) {  
    	var point = current.find('input :first').attr('id').replace('num_','');
        var newPoint = point*1 + 1;
		//当前行改下标值
		changeCurrentObj(obj, newPoint);
        //下一行改下标值
		changeObjData(obj, point, 'nextObj');
        current.insertAfter(next);  
    }  
}  

var changeCurrentObj = function(obj, newPoint){
	var current = $(obj).parent().parent();


	changePoint(current,newPoint);

}

var changeObjData = function(obj, point, check){
	var current=$(obj).parent().parent(); 
	if(check=='prevObj'){
		var currentObj = current.prev();
	}else if(check=='nextObj'){
		var currentObj = current.next();
	}

	changePoint(currentObj,point);

}

//更改指定行下标值

function changePoint(currentObj,point){
	//order
	$(currentObj).find("input[id^=num_]").attr('id','num_'+point);
	$(currentObj).find("input[id^=num_]").attr('name','titleSpan['+point+']');
	$(currentObj).find("input[id^=num_]").attr('value',point);
	//title
	$(currentObj).find("input[id^=inputTitle_]").attr('id','inputTitle_'+point);
	$(currentObj).find("input[id^=inputTitle_]").attr('name','inputTitle['+point+']');
	//value
	$(currentObj).find("input[id^=value_]").attr('id','value_'+point);
	$(currentObj).find("input[id^=value_]").attr('name','valueSpan['+point+']');
	$(currentObj).find("input[id^=spanShow_]").attr('id','spanShow_'+point);
	$(currentObj).find("input[id^=select_]").attr('id','select_'+point);
	$(currentObj).find("input[id^=selectSpan_]").attr('id','selectSpan_'+point);
	//expresion
	$(currentObj).find("textarea[id^=expression_]").attr('id','expression_'+point);
	$(currentObj).find("textarea[id^=expression_]").attr('name','expressionText['+point+']');
	//condition condition_
	$(currentObj).find("input[id^=condition_]").attr('id','condition_'+point);
	$(currentObj).find("input[id^=condition_]").attr('name','condition['+point+']');
	$(currentObj).find("input[id^=condition_]").attr('onclick','checkCondition(this,'+point+')');
	$(currentObj).find("div[id^=data_type_div_]").attr('id','data_type_div_'+point);
	$(currentObj).find("select[id^=data_type_]").attr('id','data_type_'+point);
	$(currentObj).find("select[id^=data_type_]").attr('name','data_type['+point+']');
	$(currentObj).find("select[id^=default_date_]").attr('id','default_date_'+point);
	$(currentObj).find("select[id^=default_date_]").attr('name','default_date['+point+']');
	//fileds
	$(currentObj).find("input[id^=filed_]").attr('id','filed_'+point);
	$(currentObj).find("input[id^=filed_]").attr('name','filed['+point+']');
	//calculate
	$(currentObj).children("td[id^=calculate_]").attr('id','calculate_'+point);
	$(currentObj).find("input[name^=calculate]").attr('name','calculate['+point+'][]');
	$(currentObj).find("input[name^=calculate]").attr('onclick','checkCalculate(this,'+point+')');
	//autoimcrement
	$(currentObj).find("input[id^=longer_]").attr('id','longer_'+point);
	$(currentObj).find("input[id^=longer_]").attr('name','longer['+point+']');
}


</script>
 
 