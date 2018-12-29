<?php Yii::app()->clientscript->scriptMap['jquery.js'] = false; ?>
<style type="text/css">
    .extend{width: 100px;height: 20px}
</style>
<div class="pageContent">
    <?php
    $form = $this->beginWidget('ActiveForm', array(
        'id' => 'configureForm',
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
    <div class="tabs">
        <div class="tabsContent" style="height:500px;">
            <div class="pageFormContent" layoutH="150" id="addselectID">
                <div class="row"><button type="button" id="addCeshi">新加一个配置</button></div>
                <?php
                if(!empty($list)){
                    foreach ($list as $k=>$value){
                        ?>
                        <script type="text/javascript">
                            $(function(){
                                $("#countryAccount<?php echo $k;?>").chosen();
                                $("#channelAccount<?php echo $k;?>").chosen();
                            })
                        </script>
                        <div class="row countryId" >
                            <div style="padding: 5px;float: left" class="sodiv"><span style="float: left;padding:6px">国家：</span>
                                <select id="countryAccount<?php echo $k;?>" style="width:100px" name="AccountDelayDelivery[<?php echo $k;?>][country]">
                                    <option value="">--选择国家--</option>
                                    <?php
                                    foreach (UebModel::model('Country')->getAbbreviation() as $key=>$val){
                                        ?>
                                        <option value='<?php echo $key;?>' <?php if($value['country'] == $key){?> selected="selected"<?php }?>><?php echo $val;?></option>
                                    <?php }?>
                                </select>
                            </div>
                            <div style="padding: 5px;float: left" class="sodiv"><span style="float: left;padding:6px">物流类型：</span>
                                <select id="channelAccount<?php echo $k;?>" style="width:150px" name="AccountDelayDelivery[<?php echo $k;?>][logistics_type]">
                                    <option value="">--选择渠道--</option>
                                    <?php
                                    $LogisticsList = UebModel::model('LogisticsType')->getLogisticsType();
                                    ?>
                                    <?php
                                    foreach ($LogisticsList as $key=>$val){
                                        ?>
                                        <option value="<?php echo $key;?>" <?php if($value['logistics_type'] == $key){?> selected="selected"<?php }?>><?php echo $val;?></option>
                                    <?php }?>
                                </select>
                            </div>
                            <div style="padding: 5px;float: left" class="sodiv">
                                <span style="float: left;padding:6px">在买家确认收货剩余</span>
                                <input type="text" name="AccountDelayDelivery[<?php echo $k;?>][remain_day]" placeholder="天" class="textInput extend" value="<?php echo $value['remain_day'];?>">
                                <span style="float: left;padding:6px">天</span>
                                <input type="text" name="AccountDelayDelivery[<?php echo $k;?>][remain_hours]" placeholder="时" class="textInput extend" value="<?php echo $value['remain_hours'];?>">
                                <span style="float: left;padding:6px">小时；发送延长天数</span>
                                <input type="text" name="AccountDelayDelivery[<?php echo $k;?>][extended_day]" placeholder="天" class="textInput extend" value="<?php echo $value['extended_day'];?>">
                                <span style="float: left;padding:6px">天</span>
                            </div>
                            <div style="padding: 5px;float: left"><button type="button" onclick="SkuDelete(this);">删除</button></div>
                        </div>
                    <?php }}else{?>
                    <div class="row countryId" >
                        <div style="padding: 5px;float: left" class="sodiv"><span style="float: left;padding:6px">国家：</span>
                            <select id="countryAccount" style="width:100px" name="AccountDelayDelivery[1][country]">
                                <option value="">--选择国家--</option>
                                <?php
                                foreach (UebModel::model('Country')->getAbbreviation() as $key=>$value){
                                    echo "<option value='".$key."'>".$value."</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div style="padding: 5px;float: left" class="sodiv"><span style="float: left;padding:6px">物流方式：</span>
                            <select id="channelAccount" style="width:200px" name="AccountDelayDelivery[1][logistics_type]">
                                <option value="">--选择物流方式--</option>
                                <?php
                                $LogisticsList = UebModel::model('LogisticsType')->getLogisticsType();
                                ?>
                                <?php
                                foreach ($LogisticsList as $key=>$value){
                                    echo "<option value='".$key."'>".$value."</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div style="padding: 5px;float: left" class="sodiv">
                            <span style="float: left;padding:6px">在买家确认收货剩余</span>
                            <input type="text" name="AccountDelayDelivery[1][remain_day]" placeholder="天" class="textInput extend" value="0">
                            <span style="float: left;padding:6px">天</span>
                            <input type="text" name="AccountDelayDelivery[1][remain_hours]" placeholder="时" class="textInput extend" value="0">
                            <span style="float: left;padding:6px">小时；发送延长天数</span>
                            <input type="text" name="AccountDelayDelivery[1][extended_day]" placeholder="天" class="textInput extend" value="0">
                            <span style="float: left;padding:6px">天</span>
                        </div>
                        <div style="padding: 5px;float: left"><button type="button" onclick="SkuDelete(this);">删除</button></div>
                    </div>
                <?php }?>
            </div>
        </div>
    </div>
    <div class="formBar">
        <ul>
            <li>
                <div class="buttonActive">
                    <div class="buttonContent">
                        <button type="button" id="extendConfig"><?php echo Yii::t('system', 'Save')?></button>
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
    $(function(){
        $("#countryAccount").chosen();
        $("#channelAccount").chosen();
        $(".chosen-container-single").css('width','150px');
        $(".chosen-container .chosen-results").css('max-height','150px');
        $("#extendConfig").click(function(){
            $.ajax({
                type: "POST",
                url:"/systems/aliexpress/configure/id/<?php echo $id;?>",
                data:$('#configureForm').serialize(),// 要提交的表单
                success: function(msg) {
                    var obj = eval('('+msg+')');
                    if(obj.status==1){
                        alertMsg.info(obj.message);
                        $.pdialog.closeCurrent();
                    }else {
                        alertMsg.info(obj.message);
                    }
                }, error: function(error){
                    alertMsg.info(error);
                }
            });
        });
    });
    $('.chosen-select',$.pdialog.getCurrent()).chosen({});
    $("#addCeshi").on('click', function() {
//        $(".countryId").append($('.countryId').clone(true));
        var String = '';
        var country= '';
        var channel= '';
        country = $('#countryAccount').html();
        channel = $('#channelAccount').html();
        String = '<div class="row countryId" >'+
            '<div style="padding: 5px;float: left" class="sodiv"><span style="float: left;padding:6px">国家：</span> <select id="" name="" style="width: 100px">'+
            country +
            '</select></div>'+
            '<div style="padding: 5px;float: left" class="sodiv"><span style="float: left;padding:6px">渠道：</span><select id="" name="" style="width: 200px">'+
            channel +
            '</select></div>'+
            '<div style="padding: 5px;float: left" class="sodiv">'+
            '<span style="float: left;padding:6px">在买家确认收货剩余</span>'+
            '<input type="text" name="" placeholder="天" class="textInput extend" value="0">'+
            '<span style="float: left;padding:6px">天</span>'+
            '<input type="text" name="" placeholder="时" class="textInput extend" value="0">'+
            '<span style="float: left;padding:6px">小时；发送延长天数</span>'+
            '<input type="text" name="" placeholder="天" class="textInput extend" value="0">'+
            '<span style="float: left;padding:6px">天</span>'+
            '</div>'+
            '<div style="padding: 5px;float: left"><button type="button" onclick="SkuDelete(this);">删除</button></div>'+
            '</div>';
        $('.countryId').last().append(String);
        $("#addselectID div.row").each(function(k){
            var sontr = $(this).children('div.sodiv');
            if(k > 0){
                sontr.eq(0).find("select").attr('name','AccountDelayDelivery['+k+'][logistics_type]');
                sontr.eq(1).find("select").attr('name','AccountDelayDelivery['+k+'][logistics_type]');
                sontr.eq(2).find("input").eq(0).attr('name','AccountDelayDelivery['+k+'][remain_day]');
                sontr.eq(2).find("input").eq(1).attr('name','AccountDelayDelivery['+k+'][remain_hours]');
                sontr.eq(2).find("input").eq(2).attr('name','AccountDelayDelivery['+k+'][extended_day]');
            }
            if(k>=2){
                sontr.eq(0).find("select").attr('id','countryAccount'+k);
                sontr.eq(1).find("select").attr('id','channelAccount'+k);
                $("#countryAccount"+k).chosen();
                $("#channelAccount"+k).chosen();

            }
        });
    })
    function SkuDelete(del) {
        if($(".countryId").size() >1){
            $(del).parent().parent().remove();
        }
    }

</script>


