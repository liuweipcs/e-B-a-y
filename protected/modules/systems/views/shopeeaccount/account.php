<?php 
if(empty($accountList)){
	exit("请先绑定账号");
}
?>
<div class="pageContent">
<?php 
	$form = $this->beginWidget('ActiveForm',array(
		'id'=>'lzadaProductTaskForm',
		'enableAjaxValidation'=>false,
		'enableClientValidation'=>false,
		'clientOptions' => array(
				'validateOnSubmit' => true,
				'validateOnChange' => true,
				'validateOnType' => false,
				'afterValidate'=>'js:afterValidate',
		),
		'action'=>Yii::app()->createUrl($this->route),
		'htmlOptions'=>array(
			'class'=>'pageForm',
			'onsubmit'=>'return validateCallback(this,dialogAjaxDone)',
		)
	));
	?>

	<!--新代码-->
	<div class="pageFormContent" layoutH="58">
		<div class="row">
			<label for="#categoryList">lazada账号（请选择）：</label>
			<select name="account" id="categoryList">
			<?php
			if(!empty($accountList)){
				foreach ($accountList as $key=>$val){
					?>
					<option value="<?php echo $key;?>"><?php echo $val;?></option>
					<?php
				}
			}
			?>
			</select>
		</div>
		<div class="row">
			<button class="btn"  type="submit"  value="选择" >选择</button>
		</div>
	</div>




	<!--原代码-->
	<!--<div class="pageFormContent" layoutH="58">
		<div class="row">
			<label for="#categoryList">lazada账号（请选择）：</label>
			<select name="account" size="10"   style="width:700px;" id="categoryList">
			<?php /*
			if(!empty($accountList)){
				foreach ($accountList as $val){
					*/?>
					<option value="<?php /*echo $val['id'];*/?>"><?php /*echo $val['seller_name'];*/?></option>
					<?php /*
				}
			}
			*/?>
			</select>
		</div>
		<div class="row">
			<button class="btn"  type="submit"  value="选择" >选择</button>
		</div>
	</div>-->
	<?php $this->endWidget()?>
</div>
<script type="text/javascript">
$('#accountList').on('dblclick','option',function(){
	$.bringBack({Name:$(this).text(),AccountId:$(this).val()});
});
</script>
