<?php Yii::app()->clientscript->scriptMap['jquery.js'] = false; ?>
<div class="pageContent"> 
    <?php
    $form = $this->beginWidget('ActiveForm', array(
        'id' => 'joomForm',
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
        	<?php echo $form->labelEx($model, 'account'); ?>                 
            <?php echo $form->textField($model, 'account', array('style'=>'width:520px;')); ?>
            <?php echo $form->error($model, 'account'); ?>    
        </div>  
        <div class="row">
            <?php echo $form->labelEx($model, 'account_name'); ?>
            <?php echo $form->textField($model, 'account_name', array('style'=>'width:520px;')); ?>
            <?php echo $form->error($model, 'account_name'); ?>
        </div>
        <div class="row">
            <?php echo $form->labelEx($model, 'short_name'); ?>
            <?php echo $form->textField($model, 'short_name', array('style'=>'width:520px;')); ?>
            <?php echo $form->error($model, 'short_name'); ?>
        </div>
		<div class="row">
            <?php echo $form->labelEx($model, 'client_id'); ?>
            <?php echo $form->textField($model, 'client_id', array('style'=>'width:520px;')); ?>
            <?php echo $form->error($model, 'client_id'); ?>
        </div>
		<div class="row">
            <?php echo $form->labelEx($model, 'client_secret'); ?>
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
		<div class="row">
            <?php echo $form->labelEx($model, 'code'); ?>
            <?php echo $form->textField($model, 'code', array('style'=>'width:450px;')); ?> <a href="https://api-merchant.joom.com/api/v2/oauth/authorize?client_id=<?php echo $model->client_id; ?>"  target="_blank">点击获取CODE</a>
            <?php echo $form->error($model, 'code'); ?>
        </div>
		<div class="row">
            <?php echo $form->labelEx($model, 'redirect_uri'); ?>
            <?php echo $form->textField($model, 'redirect_uri', array('style'=>'width:520px;','value'=>'https://'.$_SERVER['SERVER_NAME'].'/systems/joom/getcode/account/'.$model->id)); ?>
            <?php echo $form->error($model, 'redirect_uri'); ?>
        </div>
		<?php endif; ?>
        <div class="row">
            <?php echo $form->labelEx($model, 'status'); ?>
            <?php echo $form->dropDownList($model, 'status', UebModel::model('JoomAccount')->getJoomAccountStatus(),array('empty'=>Yii::t('system','Please Select'))); ?>
            <?php echo $form->error($model, 'status'); ?>
        </div>
        <div class="row">
            <?php echo $form->labelEx($model, 'image_host'); ?>
            <?php echo $form->textField($model, 'image_host',array('style'=>'width:450px;')); ?>
            <?php echo $form->error($model, 'image_host'); ?>
        </div>
    </div>
    <div class="formBar">
        <ul>    
			<?php if($model->client_id && $model->client_secret && $model->code && $model->redirect_uri): ?>
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
	$.post("/systems/joom/getaccesstoken",{ 'id': id}, function(data) {
		data = eval("("+data+")");
		if(data.statusCode == 300){
			alert("获取Access Token失败！请检查是否信息缺失！");
		}else{
			alert("获取Access Token成功！关闭弹出框即可使用。");
		}
	});	
}
</script>
