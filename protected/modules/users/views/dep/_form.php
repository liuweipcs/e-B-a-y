<?php Yii::app()->clientscript->scriptMap['jquery.js'] = false; ?>
<div class="pageContent"> 
    <?php
//     $currentUrl = Yii::app()->createUrl($this->route,array('id'=>$model->filterName($model->id)));
//     if ( $action != 'create') {
//         $currentUrl .= '/id/'.$model->filterName($model->id);
//     }
    $form = $this->beginWidget('ActiveForm', array(
        'id' => 'depForm',
        'enableAjaxValidation' => false,  
        'enableClientValidation' => true,
    	'focus' => array($model, 'department_name'),
        'clientOptions' => array(
            'validateOnSubmit' => true,
            'validateOnChange' => true,
            'validateOnType' => false,
            'afterValidate'=>'js:afterValidate',
        ),
        'action' => Yii::app()->createUrl($this->route,array('id'=>$model->filterName($model->id))), 
        'htmlOptions' => array(        
            'class' => 'pageForm',         
           )
    ));
    
    ?>   
    <div class="pageFormContent" layoutH="56">
        <div class="row">
            <?php echo $form->labelEx($model, 'department_name'); ?>
            <?php echo $form->textField($model, 'department_name', array('size' => 40,'maxlength' => 30)); ?>
            <?php echo $form->error($model, 'department_name'); ?>
        </div>
        <div class="row">
            <?php echo $form->labelEx($model, 'department_code'); ?>
            <?php echo $form->textField($model, 'department_code', array('size' => 40,'maxlength' => 30)); ?>
            <?php echo $form->error($model, 'department_code'); ?>
        </div>
        
        <?php if ( $action != 'create'):?>
        <div class="row">
            <?php echo $form->labelEx($model, 'parent'); ?>
            <div style=" float:left; display:block;  overflow:auto; width:200px; height:250px; border:solid 1px #CCC; line-height:21px; background:#FFF;"> 
                <?php echo $this->renderPartial('users.components.views.DeptTree', array('class' => 'tree expand', 'id' => 'dep_tree_seleced','menuId' => $model->id)); ?>                                            
            </div>
            <?php echo $form->error($model, 'parent'); ?> 
        </div>
        <?php endif;?>
        <div class="row">
            <?php echo $form->labelEx($model, 'department_description'); ?>
            <?php echo $form->textArea($model, 'department_description', array('cols' => 50,'rows'=>6,'maxlength' => 250)); ?>
            &nbsp;<?php echo $form->error($model, 'department_description'); ?>          
        </div>                         
    </div>
    <div class="formBar">
        <ul>              
            <li>
                <div class="buttonActive">
                    <div class="buttonContent">
                        <?php echo $form->hiddenField($model, 'parent', array('type' => "hidden","value"=>$model->parent)); ?>                        
                        <button type="submit"><?php echo Yii::t('system', 'Save');?></button>                     
                    </div>
                </div>
            </li>
            <li>
                <div class="button"><div class="buttonContent"><button type="button" class="close"><?php echo Yii::t('system', 'Cancel')?></button></div></div>
            </li>
        </ul>
    </div>
    <?php $this->endWidget(); ?>
</div>


