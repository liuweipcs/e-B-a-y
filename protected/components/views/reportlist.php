<?php Yii::app()->clientscript->scriptMap['jquery.js'] = false; ?>
<?php if($list):
$this->renderPartial('application.components.views._searchReport', array( 'pages' => $pages,'className' => $className,
    	    'list' => $list,'scheme_id' => $scheme_id,'subTitle' => $subTitle));
endif;?>
<div class="pageContent" style="border-left:1px #B8D0D6 solid;border-right:1px #B8D0D6 solid">
    <div class="panelBar">
		<ul class="toolBar">
			<!-- <li style="padding:2px;margin-right:8px;width:auto;">
                <input id="allPagesSelected" group="< ?php echo $modelName;?>-grid_c0[]" 
                onclick="allPagesSelected(this, '#< ?php echo $modelName;?>-grid_c0_all');" value="1" name="allPagesSelected" type="checkbox">
                < ?php echo Yii::t('system', 'All the pages selected');?>
            </li> -->
            <li style="padding:2px;margin-right:8px;width:auto;">
                <button id="createChart" class="icon" onclick="createChart(this);"><?php echo Yii::t('system', 'To view statistics');?></button>
            </li>
            <li><button id="getcsv">获取CVS数据</button></li>
			<li class="line">line</li>
			<li><button id="download">导出Excel文件</button></li>
			<li style="padding-top:6px;"><?php echo Yii::t('system', 'Click the button to switch the chart');?>：</li>
			
			<li style="margin:2px 0px 2px 5px;">
			      <button class="change" for="line"><?php echo Yii::t('system','Line chart');?></button>
			      <button class="change" for="spline"><?php echo Yii::t('system','Spline chart');?></button>
			      <button class="change" for="pie"><?php echo Yii::t('system','Pie chart');?></button>
			      <button class="change" for="area"><?php echo Yii::t('system','Area chart');?></button>
			      <button class="change" for="column"><?php echo Yii::t('system','Column chart');?></button>
			      <button class="change" for="areaspline"><?php echo Yii::t('system','AreaSpline chart');?></button>
			      <button class="change" for="bar"><?php echo Yii::t('system','Bar chart');?></button>
			      <button class="change" for="scatter"><?php echo Yii::t('system','Scatter chart');?></button>
			</li>
			
		</ul>
	</div>
	<?php if ($_REQUEST['ac']):?>
	<div id="w_list_print" layoutH="140">
	<?php 
	if(isset($list['is_value']) && !empty($list['is_value'])):?>
    <table id="datatable" class="dataintable" width="99%"
    <?php if ( $_REQUEST['target'] == 'dialog'):?>targetType="dialog"<?php else:?>rel="<?php echo $_REQUEST['target'];?>" 
    layoutH="150"<?php endif;?> >
        <thead>
            <tr>
            	<th id="<?php echo $modelName;?>-grid_c0" class="checkbox-column" style="width: 26px; cursor: default;">
            		<div title="" class="gridCol"><input type="checkbox" id="<?php echo $modelName;?>-grid_c0_all" 
            		name="<?php echo $modelName;?>-grid_c0_all" value="1"></div>
            	</th>
                <th width="5%"><?php echo Yii::t('system', 'NO.')?></th>
                <th width="8%"></th>
                <?php
                $allDate = array_keys($data['total']);
                foreach($data['total'] as $k=>$v):
                ?>
				<th ><?php echo $k;?></th>
				<?php endforeach;?>
            </tr>
        </thead>
        <tbody>
            <?php
            $i = 0;
            $xAxis = $allDate;
			foreach ($data as $k=>$v):
				$chartDataArr[$k]['name'] = $k;
				$seriesName = array();
			?>
				<tr target="sid_obj" rel="<?php echo $i;?>">
					<td><div>
						<input id="<?php echo $modelName;?>-grid_c0_<?php echo $i;?>" type="checkbox" 
						name="<?php echo $modelName;?>-grid_c0[]" value="<?php echo $k;?>" onclick="cancelSelectAll(this);">
						</div></td>
					<td><?php echo $i+1;?></td>
					<td><?php echo $k;?></td>
					<?php 
					foreach ($allDate as $kk=>$day){
						echo "<td>";
							if(isset($v[$day])) {
								echo $v[$day];
								$seriesName[] = $v[$day];
							}else {
								echo 0; //没有查询到数据的，为0
								$seriesName[] = 0;
							}
						echo "</td>";
					}
					?>
				</tr>
			<?php
			$chartDataArr[$k]['data'] = $seriesName;
			$i++;
			endforeach;
			$chartDataArr = array_values($chartDataArr);
			?>
        
        <tr>
        <TD colspan="<?php echo count($data['total'])+3;?>" id="containerss"
         style="height:auto;<?php if(empty($_POST['is_value'])){echo 'display:none;';}?>">
        </TD>
        </tr>
        <tr><TD colspan="<?php echo count($data['total'])+3;?>" id="containers" style="height:auto;display:none;"></TD></tr>
        </tbody>
    </table>
    
    <?php  endif;?>
    </div>
    <?php $numPerPage = $_REQUEST['numPerPage'] ? $_REQUEST['numPerPage']:10;?>
    <div class="panelBar" id="yw0">
		<div class="pages">
			<span><?php echo Yii::t('system', 'Show'); ?></span>
			<select class="combox" name="numPerPage"
			 onchange="navTabPageBreak({numPerPage:this.value}, '<?php echo @$_REQUEST['target'];?>')">
				<option value="3" <?php if($numPerPage==3) echo "selected";?>>3</option>
				<option value="10" <?php if($numPerPage==10) echo "selected";?>>10</option>
				<option value="20" <?php if($numPerPage==20) echo "selected";?>>20</option>
				<option value="50" <?php if($numPerPage==50) echo "selected";?>>50</option>
			</select>
			<span><?php echo Yii::t('system', 'Item') ?>，<?php echo Yii::t('system', 'Total') ?>
			<?php echo $total;?><?php echo Yii::t('system', 'Item') ?></span>
		</div>
		
		<div class="pagination" rel="<?php echo @$_REQUEST['target'];?>" totalCount="<?php echo $total;?>" 
		numPerPage="<?php echo $numPerPage;?>" 
		pageNumShown="<?php echo ceil($total/$numPerPage);?>" 
		currentPage="<?php echo $_POST['pageNum'] ? $_POST['pageNum'] :1;?>">
		</div>

	</div>
    <?php //$this->renderPartial('application.components.views._pageFooter', array( 
			//'target' => $_REQUEST['target'], 'pages' => $pages)); ?>
    <?php  endif;?>
</div>

<script>
var series=[];
var categories=[];
var subtitle='<?php echo $subTitle;?>';
var text='<?php echo $unit;?>';
var p = navTab.getCurrentPanel();
//charts
var createChart = function(obj){
	var ids = "";
	var arrChk= $("input[name='<?php echo $modelName;?>-grid_c0[]']:checked");
	if(arrChk.length==0){
		alertMsg.error('<?php echo Yii::t('system', 'Please Select'); ?>');
		return false;
	}
	if(arrChk.length>10){
		alertMsg.error('<?php echo Yii::t('system', "can\'t more than 10"); ?>');
		return false;
	}
	//横坐标
	$('thead',p).find('th:gt(2)').each(function() {
		categories.push($(this).text());
	});
	
		
    //遍历得到每个checkbox的value值
	arrChk.each(function(){
		//纵坐标
    	var chartObj={name:'',data:[]},
    	name='',
    	data=[];
		$tr = $(this).parents('tr');
		$tr.find('td:gt(1)',p).each(function(id) {
    		var text= $(this).text();
    		if(id == 0) {
    			name=$(this).text();	
    		}else if(id > 0) {
    			data.push( parseFloat($(this).text()) );
    		}													
    	});
    	chartObj.name=name;
    	chartObj.data=data;		
		series.push(chartObj);
		
	});
   
	_createChart(series,subtitle,categories,text);
	//清空上次数据
	series=[];
	categories=[];
}
var _createChart = function(series,subtitle,categories,text){
	var chart = new Highcharts.Chart(optionArr);
	//optionArr.title.text = subtitle;
	optionArr.xAxis.categories = categories;
	optionArr.yAxis.title.text = text;
	optionArr.series = series;
	
	chart = new Highcharts.Chart(optionArr);
	

}
<?php if ($_REQUEST['is_value']):?>
	var optionArr = {
		chart: {
			plotBackgroundColor: null, plotBorderWidth: null, plotShadow: false, renderTo:'containerss', type: 'column' 
		},
		title: { text: '<?php echo $subTitle;?>', x: -20 },
		//subtitle: { text: '<?php echo $subTitle;?>' }, 
		xAxis: { categories: <?php echo json_encode($xAxis);?> },
		yAxis: { min: 0, title: { text: 'value' },plotLines: [{ value: 0, width: 1, color: '#808080' }]  },
		tooltip: {
			//pointFormat: '{series.name}: <b>{point.y}</b>',
			headerFormat: '<span style="font-size:10px">{point.key}</span><table style="border:1;padding:5px;">', 
			pointFormat: '<tr><td style="color:{series.color};padding:0;border:0;height:28px;">{series.name}: </td>' 
				+ '<td style="padding:0;border:0;"><b>{point.y:.1f} </b></td></tr>', 
			footerFormat: '</table>', 
			shared: true, 
			useHTML: true
		},
		legend: { layout: 'vertical', align: 'right', verticalAlign: 'middle', borderWidth: 0 }, 
		plotOptions: { column: { pointPadding: 0.2, borderWidth: 0 } },
		labels:{
			items:[{
				html:'',
				style: { left:'532px', top:'160px', }
			}],
			style:{
				color:'red', fontSize:45, fontWeight:'bold', zIndex:1000 
			}
		}, 
		series: <?php echo json_encode(array_values($chartDataArr));?>
		
	};
	
	$(document).ready(function(){
		$("button.change").click(function(){
			var type = $(this).html();
			type = $(this).attr('for');
			if(type == "pie") {
				optionArr.tooltip.pointFormat = '<tr><td style="color:{series.color};padding:0;border:0;height:28px;">{series.name}: </td>' 
					+ '<td style="padding:0;border:0;"><b>{point.percentage:.1f}% </b></td></tr>';
			}else {
				optionArr.tooltip.pointFormat = '<tr><td style="color:{series.color};padding:0;border:0;height:28px;">{series.name}: </td>' 
					+ '<td style="padding:0;border:0;"><b>{point.y:.1f} </b></td></tr>';
			}
			optionArr.chart.type = type;
			chart = new Highcharts.Chart(optionArr);
			
		});
		$('#getcsv').click(function () {
			chart = new Highcharts.Chart(optionArr);
	    	alert(chart.getCSV());
	    });
		$("#download").click(function(){
			chart = new Highcharts.Chart(optionArr);
			Highcharts.post('/report/statisticsreport/exportCvs', {
				csv: chart.getCSV()
			});
	    })
		
	});
<?php endif;?>	
</script>

<script>
	var p = navTab.getCurrentPanel();
	$(function() {
		var pageSelectedId = '#<?php echo $modelName;?>-grid_c0_all',
			selectAllName = '<?php echo $modelName;?>-grid_c0[]';
        //allPagesSelected($('#allPagesSelected'), pageSelectedId);
        selectAll(pageSelectedId,selectAllName);
	});

	function selectAll(pageSelectedId,selectAllName){
		$(pageSelectedId,navTab.getCurrentPanel()).click(function(){
			var checked = !$.isEmptyObject($(pageSelectedId).attr('checked')) ? true : false,
		        checkboxLi = $(p).find(':checkbox[name=\"'+selectAllName+'\"]');
			if ( checked ) { 
	             checkboxLi.attr({'checked':true})
	        } else {                       
	            checkboxLi.attr({'checked':false})
	        }
		});
	}
	function cancelSelectAll(obj){
		var pageSelectedId = '#<?php echo $modelName;?>-grid_c0_all';
		checkboxLi = $(p).find(':checkbox[name="<?php echo $modelName;?>-grid_c0[]"]');
		var flag = true;
		for(i=0;i<checkboxLi.length;i++){
			var cur_id = "#<?php echo $modelName;?>-grid_c0_"+i;
			var checked = !$.isEmptyObject($(cur_id).attr('checked')) ? true : false;
			if ( checked ) { 
	        } else {                       
	        	 flag = false;
	        }
		}
		if(flag){
			$(pageSelectedId,p).attr({'checked':true});
		}else{
			$(pageSelectedId,p).attr({'checked':false});
		}
	}
	
</script>
