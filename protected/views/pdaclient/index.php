<div class="pdaMenuTree" style="width:300px;">
	<ul style="margin:0;padding:0;">
		<?php foreach($menuList as $menu): ?>
		<li style="width:120px;float:left;list-style:none;">
			<?php
				echo CHtml::link($menu['name'], Yii::app()->baseUrl . $menu['menu_url'],array('target'=>'_self'));
			?>	
		</li>
		<?php endforeach; ?>
	</ul>	
</div><?php echo CHtml::link(Yii::t('system','Logout'), Yii::app()->baseUrl.PdaClientModel::createPdaClientLink('/pdaclient/login'));?>
<div style="clear:both;"></div>