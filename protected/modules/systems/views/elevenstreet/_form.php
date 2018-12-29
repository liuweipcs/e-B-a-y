<?php Yii::app()->clientscript->scriptMap['jquery.js'] = false; ?>
<div class="pageContent"> 
    <?php
    $form = $this->beginWidget('ActiveForm', array(
        'id' => 'lazadaForm',
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
		<?php echo $form->labelEx($model,'seller_name'); ?>
		<?php echo $form->textField($model,'seller_name'); ?>
		<?php echo $form->error($model,'seller_name'); ?>
	</div>
	<div class="row">
		<?php echo $form->labelEx($model,'token'); ?>
		<?php echo $form->textField($model,'token'); ?>
		<?php echo $form->error($model,'token'); ?>
	</div>
  <div class="row">
            <?php echo $form->labelEx($model, 'status'); ?>
            <?php echo $form->dropDownList($model, 'status', UebModel::model('LazadaAccount')->getLazadaAccountStatus(),array('empty'=>Yii::t('system','Please Select'))); ?>
            <?php echo $form->error($model, 'status'); ?>
        </div>
	<div class="row">
		<?php echo $form->labelEx($model,'user_id'); ?>
		<?php echo $form->dropDownList($model,'user_id',UebModel::model('User')->getSelectByDepartments([17,49]),array('empty'=>Yii::t('system', 'Please Select')));?>
		<?php echo $form->error($model,'user_id'); ?>
	</div>
        <div class="row">
            <?php echo $form->labelEx($model,'email'); ?>
            <?php echo $form->textField($model,'email'); ?>
            <?php echo $form->error($model,'email'); ?>
        </div>
	<div class="row buttons">
		 <div class="buttonActive">
                    <div class="buttonContent">                        
                        <button type="submit"><?php echo Yii::t('system', 'Save') ?></button>                     
                    </div>
          </div>
          <div class="buttonActive">
                    <div class="buttonContent">                        
                        <button type="button" onclick="getAccessToken();">检查author</button>                     
                    </div>
          </div>
	</div>
    </div>
    <?php $this->endWidget(); ?>
</div>

<script>
function getAccessToken(){
	var userName = $('#LazadaAccount_email').val();
	var key = $('#LazadaAccount_token').val();
	if($.trim(userName).length==0 || $.trim(key).length==0 ){
		alertMsg.warn("请填写邮箱和token");
		return;
	}
	$.post('/services/lazada/lazada/author',{'userName':userName,'key':key},function(data){
		data = $.parseJSON(data);
		if(data.statusCode==300){
			alertMsg.warn("验证失败，请检查填写的信息");
		}else{
			alertMsg.correct("验证成功");
		}
	});
}
</script>
