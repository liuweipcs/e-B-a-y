<?php Yii::app()->clientscript->scriptMap['jquery.js'] = false; ?>
<style type="text/css">
#Language_attributed label {width:50px}
</style>
<div class="pageContent"> 
    <?php
    $form = $this->beginWidget('ActiveForm', array(
        'id' => 'menuForm',
        'enableAjaxValidation' => false,
        'enableClientValidation' => true,
    	'focus' => array($model, 'language_code'),
        'clientOptions' => array(
            'validateOnSubmit' => true,
            'validateOnChange' => true,
            'validateOnType' => false,
            'afterValidate' => 'js:afterValidate',
        ),
        'action' => Yii::app()->createUrl($this->route) . '/id/' . $model->id,
        'htmlOptions' => array(
            'class' => 'pageForm',
        )
    ));
    ?>   
    <div class="pageFormContent" layoutH="56"> 
    	<div class="row">
            <?php echo $form->labelEx($model, 'language_code'); ?>                 
            <?php echo $form->dropDownList($model, 'language_code', Yii::app()->params['multi_language'],array('empty'=>Yii::t('system','Please Select'))); ?>
            <?php echo $form->error($model, 'language_code'); ?>          
        </div>
        <!-- 
        <div class="row">
            <?php //echo $form->labelEx($model, 'language_code'); ?>
            <?php //echo $form->textField($model, 'language_code', array( 'size' => 38)); ?>
            <?php //echo $form->error($model, 'language_code'); ?>          
        </div> -->
        <div class="row">
            <?php echo $form->labelEx($model, 'google_code'); ?>
            <?php echo $form->textField($model, 'google_code', array( 'size' => 38)); ?>
            <?php echo $form->error($model, 'google_code'); ?>          
        </div>
        <div class="row">
            <?php echo $form->labelEx($model, 'cn_code'); ?>
            <?php echo $form->textField($model, 'cn_code', array( 'size' => 38)); ?>
            <?php echo $form->error($model, 'cn_code'); ?>
        </div>
        <div class="row">
        	<?php echo $form->labelEx($model, 'attributed'); ?>
        	<?php foreach ($options as $key => $val) {
				$flag = (!empty($model->attributed) && in_array($key,$model->attributed)) ? true : false;
				echo CHtml::checkBox('Language[attributed][]', $flag, array( 'value' =>$key,'id' =>'Language_attributed_'.$key));
                echo $val;
            }?> 
        </div>
        <div class="row">
            <?php echo $form->labelEx($model, 'sort'); ?>
            <?php echo $form->textField($model, 'sort', array( 'size' => 5)); ?>
            <?php echo $form->error($model, 'sort'); ?>
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


