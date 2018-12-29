<div class="panelBar">
    <ul class="toolBar">
        <li>
            <a class="add" href="javascript:void(0);" onclick="$.refreshConfigCache('image');" >
                <span><?php echo Yii::t('system', 'Refresh Cache')?></span>
            </a>
        </li>          
    </ul>
</div>

<?php Yii::app()->clientscript->scriptMap['jquery.js'] = false; 
$form = $this->beginWidget('CActiveForm', array(
    'id' => 'imgsetForm',
    'enableAjaxValidation' => true,
    'enableClientValidation' => true,
    'clientOptions' => array(
        'validateOnSubmit' => true,
        'validateOnChange' => true,
        'validateOnType' => false,
        'afterValidate'=>'js:afterValidate',
    ),
    'action' => Yii::app()->createUrl($this->route),//.'/id/'.$model->id,
    'htmlOptions' => array(
        'class' => 'pageForm ',
//     	'onsubmit'=>'return validateCallback(this, navTabAjaxDone)'
    )
));
?>
<style type="text/css">
.row label {width:200px}
</style>
<div class="pageFormContent" layoutH="80">
	<div class="bg14 pdtb2 dot">
            <strong><?php echo Yii::t('system', 'Local setting');?></strong>           
    </div>
    <div class="dot7 pd5" style="height:355px;">
    	<?php foreach ($arr['local_setting'] as $key => $val):?>
    	<div class="row">
    		<?php $value = isset($img_para[$key]) ? $img_para[$key] : '';?>
    		<?php echo $form->labelEx($model, $key); ?>
    		<?php echo $form->textField($model, $key, array( 'size' => 40,'value'=>$value)); ?>
    		<?php //echo CHtml::textField("Imgset[$key]", $value, array( 'size' => 40,'id'=>'Imgset_'.$key))?>
    		<?php echo $form->error($model, $key); ?>
        </div>
		<?php endforeach;?>
    </div>
    <br/>
    <div class="bg14 pdtb2 dot">
            <strong><?php echo Yii::t('system', 'FTP setting');?></strong>
    </div>
    <div class="dot7 pd5" style="height:285px;">
    	<?php foreach ($arr['ftp_setting'] as $key => $val):?>
    	<div class="row">
    		<?php 
    			$value = isset($img_para[$key]) ? $img_para[$key] : '';
    		?>
    		<?php echo $form->labelEx($model, $key); ?>
    		<?php echo $form->textField($model, $key, array( 'size' => 40,'value'=>$value)); ?>
    		<?php //echo CHtml::textField("Imgset[$key]", $value, array( 'size' => 40,'id'=>'Imgset_'.$key))?>
    		<?php echo $form->error($model, $key); ?>
        </div>
		<?php endforeach;?>
    </div>
    <br/>
    
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




