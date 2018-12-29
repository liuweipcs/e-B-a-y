<?php Yii::app()->clientscript->scriptMap['jquery.js'] = false; ?>
<div class="pageContent"> 
    <?php
    $form = $this->beginWidget('ActiveForm', array(
        'id' => 'bankForm',
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
        	<?php echo $form->labelEx($model, 'payment_platform_id'); ?>                 
            <?php echo $form->dropDownList($model, 'payment_platform_id', UebModel::model('PaymentPlatform')->getPaymentPlatformListByType(1),array('empty'=>Yii::t('system','Please Select'))); ?>
            <?php echo $form->error($model, 'payment_platform_id'); ?>    
        </div>  
        <div class="row">
        	<?php echo $form->labelEx($model, 'bank_address'); ?>                 
            <?php
                $param = array( 
					'region_bank' 	=> isset($region_bank) ? $region_bank : array(),
					'fieldName'		=> array('bank_province_id','bank_city_id','bank_area_id'),
					'form'				=> $form,
					'model'				=> $model,
					'changeid'			=>'Bank_bank_region_id'
				);
                echo $this->renderPartial('application.components.views.AreaDropList',$param); ?>
        </div>
        <div class="row">
            <?php echo $form->labelEx($model, 'bank_name'); ?>
            <?php echo $form->textField($model, 'bank_name', array('size' => 40)); ?>
            <?php echo $form->error($model, 'bank_name'); ?>
        </div>
    </div>
    <div class="formBar">
        <ul>              
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


