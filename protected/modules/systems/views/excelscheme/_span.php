<span class="tx-cen" id="leftListTable"  >
        <div class="tbox" >
            <table width="100%"> 
                <thead class="bg5">     
                    <tr class="hide ">
                        <th style="text-align:center"></th>   
                    </tr>
                </thead> 
                <?php $key = 0;?>
<?php if (! empty($schemeNameArr)):?>
<?php foreach ($schemeNameArr as $key => $val):?>
                <tr class="bg4" rowuuid="138<?php echo $key?>" onclick="changeColor();" onmouseover="mOver(this);" onmouseout="mOut(this);">    
                    <td>  
                        <div style="padding:15px 5px;margin:5px 3px;word-wrap:break-word;" class="colAlign-left">
                            <div class="left h30 mt2">
                                <input type="radio" id="radio<?php echo $key?>" name="excelList" class="mr5 left mt3" onclick="createListBox(id);">
                                <span class="spanText" id="spanText<?php echo $key?>" onclick="spanClick(id);"><?php echo $val['scheme_name']?></span>
                                <span class=""></span>
                                <input type="hidden" id="inputSchemeId<?php echo $key?>" value="<?php echo $val['id']?>" />
                                <input type="hidden" id="is_report_<?php echo $key?>" value="<?php echo $val['is_report']?>" />
                                <input type="hidden" id="main_model_<?php echo $key?>" value="<?php echo $val['main_model']?>" />
                            </div>
                            <div class="right mt2">
                                <span class="t45 mr3" title="<?php echo Yii::t('commom', 'Copy')?>" id="copyBtn<?php echo $key?>" onclick="copySchemeType(id);"></span>
                                <span class="t216 mr3" title="<?php echo Yii::t('commom', 'Save')?>" id="saveBtn_<?php echo $key?>" style="display:none" onclick="saveText(id);"></span>
                                <span class="t15" title="<?php echo Yii::t('commom', 'Delete')?>" onclick = "deleteArr(this);"></span>
                            </div>
                            
                            <div class="clear"></div>
                            
                        </div>  
                    </td>   
                </tr>
<?php endforeach;?>
<?php else:?>
 				<tr class="bg4" rowuuid="1380" onclick="changeColor();" onmouseover="mOver(this);" onmouseout="mOut(this);">    
                    <td>  
                        <div style="padding:15px 5px;margin:5px 3px;word-wrap:break-word;" class="colAlign-left">
                            <div class="left h30 mt2">
                                <input type="radio" id="radio0" name="excelList" class="mr5 left mt3" onclick="createListBox(id);">
                                <span class="spanText" id="spanText0" onclick="spanClick(id);"></span>
                                <span class=""><input type="text" id="inputText0" onblur="inputBlur(this);"></span>
                             	<input type="hidden" id="inputSchemeId0" value="" />
                                <input type="hidden" id="is_report_0" value="0" />
                                <input type="hidden" id="main_model_0" value="" />
                            </div>
                            <div class="right mt2">
                                <span class="t45 mr3" title="<?php echo Yii::t('commom', 'Copy')?>" id="copyBtn0" onclick="copySchemeType(id);"></span>
                                <span class="t216 mr3" title="<?php echo Yii::t('commom', 'Save')?>" id="saveBtn_0" style="display:none" onclick="saveText(id);"></span>
                                <span class="t15" title="<?php echo Yii::t('commom', 'Delete')?>" onclick = "deleteArr(this);"></span>
                            </div>
                            <div class="clear"></div>
                        </div>  
                    </td>   
                </tr>
<?php endif;?>
            </table> 
        </div>
</span>

<script>
var index = parseInt('<?php echo $key;?>');
var createExcelTemplate = function(obj) {
	var $p = navTab.getCurrentPanel();
   index++;
   if($('#leftListTable :text', $p).val()==''){
	   alertMsg.warn($.regional.excel.msg.pleaseFinishFirst);
		return false;
   }
   $.ajax({
       type: "post",
       url: "/systems/excelscheme/getExcelTemplateAttr",
       data: {index: index,schemeTypeId: <?php echo $schemeTypeId;?>},
       async: false,   
       dataType:'html',
       success: function(data) {
          if ( data ) {                  
        	  $('#leftListTable', $p).find('table').append(data);
        	  $('#leftListTable tr:last :text', $p).focus();
          }
       }
   });               
}  


function checkReport(obj){
	if(obj.checked==true){
		$("#mainTable").show();
	}else{
		$("#mainTable").hide();
	}
}

function isDefaultDate(obj){
	$(obj).parent().next().hide();
	$(obj).parent().next().next().hide();
	if(obj.value == '<?php echo ExcelSchemeColumn::_DATETIME;?>'){
		$(obj).parent().next().show();
	}else if(obj.value == '<?php echo ExcelSchemeColumn::_CHECKBOX;?>' || obj.value == '<?php echo ExcelSchemeColumn::_SELECT;?>'){
		$(obj).parent().next().next().show();
	}else{
		$(obj).parent().next().hide();
	}
}

function checkCalculate(obj,k){
	var flag = false;
	if(obj.checked == true && obj.value == '<?php echo ExcelSchemeColumn::_IS_GROUP;?>'){
		$("input[id='filed_"+k+"']").attr("checked",true);
		$("input[id='condition_"+k+"']").attr("disabled",false);
			$("td[id='calculate_"+k+"'] input").each(function(k,v){
					if($(v).val() == '<?php echo ExcelSchemeColumn::_IS_GROUP;?>'){
							$(v).attr('disabled',false);
						}else{
							$(v).attr('disabled',true);
						}
				});
	}
	if(obj.checked == false && obj.value == '<?php echo ExcelSchemeColumn::_IS_GROUP;?>'){
		$("td[id='calculate_"+k+"'] input").each(function(k,v){
				if($(v).val() == '<?php echo ExcelSchemeColumn::_IS_HAVING;?>'){
					$(v).attr('disabled',true);
				}else{
					$(v).attr('disabled',false);
				}
			});
	}
	if(obj.checked == true && obj.value != '<?php echo ExcelSchemeColumn::_IS_GROUP;?>'){
		$("#data_type_div_"+k).hide();
		$("#data_type_div_"+k).next().hide();
		$("input[id='condition_"+k+"']").attr("disabled",true);
		$("td[id='calculate_"+k+"'] input").each(function(k,v){
			
				if($(v).val() == '<?php echo ExcelSchemeColumn::_IS_GROUP;?>'){
						$(v).attr('disabled',true);
					}else{
						$(v).attr('disabled',false);
					}
			});
	}
	if(obj.checked == false && obj.value != '<?php echo ExcelSchemeColumn::_IS_GROUP;?>'){
		$("td[id='calculate_"+k+"'] input").each(function(k,v){
			if($(v).attr('checked') == 'checked' && $(v).val() != '<?php echo ExcelSchemeColumn::_IS_HAVING;?>')
			{
				flag = true;
			}
			});
		if(flag == false){
				$("td[id='calculate_"+k+"'] input").attr('disabled',false);
				$("input[id='condition_"+k+"']").attr("disabled",false);
				$("td[id='calculate_"+k+"'] input[value='<?php echo ExcelSchemeColumn::_IS_HAVING;?>']").attr('disabled',true);
			}
	}
}

var mOver = function(obj){
	$(obj).addClass('tboxHover');
}

var mOut = function(obj){
	$(obj).removeClass('tboxHover');
}

var copySchemeType = function(id){
	var $p = navTab.getCurrentPanel();
	var index = id.substr(-1,1);
	var schemeName = $('#spanText'+index, $p).html();
	if(schemeName==''){
		   alertMsg.warn($.regional.excel.msg.pleaseFinishFirst);
			return false;
	}
	var lastIndex = $('#leftListTable table tr:last :radio', $p).attr('id').substr(-1,1);
	var newIndex = lastIndex*1 + 1; 
	$.ajax({
	       type: "post",
	       url: "/systems/excelscheme/getExcelTemplateAttr",
	       data: {index: newIndex},
	       async: false,   
	       dataType:'html',
	       success: function(data) {
	          if ( data ) {                  
	        	  $('#leftListTable', $p).find('table').append(data);
	        	  $('#leftListTable tr:last :text', $p).val(schemeName+' <?php echo Yii::t('system', 'Transcript')?>');
	        	  $('#leftListTable tr:last :text', $p).focus();
				  $('#radio'+newIndex, $p).attr('checked','checked');
	        	  createListBox(id);
	        	  $($("#"+id).next()).hide();
	        	  $('#leftListTable tr:last', $p).find("span[id^='saveBtn_']").show();
	          }
	       }
	   }); 
}


</script>
