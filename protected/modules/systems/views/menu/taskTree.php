<?php
/**
 * 权限任务列表
 * @author Gordon
 * @since 2014-06-05
 */
// var_dump($loginRoleResources);exit;
?>

<form class="pageForm">
	<div layoutH="35" style="float:left; display:block; overflow:auto; width:98%; border:solid 1px #CCC; line-height:21px; background:#fff">
		<ul class="tree treeFolder expand" rel = "taskTreePanel">
			<li>
                <a><?php echo Yii::t('users', 'All Resources') ?></a>
                <?php echo $this->renderPartial('systems.components.views.MenuView',array(
                		'type' => 'menuTask','menuId'=>'0','uid'=>$uid,'resources' => $resources,'loginRoleResources' => $loginRoleResources
                )); ?>
            </li>
        </ul>  
    </div>
</form>