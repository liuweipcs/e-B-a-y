<script>
$("a[class^=roleAccessId_]").roleAccessAjax1(); 
</script>



<div layoutH="10" style="float:left; display:block; overflow:auto; width:322px; border:solid 1px #CCC; line-height:21px; background:#fff; resize: both; ">
	<div class="panelBar"></div>
	<div  style="border:solid 1px #DFE8F6; overflow: auto; resize: both;  width: 320px;  height: 350px;  min-height: 280px; ">
		<?php echo $this->renderPartial('users.components.views.Listuser', array( 'class' => 'tree treeFolder', 'id' => 'roleTreePanel2', 'root' => Yii::t('users', '所有用户'),'menuId' => '','menuIdl' => $menuid,'hasroleIds' => $hasroleIds)); ?>
    </div>
	<div style="border:solid 1px #DFE8F6; overflow: auto; resize: both;  width: 320px;  height: 430px;  min-height: 320px;">
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
</div>  