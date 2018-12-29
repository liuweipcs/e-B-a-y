<?php Yii::app()->clientscript->scriptMap['jquery.js'] = false; ?>
<div class="pageContent"> 
    <?php
    $form = $this->beginWidget('ActiveForm', array(
        'id' => 'shopeeaccountForm',
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
		<?php echo $form->labelEx($model,'shop_id'); ?>
		<?php echo $form->textField($model,'shop_id',array(
				'style'=>'width:500px;'
		)); ?>
		<?php echo $form->error($model,'shop_id'); ?>
	</div>
	<div class="row">
			<?php echo $form->labelEx($model,'partner_id'); ?>
			<?php echo $form->textField($model,'partner_id',array(
				'style'=>'width:500px;'
		)); ?>
			<?php echo $form->error($model,'partner_id'); ?>
		</div>
		<div class="row">
			<?php echo $form->labelEx($model,'partner_name'); ?>
			<?php echo $form->textField($model,'partner_name',array(
				'style'=>'width:500px;'
		)); ?>
			<?php echo $form->error($model,'partner_id'); ?>
		</div>
	<div class="row">
		<?php echo $form->labelEx($model,'seller_name'); ?>
		<?php echo $form->textField($model,'seller_name',array(
				'style'=>'width:500px;'
		)); ?>
		<?php echo $form->error($model,'seller_name'); ?>
	</div>
    <div class="row">
		<?php echo $form->labelEx($model,'short_name'); ?>
		<?php echo $form->textField($model,'short_name',array(
				'style'=>'width:500px;'
		)); ?>
		<?php echo $form->error($model,'seller_name'); ?>
	</div>
	<div class="row">
		<?php echo $form->labelEx($model,'secret_key'); ?>
		<?php echo $form->textField($model,'secret_key',array(
				'style'=>'width:500px;'
		)); ?>
		<?php echo $form->error($model,'secret_key'); ?>
	</div>
	<div class="row">
		<?php echo $form->labelEx($model,'country_code');?>
		<?php echo $form->dropDownList($model,'country_code',$model->getCountryCode());?>
		<?php echo $form->error($model,'country_code'); ?>
	</div>
        <div class="row">
            <?php echo $form->labelEx($model,'brand');?>
            <?php echo $form->textField($model,'brand');?>
            <?php echo $form->error($model,'brand'); ?>
        </div>
	<div class="row">
		<?php echo $form->labelEx($model,'rate');?>
		<?php echo $form->textField($model,'rate');?>
		<?php echo $form->error($model,'rate'); ?>
	</div>
	<div class="row">
		<?php echo  $form->labelEx($model,'express_way');?>
		<?php echo $form->hiddenField($model,'express_way');?>
		<?php echo CHtml::dropDownList('express', '', array(),array(
				'multiple'=>'multiple','size'=>10,
		));?>
	</div>
	<div class="row">
		<?php echo $form->labelEx($model,'user_id'); ?>
		<?php echo $form->dropDownList($model,'user_id',UebModel::model('User')->getSelectByDepartments([17,49]),array('empty'=>Yii::t('system', 'Please Select')));?>
		<?php echo $form->error($model,'user_id'); ?>
	</div>
        <div class="row">
            <?php echo $form->labelEx($model, 'group_id'); ?>
            <?php echo  $form->dropDownList($model, 'group_id',  UebModel::model('ShopeeStoreGroup')->getList(), array('options'=>array($model->group_id=>array('selected'=>'selected')),'empty'=>Yii::t('system','Please Select'))); ?>
            <?php echo $form->error($model, 'group_id'); ?>
        </div>
	<div class="row buttons">
		 <div class="buttonActive">
                    <div class="buttonContent">                        
                        <button type="submit"><?php echo Yii::t('system', 'Save') ?></button>
                    </div>
          </div>
	</div>
    </div>
    <?php $this->endWidget(); ?>
</div>
<script>
//ShopeeAccount_country_code，
var expressWay = '<?php echo $expressWay;?>';
expressWay = $.parseJSON(expressWay);
//$('select[name=express]').empty();
var wayId =$('#ShopeeAccount_express_way').val();
wayId = wayId.split(',');
$('#ShopeeAccount_country_code').change(function(){
	console.log($(this).val());
	initExpressway($(this).val());
});
if(wayId.length>0){
	initExpressway($('#ShopeeAccount_country_code').val());
}
$('#express').change(function(){
	$('#ShopeeAccount_express_way').val($(this).val());
});
function initExpressway(countryCode){
	var expressList = typeof(expressWay[countryCode]) != 'undefined' ? expressWay[countryCode] : [];
	if(expressList.length>0){
		var html = '';
		for(var i = 0;i<expressList.length;i++){
			html += '<option value="'+expressList[i].logistic_id+'"';
			if(wayId.length>0){
				for(var j=0;j<wayId.length;j++){
					if(wayId[j]>0 && wayId[j]==expressList[i].logistic_id){
						html += 'selected="selected"';
					}
				}
			}
			html += '>'+expressList[i].name+'</option>';
		}
		$('#express').empty().append(html);
	}
}
</script>
