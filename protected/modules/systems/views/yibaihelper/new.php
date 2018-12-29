<div class="pageContent" style="padding:5px;">
	<div class="tabsContent">
		<div layouth="146" style="float: left; display: block; overflow: auto; width: 240px; border: 1px solid rgb(204, 204, 204); line-height: 21px; background: rgb(255, 255, 255); height: 331px;">
			<ul class="tree treeFolder" >
			<?php 
			foreach ($topCate as $value){
				echo '<li>';
				if(!empty($sonList[$value['id']])){
					echo '<a href="javascript:;">'.$value['name'].'</a>';
				}else{
					echo '<a href="/systems/yibaihelper/list/id/',$value['id'],'" target="ajax" rel="newsJbsxBox">',$value['name'],'</a>';
				}
				if(isset($sonList[$value['id']])){
					echo '<ul>';
					foreach ($sonList[$value['id']] as $val){
						echo '<li>';
						echo '<a href="/systems/yibaihelper/list/id/',$val['id'],'" target="ajax" rel="newsJbsxBox">',$val['name'],'</a>';
						echo '</li>';
					}
					echo '</ul>';
				}
				echo '<li>';
			}
			?>
			</ul>
		</div>
		<div id="newsJbsxBox" class="unitBox" style="margin-left:246px;">
		
		   <?php echo $this->renderPartial('systems.components.views.newlist', array('model' => $model)); ?>
		</div>
	</div>
</div>