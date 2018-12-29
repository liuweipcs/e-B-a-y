<link rel="stylesheet" type="text/css" href="/assets/be906584/gridview/styles.css" />
<div class="pageHeader">
	<form action="" method="post" class="pageForm" onsubmit="return navTabSearch(this);">
		<div class="row">
		<span>账号</span>
		<?php echo CHtml::dropDownList('wishId', '0', $accountList)?>
		<?php echo CHtml::submitButton('搜索');?>
		</div>
	</form>
		<?php if(!empty($_REQUEST['wishId'])){?>
		<div class="row"><button onclick="addItem();">添加</button><button onclick="saveItem();">保存</button></div>
		<?php }?>
</div>
<div class="pageContent">
<form method="post"  onsubmit="return navTabSearch(this);" class="pageForm"  id="saveItems" action="<?php echo Yii::app()->createUrl('/systems/wishrate/updatebatch')?>">
<?php if(!empty($_REQUEST['wishId'])){?>
<input type="hidden"  name="wishId" value="<?php echo $_REQUEST['wishId']?>"/>
<?php }?>
	<table layoutH="120"  class="table" style="width:90%">
	<thead>
		<tr>
			<th style="width:230px">成本区间</th>
			<th>标准利率</th>
			<th>最低利率</th>
			<th>浮动利率</th>
			<th>运费(USD)</th>
			<th>操作</th>
		</tr>
	</thead>
	<tbody>
	<?php if(!empty($list)){
			foreach ($list as $val){
				echo '<tr>';
				echo '<input type="hidden" name="d[]" value="',$val->id,'">';
				echo '<td>',CHtml::textField('s[]',$val->start_price,array('style'=>'width:60px;')),'--',CHtml::textField('t[]',$val->top_price,array('style'=>'width:60px;')),'</td>';
				echo '<td>',CHtml::textField('b[]',$val->basic_rate),'</td>';
				echo '<td>',CHtml::textField('m[]',$val->mini_rate),'</td>';
				echo '<td>',CHtml::textField('f[]',$val->float_rate),'</td>';
				echo '<td>',CHtml::textField('p[]',$val->ship_fee),'</td>';
				echo '<td><button onclick="remove(this);">删除</button></td>';
				echo '</tr>';
			}
		}else{
			echo '<tr><td colspan="6">请选择wish账号</td></tr>';
		}?>
	</tbody>
	</table>
</form>
</div>
<script type="text/javascript">

function remove(obj){
	var tds = $(obj).parent().parent('tr').find('input').eq(0);
	if($(tds).val()==0){
		$(obj).parent().parent('tr').remove();
	}else{
		if(confirm("删除后，将无法恢复")){
			$.post('<?php echo Yii::app()->createUrl('/systems/wishrate/delete')?>',{id:$(tds).val()},function(data){
				data = $.parseJSON(data);
				if(data.statusCode==200){
					alertMsg.correct(data.message);
					$(obj).parent().parent('tr').remove();
				}else{
					alertMsg.error(data.message);
				}
			});
		}
	}
}

function edit(obj){
	var tds = $(obj).parent().parent('tr').find('input');
	var data = {};
	data.id = $(tds).eq(0).val();
	data.start = $(tds).eq(1).val();
	data.top = $(tds).eq(2).val();
	data.basic = $(tds).eq(3).val();
	data.mini = $(tds).eq(4).val();
	data.float_rate = $(tds).eq(5).val();
	data.ship = $(tds).eq(6).val();
	$.post('<?php echo Yii::app()->createUrl('/systems/wishrate/update')?>',data,function(data){
		data = $.parseJSON(data);
		if(data.statusCode==200){
			alertMsg.correct(data.message);
		}else{
			alertMsg.error(data.message);
		}
	});
}

function addItem(){
	var html = '<tr><input type="hidden" name="d[]" value="">';
	html+='<td><input type="text" style="width:60px;" name="s[]" />--<input type="text" style="width:60px;" name="t[]" /></td>';
	html += '<td><input type="text"  name="b[]" /></td>';
	html += '<td><input type="text"  name="m[]" /></td>';
	html += '<td><input type="text"  name="f[]" /></td>';
	html += '<td><input type="text"  name="p[]" /></td>';
	html +='<td><button onclick="remove(this);">删除</button></td>';
	html +='</tr>'
	$('table>tbody').append(html);
}

function saveItem(){
	$('#saveItems').submit();
}
</script>
