<div id="leftside">
    <div id="sidebar_s">
        <div class="collapse">
            <div class="toggleCollapse"><div></div></div>
        </div>
    </div>
    <div id="sidebar">
        <div class="toggleCollapse"><h2>主菜单</h2><div>收缩</div></div>
        <div class="accordion" fillSpace="sidebar" >
			<?php 
			$menuList = Menu::getTreeList(3,3);
			foreach(Menu::model()->getNavMenu() as $values){ ?>
			<div class="accordionHeader">
				<h2><span>Folder</span><?php echo strip_tags($values['label']);//exit;//$data->menu_display_name;?></h2>
			</div>
			<?php foreach($values['itemOptions'] as $vals){ ?>
			<div class="accordionContent">
	<?php
$flag = true;
echo '<ul class="tree treeFolder" id="tree-menu" > ';
$data = $menuList[$values["itemOptions"]["id"]]['submenu'];
echo Menu::model()->getSubMenu($data, $flag);
echo '</ul>';
?>	
<?php //$this->renderPartial('systems.components.views.MenuSider', array('parentId' => $vals[0])); ?>	
			</div>
			<?php }  ?>
			<?php }  ?>
            <?php echo $this->renderPartial('systems.components.views.NomalSider'); ?>	
            <?php echo $this->renderPartial('systems.components.views.HistorySider'); ?>         
        </div>   
        
    </div>
</div>