<?php Yii::app()->clientscript->scriptMap['jquery.js'] = false; ?>
<div class="pageContent"> 
    <?php
    $form = $this->beginWidget('ActiveForm', array(
        'id' => 'menuForm',
        'enableAjaxValidation' => false,
        'enableClientValidation' => true,
    	'focus' => array($model, 'page_tag'),
        'clientOptions' => array(
            'validateOnSubmit' => true,
            'validateOnChange' => true,
            'validateOnType' => false,
            'afterValidate' => 'js:afterValidate',
        ),
        'action' => Yii::app()->createUrl($this->route, array('id' => $model->id)),
        'htmlOptions' => array(
            'class' => 'pageForm',
        )
    ));
    ?>   
    <div class="pageFormContent" layoutH="56">  
        <div class="row">
            <?php echo $form->labelEx($model, 'page_tag'); ?>
            <?php echo $form->textField($model, 'page_tag', array( 'size' => 38)); ?>
            <?php echo $form->error($model, 'page_tag'); ?>          
        </div>    
        <div class="row">
            <?php echo $form->labelEx($model, 'page_note'); ?>
            <?php echo $form->textField($model, 'page_note', array( 'size' => 38)); ?>
            <?php echo $form->error($model, 'page_note'); ?>          
        </div>       
        <div class="row">
            <?php echo $form->labelEx($model, 'page_help_content'); ?>
            <?php echo $form->textArea($model, 'page_help_content', array('cols' => 60,'id' => 'menuForm_desc','style'=>'width:240px;height:100px;')); ?>
            <?php echo $form->error($model, 'page_help_content'); ?>          
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
<script type="text/javascript">
$(function (){
	var id_desc = "menuForm_desc";
	kedit(id_desc);
});

function kedit(keid){ 
	var keditor =  KindEditor.create('#' + keid,{
		allowFileManager: true,
		width: '80%'
	});
}

</script>


