<style type="text/css">
    .grid .gridTbody td div{
        height:auto;
        overflow: hidden;
    }
</style>
<script type="text/javascript">
    $(function() {
        $("img").lazyload({
            effect : "fadeIn"
        });

        $('[name="user_map_position_type"]').click(function(){
            var bringObj = $('#user_map_position_bring_data');
            bringObj.attr('urlname','/systems/usermapposition/list');
            var rel = eval('('+bringObj.attr('rel')+')');
            var typeObj = {'userMapPositionType':$(this).val()};
            $.extend(rel,typeObj);
            bringObj.attr({'rel':JSON.stringify(rel)});
        });
    });
    function bringDivLoadCustom(args)
    {
        $box = $('#' + args.target);
        if (!$.isEmptyObject($box.attr('rel'))) {
            $.extend(args, DWZ.jsonEval($box.attr('rel')));
            if ($box.attr('rel')[0]) {
                if(args.selectType=='single'){
                    //update by ethanhu 2013-12-27
                    if( args.id ){
                        var idArr = args.id.split(',');
                        if(idArr.length > 1){
                            for(i in args){
                                delete args[i];
                            }//update end
                            alertMsg.warn($.regional.system.msg.onlySelectOne);//'只能选择一条记录'
                            return false;
                        }
                    }
                }
            }

            if ( !$.isEmptyObject(args.callback) ) {
                var callback = args.callback;
            } else {
                var callback = function(data) {
                    if ( args.type == 'input' ) {
                        $('#'+args.target ).val(data);
                    } else {
                        $box.find("[layoutH]").layoutH();
                    }
                };
            }
            $box.ajaxUrl({
                type: "POST",
                url:args.url,
                data: args,
                callback: function(data) {
                    setTimeout(callback+"('"+data+"')", 10);
                    $.pdialog.closeCurrent();
                }
            });
        } else {
            alertMsg.warn($.regional.system.msg.pleaseSelectAOption);
            return false;
        }
//            $.pdialog.closeCurrent();
    }
    function userMapPositionBringUser(obj)
    {
        var $this = $(obj), args={};
        var $unitBox = $this.parents(".unitBox:first");
        $unitBox.find("[name='"+$this.attr("multLookupCustom")+"']").filter(":checked").each(function(){
            reg=/^{(.*)}$/;   //extentions by Bob
            if(! reg.test($(this).val())) {
                var _args = {};
                _args.id = $(this).val();
            } else {
                var _args = DWZ.jsonEval($(this).val());
            }

            for (var key in _args) {
                var value = args[key] ? args[key]+"," : "";
                args[key] = value + _args[key];
            }
        });
        if ($.isEmptyObject(args)) {
            alertMsg.error($this.attr("warn") || DWZ.msg("alertSelectMsg"));
            return false;
        }
        //extentions by Bob
        if ($this.attr('rel')[0]) {
            $.extend(args, DWZ.jsonEval($this.attr('rel')),{'userMapPositionType':$('[name="user_map_position_type"]:checked').val()});
            bringDivLoadCustom(args);
        } else {
            $.bringBack(args);
        }
    }
</script>
<?php
Yii::app()->clientscript->scriptMap['jquery.js'] = false;
$this->widget('UGridView', array(
    'id' => 'user-grid',
    'dataProvider' => $model->search('user_status=1'),
    'filter' => $model,
    'columns' => array(
        array(
            'class'              => 'CCheckBoxColumn',
            'name'               => 'orgId',
            'selectableRows'     => 2,
            'value'              => '$data->id',
            'htmlOptions'=> array('style' => 'width:10px;height:32px;',),
        ),
        array(
            'name' => 'id',
            'value' => '$row+1',
            'htmlOptions'=> array('style' => 'width:30px;height:32px;',),
        ),
        array(
            'name' => 'user_name',
            'value' => '$data->user_name',
            'htmlOptions'=> array('style' => 'width:80px;height:32px;',),
        ),
        array(
            'name' => 'en_name',
            'value' => '$data->en_name',
            'htmlOptions'=> array('style' => 'width:80px;height:32px;',),
        ),
        array(
            'name' => 'user_full_name',
            'value' => '$data->user_full_name',
            'htmlOptions'=> array('style' => 'width:80px;height:32px;',),
        ),
        array(
            'name' => 'en_name',
            'value' => '$data->en_name',
            'htmlOptions'=> array('style' => 'width:80px;height:32px;',),
        ),
        array(
            'name' => 'user_full_name',
            'value' => '$data->user_full_name',
            'htmlOptions'=> array('style' => 'width:80px;height:32px;',),
        ),
        array(
            'name'  => 'department_id',
            'value' => '$data->department_id>0 ? UebModel::model("Department")->getDepartment($data->department_id):"--"',
            'htmlOptions'=> array('style' => 'width:80px;height:32px;',),
        ),
        array(
            'name'  => 'user_status',
            'value' => 'VHelper::getStatusLable($data->user_status)',
            'htmlOptions'=> array('style' => 'width:80px;height:32px;',),
        ),
        array(
            'name'  => 'is_intranet',
            'value' => 'empty($data->is_intranet) ? "不允许":"允许"',
            'htmlOptions'=> array('style' => 'width:80px;height:32px;',),
        )
    ),
    'toolBar'       =>array(
        array(
            'text' => Yii::t('system', 'Please Select'),
            'type' => 'button',
            'htmlOptions' => array(
//                'class' => 'edit',
                'multLookupCustom' => 'user-grid_c0[]',
                'warn' => Yii::t('users', 'Please select a user'),
                'rel' => '{target:"jobPositionUser", url:"/systems/usermapposition/list"}',
                'urlname'  => '/systems/usermapposition/list',
                'id'=>'user_map_position_bring_data',
                'onclick' => 'userMapPositionBringUser(this)'
            )
        ),
        array(
            'type' => 'radioList',
            'data' => UserMapPosition::$typeMap,
            'value' => 1,
            'name' => 'user_map_position_type',
            'lineFeed' => false,   //不换行
            'htmlOptions' => array(
                'urlname'  => '/systems/usermapposition/list'
                /*'class' => 'edit',
                'multLookup' => 'user-grid_c0[]',
                'warn' => Yii::t('users', 'Please select a user'),
                'rel' => '{target:"jobPositionUser", url:"/systems/usermapposition/list"}',
                'urlname'  => '/systems/usermapposition/list',*/
            )
        ),
    ),
    'tableOptions' 	=> array(
        'layoutH' 	=> 160,
    ),
    'pager' 		=> array()
));

?>
