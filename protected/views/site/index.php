<style type="text/css">
    .dashboard_div{
        width: 42%;
        border: 1px solid #e66;
        margin: 5px;
        float: left;
        min-height: 100px;
    }
    .dashboard_div_index{
        width: 54%;
        border: 1px solid #e66;
        margin: 5px;
        float: left;
        min-height: 100px;
    }
    .dashboard_row{
        border: 1px solid rgb(184, 208, 214);
        padding: 5px;
        margin: 5px;
        display: block;
    }

</style>
<div class="page unitBox" style="display: block;">
    <table style="margin-left: 10px;"><tr><td><b>控制面板 ---</b></td><td><a class='setDashBoard btnEdit' style="margin:4px 3px 0 15px;" href="javascript:void(0);"></a><a class="btnRefresh" href="javascript:void(0);"></a></td></tr></table><hr>
    <div class="pageContent" layouth="42" style=" overflow: auto;">
        <!--左边列-->
        <div class="sortDrag dashboard_div" selector=".panelBar" style="height: auto;">

            <iframe id="iframeId"  width="100%" src="<?php echo $this->createUrl('/systems/tasks/profile');?>"  frameborder="0"  marginheight="no" marginwidth="no" scrolling="no" frameborder="0"></iframe>
            <script type="text/javascript" language="javascript">

                $("#iframeId").load(function () {
                    var mainheight = $(this).contents().find("body").height() + 30;
                    $(this).height(mainheight);
                });

            </script>
            <?php if (isset($areas['left'])):?>
            <?php foreach ($areas['left'] as $d):?>
                    <?php if ($d['dashboard_url'] == '/systems/tasks/profile') continue; ?>
            <div class="dashboard_row dashboard_<?php echo $d['id'];?>" data-id="<?php echo $d['id'];?>">
                <div class="searchBar" style="float:right ">
                    <table class=""  border="0">
                        <tr>
                            <td><a class="btnEdit" href="javascript:doShowSet(this,<?php echo $d['id'];?>);"></a></td>
                            <td><a class="btnRefresh" href="javascript:doReload(this,<?php echo $d['id'];?>);"></a></td>
                            <td><a class="btnDel" href="javascript:doAreaHidd(this,'hiddbyx', <?php echo $d['id'];?>);"></a></td>
                        </tr>
                    </table>
                </div>
                <!--控制台标题区  拖拽区-->
               
                <div class="panelBar">
                    <ul class="toolBar">
                        <li class=""><h1><span><?php echo $d['dashboard_title'];?></span></h1></li>
                    </ul>
                </div>
                <!--控制台内容区-->
                <div class="dashboard_info" data-id="<?php echo $d['id'];?>" data-url="<?php echo $d['dashboard_url'];?>" id="dashboard_info_<?php echo $d['id'];?>">
                </div>
            </div>
            <?php endforeach?>
            <?php endif?>
        </div>
        	
        <!--右边列-->
        <div class="sortDrag dashboard_div_index" selector=".panelBar" style="height: auto;">
            <iframe id="iframeId1"  width="100%" height="600" src="<?php echo $this->createUrl('/systems/yibaihelper/listindex');?>"  frameborder="0"  marginheight="no" marginwidth="no" scrolling="no" frameborder="0"></iframe>

            <script type="text/javascript" language="javascript">

                $("#iframeId1").load(function () {
                    var mainheight = $(this).contents().find("body").height() + 40;
                    $(this).height(mainheight);
                });

            </script>
            <?php if (isset($areas['right'])):?>
            <?php foreach ($areas['right'] as $d):?>
                    <?php if ($d['dashboard_url'] == '/systems/yibaihelper/listindex') continue; ?>
            <div class="dashboard_row dashboard_<?php echo $d['id'];?>" data-id="<?php echo $d['id'];?>">
                    <div class="searchBar" style="float:right ">
                        <table class="list"  border="0">
                            <tr>
                                <td><a class="btnEdit" href="javascript:doShowSet(this,<?php echo $d['id'];?>);"></a></td>
                                <td><a class="btnRefresh" href="javascript:doReload(this,<?php echo $d['id'];?>);"></a></td>
                                <td><a class="btnDel" href="javascript:doAreaHidd(this,'hiddbyx', <?php echo $d['id'];?>);"></a></td>
                            </tr>
                        </table>
                    </div>
                    <!--控制台标题区  拖拽区-->
                    <div class="panelBar">
                        <ul class="toolBar">
                            <li class=""><h1><span><?php echo $d['dashboard_title'];?></span></h1></li>
                        </ul>
                    </div>
                    <!--控制台内容区-->
                    <div class="dashboard_info" data-id="<?php echo $d['id'];?>" data-url="<?php echo $d['dashboard_url'];?>" id="dashboard_info_<?php echo $d['id'];?>"></div>
                </div>
            <?php endforeach?>
            <?php endif?>
        </div>
        
        <!--start 所有控制台栏目列表区域-->
        <div class="sortDrag dashboard_div dashboard_control" selector='.panelBar' style="width:10%;height:100%;float: right;display: none" data-info="father">
          
            <?php
//             $this->widget('UDragView', array(
//             		'dataProvider'=>$dataProvider, 
//             		'itemView'=>'_view',
//             		'template'=>"{items}\n{pager}",
//             ));
            ?>
            
            <p style="margin-top:80%;text-align: center">拖拽到此区域关闭消息框</p>
<!--            <input type="submit" value="保 存" name="yt0">-->
        </div>
        <!--end 所有控制台栏目-->
    </div>
</div>
<script language="javascript">
$(".btnRefresh").click(function(){
	var url = '/systems/dashboardrole/asynrefresh';
	var data = {'type':1};
	$.ajax({
		type : "get",
		async : false,  
		url : url,
		data : data,
		timeout:1000,
		success:function(dates){

		}
	});
});
        $(function () {
            //get every dashboard config and data
            $("div .dashboard_info").each(function(){
                var dashboard_id = $(this).attr('data-id');
                var dashboard_url = $(this).attr('data-url'); 
                var self = this;
//                $(this).ajaxUrl({
//                    type: "POST", url: dashboard_url, data: {'dashboard_id':dashboard_id}, callback: function() {}
//                });
                $.ajax({ url: dashboard_url, data: {'dashboard_id':dashboard_id}, success: function(responseText){
                    $(self).html(responseText).initUI();
                    $(self).find('.buttonContent').children().attr('alt','1');
                }});
            });
        $("a.setDashBoard").click(function(){
            if($("div.dashboard_control").is(":hidden")){
                $("div.dashboard_div").css('width','43%');
                $("div.dashboard_control").css('width','10%');
                $("div.dashboard_control").show();
            }else{
                $("div.dashboard_div").css('width','48%');
                $("div.dashboard_control").hide();
            }
        });
    })

    /**
     * to save user toolbar config
     * @param obj
     * @returns {boolean}
     */
    function doToolBar(obj){
        var config=$(obj).serialize();
        //save user config
        $(obj).ajaxUrl({
            type: "POST", url: '/systems/dashboardrole/person', data: config, callback: function() {}
        })
        //reload data
        var dashboard_id = $(obj).parents('div .dashboard_info').attr('data-id');
        var dashboard_url = $(obj).parents('div .dashboard_info').attr('data-url');
        $(obj).parents('div .dashboard_info').ajaxUrl({
            type: "POST", url: dashboard_url, data: {'dashboard_id':dashboard_id}, callback: function() {}
        })
        return false;
    }

    /**
     * show or hidden a dashboard
     * @param todo  'hidd'
     * @param dashboard_id
     */
        function doAreaHidd(obj, todo, dashboard_id){
            if (todo=='hiddbyx') {
            	$('#dashboard_info_'+dashboard_id).parent().toggle('slow');
            }
            $(obj).children('div .dashboard_info').hide();
            $(obj).children('div .searchBar').hide();
            $.ajax({
                type: "POST",
                url: '/systems/dashboardrole/person',
                data: {
                    'UserConfig[todo]':'hidd',
                    'UserConfig[dashboard_id]':dashboard_id
                },
                callback: function() {}
            })
        }
        function doAreaAdd(obj,todo, dashboard_id){

           $(obj).children('div .searchBar').show();
//            $(obj).children('div .dashboard_info').show();
            doReload(obj,dashboard_id);
            $.ajax({
                type: "POST",
                url: '/systems/dashboardrole/person',
                data: {
                    'UserConfig[todo]':todo,
                    'UserConfig[dashboard_id]':dashboard_id
                },
                callback: function() {}
            })
        }
        /**
         * sort every area
         * @param obj
         * @param everyDivIds
         * @param everyDivPos
         * @returns {boolean}
         */
        function doAreaSort(obj, everyDivIds, everyDivPos){
            var dashboard_id = $(obj).attr('data-id');
            $.ajax({
                type: "POST",
                url: '/systems/dashboardrole/person',
                data: {
                    'UserConfig[sort][ids]':everyDivIds,
                    'UserConfig[sort][pos]':everyDivPos,
                    'UserConfig[dashboard_id]':dashboard_id
                },
                callback: function() {}
            })
            return false;
        }
        function doReload(obj, dashboard_id){
            var dashboard_url = $('#dashboard_info_'+dashboard_id).attr('data-url');
            $('#dashboard_info_'+dashboard_id).show();
            $('#dashboard_info_'+dashboard_id).ajaxUrl({
                type: "POST", url: dashboard_url, data: {'dashboard_id':dashboard_id}, callback: function() {}
            })
        }
        function doShowSet(obj, dashboard_id){
            $('#dashboard_info_'+dashboard_id).find('.pageHeader').toggle();
        }

      

</script>