<?php
/* @var $this YibaihelperarticleController */
/* @var $model YibaihelperArticle */
/* @var $form CActiveForm */
?>
<?php Yii::app()->clientscript->scriptMap['jquery.js'] = false; ?>
<div class="form">
	<?php
	$form = $this->beginWidget('ActiveForm',array(
		'enableAjaxValidation'=>false,
		'enableClientValidation'=>false,
		'clientOptions' => array(
			'validateOnSubmit' => true,
			'validateOnChange' => true,
			'validateOnType' => false,
			'afterValidate'=>'js:afterValidate',
		),
		'action'=>Yii::app()->createUrl($this->route,array("id"=>$model->id)),
		'htmlOptions'=>array(
			'class'=>'pageForm',
			'onsubmit'=>'return validateCallback(this,dialogAjaxDone)',
		)
	));
	?>

	<p class="note">Fields with <span class="required">*</span> are required.</p>

	<?php echo $form->errorSummary($model); ?>

	<div class="row">
		<?php echo CHtml::label('所属分类');?>
		<?php echo $form->dropDownList($model,'category_id',$model->getYibaihelpercategory()); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'title'); ?>
		<?php echo $form->textField($model,'title',array('size'=>60,'maxlength'=>100)); ?>
		<?php echo $form->error($model,'title'); ?>
	</div>

	<div class="row">
		<?php echo CHtml::label('文章内容');?>
		<?php echo CHtml::textArea('YibaihelperArticle[content]',$model->content,array('style'=>'width:450px;height:357px;'));?>
	</div>

	<div class="row buttons">
		<?php echo CHtml::submitButton($model->isNewRecord ? 'Create' : 'Save'); ?>
	</div>

<?php $this->endWidget(); ?>

</div>
<script type="text/javascript">
var keditor = null;
$(function(){
	keditor  = kedit("YibaihelperArticle_content");
});
function kedit(keid){
	var keditor =  KindEditor.create('#' + keid,{
		allowFileManager: true,
		width: '91%',
		afterCreate : function() {
			this.sync();
		},
		afterBlur:function(){
			this.sync();
		}
	});
	return keditor;
}



</script>
