<?php header('content-type:text/html;charset=utf-8');
class ProductSalesExport extends CComponent {
	public $limit = 90000;
	
	//导出EXCEL欠货订单
	public function export_sale_date_excel($data) {
		$objMyExcel = new MyExcel();
		
		$head_arr = array(
				0=>'序号',
				1=>'SKU',
				2=>'产品销量',
				3=>'平台',
				4=>'仓库名称',
				5=>'销售日期',
		);
		$sheet_values = array();
		$count = 0;
		foreach ($data as $key=>$info){
			$sheet_values[$count][] = $count+1;
			$sheet_values[$count][] = strip_tags($info['sku']);
			$sheet_values[$count][] = strip_tags($info['sale_num']);
			$sheet_values[$count][] = strip_tags($info['platform_code']);
			$sheet_values[$count][] = strip_tags($info['warehouse_name']);
			$sheet_values[$count][] = strip_tags($info['sale_date']);
			$count++;
		}
		
		$filename = date('Y-m-d').'_SKU销量 ';
		$objMyExcel->export_excel($head_arr,$sheet_values,"{$filename}.xls", $this->limit);	
		exit('end');
	}
	
}

?>