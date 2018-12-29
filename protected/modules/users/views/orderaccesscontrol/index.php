<script type="text/javascript">
    $("a[id^=dep_]").depCmDialog();
</script>
<style type="text/css">
    ul.rightTools {float:right; display:block;}
    ul.rightTools li{float:left; display:block; margin-left:5px}   
</style>
<div class="pageContent" style="padding:5px;">         
    <div>
        <div layoutH="10" style="float:left; display:block; overflow:auto; width:240px; border:solid 1px #CCC; line-height:21px; background:#fff">
            <div class="panelBar">
                <ul class="toolBar">
                    <li></li>          
                </ul>
            </div>
            <?php echo $this->renderPartial('users.components.views.DeptTree3', array( 'class' => 'tree treeFolder', 'id' => 'depTree','menuId' => '0')); ?>

        </div>
        <div id="jbsxBox" class="unitBox" style="margin-left:246px;"></div>
    </div>                    
</div>
<script type="text/javascript">
    $(function(){           
        setTimeout(function(){
            $('#depTree a:first').trigger('click');
        }, 10);
    });
</script>


