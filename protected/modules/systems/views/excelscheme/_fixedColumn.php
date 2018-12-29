<tr class="bg4" rowuuid="138<?php echo $index ?>" onclick="changeTrColor();" onmouseover="mOver(this);" onmouseout="mOut(this);">
        <td>
    		<div title="<?php echo $index ?>" style="padding:3px 5px;white-space:nowrap;text-overflow:ellipsis;-o-text-overflow:ellipsis;overflow:hidden;width:50px;" class="colAlign-center"><input id="num_<?php echo $index ?>" class="titleSpan noBorder" type="number" name="titleSpan[<?php echo $index ?>]" value="<?php echo $index ?>"/></div>
    	</td>
    	<td> 
    		<div title="" style="padding:3px 5px;white-space:nowrap;text-overflow:ellipsis;-o-text-overflow:ellipsis;overflow:hidden;width:150px;" class="colAlign-left">
    			<input type="text"  name="inputTitle[<?php echo $index ?>]"  id="inputTitle_<?php echo $index ?>">
    		</div>
    	</td>
    	<td>
    		<div title="" style="padding:3px 5px;white-space:nowrap;text-overflow:ellipsis;-o-text-overflow:ellipsis;overflow:hidden;width:250px;" class="colAlign-left">
		    	<input id="value_<?php echo $index ?>"  name="valueSpan[<?php echo $index ?>]" class="valueSpan hide noBorder" type="hidden" value="">
                <span id="spanShow_<?php echo $index ?>" onclick="spanValueClick(id);"></span>
		    	<span class=" mr3">
			    	<select id="select_<?php echo $index ?>" name="" class="np t_combo_box " onchange="selectDataChange(this,id);">
				    	<option id="" value="systemData"><?php echo Yii::t('system', 'System data')?></option>
	                    <option id="" value="fixedValue"><?php echo Yii::t('system', 'Fixed value')?></option>
	                    <option id="" value="empty" selected="true"><?php echo Yii::t('system', 'Keep empty')?></option>
	                    <option id="" value="empty" ><?php echo Yii::t('system', 'Column expression')?></option>
			    	</select>
		    	</span>
    			<span id="selectSpan_<?php echo $index ?>"></span>
    		</div>
    	</td>
    	<td>
            <textarea id="expression_<?php echo $index ?>"  class="textInput" cols="30" rows="" name="expressionText[<?php echo $index ?>]"  ></textarea>
        </td>
        
                            <td>
                            	
                            	<input id="condition_<?php echo $index ?>" value="1" type="checkbox" onclick="checkCondition(this,<?php echo $index?>)" name="condition[<?php echo $index?>]" />
                            	<?php echo Yii::t('system', 'Is condition for report')?>
                            	
                            	<div id="data_type_div_<?php echo $index?>" style="display:none;margin:8px 3px">
                            		<?php echo CHtml::dropDownList('data_type['.$index.']', 0, $model->getDataType(),array('onchange'=>'isDefaultDate(this)')); ?> 
                            	</div>
                            	<div style="display:none;margin:8px 3px">
                            		<?php echo CHtml::dropDownList('default_date['.$index.']', 0, $model->getDefaultDate()); ?>
                            	</div>
                            	<div style="display:none;margin:8px 3px">
                            		<?php echo Yii::t('system', 'Init Value'); ?>: <?php echo CHtml::textField('default_value['.$index.']', '', array('size'=>'10')); ?>
								</div>
                            </td> 
                            <td>
                            	<input id="filed_<?php echo $index ?>" value="1" type="checkbox" name="filed[<?php echo $index?>]" />
                            	<?php echo Yii::t('system', 'Is filed for report')?>
                            </td> 
                            <td id="calculate_<?php echo $index;?>">
                            	<?php foreach ($model->getCalculateType() as $k=>$v):?>
                            	<?php if($k == ExcelSchemeColumn::_IS_HAVING):$disabled = true;else :$disabled = false;endif;?>
                            	<?php echo CHtml::checkBox("calculate[$index][]", 0, array('value' =>$k,'disabled'=>$disabled,'onclick'=>"checkCalculate(this,$index)",'id' =>'calculate_'.$k.'_1')) . $v;?>
                            	<br />
                            	<?php endforeach;?>
                            	
                            </td>
        <td>
            <input id="longer_<?php echo $index ?>"  type="checkbox" name="longerInput[<?php echo $index ?>]" />
        </td>
        <td style="text-align: right;">
        	<span class="t181 vem mr3" title="<?php echo Yii::t('commom', 'Upgrade')?>" onclick="moveUp(this);"></span>
			<span class="t182 vem mr3" title="<?php echo Yii::t('commom', 'Degradation')?>" onclick="moveDown(this);"></span>
    		<span class="t15 vem mr10" title="<?php echo Yii::t('commom', 'Delete')?>" onclick="deleteFixedColumnArr(this);"></span>
        </td>
</tr>



