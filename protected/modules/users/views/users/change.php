<?php Yii::app()->clientscript->scriptMap['jquery.js'] = false; ?>
<div class="pageContent"> 
    <?php
    $form = $this->beginWidget('ActiveForm', array(
        'id' => 'menuForm',
        'enableAjaxValidation' => false,  
        'enableClientValidation' => true,
    	'focus' => array($model, 'new_password'),
        'clientOptions' => array(
            'validateOnSubmit' => true,
            'validateOnChange' => true,
            'validateOnType' => false,
            'afterValidate'=>'js:afterValidate',
        ),
        'action' => Yii::app()->createUrl($this->route,array('id'=>$model->id)), 
        'htmlOptions' => array(        
            'class' => 'pageForm',         
           )
    ));
    ?>   
    <div class="pageFormContent" layoutH="56">
        <div class="row">
            <label for="User_old_password" class="required">原密码 <span class="required">*</span></label>
            <?php echo $form->passwordField($model, 'old_password', array( 'size' => 20,'onblur'=>'oldPassword();')); ?>
            <span class="errorMessage" id="User_old_password_errorMessage" style=""></span>
        </div>
        <div class="row">
            <?php echo $form->labelEx($model, 'new_password'); ?>
            <?php echo $form->passwordField($model, 'new_password', array( 'size' => 20)); ?>
            <?php echo $form->error($model, 'new_password'); ?>          
        </div>
    </div>
    <input id="user_id" type="hidden" name="user_id" value="<?php echo $model->id;?>">
    <div class="formBar">
        <ul>              
            <li>
                <div class="buttonActive">
                    <div class="buttonContent">
                        <button type="button" id="changeSubmit" onclick="oldPassword();"><?php echo Yii::t('system', 'Save')?></button>
                    </div>
                </div>
            </li>           
        </ul>
    </div>
    <?php $this->endWidget(); ?>
</div>
<script type="text/javascript">
    function oldPassword() {
        $.post("/users/users/queryoldpwd",
            {
                user_id:$('#user_id').val(),
                old_password:$('#User_old_password').val()
            },
            function(data){
                var obj = eval('('+data+')');
                if(obj.status == 1){
                    $("#User_old_password_errorMessage").text('');
                    $("#changeSubmit").remove();
                    $(".buttonContent").html('<button type="submit" id="changeSubmit">保存</button>');
                }else {
                    $("#User_old_password_errorMessage").text(obj.msg);
                    $("#changeSubmit").remove();
                    $(".buttonContent").html('<button type="button" id="changeSubmit">保存</button>');
                }

            });
    }
</script>


