<?php
Yii::app()->clientscript->scriptMap['jquery.js'] = false; 
$form = $this->beginWidget('ActiveForm', array(
		'id' => 'RefreshForm',
		'enableAjaxValidation' => false,
		'enableClientValidation' => true,
		'focus' => array($model, 'pay'),
		'clientOptions' => array(
				'validateOnSubmit' => true,
				'validateOnChange' => true,
				'validateOnType' => false,
				'afterValidate'=>'js:afterValidate',
		),
		'action' => Yii::app()->createUrl($this->route),
		'htmlOptions' => array(
				'class' => 'pageForm',
		)
));

?>
<div class="pageContent">   
    <div class="tabs"> 
	 	<div class="tabsContent" style="height:90px;"> 
 			<div class="pageFormContent" layoutH="180" style="border:1px solid #B8D0D6">
				
				<div class="row" style="display:none;">
	                <?php echo $form->labelEx($model, 'client_id');?>
	                <?php echo $form->hiddenField($model, 'client_id', array( 'size' => 8)); ?>
	                <?php echo $form->error($model, 'client_id'); ?> 
	            </div>
				<?php if($model->refresh_token): ?>
				<div class="row" style="text-align:center;">
	                确认刷新Access Token？
	            </div>
				<?php else: ?>
				<div class="row" style="text-align:center;">
	                尚未获取Token信息，请获取Token信息后刷新！
	            </div>
				<?php endif; ?>
	 	</div>
    </div>
</div>
<input type="hidden" id="mall_id" name="mall_id" value="<?=$mall?>" />
    <div class="formBar">
        <ul>  
			<?php if($model->refresh_token): ?>		
            <li>
                <div class="buttonActive">
                    <div class="buttonContent">                        
                        <button type="submit"><?php echo Yii::t('system', '确认刷新')?></button>                     
                    </div>
                </div>
            </li>
			<?php endif; ?>
            <li>
                <div class="button"><div class="buttonContent"><button type="button" class="close"><?php echo Yii::t('system', 'Cancel')?></button></div></div>
            </li>
        </ul>
    </div>
    <?php $this->endWidget(); ?>
</div>