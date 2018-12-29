<script type="text/javascript">
    $("a[id^=treeItem_]").menuCmDialog(); 
</script>

<div class="pageContent" style="padding:5px">        
    <div>
		<div class="panelBar">
			<ul class="toolBar">
				<li></li>       
			</ul>
		</div>
		<div layoutH="50" style="float:left; display:block; overflow:auto; width:300px; border:solid 1px #CCC; line-height:21px; background:#fff">
			<ul class="tree treeFolder" rel="treeCm" >
				<li>
					<a id="treeItem_0" ><?php echo Yii::t('system', 'Root')?></a>
					<?php echo $this->renderPartial('systems.components.views.MenuTree', array( 'type' => 'menu','menuId'=>'0')); ?>
				</li>
			</ul>
		</div>	
		<div style="border:solid 1px #DFE8F6; overflow: auto; resize: both;  width: 420px;  height: 630px;  min-height: 320px;float: left;">
				
			<div id="roleUserPanel2"></div>
		</div>  
		<div id="addanddelete" style="border:solid 1px #DFE8F6; overflow: auto; resize: both;  width: 320px;  height: 430px;  min-height: 320px;">
			<div class="panelBar">
				<ul class="toolBar">
					<li class="">
						<a class="add" mask="true" lookupGroup="org3"  href="/users/users/list1/target/dialog/pagenum/100" width="800" height="480" >
							<span><?php echo Yii::t('users', 'Add User');?></span>
						</a>                         
					</li>
					<li>
						<a title="<?php echo Yii::t('system', 'Really want to delete these records?')?>" target="selectedTodo" id= "deleteRoleUsersm" rel="ids" href="/users/users/ulist" postType="string" class="delete" callback='ajaxDeleteCallback'>
							<span><?php echo Yii::t('users', 'Batch delete user')?></span>
						</a>
					</li>
				</ul>                  
			</div>
			<div id="roleUserPanel1"></div>
	    </div>		

		<div id="menuAccessPanel2" class="unitBox" style="margin-left:246px;"></div>
    </div>
</div>


