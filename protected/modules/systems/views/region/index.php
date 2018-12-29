<style type="text/css">
    ul.rightTools {float:right; display:block;}
    ul.rightTools li{float:left; display:block; margin-left:5px}
</style>
<div class="pageContent" style="padding:5px;">         
    <div>
        <div layoutH="10" style="float:left; display:block; overflow:auto; width:240px; border:solid 1px #CCC; line-height:21px; background:#fff">
            <!--<div class="panelBar">
                <ul class="toolBar">
                    <li></li>
                </ul>
            </div>-->
            <?php echo $this->renderPartial('systems.components.views.ProTree', array( 'class' => 'tree treeFolder', 'id' => 'proTreePanel')); ?>                  
        </div>
        <div id="cityblock" layoutH="10" style="float:left;margin-left:10px; display:block; overflow:auto; width:240px; border:solid 1px #CCC; line-height:21px; background:#fff">
            <div>
                <li style="padding:10px;" id="cityBox">
                </li>          
            </div>
                              
        </div>
        <div id="areablock" layoutH="10" style="float:left;margin-left:10px; display:block; overflow:auto; width:240px; border:solid 1px #CCC; line-height:21px; background:#fff">
            <div>
                <li style="padding:10px;" id="areaBox">
                </li>          
            </div>
        </div>
        
        <!--  <div id="areaBox" class="unitBox" style="margin-left:500px;">ddd</div>-->
        
    </div>                    
</div>
<script type="text/javascript">
    $(function(){           
        $('#cityblock').hide();
        $('#areablock').hide();
    });
    function showarea(region){
    	if(region=='pro'){          
	        $('#cityblock').show();
	        $('#areablock').hide();
    	}
    	if(region=='city'){          
    		$('#areablock').show();
    	}
    }
</script>


