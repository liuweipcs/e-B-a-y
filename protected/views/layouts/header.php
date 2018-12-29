<div id="header">
    <div class="headerNav">
        <a class="logo" href="<?php echo Yii::app()->request->getHostInfo();?>">标志</a>
        <ul class="nav">
        	<span style="float:left;"></span>
        	<li><a href="#" >欢迎您: <?php echo Yii::app()->user->full_name;?></a></li>					
            <li><a href="<?php echo Yii::app()->createUrl("/users/users/change", array("id" =>Yii::app()->user->id))?>" target="dialog">修改密码</a></li>
            <li><a href="<?php echo Yii::app()->createUrl("/systems/tasks/index")?>" target="navTab" id="page_506" rel="page506">任务列表</a></li>
            <li><a href="http://caigou.yibainetwork.com" target="_blank">采购系统</a></li>
            <li><a href="http://wms.yibainetwork.com/Login/Defaults/index" target="_blank">仓库WMS</a></li>
            <li><a href="http://kefu.yibainetwork.com" target="_blank">客服系统</a></li>
            <li><a href="http://yibai.tongtool.com/" target="_blank">通途系统</a></li>
            <li><a href="http://hwc.yibainetwork.com" target="_blank">物流系统线上</a></li>
            <li><?php echo CHtml::link(Yii::t('app', 'Logout'), array('/site/logout')); ?></li>
			<li><?php echo CHtml::link(Yii::t('app', 'Help'), array('/upload/operations_guide/guide.html'),array('target'=>'_blank')); ?></li>
        </ul>
        <!--ul class="themeList" id="themeList">
            <li theme="default"><div class="selected">蓝色</div></li>
            <li theme="green"><div>绿色</div></li>
            <li theme="purple"><div>紫色</div></li>
            <li theme="silver"><div>银色</div></li>
            <li theme="azure"><div>天蓝</div></li>
        </ul-->
    </div>		
    
        <?php
		/*<div id="navMenu">
        $this->widget('zii.widgets.CMenu', array(
					'id'=>'main-menu',
					'encodeLabel'=>false,
					'htmlOptions'=>array('class'=>'main-menu'),
					'items'=>Menu::model()->getNavMenu()</div>
				));*/
        ?>             
    
</div>
