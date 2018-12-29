<script type="text/javascript">
    $("a[id^=roleAccessId_]").roleAccessAjax(); 
    $("a[id^=roleAccessId_]").roleCmDialog();
    //$("a#setAuth").userAuthAjax();
</script>
<div class="pageContent" style="padding:5px">        
    <div>
        <div layoutH="10" style="float:left; display:block; overflow:auto; width:322px; border:solid 1px #CCC; line-height:21px; background:#fff; resize: both; ">
            <div class="panelBar"></div>
            <div  style="border:solid 1px #DFE8F6; overflow: auto; resize: both;  width: 320px;  height: 350px;  min-height: 280px; ">
                <?php echo $this->renderPartial('users.components.views.RoleTree', array( 'class' => 'tree treeFolder collapse', 'id' => 'roleTreePanel', 'root' => Yii::t('users', 'All Roles'),'menuId' => '')); ?>
            </div>
            <div style="border:solid 1px #DFE8F6; overflow: auto; resize: both;  width: 320px;  height: 430px;  min-height: 320px;">
                <div class="panelBar">
                    <ul class="toolBar">
                        <li class="">
                            <a class="add" mask="true" lookupGroup="org3"  href="/users/users/list/target/dialog/pagenum/100" width="800" height="480" >
                                <span><?php echo Yii::t('users', 'Add User');?></span>
                            </a>                         
                        </li>
                        <li>
                            <a title="<?php echo Yii::t('system', 'Really want to delete these records?')?>" target="selectedTodo" id= "deleteRoleUsers" rel="ids" href="/users/users/ulist" postType="string" class="delete" callback='ajaxDeleteCallback'>
                                <span><?php echo Yii::t('users', 'Batch delete user')?></span>
                            </a>
                        </li>
                    </ul>                  
                </div>
                <div id="roleUserPanel"></div>
            </div>      
        </div>  
        <div id="menuAccessPanel" class="unitBox" style="margin-left:246px;"></div>
    </div>
</div>