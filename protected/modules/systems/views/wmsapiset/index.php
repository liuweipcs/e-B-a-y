<div class="panelBar">
    <ul class="toolBar">
        <li>
            <a class="add" href="javascript:void(0);" onclick="$.refreshConfigCache('<?php echo Wmsapiset::WMS_API_PARA_TYPE;?>');" >
                <span><?php echo Yii::t('system', 'Refresh Cache')?></span>
            </a>
        </li>          
    </ul>
</div>

<?php
Yii::app()->clientscript->scriptMap['jquery.js'] = false; 
$form = $this->beginWidget('ActiveForm', array(
    'id' => 'wmsapisetForm',
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
.btn_help  {width:20px;height:20px;}
.errorMessage  {width:auto;}
</style>
<div class="pageFormContent" layoutH="90">
	<div class="bg14 pdtb2 dot">
            <strong><?php echo Yii::t('order', 'Order set');?></strong>           
    </div>
    <div class="dot7 pd5" style="height:275px;">
    	<div class="row">
            <?php echo $form->labelEx($model, 'wms_username'); ?>
            <div class="con">
            <?php echo $form->textField($model, 'wms_username', array('size' => 12)); ?>          
            </div>
            <?php echo $form->error($model, 'wms_username'); ?>
        </div>
        <div class="row" >
            <?php echo $form->labelEx($model, 'wms_password'); ?>
            <div class="con">
            <?php echo $form->textField($model, 'wms_password', array('size' => 12)); ?>           
			</div>
			<?php echo $form->error($model, 'wms_password'); ?>          
        </div>
        <div class="row" >
            <?php echo $form->labelEx($model, 'wms_url'); ?>
            <div class="con">
            <?php echo $form->textField($model, 'wms_url', array('size' => 55)); ?>            
			</div>
			<?php echo $form->error($model, 'wms_url'); ?>          
        </div>
        <div class="row">
            <?php echo $form->labelEx($model,'wms_customerid'); ?>
            <div class="con">
            <?php echo $form->textField($model,'wms_customerid',array('size'=>12)); ?>            
            </div>
            <?php echo $form->error($model, 'wms_customerid'); ?>          
        </div>
         <div class="row">
            <?php echo $form->labelEx($model,'wms_key'); ?>
            <div class="con">
            <?php echo $form->textField($model,'wms_key',array('size'=>30)); ?>            
            </div>
            <?php echo $form->error($model, 'wms_key'); ?>          
        </div>
        <div class="row">
            <?php echo $form->labelEx($model,'erp_url'); ?>
            <div class="con">
            <?php echo $form->textField($model,'erp_url',array('size'=>55)); ?>            
            </div>
            <?php echo $form->error($model, 'erp_url'); ?>          
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




