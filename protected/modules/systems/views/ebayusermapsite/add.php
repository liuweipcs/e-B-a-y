<?php Yii::app()->clientscript->scriptMap['jquery.js'] = false; ?>
<script type="application/javascript">
    $(function(){
        //系统账号change事件
        /*$('#UserMapEbayAccount_user_id').change(function(){
            var userValue = $(this).val();
            if(userValue.length > 0)
            {
                $.post('/systems/usermapebayaccount/getebayaccount',{'user':userValue},function(data){
                    switch(data.status)
                    {
                        case 'error':
                            alertMsg.error(data.message);
                            $('.ebay_account_info_set').html('');
                            break;
                        case 'success':
                            $('.ebay_account_info_set').html(data.content);
                    }
                },'json');
            }
            else
                $('.ebay_account_info_set').html('');
        });*/
        $('.select_batch_ebay_account').click(function(){
            switch($(this).attr('role'))
            {
                case 'all':
                    $('.ebay_account_info_set').find(':checkbox').attr('checked',true);
                    break;
                case 'none':
                    $('.ebay_account_info_set').find(':checkbox').attr('checked',false);
                    break;
                case 'opposite':
                    $('.ebay_account_info_set').find(':checkbox').click();
            }
        });
    });
</script>
<div class="pageContent">
    <?php
    $userOption = array('empty' => Yii::t('system', 'Please Select'),'style'=>'width:202px');
    if($this->action->id == 'edit')
    {
        $userOption['disabled'] = 'disabled';
        $actionUrl = Yii::app()->createUrl($this->route,array('id'=>$model->user_id));
    }
    else
        $actionUrl = Yii::app()->createUrl($this->route);
    $form = $this->beginWidget('ActiveForm', array(
        'id' => 'EbayUserMapSiteAddForm',
        'enableAjaxValidation' => false,
        'enableClientValidation' => true,
        'clientOptions' => array(
            'validateOnSubmit' => true,
            'validateOnChange' => true,
            'validateOnType' => false,
            'afterValidate'=>'js:afterValidate',
        ),
        'action' => $actionUrl,
        'htmlOptions' => array(
            'class' => 'pageForm',
        )
    ));
    ?>
    <div class="pageFormContent" layoutH="56">
        <div class="pd5" style="height:150px;">
            <div class="row">
                <?php echo $form->labelEx($model,'user_id');?>
                <?php echo $form->dropDownList($model,'user_id',EbayUserMapSite::getEbayAllMember(),array('empty' => Yii::t('system', 'Please Select'),'style'=>'width:202px')); ?>
                <?php echo $form->error($model,'user_id');?>
            </div>
            <div class="row">
                <?php echo $form->labelEx($model, 'is_valid'); ?>
                <?php echo $form->dropDownList($model, 'is_valid',array(1=>'是',0=>'否'),array('style'=>'width:150px')); ?>
                <?php echo $form->error($model, 'is_valid'); ?>
            </div>
            <div class="row">
                <?php echo $form->labelEx($model, 'siteid');?>
                <div style="border: 1px solid #9AA4BA;float:left;margin-top: 2px;-webkit-box-shadow: inset 0 0 5px #9AA4BA;width:200px;overflow: auto;max-height: 295px" class="ebay_account_checkbox_area">
                    <button class="select_batch_ebay_account" role="all" type="button">全选</button>
                    <button class="select_batch_ebay_account" role="opposite" type="button">反选</button>
                    <button class="select_batch_ebay_account" role="none" type="button">全不选</button>
                    <br/>
                    <div class="ebay_account_info_set">
                        <?php
                            echo $form->checkBoxList($model,'siteid',UebModel::model('EbaySites')->GetListAll());
                        ?>
                    </div>
                </div>
                <?php echo $form->error($model, 'siteid'); ?>
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
                <div class="button"><div class="buttonContent"><button type="button" class="close"><?php echo Yii::t('system', 'Cancel')?></button></div></div>
            </li>
        </ul>
    </div>
    <?php $this->endWidget(); ?>
</div>