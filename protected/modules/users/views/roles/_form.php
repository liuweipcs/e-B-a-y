<?php Yii::app()->clientscript->scriptMap['jquery.js'] = false; ?>
<div class="pageContent"> 
    <?php
//     $currentUrl = Yii::app()->createUrl($this->route);
//     if ( $action != 'create') {
//         $currentUrl .= '/id/'.$model->filterName($model->name);
//     }
 //  var_dump($model);
    $form = $this->beginWidget('ActiveForm', array(
        'id' => 'rolesForm',
        'enableAjaxValidation' => false,  
        'enableClientValidation' => true,
    	'focus' => array($model, 'description'),
        'clientOptions' => array(
            'validateOnSubmit' => true,
            'validateOnChange' => true,
            'validateOnType' => false,
            'afterValidate'=>'js:afterValidate',
        ),
        'action' => Yii::app()->createUrl($this->route,array('id'=>$model->filterName($model->name))), 
        'htmlOptions' => array(        
            'class' => 'pageForm',         
           )
    ));
    ?>   
    <div class="pageFormContent" layoutH="56">            
        <div class="row">
            <?php echo $form->labelEx($model, 'description'); ?>
            <?php echo $form->textField($model, 'description', array( 'size' => 30)); ?>
            <?php echo $form->error($model, 'description'); ?>          
        </div>
        <?php if ( $action != 'create'):?>
        <div class="row">
            <?php echo $form->labelEx($model, 'parent'); ?>    
            <div style=" float:left; display:block;  overflow:auto; width:190px; height:200px; border:solid 1px #CCC; line-height:21px; background:#FFF;"> 
                <?php echo $this->renderPartial('users.components.views.RoleTree', array('class' => 'tree expand', 'id' => 'role_tree_seleced', 'root' => Yii::t('system', 'Root'),'menuId' => $model->name)); ?>                                            
            </div>
            <?php echo $form->error($model, 'parent'); ?> 
        </div>
        <?php endif;?>
        <div class="row">
            <?php echo $form->labelEx($model, 'name'); ?>
            <?php echo $form->textField($model, 'name', array( 'size' => 30)); ?>
            <?php echo $form->error($model, 'name'); ?>
        </div>                         
    </div>
    <div class="formBar">
        <ul>              
            <li>
                <div class="buttonActive">
                    <div class="buttonContent">                                                  
                        <?php echo $form->hiddenField($model, 'parent', array('type' => "hidden")); ?>                        
                        <button type="submit"><?php echo Yii::t('system', 'Save')?></button>                     
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


