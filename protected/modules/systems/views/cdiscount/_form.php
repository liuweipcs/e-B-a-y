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
		<?php echo $form->labelEx($model,'email'); ?>
		<?php echo $form->textField($model,'email'); ?>
		<?php echo $form->error($model,'email'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'seller_name'); ?>
		<?php echo $form->textField($model,'seller_name'); ?>
		<?php echo $form->error($model,'seller_name'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'short_name'); ?>
		<?php echo $form->textField($model,'short_name'); ?>
		<?php echo $form->error($model,'short_name'); ?>
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
            <?php echo $form->labelEx($model, 'group_id'); ?>
            <?php echo  $form->dropDownList($model, 'group_id',  UebModel::model('CdiscountStoreGroup')->getList(), array('options'=>array($model->group_id=>array('selected'=>'selected')),'empty'=>Yii::t('system','Please Select'))); ?>
            <?php echo $form->error($model, 'group_id'); ?>
        </div>
	<div class="row buttons">
		 <div class="buttonActive">
                    <div class="buttonContent">                        
                        <button type="submit"><?php echo Yii::t('system', 'Save') ?></button>                     
                    </div>
          </div>
          <?php if($model->id>0){?>
           <div class="buttonActive">
                    <div class="buttonContent">                        
                        <button type="button"  id="refreshToken">获取token</button>                     
                    </div>
          </div>
          <?php }?>
	</div>
    </div>
    <?php $this->endWidget(); ?>
</div>
 <?php if($model->id>0){?>
 <script type="text/javascript">
 var isDone = false;
$('#refreshToken').click(function(){
	idDone = true;
	if(isDone){
		alertMsg.warn("正在请求中");
		return ;
	}
	$.post('<?php echo Yii::app()->createUrl('/services/cdiscount/cdiscount/refreshtoken',array('account'=>$model->id));?>',function(data){
			data = $.parseJSON(data);
			if(data.statusCode==200){
				alertMsg.info(data.message);
			}else{
				alertMsg.warn(data.message);
			}
	});
})
 </script>
 <?php }?>