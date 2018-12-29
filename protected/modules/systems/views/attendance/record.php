<?php
Yii::app()->clientscript->scriptMap['jquery.js'] = false;



?>
<div class="pageContent">
	<div class="help_bar hide" id="help_bar"></div>
	<div class="panelBar">
		<ul class="toolBar">
			<li>&nbsp;&nbsp;&nbsp;</li>

			<li><a class="add" target="dialog" rel="language-grid" posttype="" callback="" href="/systems/attendance/twofloor"><span>导入execl</span></a></li>


		</ul>
	</div>

<div class="grid"><div class="gridHeader"><div class="gridThead"><table style="width:880px;">
				<thead>
				<tr>

					<th id="attendance-grid_c1" style="width: 50px; cursor: default;" class="">
						<div class="gridCol" title="ID">ID</div>
						</th>
					<th id="attendance-grid_c2" style="width:50px;">
						<div class="gridCol" title="工号">工号</div>
					</th>
					<th id="attendance-grid_c3" style="width:100px;">
						<div class="gridCol" title="姓名">姓名</div>
					</th>
					<th id="attendance-grid_c4" style="width:50px;">
						<div class="gridCol" title="应出勤">年</div>
					</th>
					<th id="attendance-grid_c4" style="width:50px;">
						<div class="gridCol" title="应出勤">月</div>
					</th>
					<th id="attendance-grid_c4" style="width:50px;">
						<div class="gridCol" title="应出勤">应出勤</div>
					</th>
					<th id="attendance-grid_c4" style="width:50px;">
						<div class="gridCol" title="实出勤">当月天数</div>
					</th>
					<th id="attendance-grid_c4" style="width:60px;">
						<div class="gridCol" title="漏打卡次数">漏打卡次数</div>
					</th>

					<th id="attendance-grid_c4" style="width:60px;">
						<div class="gridCol" title="迟到次数">迟到次数</div>
					</th>
					<th id="attendance-grid_c4" style="width:60px;">
						<div class="gridCol" title="早退次数">早退次数</div>
					</th>
					<th id="attendance-grid_c4" style="width:150px;">
						<div class="gridCol" title="加班的总时间">加班的总时间</div>
					</th>
					<th id="attendance-grid_c4" style="width:80px;">
						<div class="gridCol" title="7点钟下班次数">7点钟下班次数</div>
					</th>

					<th id="attendance-grid_c5" style="width: 150px; cursor: default;" class="">
						<div class="gridCol" title="导入时间">导入时间</div>
					</th></tr>
				</thead>
			</table>
		</div>
	</div>
	<div id="gridScroller" class="gridScroller" layouth="105" style="width: 1703px; height: 185px; overflow: auto;"><div class="gridTbody">
			<table style="width:880px;">
				<tbody>
				<?php foreach ($model as $v){?>

				<tr class="odd">

					<td style="width:50px;height:32px;"><?=$v['id']?></td>
					<td style="width:50px"><?=$v['number']?></td>
					<td style="width:100px"><?=$v['name']?></td>
					<td style="width:50px"><?=$v['years']?></td>
					<td style="width:50px"><?=$v['month']?></td>
					<td style="width:50px"><?=UebModel::model('Attendance')->getDay($v['years'].$v['month'])-4?></td>
					<td style="width:50px"><?=UebModel::model('Attendance')->getDay($v['years'].$v['month'])?></td>
					<td style="width:60px;color: red;"><?=UebModel::model('Attendance')->getWorking_time($v['number'],$v['years'].$v['month'])?></td>
					<td style="width:60px;color:orange"><?=UebModel::model('Attendance')->getLate($v['number'])?></td>
					<td style="width:60px;color: #00B7EE"><?=UebModel::model('Attendance')->getLeave($v['number'])?></td>
					<td style="width:150px;color: #00B7EE"><?=UebModel::model('Attendance')->getDays($v['number'],$v['years'].$v['month'])?></td>
					<td style="width:80px;color: #00B7EE"><?=UebModel::model('Attendance')->getLeaves($v['number'])?></td>
					<td style="width:150px"><?=date('Y-m-d H:i:s',$v['create_time'])?></td>
				</tr>
				<?}?>
				</tbody>
			</table>
		</div>
	</div>
	<div class="resizeMarker" style="height:300px; left:57px;display:none;"></div>
	<div class="resizeProxy" style="height:300px; left:377px;display:none;"></div>
</div>

