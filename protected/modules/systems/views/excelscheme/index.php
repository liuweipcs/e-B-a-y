<style type="text/css">
    ul.rightTools {float:right; display:block;}
    ul.rightTools li{float:left; display:block; margin-left:5px}
</style>
<script type="text/javascript">
    $('#siderBarMenubox').siderBarMenu();
</script>
<div class="pageContent" style="padding:5px;">                       
    <div>
        <div layoutH="10" style="float:left; display:block; overflow:auto; width:300px; border:solid 1px #CCC; line-height:21px; background:#fff">          
            <?php echo $this->renderPartial('systems.components.views.SiderBarExcelSchemebox'); ?>                                   
        </div>
        <div id="excelSchemeListBox" class="unitBox" style="margin-left:305px;"></div>
    </div>                     
</div>
<script type="text/javascript">
    $(function(){           
    	var $p = navTab.getCurrentPanel();
        setTimeout(function(){           
            $('#siderBarMenubox div a:first', $p).trigger('click');
        }, 10);
    });
</script>
