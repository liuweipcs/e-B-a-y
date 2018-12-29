<div class="grid-view">
    <div style="border:1px #B8D0D6 solid;display: none" class="pageHeader">
        <form method="post" action="#" id="pagerForm" onsubmit="return doToolBar(this);">
            <div class="searchBar">
                <div class="searchContent">
                    <div class="left h25 ml10">
                        配置参数1：<input type="text" id="UserConfig[config1]" name="UserConfig[config1]" value="<?php if (isset($config1)) echo $config1;?>" class="textInput">
                        配置参数2：<input type="text" id="UserConfig[config2]" name="UserConfig[config2]" value="<?php if (isset($config2)) echo $config2;?>" class="textInput">
                        <input type="hidden" id="UserConfig[dashboard_id]" name="UserConfig[dashboard_id]" value="<?php if (isset($_REQUEST['dashboard_id'])) echo $_REQUEST['dashboard_id'];?>" class="textInput">
                    </div>
                </div>
                <div class="subBar">
                    <ul>
                        <li>
                            <div class="buttonActive">
                                <div class="buttonContent">
                                    <button type="submit">保 存</button>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </form>
    </div>

    <div class="gridTbody">
        <table style="width:50%;text-align: center;"><tbody>
            <tr class="odd">
                <td>控制台ID：<?php echo $_REQUEST['dashboard_id'];?></td>
            </tr>
            <tr class="even">
                <td>物流类型总数：<a href="/logistics/logisticstype/dashboardtest/dashboard_id/1" style="color:blue;" target="navTab"><?php echo $total;?></a></td>
            </tr>
            </tbody>
        </table>
    </div>

</div>