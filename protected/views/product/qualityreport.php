<div>
	<div style="text-align: center;margin-bottom:5px;color:red;font-size:18px; line-height:28px;">品检类型 (<?php echo UebModel::model('Product')->getProductQualityLableList($quality_lable); ?>)</div>
	</div>
	<div style="text-align: center;margin-bottom:5px;color:red;font-size:18px; line-height:28px;">质检标准</div>
		<div style="line-height:20px;"><?php echo $quality_standard; ?></div>
	</div>
	<div style="text-align: center;margin-bottom:5px;color:red;font-size:18px;line-height:28px;">审核备注</div>
		<div style="line-height:20px;"><?php echo $quality_remark; ?></div>
	</div>
</div>
<?php 
exit();
?>