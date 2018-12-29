<?php
Yii::app()->getClientScript()->registerCoreScript('jquery');

Yii::app()->getClientScript()->registerScriptFile("/js/core/dwz.ajax.js");


?>
<div class="pageContent">
	<form class="pageForm" name="frmBatchSettle" id="excel-form" action="/systems/excel/import" method="post" enctype="multipart/form-data">
		请选择EXCEL文件
		<input type="file" name="batchFile" value="">
		<input type="submit" value="上传">
		<!--  
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
    	-->
	</form>
	<a href="/systems/excel/export" target="dwzExport" targetType="dialog" title="实要导出这些记录吗?">导出EXCEL</a>

	
</div>
