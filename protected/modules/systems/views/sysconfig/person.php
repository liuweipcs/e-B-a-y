<?php Yii::app()->clientscript->scriptMap['jquery.js'] = false; ?>
<div class="panelBar">
    <ul class="toolBar">
        <li>
            <a class="add" href="javascript::void(0);" onclick="$.refreshConfigCache('person');" >
                <span><?php echo Yii::t('system', 'Refresh Cache')?></span>
            </a>
        </li>          
    </ul>
</div>
<h2 class="contentTitle"><?php echo Yii::t('system', 'Personalized Settings');?></h2>
<div class="pageContent"> 
    <?php
    $form = $this->beginWidget('CActiveForm', array(
        'id' => 'menuForm',
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
            'class' => 'pageForm',         
           )
    ));
    ?>   
    <div class="pageFormContent" layoutH="125">            
        <div class="row">
            <?php echo $form->labelEx($model, 'theme'); ?>
            <?php echo $form->dropDownList($model, 'theme', $model->getThemeConfig(), array( 'style' => 'width:160px;')); ?>
            <?php echo $form->error($model, 'theme'); ?>          
        </div>       
        <div class="row">
            <?php echo $form->labelEx($model, 'language'); ?>
            <?php echo $form->dropDownList($model, 'language', $model->getLanguageConfig(), array( 'style' => 'width:160px;')); ?>
            <?php echo $form->error($model, 'language'); ?>
        </div>
        <div class="row">
            <?php echo $form->labelEx($model, 'per_page_num'); ?>
            <?php echo $form->textField($model, 'per_page_num', array( 'size' => 5)); ?>   
            <?php echo $form->error($model, 'per_page_num'); ?>
        </div>       
        <div class="row">
            <?php echo $form->labelEx($model, 'msg_notify_interval'); ?>
            <?php echo $form->textField($model, 'msg_notify_interval', array( 'size' => 5)); ?>   
            <?php echo $form->error($model, 'msg_notify_interval'); ?>
        </div>
        <div class="row">
            <?php echo $form->labelEx($model, 'msg_notify_show_count'); ?>
            <?php echo $form->textField($model, 'msg_notify_show_count', array( 'size' => 5)); ?>   
            <?php echo $form->error($model, 'msg_notify_show_count'); ?>
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
                <div class="button"><div class="buttonContent"><button type="reset"><?php echo Yii::t('system', 'Reset')?></button></div></div>
            </li>
        </ul>
    </div>
    <?php $this->endWidget(); ?>
</div>


