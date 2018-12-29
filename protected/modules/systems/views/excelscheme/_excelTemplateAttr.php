<tr class="bg4" rowuuid="138<?php echo $index ?>" onclick="changeColor();" onmouseover="mOver(this);" onmouseout="mOut(this);">
	<td>
		<div style="padding:15px 5px;margin:5px 3px;word-wrap:break-word;" class="colAlign-left">
			<div class="left h30 mt2">
				<input type="radio" id="radio<?php echo $index ?>" name="excelList" class="mr5 left mt3" onclick="createListBox(id);">
				<span class="" id="spanText<?php echo $index ?>" onclick="spanClick(id);"></span>
				<span class=""><input type="text" onblur="inputBlur(this);"></span>
			 	<input type="hidden" id="inputSchemeId<?php echo $index?>" value="" /></div>
			<div class="right mt2">
				<span class="t45 mr3" title="<?php echo Yii::t('commom', 'Copy')?>" id="copyBtn<?php echo $index ?>" onclick="copySchemeType(id);"></span>
				<span class=" t216 mr3 " title="<?php echo Yii::t('commom', 'Save')?>" id="saveBtn_<?php echo $index ?>" style="display:none" onclick="saveText(id);"></span>
				<span class="t15" title="<?php echo Yii::t('commom', 'Delete')?>" onclick = "deleteArr(this);"></span>
			</div>
		<div class="clear"></div>
		</div>
	</td>
</tr>
