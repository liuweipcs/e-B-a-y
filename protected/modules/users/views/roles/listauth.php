<style type="text/css">
    ul.rightTools {float:right; display:block;}
    ul.rightTools li{float:left; display:block; margin-left:5px}  
</style>
<script>
$("a[class^=roleAccessId_]").roleAccessAjax1(); 
</script>
<form method="post" " class="pageFormmenuauth" action="systems/menu/ulistm"  onsubmit="return divSubmitRefreshmenu(this)">
   <div class="panelBar" style="position:fixed;margin-top: -27px;">  
        <?php echo CHtml::submitButton(Yii::t('users', 'Save')) ?>
        <?php echo CHtml::hiddenField('roleId', @$_REQUEST['roleId']); ?>
        <?php echo CHtml::hiddenField('userId', @$_REQUEST['userId']); ?>
        <?php echo CHtml::hiddenField('resources', @$_REQUEST['resources'], array('id' => 'resourcesm')); ?>
    </div>
	<div class="pageContent" style="padding:5px;">         
		<div>
			<div layoutH="10" style="float:left; display:block; overflow:auto; width:340px; border:solid 1px #CCC; line-height:21px; background:#fff">
				<div class="panelBar">
					<ul class="toolBar">
						<li></li>          
					</ul>
				</div>
				
				<div  style="border:solid 1px #DFE8F6; overflow: auto; resize: both;  width: 420px;    min-height: 280px; ">
					<?php echo $this->renderPartial('users.components.views.Listauth', array( 'class' => 'tree treeFolder', 'id' => 'roleTreePanel', 'root' => Yii::t('users', 'All Roles'),'menuId' => '','menuIdl' => $menuid,'hasroleIds' => $hasroleIds)); ?>
				</div>
			   
			</div>
			
		</div>                    
	</div>
	
</form>

<script type="text/javascript">
    var menuid=<?php echo $menuid ?>;
    $(function(){           
        setTimeout(function(){
            $('#depTreePanel a:first').trigger('click');
        }, 10);
    });
	function divSubmitRefreshmenu(form) {
        var $form = $(form);
        var resources = [];
        $form.find('.checked').each(function() {
            resources.push($(this).parent('div').find('a').attr('id'));
        });
        if (resources.length > 0) {
            resources.join()
            $('#resourcesm').val(resources);
        } else {
            $('#resourcesm').val('');
        }
        $.ajax({
            type: form.method || 'POST',
            url: $form.attr("action"),
            data: $form.serializeArray(),
            dataType: "json",
            cache: false,
            success: function(json) {
                DWZ.ajaxDone(json);
				$("#treeItem_"+menuid).trigger("click");
            },
            error: DWZ.ajaxError
        });
        return false;
    }
	
</script>