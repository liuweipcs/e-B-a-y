<?php Yii::app()->clientscript->scriptMap['jquery.js'] = false; ?>
<div class="pageContent"> 
    <?php
    $form = $this->beginWidget('ActiveForm', array(
        'id' => 'wishForm',
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
        	<?php echo $form->labelEx($model, 'account'); ?>                 
            <?php echo $form->textField($model, 'account', array('style'=>'width:450px;')); ?>
            <?php echo $form->error($model, 'account'); ?>    
        </div>  
        <div class="row">
            <?php echo $form->labelEx($model, 'store_name'); ?>
            <?php echo $form->textField($model, 'store_name', array('style'=>'width:450px;')); ?>
            <?php echo $form->error($model, 'store_name'); ?>
        </div>
        <div class="row">
            <?php echo $form->labelEx($model, 'short_name'); ?>
            <?php echo $form->textField($model, 'short_name', array('style'=>'width:450px;')); ?>
            <?php echo $form->error($model, 'short_name'); ?>
        </div>

		<div class="row">
            <?php echo $form->labelEx($model, 'app_key'); ?>
            <?php echo $form->textField($model, 'app_key', array('style'=>'width:450px;')); ?>
            <?php echo $form->error($model, 'app_key'); ?>
        </div>
        <div class="row">
            <?php echo $form->labelEx($model, 'secret_key'); ?>
            <?php echo $form->textField($model, 'secret_key', array('style'=>'width:450px;')); ?>
            <?php echo $form->error($model, 'secret_key'); ?>
        </div>
        <div class="row">
            <?php echo $form->labelEx($model, 'email'); ?>
            <?php echo $form->textField($model, 'email', array('style'=>'width:450px;')); ?>
            <?php echo $form->error($model, 'email'); ?>
        </div>
        <div class="row">
            <?php echo $form->labelEx($model, 'access_token'); ?>
            <?php echo $form->textArea($model, 'access_token', array('style'=>'width:450px;height:60px;')); ?>
            <?php echo $form->error($model, 'access_token'); ?>
        </div>
        <div class="row">
            <?php echo $form->labelEx($model, 'refresh_token'); ?>
            <?php echo $form->textArea($model, 'refresh_token', array('style'=>'width:450px;height:60px;')); ?>
            <?php echo $form->error($model, 'refresh_token'); ?>
        </div>

        <div class="row">
            <?php echo $form->labelEx($model, 'redirect_uri'); ?>
            <?php echo $form->textArea($model, 'redirect_uri', array('style'=>'width:450px;height:60px;','value'=>"http://".$_SERVER['SERVER_NAME'].'/systems/aliexpress/getcode/account/'.$model->id)); ?>
            <?php echo $form->error($model, 'redirect_uri'); ?>
        </div>

        <div class="row">
            <?php echo $form->labelEx($model, 'status'); ?>
            <?php echo $form->dropDownList($model, 'status', UebModel::model('AliexpressAccount')->getAliexpressAccountStatus(), array('options'=>array($model->status=>array('selected'=>'selected')),'empty'=>Yii::t('system','Please Select'))); ?>
            <?php echo $form->error($model, 'status'); ?>
        </div>
        <div class="row" style="margin-bottom:10px;margin-top: 20px; border:1px dashed #000; overflow:hidden;_height:10px; min-height:10px;">
            <?php echo $form->labelEx($model, 'categroy_id'); ?>
        <?php
           $sf=UebModel::model('ProductAliexpressCategory')->getCategoryall('cid,name');
            foreach($sf as $k=>$v)
            {
               $ss=explode(',',$model->categroy_id);
                if (!in_array($v['cid'], $ss))
                {

                    echo "<input id='AliexpressAccount_categroy_id_{$k}'  value='{$v['cid']}' type='checkbox' name='AliexpressAccount[categroy_id][]'>{$v['name']}";
               ;
                } else {
                    echo "<input id='AliexpressAccount_categroy_id_{$k}' checked='checked' value='{$v['cid']}' type='checkbox' name='AliexpressAccount[categroy_id][]'>{$v['name']}";
                }
            }?>
            <?php /*echo $form->checkBoxList($model, 'categroy_id', UebModel::model('ProductAliexpressCategory')->queryPairs('id,name'), array(
                'data-placeholder'     => Yii::t('system', 'Please Select'),
                'style'                => 'width:12px;',
                'options'              => $model->categroy_id,
                'class'=>'labelForRadio',
                'separator'=>'&nbsp;',
            ));*/
            ?>
            <?php echo $form->error($model, 'categroy_id'); ?>
        </div>
        <div class="row" style="margin-bottom:10px;margin-top: 10px; border:1px dashed #000; overflow:hidden;_height:10px; min-height:10px;">
            <?php echo $form->labelEx($model, 'user_id'); ?>
            <?php
            $user = Yii::app()->db->createCommand()
                ->select('id,user_name,user_full_name')
                ->from('ueb_user')
                ->where('department_id=:department_id', array(':department_id'=>4))
                ->queryAll();
            $account_config = UebModel::model('Aliexpressaccountconfig')->getQueryAccount($model->id);
            $ids = array();
            if(!empty($account_config)){
                foreach ($account_config as $value){
                    $ids[] = $value['user_id'];
                }
            }
            if(!empty($user)){
                foreach ($user as $value){
                    if (in_array($value['id'], $ids))
                    {
                        echo "<input id='AliexpressAccount_user_id_{$k}' checked='checked' value='{$value['id']}' type='checkbox' name='AliexpressAccount[user_id][]'>{$value['user_full_name']}";
                         } else {
                        echo "<input id='AliexpressAccount_user_id_{$k}'  value='{$value['id']}' type='checkbox' name='AliexpressAccount[user_id][]'>{$value['user_full_name']}";
                    }
                }
            }else{
                echo '没有找到速卖通部门的人员信息';
            }

            ?>
            <?php echo $form->error($model, 'user_id'); ?>
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


