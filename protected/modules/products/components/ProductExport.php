<?php
class ProductExport extends CComponent {
	
	public $limit = 800;
	 
	public function export_suggest_product($exportData,$id='',$flag,$fileDate=''){
		$objMyExcel = new MyExcel();
		$sheet_values = array();
		//设置头部
		$head_arr = array(	0=>'Itemid', 1=>'图片', 2=>'标题', 3=>'链接',4=>'类别',5=>'报价');
		
		//设置列宽
		$column_width = array(	1=>10,3=>10,5=>15	);
		
		//设置图片临时存放路径
		$path = $_SERVER['DOCUMENT_ROOT'].'new_product/product_excel/';
		
		if(!file_exists($path)){
			mkdir($path,'0777',true);
		}
	
		$count = 0;
		foreach ($exportData as $info){
			//将链接图片保存至本地
			$img = UebModel::model('SuggestProductOther')->getImage($info['gallery_url'],$path,trim($info['item_id']).'.JPG',0);
			$imgName = trim($info['item_id']).'.JPG';//图片名
			$savepath = $path.trim($info['item_id']).'.JPG';//图片绝对路径
				
			$sheet_values[$count]['0'] = trim($info['item_id']).' ';
			$sheet_values[$count]['1'] = 'displayPic|'.$savepath;//以displayPic|开头，表示以图片输出
			$sheet_values[$count]['2'] = trim($info['en_title']);
			//$sheet_values[$count]['3'] = array('title'=>'报价范围','list'=>array('3~6','6以上'));
			$sheet_values[$count]['3'] = trim($info['item_url']);
			$sheet_values[$count]['4'] = trim($info['category_name']);
			$sheet_values[$count]['5'] = '';
			$unlinkPath[$count] = $savepath;
			$count++;
		}
		//执行导出
		if($flag == '1'){
			$fileDate = 'new_product'.date("YmdHis");
			$objMyExcel->export_excel($head_arr,$sheet_values,$fileDate,$this->limit,1,$column_width);
			exit;
		}
		if($flag == '0'){
			$objMyExcel->export_excel($head_arr,$sheet_values,$fileDate,$this->limit,0,$column_width);
			foreach ($unlinkPath as $itm){
				unlink($itm);
			}
			UebModel::model('ProductExportLog')->updateLog($id);
			return ture;
		}
	}
	
	
	
	
	
}