<div class="panelBar">
    <ul class="toolBar">
        <li>
            <a class="add" href="javascript:void(0);" onclick="$.refreshConfigCache('<?php echo OrderSet::PARA_TYPE;?>');" >
                <span><?php echo Yii::t('system', 'Refresh Cache')?></span>
            </a>
        </li>          
    </ul>
</div>

<?php 
Yii::app()->clientscript->scriptMap['jquery.js'] = false; 
$form = $this->beginWidget('ActiveForm', array(
    'id' => 'ordersetForm',
    'enableAjaxValidation' => false,
    'enableClientValidation' => true,
    'clientOptions' => array(
        'validateOnSubmit' => true,
        'validateOnChange' => true,
        'validateOnType' => false,
        'afterValidate'=>'js:afterValidate',
    ),
    'action' => Yii::app()->createUrl($this->route),
    'htmlOptions' => array(
        'class' => 'pageForm ',
    )
));
?>
<style type="text/css">
.row label {width:140px;line-height:24px;}
.con {float:left;padding-top:4px;}
.con_tips {float:left;padding-left:6px;padding-top:6px;}
.btn_help  {width:20px;height:20px;}
.errorMessage  {width:auto;}
</style>
<div class="pageFormContent" layoutH="90">
	<div class="bg14 pdtb2 dot">
            <strong><?php echo Yii::t('order', 'Order set');?></strong>           
    </div>
    <div class="dot7 pd5" style="height:275px;">
    	<div class="row">
            <?php echo $form->labelEx($model, 'order_partially_ship_days'); ?>
            <div class="con">
            <?php echo $form->textField($model, 'order_partially_ship_days', array('size' => 6,'inc_sub_size' => 1)); ?>
            <div class="con_tips"><?php echo Yii::t('purchases','Days');?></div>
            </div>
            <?php echo $form->error($model, 'order_partially_ship_days'); ?>
        </div>
        <div class="row" >
            <?php echo $form->labelEx($model, 'order_refund_apply_days'); ?>
            <div class="con">
            <?php echo $form->textField($model, 'order_refund_apply_days', array('size' => 6,'inc_sub_size' => 1)); ?>
            <div class="con_tips"><?php echo Yii::t('purchases','Days');?></div>
			</div>
			<?php echo $form->error($model, 'order_refund_apply_days'); ?>          
        </div>
        <div class="row" >
            <?php echo $form->labelEx($model, 'order_kf_pending_days'); ?>
            <div class="con">
            <?php echo $form->textField($model, 'order_kf_pending_days', array('size' => 6,'inc_sub_size' => 1)); ?>
            <div class="con_tips"><?php echo Yii::t('purchases','Days');?></div>
			</div>
			<?php echo $form->error($model, 'order_kf_pending_days'); ?>          
        </div>
        <div class="row">
            <?php echo $form->labelEx($model,'order_profit_limit'); ?>
            <div class="con">
            <?php echo $form->textField($model,'order_profit_limit',array('size'=>6,'inc_sub_size'=>1)); ?>
            <div class="con_tips"><?php echo Yii::t('order','元');?></div>
            </div>
            <?php echo $form->error($model, 'order_profit_limit'); ?>          
        </div>
        <div class="row">
        	<?php echo $form->labelEx($model, 'platform_code'); ?>
			<span class="con">
            <?php echo $form->checkBoxList($model, 'platform_code', $platformCode,
            		array('separator'=>'&nbsp','template'=>'{input} {label}'));?>
             </span>
            <?php echo $form->error($model, 'platform_code'); ?>
           

		</div>

		
		
        <div class="row">
        	<?php echo $form->labelEx($model, '发货地址出错时处理'); ?>
			<span class="con">
            <?php echo $form->checkBoxList($model, 'shipping_address_exception', $shippingAddressException,
            		array('separator'=>'&nbsp','template'=>'{input} {label}'));?>
             </span>
            <?php echo $form->error($model, 'shipping_address_exception'); ?>
           

		</div>		
		
        <div class="row">
        	<?php echo $form->labelEx($model, '发货地址'); ?>
			<span class="con">
            <?php echo $form->radioButtonList($model, 'shipping_address', $shippingAddress, array('separator'=>'&nbsp','template'=>'{input}{label}', 'style'=>'float:left;height:24px;line-height:24px;'));?>
   
            <?php echo $form->error($model, 'shipping_address'); ?>
            </span>
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
            <div class="button"><div class="buttonContent"><button type="button" class="close" ><?php echo Yii::t('system', 'Cancel')?></button></div></div>
        </li>
    </ul>
</div>
<?php $this->endWidget(); ?>




