<?php Yii::app()->clientscript->scriptMap['jquery.js'] = false; ?>
<style>
    .addlogistics {
        margin-top: -1px;
        border: 1px solid #c0a2bb;
        height: 300px;
        width: 350px;
        overflow: auto;
        float: left;
    }
    .addlogistics input{
        width: 350px;
    }
    .addlogistics1 {
        margin-top: -1px;
        border: 1px solid #c0a2bb;
        height: 200px;
        width: 350px;
        overflow: auto;
        float: left;
    }
    .addlogistics1 input{
        width: 350px;
    }
    .addaccount{
        margin-top: -1px;
        margin-left: 50px;
        border: 1px solid #c0a2bb;
        height: 300px;
        width: 400px;
        overflow: auto;
    }
    .addaccount input{
        width: 400px;
    }
    .chosen-container .chosen-container-single{width: 226px;}
    .chosen-container-single .chosen-single{height:20px; padding-top: 3px;}
    #Logisticsruleconfig_logistic_way_type,#Logisticsruleconfig_rule_id,#CargoCompany{width:226px;}
</style>
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
        'action' => Yii::app()->createUrl($this->route,array('logistics_type'=>$logisticsList['logistics_type'])),
        'htmlOptions' => array(
            'class' => 'pageForm',
        )
    ));
    ?>   
    <div class="pageFormContent" layoutH="56">
        <div class="row logistic_way_type_box_s">
            <?php echo $form->labelEx($model, 'country',array('style'=>'width:60px;')); ?>
            <?php echo $form->dropDownList($model, 'country',UebModel::model('Country')->getAbbreviation(),array('options'=>array( 'empty' => Yii::t('system', 'Please Select')),'style'=>'width:500px;height:120px;','id'=>'Delaydelivery_channel_country')); ?>
            <?php echo $form->error($model, 'country'); ?>
        </div>
        <div class="row">
            <div style="float: left;margin-top: 10px; margin-left:100px;width: 400px; overflow: auto;"><input type="button" id="selectAll" value="全选" style="width: 30%;float: left">
                <input type="button" id="cancelAll" value="取消全选" style="width: 30%;float: left">
            </div>
        </div>
        <div class="row ">
            <?php echo $form->labelEx($model,'已选国家',array('style'=>'width:60px;'));?>
            <br>
            <div class="addlogistics">

                <?php
                if(!empty($logisticsList['country'])) {
                    foreach ($logisticsList['country'] as $key => $value) {
                        ?>
                        <div class='abrands'><input
                                type="checkbox" name='AccountDelayDelivery[addlogistics][]'  value='<?php echo $key; ?>' style="width: 15px;height: 15px;"  checked="checked" /><?php echo $value; ?>
                        </div>
                    <?php }
                }else{ $listCountry = UebModel::model('Country')->getAbbreviation();
                    foreach ($listCountry as $k=>$v){
                    ?>
                        <div class='abrands'><input type="checkbox" name='AccountDelayDelivery[addlogistics][]'
                                                    value='<?php echo $k; ?>' style="width: 15px;height: 15px;"/><?php echo $v; ?>
                        </div>
                <?php }}?>
            </div>
            <?php echo $form->labelEx($model,'输入国家以分号(请使用英文分号";")隔开进行查询(仅限国家英文名)',array('style'=>'width:60px;'));?>
            <div class="addlogistics1">
                <textarea name="search" style="width: 350px;height: 200px" id="searchName"></textarea>
            </div>
            <div style="float: left;margin-left: 200px;margin-top: 20px"><input type="button" value="查询国家" id="searchCountry"></div>
        </div>

        <div class="row" style="margin-top: 10px">
            <?php echo $form->labelEx($model, 'logistics_type',array('style'=>'width:60px;')); ?>
            <?php echo $form->dropDownList($model, 'logistics_type',UebModel::model('LogisticsType')->getLogisticsType(),array('style'=>'width:300px;height:120px;','id'=>'Delaydelivery_channel','options'=>array($logisticsList['logistics_type']=>array('selected'=>'selected')), 'empty' => Yii::t('system', 'Please Select'),)); ?>
            <?php echo $form->error($model, 'logistics_type'); ?>
        </div>
        <div class="row account_way_type_box_s" style="margin-top: 10px">
            <?php echo $form->labelEx($model, 'account_id',array('style'=>'width:60px;')); ?>
            <?php echo $form->dropDownList($model, 'account_id',UebModel::model('AliexpressAccount')->getIdNamePairs(),array('style'=>'width:500px;height:120px;','id'=>'Delaydelivery_account_id','options'=>array('empty' => Yii::t('system', 'Please Select')))); ?>
            <?php echo $form->error($model, 'account_id'); ?>
        </div>
        <div class="row ">
            <?php echo $form->labelEx($model,'已选账号',array('style'=>'width:60px;'));?>
            <br>
            <div class="addaccount">
                <?php
                if(!empty($logisticsList['account_id'])) {
                    foreach ($logisticsList['account_id'] as $key => $value) {
                        ?>
                        <div class='abrands'><input name='addaccount_name' readOnly='true'
                                                    value='<?php echo $value; ?>'/><input
                                name='AccountDelayDelivery[addaccount][]' type='hidden' value='<?php echo $key; ?>'/>
                        </div>
                    <?php }
                }?>
            </div>
        </div>

        <div style="padding: 5px;float: left" class="sodiv">
            <span style="float: left;padding:6px">在买家确认收货剩余</span>
            <input type="text" name="AccountDelayDelivery[remain_day]" placeholder="天" class="textInput extend" value="<?php echo $logisticsList['remain_day'];?>">
            <span style="float: left;padding:6px">天</span>
            <input type="text" name="AccountDelayDelivery[remain_hours]" placeholder="时" class="textInput extend" value="<?php echo $logisticsList['remain_hours'];?>">
            <span style="float: left;padding:6px">小时；发送延长天数</span>
            <input type="text" name="AccountDelayDelivery[extended_day]" placeholder="天" class="textInput extend" value="<?php echo $logisticsList['extended_day'];?>">
            <span style="float: left;padding:6px">天</span>
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
    $("#selectAll").click(function () {
        $("input[name='AccountDelayDelivery[addlogistics][]']").attr("checked","true");
    });
    $("#cancelAll").click(function () {
        $("input[name='AccountDelayDelivery[addlogistics][]']").removeAttr("checked");
    });
    $("#searchCountry").click(function () {
        var searchName = $("#searchName").val();
        $.post("<?php echo Yii::app()->createUrl($this->route);?>",{searchName:searchName},function(result){
            var obj = eval('('+result+')');
           var string = '';
            $.each (obj.data,function (k,value) {
                string += '<div class="abrands"><input type="checkbox" name="AccountDelayDelivery[addlogistics][]" value="'+k+'" style="width: 15px;height: 15px;"/>'+value+'</div>';
            })
            $(".addlogistics").html('');
            $(".addlogistics").append(string);
        });
    });
    $(function(){
        $("#Delaydelivery_channel,#Delaydelivery_channel_country,#Delaydelivery_account_id").chosen();
        $(".chosen-container-single").css('width','300px');
        $(".chosen-container .chosen-results").css('max_height','160px');
    });
    $(".addlogistics").delegate('.abrands', 'dblclick', function(event){
        $(this).remove();
    });
    $(".addaccount").delegate('.abrands', 'dblclick', function(event){
        $(this).remove();
    });
    var get_li_obj=$(".logistic_way_type_box_s .chosen-container a");
    $('.logistic_way_type_box_s').delegate(get_li_obj,'change',function (){
        var datas=$('.logistic_way_type_box_s .chosen-drop ul li');
        datas.click(function(event){
            var value_name=$(this).text();
            $("#Delaydelivery_channel_country option").each(function(){
                var $option = $(this);
                var html = $option.html();
                var value = $option.val();
                if(html==value_name)
                {
                    if(value !=''){
                        var testis=$(this).find("option:selected").text();
                        var obj = $(".addlogistics input[value="+value+" ]");
                        if(obj && obj.length>0) {
                            alertMsg.error("该国家已存在,如要删除请双击");
                            return false;
                        }
                        $(".addlogistics").append("<div class='abrands'><input name='AccountDelayDelivery[addlogistics][]'  type='checkbox' value='"+value+"' style='width: 15px;height: 15px;'/>"+value_name+"</div>");
                    }
                }
            });
        });
    });
    var get_account_obj=$(".account_way_type_box_s .chosen-container a");
    $('.account_way_type_box_s').delegate(get_account_obj,'change',function (){
        var datas=$('.account_way_type_box_s .chosen-drop ul li');
        datas.click(function(event){
            var value_name=$(this).text();
            $("#Delaydelivery_account_id option").each(function(){
                var $option = $(this);
                var html = $option.html();
                var value = $option.val();
                if(html==value_name)
                {
                    if(value !=''){
                        var testis=$(this).find("option:selected").text();
                        var obj = $(".addaccount input[value="+value+" ]");
                        if(obj && obj.length>0) {
                            alertMsg.error("该账号已存在,如要删除请双击");
                            return false;
                        }
                        $(".addaccount").append("<div class='abrands'><input name='addaccount_name'  readOnly='true' value='"+value_name+"'/><input name='AccountDelayDelivery[addaccount][]'  type='hidden'  value='"+value+"'/></div>");
                    }
                }
            });
        });
    });

</script>


