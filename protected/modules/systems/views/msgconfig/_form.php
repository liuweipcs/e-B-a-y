<?php Yii::app()->clientscript->scriptMap['jquery.js'] = false; ?>
<div class="pageContent"> 
    <?php
    $form = $this->beginWidget('ActiveForm', array(
        'id' => 'menuForm',
        'enableAjaxValidation' => false,  
        'enableClientValidation' => true,
    	'focus' => array($model, 'name'),
        'clientOptions' => array(
            'validateOnSubmit' => true,
            'validateOnChange' => true,
            'validateOnType' => false,
            'afterValidate'=>'js:definedAfterValidate',
        ),
        'action' => Yii::app()->createUrl($this->route,array('id'=>$model->id)), 
        'htmlOptions' => array(        
            'class' => 'pageForm',         
           )
    ));
    ?>   
    <div class="pageFormContent" layoutH="56">            
        <div class="row">
            <?php echo $form->labelEx($model, 'name'); ?>
            <?php echo $form->textField($model, 'name', array( 'size' => 38)); ?>
            <?php echo $form->error($model, 'name'); ?>          
        </div>
        <div class="row">
            <?php echo $form->labelEx($model, 'code'); ?>
            <?php echo $form->textField($model, 'code', array( 'size' => 38)); ?>
            <?php echo $form->error($model, 'code'); ?>
        </div>       
        <div class="row">
            <?php echo $form->labelEx($model, 'send_roles'); ?>  
            <?php echo $form->hiddenField($model, 'send_roles', array('id' => 'send_roles', 'type' => "hidden")); ?>
            <div  style="border:solid 1px #DFE8F6; overflow: auto; resize: both;  width:240px;  height: 300px;  min-height: 280px; ">
                <?php $htmlOptions = array( 'class' => 'tree treeFolder treeCheck expand', 'id' => 'msg_roleTreePanel', 'root' => Yii::t('users', 'All Roles'));?>
                <?php if ( isset($sendRoles) ) { $htmlOptions['checkedArr'] = $sendRoles; }?>
                <?php echo $this->renderPartial('users.components.views.RoleTree', $htmlOptions); ?>
            </div>
            <?php echo $form->error($model, 'send_roles'); ?> 
        </div>                          
        <div class="row">
            <?php echo $form->labelEx($model, 'status'); ?>                 
            <?php echo $form->dropDownList($model, 'status', VHelper::getStatusConfig()); ?>
            <?php echo $form->error($model, 'status'); ?>          
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
                <div class="button"><div class="buttonContent"><button type="button" class="close"><?php echo Yii::t('system', 'Cancel')?></button></div></div>
            </li>
        </ul>
    </div>
    <?php $this->endWidget(); ?>
</div>
<script type="text/javascript">  
/** 
 * @param {type} form
 * @param {type} data
 * @param {type} hasError
 * @returns {Boolean}
 */
function definedAfterValidate(form, data, hasError) { 
    if (hasError) {       
        return false;
    }  
    var $form = $(form),
        roles = [];
       $form.find('.checked').each(function() {
           roles.push($(this).parent('div').find('a').attr('id'));          
       });
       if (roles.length > 0) {
           roles.join()
           $('#send_roles').val(roles);
       } else {          
           alertMsg.warn('<?php echo Yii::t('system', 'Please select the roles')?>');             
           return false;
       }
    var _submitFn = function() {
        $.ajax({
            type: form.method || 'POST',
            url: $form.attr("action"),
            data: $form.serializeArray(),
            dataType: "json",
            cache: false,
            success: function(json) {               
                ajaxCallback(json);
            },
            error: DWZ.ajaxError
        });
    }   
    _submitFn();
    return false;
}
</script>


