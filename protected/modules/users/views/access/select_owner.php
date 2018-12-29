<?php Yii::app()->clientscript->scriptMap['jquery.js'] = false;?>
<div class="pageContent"> 
    <div class="pageFormContent" layoutH="56" style="padding-top:15px;padding-left:60px;">
        <div>
        	<div style=" float:left; display:block;  overflow:auto; width:190px; height:300px; border:solid 1px #CCC; line-height:21px; background:#FFF;"> 
                <?php echo $this->renderPartial('users.components.views.RoleSelect', array('class' => 'tree treeFolder', 'id' => 'role_tree_seleced', 'root' => Yii::t('users', 'All Roles'))); ?>                                            
            </div>
            
        </div>
        <div style="height:200px;float:left;padding-top:100px; width:40px;background:#fff;text-align:center;overflow: hidden;">>>></div>
        <div id="userinfo" style="height:300px;float:left;margin-left:10px; display:block; overflow:auto; width:240px;border:solid 1px #CCC; line-height:21px; background:#fff">
            <div>
            	<div style="padding:8px;line-height:24px;border-bottom:1px solid #ccc;color:#15428B;background:#efefef"><a href="javascript:void(0);"><?php echo Yii::t('users','User List');?></a></div>
            	       
                <li style="padding:20px;" id="userBox"></li>
            </div>
        </div>
     </div>
     <div class="formBar">
        <ul>              
            <li><div class="button"><div class="buttonContent"><button type="button" class="close"><?php echo Yii::t('system', 'Closed') ?></button></div></div>
            </li>
        </ul>
    </div>

</div>



