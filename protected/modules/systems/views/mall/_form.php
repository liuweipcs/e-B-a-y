<?php Yii::app()->clientscript->scriptMap['jquery.js'] = false; ?>
<div class="pageContent"> 
    <?php
    $form = $this->beginWidget('ActiveForm', array(
        'id' => 'mallForm',
        'enableAjaxValidation' => false,
        'enableClientValidation' => true,
    	'clientOptions' => array(
            'validateOnSubmit' => true,
            'validateOnChange' => true,
            'validateOnType' => false,
            'afterValidate' => 'js:afterValidate',
        ),
        'action' => Yii::app()->createUrl($this->route,array('id'=>$model->id)),
        'htmlOptions' => array(
            'class' => 'pageForm',
        )
    ));
    ?>   
    <div class="pageFormContent" layoutH="56">  
        <div class="row">
        	<?php echo $form->labelEx($model, 'user_name<span style="color:red">*</span>'); ?>                 
            <?php echo $form->textField($model, 'user_name', array('style'=>'width:520px;')); ?>
            <?php echo $form->error($model, 'user_name'); ?>    
        </div>  
        <div class="row">
        	<?php echo $form->labelEx($model, 'short_name<span style="color:red">*</span>'); ?>                 
            <?php echo $form->textField($model, 'short_name', array('style'=>'width:520px;')); ?>
            <?php echo $form->error($model, 'short_name'); ?>    
        </div>  
		<div class="row">
            <?php echo $form->labelEx($model, 'password<span style="color:red">*</span>'); ?>
            <?php echo $form->textField($model, 'password', array('style'=>'width:450px;','type'=>'password')); ?>
            <?php echo $form->error($model, 'password'); ?>
        </div>        
		<div class="row">
            <?php echo $form->labelEx($model, 'client_id<span style="color:red">*</span>'); ?>
            <?php echo $form->textField($model, 'client_id', array('style'=>'width:520px;')); ?>
            <?php echo $form->error($model, 'client_id'); ?>
        </div>
		<div class="row">
            <?php echo $form->labelEx($model, 'client_secret<span style="color:red">*</span>'); ?>
            <?php echo $form->textField($model, 'client_secret', array('style'=>'width:520px;')); ?>
            <?php echo $form->error($model, 'client_secret'); ?>
        </div> 
        <div class="row">
            <?php echo $form->labelEx($model, 'access_token'); ?>
            <?php echo $form->textArea($model, 'access_token', array('style' => 'width:520px;height:40px;')); ?>
            <?php echo $form->error($model, 'access_token'); ?>
        </div>
        <div class="row">
            <?php echo $form->labelEx($model, 'refresh_token'); ?>
            <?php echo $form->textArea($model, 'refresh_token', array('style' => 'width:520px;height:40px;')); ?>
            <?php echo $form->error($model, 'refresh_token'); ?>
        </div>
		<?php if($model->client_id): ?>
		<?php endif; ?>
        <div class="row">
            <?php echo $form->labelEx($model, 'status'); ?>
            <?php echo $form->dropDownList($model, 'status', UebModel::model('MallAccount')->getMallAccountStatus(),array('empty'=>Yii::t('system','Please Select'))); ?>
            <?php echo $form->error($model, 'status'); ?>
        </div>
  
    </div>
    <div class="formBar">
        <ul>    
			<?php if($model->client_id && $model->client_secret&&$model->user_name&&$model->password ): ?>
			<li>
                <div class="buttonActive">
                    <div class="buttonContent">                        
                        <button type="button" onclick="getAccessToken(<?php echo $model->id; ?>)"><?php echo Yii::t('system', '获取Access Token') ?></button>                     
                    </div>
                </div>
            </li>
			<?php endif; ?>
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

<script>
function getAccessToken(id){
	$.post("/systems/mall/getaccesstoken",{ 'id': id}, function(data) {
		data = eval("("+data+")");
		if(data.statusCode == 300){
			alert("获取Access Token失败！请检查是否信息缺失！");
		}else{
			alert("获取Access Token成功！关闭弹出框即可使用。");
		}
	});	
}
</script>
