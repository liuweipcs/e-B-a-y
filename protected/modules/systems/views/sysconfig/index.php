<style type="text/css">
    ul.rightTools {float:right; display:block;}
    ul.rightTools li{float:left; display:block; margin-left:5px}
</style>
<script type="text/javascript">
    $('#sysconfig_siderBarMenubox').siderBarMenu();
</script>
<div class="pageContent" style="padding:5px;">                       
    <div>
        <div layoutH="10" style="float:left; display:block; overflow:auto; width:240px; border:solid 1px #CCC; line-height:21px; background:#fff">
            <div class="panelBar">
                <ul class="toolBar">
                    <li></li>          
                </ul>
            </div>
            <?php echo $this->renderPartial('systems.components.views.SiderBarMenubox'); ?>                                   
        </div>
        <div id="settingMenuBox" class="unitBox" style="margin-left:246px;"></div>
    </div>                     
</div>
<script type="text/javascript">
    $(function(){           
        setTimeout(function(){           
            $('#sysconfig_siderBarMenubox div a:first').trigger('click');
        }, 10);
    });
</script>





