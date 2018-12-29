<div class="panelBar">
    <ul class="toolBar">
        <li>
            <a class="add" href="javascript:void(0);" onclick="$.refreshConfigCache('<?php echo UebModel::model('logisticsSet')->getSettingType();?>');" >
                <span><?php echo Yii::t('system', 'Refresh Cache')?></span>
            </a>
        </li>          
    </ul>
</div>

<?php 
Yii::app()->clientscript->scriptMap['jquery.js'] = false; 
$form = $this->beginWidget('ActiveForm', array(
    'id' => 'purchasesetForm',
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
            <strong><?php echo Yii::t('logistics', 'Pick Rule Setting');?></strong>           
    </div>
    <div class="dot7 pd5" style="height:275px;">
    	<div class="row">
            <?php echo $form->labelEx($model, 'group_pick_package_num'); ?>
            <?php echo $form->textField($model, 'group_pick_package_num', array('size' => 6,'inc_sub_size' => 1)); ?>
            <?php echo $form->error($model, 'group_pick_package_num'); ?>
        </div>
        <div class="row">
            <?php echo $form->labelEx($model, 'big_package_product_num'); ?>
            <?php echo $form->textField($model, 'big_package_product_num', array('size' => 6,'inc_sub_size' => 1)); ?>
            <?php echo $form->error($model, 'big_package_product_num'); ?>
        </div>
        <div class="row">
            <?php echo $form->labelEx($model, 'max_sku_qty'); ?>
            <?php echo $form->textField($model, 'max_sku_qty', array('size' => 6,'inc_sub_size' => 1)); ?>
            <?php echo $form->error($model, 'max_sku_qty'); ?>
        </div>
        <div class="row">
            <?php echo $form->labelEx($model, 'express_cost'); ?>
            <?php echo $form->textField($model, 'express_cost', array('size' => 6,'inc_sub_size' => 1)); ?>
            <?php echo $form->error($model, 'express_cost'); ?>
        </div>
        <div class="row">
            <?php echo $form->labelEx($model, 'express_weight'); ?>
            <?php echo $form->textField($model, 'express_weight', array('size' => 6,'inc_sub_size' => 1)); ?>
            <?php echo $form->error($model, 'express_weight'); ?>
        </div>
        <div class="row">
            <?php echo $form->labelEx($model, 'gh_price'); ?>
            <?php echo $form->textField($model, 'gh_price', array('size' => 6,'inc_sub_size' => 1)); ?>
            <?php echo $form->error($model, 'gh_price'); ?>
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