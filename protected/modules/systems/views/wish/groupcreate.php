<?php Yii::app()->clientscript->scriptMap['jquery.js'] = false; ?>
<div class="pageContent"> 
    <?php
    $form = $this->beginWidget('ActiveForm', array(
        'id' => 'AccountgroupForm',
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
        <?php
        $user = Yii::app()->db->createCommand()
        ->select('id,user_name,user_full_name')
        ->from('ueb_user')
        ->where('department_id=:department_id AND user_status=:user_status', array(':department_id'=>9,':user_status'=>1))
        ->queryAll();
        $list=array_column($user,'user_full_name','id');
        ?>
        <div class="row">
        	<?php echo $form->labelEx($model, 'group_name'); ?>
            <?php echo $form->textField($model, 'group_name', array('style'=>'width:200px;')); ?>
            <?php echo $form->error($model, 'group_name'); ?>
        </div>  
        <div class="row">
            <?php echo $form->labelEx($model, 'sort'); ?>
            <?php echo $form->textField($model, 'sort', array('style'=>'width:100px;')); ?>
            <?php echo $form->error($model, 'sort'); ?>
        </div>
        <div class="row">
            <?php echo $form->labelEx($model, 'group_leader'); ?>
            <?php echo $form->dropdownlist($model, 'group_leader', $list,array('style'=>'width:100px;','empty' => Yii::t('system', 'Please Select'))); ?>
            <?php echo $form->error($model, 'group_leader'); ?>
        </div>
        <div class="row">
            <?php echo $form->labelEx($model, 'charge'); ?>
            <?php echo $form->dropdownlist($model, 'charge', $list,array('style'=>'width:100px;','empty' => Yii::t('system', 'Please Select'))); ?>
            <?php echo $form->error($model, 'charge'); ?>
        </div>
<!--        <div class="row" style="margin-top: 20px;width: 100%">-->
<!--            <div style="width: 100%;height: 30px"><label for="AliexpressStoreGroup_group_leader"><b>组员：</b></label></div>-->
<!--            --><?php
//            $team_members=(explode(',',$model->team_members ));
//            foreach ($list as $key=>$val){
//                echo "<input type='checkbox'  name='crewmemberArr[]'  value='".$key."'/>".$val;
//            }
//            ?>
<!--        </div>-->
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


