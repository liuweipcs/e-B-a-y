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
    ->where('department_id=:department_id AND user_status=:user_status', array(':department_id'=>4,':user_status'=>1))
    ->queryAll();
    $list=array_column($user,'user_full_name','id');
    ?>
        <div class="row">
        	<?php echo $form->labelEx($model, 'group_name'); ?>
            <?php echo $form->textField($model, 'group_name', array('style'=>'width:200px;')); ?>
            <?php echo $form->error($model, 'group_name'); ?>
        </div>
        <div class="row" style="margin-top: 20px">
            <?php echo $form->labelEx($model, 'sort'); ?>
            <?php echo $form->textField($model, 'sort', array('style'=>'width:100px;')); ?>
            <?php echo $form->error($model, 'sort'); ?>
        </div>
        <div class="row" style="margin-top: 30px">
            <?php echo $form->labelEx($model, 'start'); ?>
            <?php echo $form->dropdownlist($model, 'start', $list,array('style'=>'width:100px;','empty' => Yii::t('system', 'Please Select'))); ?>
            <?php echo $form->error($model, 'start'); ?>
           <input type="button" value="查看店铺" id="button_start">
        </div>
        <div class="row" id="button_start_blak" style="display: none">
            <div style="margin-top: 5px;width: 100%">
                <div style="float: left;width: 90%;margin: 5px;border: 2px solid #00a157">
                    <span>
                        <script type="text/javascript">
                             $("#button_start").click(function(){
                                 $("#button_start_blak").toggle();
                             });
                                $("#startselectAll").click(function () {
                                    $("input[name='start[]']").attr("checked","true");
                                });
                                $("#startcancelAll").click(function () {
                                    $("input[name='start[]']").removeAttr("checked");
                                });
                        </script>
                        <input type="button" id="startselectAll" value="全选" style="width: 10%;float: left">
                        <input type="button" id="startcancelAll" value="取消全选" style="width: 10%;float: left">
                    </span>
                    <?php
                    foreach (UebModel::model('AliexpressAccount')->getIdNamePairs() as $akey=>$value) {
                        ?>
                        <span style="padding: 5px;">
                                    <input type="checkbox" value="<?=$akey;?>" name="start[]"><?=$value;?></span>
                        <?php
                    }
                    ?>
                </div>
            </div>
        </div>
        <div class="row" style="margin-top: 20px">
            <?php echo $form->labelEx($model, 'charge'); ?>
            <?php echo $form->dropdownlist($model, 'charge', $list,array('style'=>'width:100px;','empty' => Yii::t('system', 'Please Select'))); ?>
            <?php echo $form->error($model, 'charge'); ?>
            <input type="button" value="查看店铺" id="button_charge">
        </div>
        <div class="row" id="button_charge_blak" style="display: none">
            <div style="margin-top: 5px;width: 100%">

                <div style="float: left;width: 90%;margin: 5px;border: 2px solid #00a157">
                    <script type="text/javascript">
                        $("#button_charge").click(function(){
                            $("#button_charge_blak").toggle();
                        });
                        $("#chargeselectAll").click(function () {
                            $("input[name='charge[]']").attr("checked","true");
                        });
                        $("#chargecancelAll").click(function () {
                            $("input[name='charge[]']").removeAttr("checked");
                        });
                    </script>
                    <input type="button" id="chargeselectAll" value="全选" style="width: 10%;float: left">
                    <input type="button" id="chargecancelAll" value="取消全选" style="width: 10%;float: left">
                    <?php
                    foreach (UebModel::model('AliexpressAccount')->getIdNamePairs() as $akey=>$value) {
                        ?>
                        <span style="padding: 5px;">
                                    <input type="checkbox" value="<?=$akey;?>" name="charge[]" <?php  if(in_array($akey,$charge)){?>checked="checked" <?php }?>><?=$value;?></span>
                        <?php
                    }
                    ?>
                </div>
            </div>
        </div>
        <div class="row" style="margin-top: 20px">
            <?php echo $form->labelEx($model, 'group_leader'); ?>
            <?php echo $form->dropdownlist($model, 'group_leader', $list,array('style'=>'width:100px;','empty' => Yii::t('system', 'Please Select'))); ?>
            <?php echo $form->error($model, 'group_leader'); ?>
            <input type="button" value="查看店铺" id="button_group_leader">
        </div>
        <div class="row" id="group_leader_blak" style="display: none">
            <div style="margin-top: 5px;width: 100%">
                <div style="float: left;width: 90%;margin: 5px;border: 2px solid #00a157">
                    <script type="text/javascript">
                        $("#button_group_leader").click(function(){
                            $("#group_leader_blak").toggle();
                        });
                        $("#group_leaderselectAll").click(function () {
                            $("input[name='group_leader[]']").attr("checked","true");
                        });
                        $("#group_leadercancelAll").click(function () {
                            $("input[name='group_leader[]']").removeAttr("checked");
                        });
                    </script>
                    <input type="button" id="group_leaderselectAll" value="全选" style="width: 10%;float: left">
                    <input type="button" id="group_leadercancelAll" value="取消全选" style="width: 10%;float: left">
                    <?php
                    foreach (UebModel::model('AliexpressAccount')->getIdNamePairs() as $akey=>$value) {
                        ?>
                        <span style="padding: 5px;">
                                    <input type="checkbox" value="<?=$akey;?>" name="group_leader[]" <?php  if(in_array($akey,$group_leader)){?>checked="checked" <?php }?>><?=$value;?></span>
                        <?php
                    }
                    ?>
                </div>
            </div>
        </div>

        <div class="row" style="margin-top: 20px;width: 100%">
            <div style="width: 100%;height: 30px"><label for="AliexpressStoreGroup_group_leader"><b>组员：</b></label></div>
            <?php
            foreach ($list as $key=>$value){
            ?>
               <div style="margin-top: 5px;width: 100%">
                    <div style="float: left;width: 10%;margin: 5px"><input type="checkbox" name="crewmemberArr[]" value="<?=$key;?>"  id="button<?=$key;?>" <?php  if(in_array($key,$crewmemberArr)){ ?>checked="checked" <?php }?>/><b style="color: #2ca02c"><?=$value;?></b></div>
                    <div <?php  if(in_array($key,$crewmemberArr)){ ?>style="float: left;width: 90%;margin: 5px;border: 2px solid #00a157" <?php }else{?>style="float: left;width: 90%;margin: 5px;border: 2px solid #00a157;display: none"<?php }?> id="toggle<?=$key;?>" >
                        <script type="text/javascript">
                            $("#button<?=$key;?>").click(function(){
                                $("#toggle<?=$key;?>").toggle();
                            });
                            $("#selectAll<?= $key; ?>").click(function () {
                                $("input[name='crewmember[<?= $key; ?>][]']").attr("checked","true");
                            });
                            $("#cancelAll<?= $key; ?>").click(function () {
                                $("input[name='crewmember[<?= $key; ?>][]']").removeAttr("checked");
                            });
                        </script>
                        <input type="button" id="selectAll<?= $key; ?>" value="全选" style="width: 10%;float: left">
                        <input type="button" id="cancelAll<?= $key; ?>" value="取消全选" style="width: 10%;float: left">
                        <?php
                            foreach (UebModel::model('AliexpressAccount')->getIdNamePairs() as $akey=>$value) {
                                ?>
                                <span style="padding: 10px;">
                                    <input type="checkbox" value="<?=$akey;?>" name="crewmember[<?= $key; ?>][]" <?php  if(in_array($akey,$crewmember[$key])){?>checked="checked" <?php }?>><?=$value;?></span>
                                <?php
                            }
                        ?>
                    </div>
                </div>
            <?php
            }
            ?>
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
    $(function(){
        $("#AliexpressStoreGroup_start,#AliexpressStoreGroup_charge,#AliexpressStoreGroup_group_leader").chosen();
        $(".chosen-container-single").css('width','150px');
        $(".chosen-container .chosen-results").css('max-height','150px');
    });
</script>


