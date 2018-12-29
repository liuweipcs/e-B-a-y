<?php Yii::app()->clientscript->scriptMap['jquery.js'] = false; ?>
<?php if($list):?>
<?php
$this->renderPartial('application.components.views._searchReport', array( 'pages' => $pages,'className' => $className,
    	    'list' => $list,'scheme_id' => $scheme_id)); ?>
<?php endif;?>
<div class="pageContent" style="border-left:1px #B8D0D6 solid;border-right:1px #B8D0D6 solid">
    <div class="panelBar">
		<ul class="toolBar">
			<li style="padding:2px;margin-right:8px;width:auto;">
                <input id="allPagesSelected" group="<?php echo $modelName;?>-grid_c0[]" 
                onclick="allPagesSelected(this, '#<?php echo $modelName;?>-grid_c0_all');" value="1" name="allPagesSelected" type="checkbox">
                <?php echo Yii::t('system', 'All the pages selected');?>
            </li>
            <li style="padding:2px;margin-right:8px;width:auto;">
                <button id="createChart" class="add" onclick="createChart(this);"><?php echo Yii::t('system', 'To view statistics');?></button>
            </li>
			<!-- <li style="padding-top:6px;">< ?php echo Yii::t('system', 'Click the button to switch the chart');?>：</li>
			<li style="margin:2px 0px 2px 5px;">
			      <button class="change" for="line">line</button>
			      <button class="change" for="spline">spline</button>
			      <button class="change" for="pie">pie</button>
			      <button class="change" for="area">area</button>
			      <button class="change" for="column">column</button>
			      <button class="change" for="areaspline">areaspline</button>
			      <button class="change" for="bar">bar</button>
			      <button class="change" for="scatter">scatter</button>
			</li> -->
			<!-- <li><a class="add" href="/report/statisticsreport/showchart" target="navTab"><span>查看统计报表</span></a></li>
			<li class="line">line</li>
			<li><a class="icon" href="demo/common/dwz-team.xls" target="dwzExport" title="实要导出这些记录吗?"><span>导出EXCEL</span></a></li> -->
		</ul>
	</div>
	<div id="w_list_print" layoutH="145">
	<?php 
	if(isset($_REQUEST['is_value']) && !empty($_REQUEST['is_value'])):?>
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
                <?php
                foreach ($_REQUEST['is_value'] as $key=>$val):
                	$is_value[] = $val;
                ?>
				<th ><?php echo $val;?></th>
				<?php endforeach;?>
            </tr>
        </thead>
        <tbody>           
            <?php
            $m=0;
            foreach ($_REQUEST['is_value'] as $k=>$v){
				if ($m>0) {
					$chartDataArr[$m]['name'] = $v;
					foreach ($data as $key=>$val){
						$newV = array_values($val);
						$chartDataArr[$m]['data'][] = $newV[$m];
					}
				}
            	$m++;
            }
            $i = 0;
			foreach ($data as $k=>$v):
				$newV = array_values($v);
			?>
				<tr target="sid_obj" rel="<?php echo $i;?>">
					<td style="width: 26px;">
						<div>
						<input id="<?php echo $modelName;?>-grid_c0_<?php echo $i;?>" type="checkbox" 
						name="<?php echo $modelName;?>-grid_c0[]" value="<?php echo $newV[0];?>" onclick="cancelSelectAll(this);">
						</div>
					</td>
					<td><?php echo $i+1;?></td>
					<?php 
					foreach ($v as $key=>$val):
					?>
					<td><?php echo !empty($val) ? $val : '0';?></td>
					<?php endforeach;?>
				</tr>
			<?php
			$i++;
			endforeach;
			?>
        
        <tr>
        <TD colspan="<?php echo count($list['is_value'])+2;?>" id="containerss" style="height:auto;<?php if(empty($_REQUEST['is_value'])){echo 'display:none;';}?>">
        </TD>
        </tr>
        <tr><TD colspan="<?php echo count($list['is_value'])+2;?>" id="containers" style="height:auto;display:none;"></TD></tr>
        </tbody>
    </table>
    
    <?php  endif;?>
    </div>
    <?php $numPerPage = $_REQUEST['numPerPage'] ? $_REQUEST['numPerPage']:10;?>
    <div class="panelBar" id="yw0">
		<div class="pages">
			<span><?php echo Yii::t('system', 'Show'); ?></span>
			<select class="combox" name="numPerPage" onchange="navTabPageBreak({numPerPage:this.value}, '<?php echo @$_REQUEST['target'];?>')">
				<option value="3" <?php if($numPerPage==3) echo "selected";?>>3</option>
				<option value="10" <?php if($numPerPage==10) echo "selected";?>>10</option>
				<option value="20" <?php if($numPerPage==20) echo "selected";?>>20</option>
				<option value="50" <?php if($numPerPage==50) echo "selected";?>>50</option>
			</select>
			<span><?php echo Yii::t('system', 'Item') ?>，<?php echo Yii::t('system', 'Total') ?><?php echo $total;?><?php echo Yii::t('system', 'Item') ?></span>
		</div>
		
		<div class="pagination" rel="<?php echo @$_REQUEST['target'];?>" totalCount="<?php echo $total;?>" 
		numPerPage="<?php echo $numPerPage;?>" 
		pageNumShown="<?php echo ceil($total/$numPerPage);?>" 
		currentPage="<?php echo $_REQUEST['pageNum'] ? $_REQUEST['pageNum'] :1;?>">
		</div>

	</div>
    <?php //$this->renderPartial('application.components.views._pageFooter', array( 
			//'target' => $_REQUEST['target'], 'pages' => $pages)); ?>
    <?php if ($_REQUEST['ac']): endif;?>
</div>
<script>
var p = navTab.getCurrentPanel();
var xyTitle=[];
var yAxisTitle=[];
var series=[];
var categories=[];
var subtitle='<?php echo $subTitle;?>';
var type=['column','spline'];
var color=['#89A54E','#4572A7'];
var yAxis=[1,0];

var data=[];

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
		xyTitle.push($(this).text());
		yAxisTitle.push($(this).text());
	});
		
    //遍历得到每个checkbox的value值
	arrChk.each(function(k){
		categories.push($(this).val());
		
		//纵坐标
		$tr = $(this).parents('tr');
		$tr.find('td:gt(2)',p).each(function(id) {
    		var text= $(this).text();
    		data[id] = data[id] ? data[id] : [];
    		data[id][k] =  !isNaN(text) ? parseFloat(text) : 0;
    	});
	});

	//data = data.sort();
	//xyTitle.sort();
	
	for(var i in xyTitle){
		var chartObj={name:[],data:[]};
		if(i<2){
			chartObj.name=xyTitle[i];
			chartObj.data=data[i];
			chartObj.color=color[i];
			chartObj.yAxis=yAxis[i];
			chartObj.type=type[i];
			series.push(chartObj);
		}
	}
	
	_createChart(series,subtitle,categories,yAxisTitle);
	//清空上次数据
	series=[];
	categories=[];
	xyTitle=[];
	yAxisTitle=[];
	data=[];
}
var _createChart = function(series,subtitle,categories,xyTitle){
	optionArr.title.text = subtitle;
	optionArr.xAxis[0].categories = categories;

	for(var i=0;i<xyTitle.length;i++){
		if(i<2) optionArr.yAxis[i].title.text = xyTitle[i];
	}

	optionArr.series = series;
	
	chart = new Highcharts.Chart(optionArr);
}


<?php if ($_REQUEST['is_value']):
$values = array_values($_REQUEST['is_value']);
?>
	var optionArr = {
		chart: {
			plotBackgroundColor: null, plotBorderWidth: null, plotShadow: false, renderTo:'containerss', type: 'line' 
		},
		title: { text: '<?php echo $subTitle;?>' },
		/*subtitle: { text: 'Source: WorldClimate.com' }, */
		xAxis: [{ categories: <?php echo json_encode($xAxis);?>,showEmpty: false }],
		yAxis: [{ // Primary yAxis
            labels: {
                format: '{value}',
                style: {
                    color: '#89A54E'
                }
            },
            title: {
                text: '<?php echo $values[1]?>',
                style: {
                    color: '#89A54E'
                }
            }}, 
        	{ // Secondary yAxis
            title: {
                text: '<?php echo $values[2]?>',
                style: {
                    color: '#4572A7'
                }
            },
            labels: {
                format: '{value} ',
                style: {
                    color: '#4572A7'
                }
            },
            opposite: true
        }],
		tooltip: {
			headerFormat: '<span style="font-size:10px">{point.key}</span><table style="border:1;padding:5px;">', 
			pointFormat: '<tr><td style="color:{series.color};padding:0;border:0;height:28px;">{series.name}: </td>' 
				+ '<td style="padding:0;border:0;"><b>{point.y:.1f} </b></td></tr>', 
			footerFormat: '</table>', 
			shared: true, 
			useHTML: true
		},
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
		legend: {
            layout: 'vertical',
            align: 'left',
            x: 120,
            verticalAlign: 'top',
            y: 100,
            floating: true,
            backgroundColor: '#FFFFFF'
        },
        series: [{
            name: '<?php echo $values[1]?>',
            color: '#89A54E',
            type: 'column',
            yAxis: 1,
            data: [1, 0, 6],
            tooltip: {
                valueSuffix: ' ddd'
            }

        }, {
            name: '<?php echo $values[2]?>',
            color: '#4572A7',
            type: 'spline',
            data: [80, 0, 523],
            yAxis: 0,
            tooltip: {
                valueSuffix: '°C'
            }
        }]
		//series: < ?php echo json_encode(array_values($chartDataArr));?>
	};
	
	$(document).ready(function(){
		//var chart = new Highcharts.Chart(optionArr);
// 		$("button.change").click(function(){
// 			var type = $(this).html();
// 			type = $(this).attr('for');
// 			if(type == "pie") {
// 				optionArr.tooltip.pointFormat = '<tr><td style="color:{series.color};padding:0;border:0;height:28px;">{series.name}: </td>' 
// 					+ '<td style="padding:0;border:0;"><b>{point.percentage:.1f}% </b></td></tr>';
// 			}else {
// 				optionArr.tooltip.pointFormat = '<tr><td style="color:{series.color};padding:0;border:0;height:28px;">{series.name}: </td>' 
// 					+ '<td style="padding:0;border:0;"><b>{point.y:.1f} </b></td></tr>';
// 			}
// 			optionArr.chart.type = type;
// 			chart = new Highcharts.Chart(optionArr);
			
// 		});
	});
<?php endif;?>	
</script>

<script>    
	$(function() {
		var pageSelectedId = '#<?php echo $modelName;?>-grid_c0_all'; 
		var p = navTab.getCurrentPanel();
		var selectAllName = '<?php echo $modelName;?>-grid_c0[]';
        //allPagesSelected($('#allPagesSelected', p), pageSelectedId);
		selectAll(pageSelectedId,selectAllName);                  
	});
	function allPagesSelected(obj, pageSelectedId) {                 
		var p = '$this->target' == 'dialog' ? $.pdialog.getCurrent() : navTab.getCurrentPanel();
		var checked = !$.isEmptyObject($(obj).attr('checked')) ? true : false,
        _name = $(obj).attr('group'),
        	checkboxLi = $(p).find(':checkbox[name=\"'+_name+'\"]');                          
        if ( checked ) {                       
             $(pageSelectedId, p).attr({'checked':true, 'disabled': true});
             checkboxLi.attr({'checked':true, 'disabled': true})
             $('#pagesChecked', p).val(1);
        } else {                       
            $(pageSelectedId, p).attr({'checked':false, 'disabled': false});
            checkboxLi.attr({'checked':false, 'disabled': false})
        	$('#pagesChecked', p).val(0);
        }                       
	}
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
