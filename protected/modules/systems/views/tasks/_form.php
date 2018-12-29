<?php
Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl.'/js/uploadimage/core/zyFile.js');
Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl.'/js/uploadimage/control/js/zyUpload.js');
Yii::app()->clientScript->registerCssFile(Yii::app()->baseUrl.'/js/uploadimage/control/css/zyUpload.css', 'screen');
Yii::app()->clientscript->scriptMap['jquery.js'] = false; 
$form = $this->beginWidget('ActiveForm', array(
		'id' => 'task-form',
		'enableAjaxValidation' => false,
		'enableClientValidation' => true,
		'focus' => array($model, 'task_name'),
		'clientOptions' => array(
				'validateOnSubmit' => true,
				'validateOnChange' => true,
				'validateOnType' => false,
				'afterValidate'=>'js:afterValidate',
		),
		'action' => Yii::app()->createUrl($this->route.'/id/'.$model->id),
		'htmlOptions' => array(
				'class' => 'pageForm',
				'enctype'=>'multipart/form-data'
		)
));
$lang_code = 'quality';
?>
<style>
<!--
label.alt{width: 128px; opacity: 1;height:20px;margin:0;line-height:22px;}
-->
</style>
<div class="pageContent">   
    <div class="tabs"> 
	    <div class="tabsHeader"> 
	 		<div class="tabsHeaderContent"> 
	 			<ul> 
	 				<li class="selected"><a href="#"><span><?php echo Yii::t('logistics', 'Basic Information')?></span></a></li>
	 			</ul> 
	 		</div> 
	 	</div>
	 	<div class="tabsContent" style="height:100%;">
 			<div class="pageFormContent" layoutH="110" style="border:1px solid #aaa">
				<p style="color: red;font-size: 12px;padding-left: 5px;">附件上传须知:不支持附件名字为中文</p>
	        	<div class="row">
	                <?php echo $form->labelEx($model, 'task_name'); ?>
	                <?php echo $form->textField($model, 'task_name', array( 'size' => 97)); ?>
	                <?php echo $form->error($model, 'task_name'); ?>
	            </div>
	            <div class="row">
	                <?php echo $form->labelEx($model, 'task_content'); ?>
	                <?php echo $form->textArea($model, 'task_content', array('cols' => 60,'id' => $lang_code.'_desc','style'=>'width:450px;height:300px;')); ?>
	                <?php echo $form->error($model, 'task_content'); ?>
	            </div> 
	            <div class="row">
                    <?php echo $form->labelEx($model, 'task_type'); ?>
                    <?php echo $form->dropDownList($model, 'task_type', VHelper::getTaskType()); ?>
                    <?php echo $form->error($model, 'task_type'); ?>
                </div>

				<div class="row">
					<?php echo $form->labelEx($model, 'task_assign'); ?>
					<?php echo $form->dropDownList($model, 'task_assign', Tasks::getDep()); ?>
					<?php echo $form->error($model, 'task_assign'); ?>

				</div>
				<div class="row">
					<label>附件</label>
					<div id="imagesupload" name="imagesupload[]">
					<div id="uploadimage" class="uploadimage" style="float: left;margin-bottom: 120px;"></div>
				</div>

				<div class="row">
					<?php echo $form->labelEx($model, 'task_claim_time'); ?>
					<?php echo $form->textField($model, 'task_claim_time', array( 'size' => 30,'datefmt'=>'yyyy-MM-dd HH:mm:ss','class'=>'date textInput','value'=>date('Y-m-d H:i:s',$model->task_claim_time ? $model->task_claim_time:time()))); ?>
					<?php echo $form->error($model, 'task_claim_time'); ?>
				</div>

            </div>
	 	</div>
    </div>
    <div class="formBar">
        <ul>              
            <li>
                <div class="buttonActive">
                    <div class="buttonContent">                        
                        <button type="submit"><?php echo Yii::t('system', 'Save')?></button>                     
                    </div>
                </div>
            </li>
            <li>
                <div class="button"><div class="buttonContent"><button type="button" class="close"><?php echo Yii::t('system', 'Cancel')?></button></div></div>
            </li>
        </ul>
    </div>
    <?php $this->endWidget(); ?>
</div>

<script type="text/javascript">
	var keditor = null;
	var keditor1 = null;
	$(function (){
		var id_desc = "<?php echo $lang_code.'_desc';?>";
		var id_inc = "<?php echo $lang_code.'_inc';?>";
		keditor = kedit(id_desc);
		keditor1 = kedit(id_inc);
		//checkLens('<?php echo $lang_code.'_title';?>','<?php echo $lang_code.'_count';?>',95);
	});
	function kedit(keid){
		var keditor =  KindEditor.create('#' + keid,{
			allowFileManager: true,
			width: '80%',
			afterCreate : function(){
				this.sync();
			},
			afterBlur:function(){
				this.sync();
			}
		});
		return keditor;
	}

	$(function(){
	// 初始化插件
	$("#uploadimage").zyUpload({
		width            :   "145px",                 // 宽度
		height           :   "30px",                 // 宽度
		itemWidth        :   "60px",                 // 文件项的宽度
		itemHeight       :   "60px",                 // 文件项的高度
		url              :   "<?php echo Yii::app()->createUrl('systems/tasks/uploadexecl',array());?>",  // 上传文件的路径
		multiple         :   true,                    // 是否可以多个文件上传
		dragDrop         :   true,                    // 是否可以拖动上传文件
		del              :   true,                    // 是否可以删除文件
		finishDel        :   false,  				  // 是否在上传文件完成后删除预览
		/* 外部获得的回调接口 */
		onSelect: function(files, allFiles){                    // 选择文件的回调方法
			console.info("当前选择了以下文件：");
			console.info(files);
			console.info("之前没上传的文件：");
			console.info(allFiles);
		},
		onDelete: function(file, surplusFiles){                     // 删除一个文件的回调方法
			console.info("当前删除了此文件：");
			console.info(file);
			console.info("当前剩余的文件：");
			console.info(surplusFiles);
		},
		onSuccess: function(file,response){                    // 文件上传成功的回调方法
			response=$.parseJSON(response);
			console.info("此文件上传成功：");
			console.log(file);
			console.log(file.index);
			console.info(file);
			console.log(response);
			if(response.statusCode==200){

			   var html='<input type="text" style="display:none" value="'+response.realPath+'" class="uploadproductimg"  name="Tasks[annex]" />';
			   $('#imagesupload').append(html);
			   $('.dragImage').arrangeable();
			}
		},
		onFailure: function(file){                    // 文件上传失败的回调方法
			console.info("此文件上传失败：");
			console.info(file);
		},
		onComplete: function(responseInfo){           // 上传完成的回调方法
			console.info("文件上传完成");
			console.info(responseInfo);
		}
	});
});
</script>