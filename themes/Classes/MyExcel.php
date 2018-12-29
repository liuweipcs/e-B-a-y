<?php  
  
/** 
 * 将PHPExcel进行常用方法的简单封装 
 * @date 2013-08-21 
 * @author ethanhu  
 * @note 需放到与 PHPExcel.php 同一目录 
 */  
/** Include PHPExcel */  
require_once dirname(__FILE__).'/PHPExcel.php';  
require_once dirname(__FILE__).'/PHPExcel/Writer/Excel5.php';   
require_once dirname(__FILE__).'/PHPExcel/IOFactory.php';  
function my_array_type($arr){  
    $c = count($arr);  
    $in = array_intersect_key($arr,range(0,$c-1));  
    if(count($in) == $c) {  
        return 'index'; //索引数组  
    }else if(empty($in)) {  
        return 'assoc'; //关联数组  
    }else{  
        return 'mix'; //混合数组  
    }  
}
  
class MyExcel extends PHPExcel{
    private $RowTitleSet = array(); //是否已经设置过标题行  
    private $file = null;  
    private $xls_bak_dir = 'backup';//导出文件备份  
    private $read_sheet_index = null;
	
    function __construct() {  
          
        parent::__construct();//构造父类  
          
        $this->getProperties()->setCreator("Maarten Balliauw")  
             ->setLastModifiedBy("Maarten Balliauw")  
             ->setTitle("Office 2007 XLSX Test Document")  
             ->setSubject("Office 2007 XLSX Test Document")  
             ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")  
             ->setKeywords("office 2007 openxml php")  
             ->setCategory("Test result file");  
    }



	/**
     * 根据文件名获取PHPExcel对象
     * @param $file
     * @return PHPExcel
     */
    public function getByFile($file)
    {
        $reader = PHPExcel_IOFactory::createReader('Excel5');
        $excel = $reader->load($file);
        return $excel;
    }

	public function get_excel_con($flie_path){
		if(!file_exists($flie_path)) return array();
		$objPHPExcel = PHPExcel_IOFactory::load($flie_path);
    	$sheetData = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
		return $sheetData;
	}
      
    /** 
     * 设置读取xls的标签页 
     * @param int $i 
     */  
    function set_read_index($i)  
    {  
        $this->read_sheet_index = $i;  
    }  
      
    /** 
     * 读取xls内容 
     * @param $start_row 开始行数, $max_row 最大行数 
     */  
    function read_xls($start_row=1,$max_row=10000)  
    {  
        if(!$this->file){  
            die("not setFile()");  
        }  
        $objReader = PHPExcel_IOFactory::createReader('Excel5');//use excel2007 for 2007 format   
        $objPHPExcel = $objReader->load($this->file);   
          
        if( !is_null($this->read_sheet_index) ){  
            $objWorksheet = $objPHPExcel->getSheet( $this->read_sheet_index );  
        }else{  
            $objWorksheet = $objPHPExcel->getActiveSheet();  
        }  
        $highestRow = $objWorksheet->getHighestRow();           //取得总行数   
        $highestColumn = $objWorksheet->getHighestColumn();  
        $highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);//总列数  
        if($highestRow>$max_row) $highestRow=$max_row; //有时候会读到6万多行        
          
        $arr_Return = array();  
        for ($row = $start_row;$row <= $highestRow;$row++)   
        {  
            $arr_info=array();  
            //注意highestColumnIndex的列数索引从0开始  
            for ($col = 0;$col < $highestColumnIndex;$col++)  
            {  
                 $cell=$objWorksheet->getCellByColumnAndRow($col, $row)->getValue(); //getValue()  getCalculatedValue()  
                 if($cell instanceof PHPExcel_RichText)     //富文本转换字符串  
                    $cell = $cell->__toString();  
                 if(substr($cell,0,1)=='='){ //公式  
                     $cell=$objWorksheet->getCellByColumnAndRow($col, $row)->getCalculatedValue();  
                 }  
                   
                 $arr_info[$col] = $cell;  
            }  
            $arr_Return[] = $arr_info;  
        }  
        return $arr_Return;  
    }  
      
      
    /** 
     * 设置标题 
     * @param 例 $arrWidth = array('A'=>'ID' ,'B'=>'中文', 'D'=>'英文') | array('ID' ,'中文', '英文') 
     */  
    function setRowTitle($arrTitle)  
    {
        $index = $this->getActiveSheetIndex();  
        $this->RowTitleSet[$index] = true;  
        if(my_array_type($arrTitle)=='assoc'){  
            foreach ($arrTitle as $Column=>$value){  
                $this->getActiveSheet()->setCellValue($Column.'1', $value);  
            }  
        }else{  
            $start = 'A';  
            for($i=0; $i<count($arrTitle); $i++){  
                $Column = $start++;  
                $this->getActiveSheet()->setCellValue($Column.'1', $arrTitle[$i]);  
            }  
        }
    }  
      
    /** 
     * 设置EXCEL每行内容 
     * 
     * @param array $xls_rows 
     * e.g. $xls_rows = array( 
     *         array('content1','content2','content3'), 
     *         array('A'=>'content1','B'=>'content2','C'=>'content3'), 
     *         ... 
     *      ) 
     */  
    function setRows($xls_rows)  
    {  
        $index = $this->getActiveSheetIndex();  
        $n = $this->RowTitleSet[$index] ? 2 : 1;  
        foreach ($xls_rows as $row) {  
            if(my_array_type($row)=='assoc') { //关联  
                foreach ($row as $Column=>$value){  
                    $this->getActiveSheet()->setCellValue($Column.$n, $value);  
                    $this->getActiveSheet()->getStyle($Column.$n)->getAlignment()->setWrapText(true)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);   
                }  
            }else{   
                $start = 'A';  
                for($i=0; $i<count($row); $i++){  
                    $Column = $start++;  
                    $this->getActiveSheet()->setCellValue($Column.$n, $row[$i]);  
                    $this->getActiveSheet()->getStyle($Column.$n)->getAlignment()->setWrapText(true)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);   
                }  
            }  
            $n++;  
              
            #横向|竖向 对齐方式 setHorizontal | setVertical (PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);  //也可生成EXCEL后手动设置也方便   
            # HORIZONTAL_RIGHT | HORIZONTAL_LEFT | HORIZONTAL_CENTER  参考PHPExcel/Style/Alignment.php  
            # VERTICAL_RIGHT | VERTICAL_LEFT | VERTICAL_CENTER  参考PHPExcel/Style/Alignment.php  
              
        }  
    }  
      
    /** 
     * 设置标题宽度 
     * @param 例 $arrWidth = array('A'=>8 ,'B'=>60, 'C'=>60,'D'=>'auto','E'=>0) | array(8,60,60,0,0) 
     */  
    function setRowWidth($arrWidth = array())  
    {  
        if(my_array_type($arrWidth)=='assoc') { //关联  
            foreach ($arrWidth as $Column=>$value){  
                if($value=='auto' || $value==0){  
                    $this->getActiveSheet()->getColumnDimension($Column)->setAutoSize(true);  
                }else{  
                    $this->getActiveSheet()->getColumnDimension($Column)->setWidth($value."pt");  
                }  
            }  
        }else{  
            $start = 'A';  
            for($i=0; $i<count($arrWidth); $i++){  
                $Column = $start++;  
                $value = $arrWidth[$i];  
                if($value=='auto' || $value==0){  
                    $this->getActiveSheet()->getColumnDimension($Column)->setAutoSize(true);  
                }else{  
                    $this->getActiveSheet()->getColumnDimension($Column)->setWidth($value."pt");  
                }  
            }  
        }  
    }  
      
    //设置要保存的文件,测试文件是否可以被打开  
    function setFile($file_excel)  
    {  
        $file_excel = iconv('utf-8','gbk',$file_excel); #能读系统下的中文名文件  
        
        if(!$fp=fopen($file_excel,'a+')){  
            throw new Exception("$file_excel can not fopen!!");  
        }  
        if($fp){  
            fclose($fp);  
        }  
        $this->file = $file_excel;  
    }  
      
    //保存文件  
    function saveFile($file_excel='',$output=1)   
    {  
        $file_excel = $this->file ? $this->file : $file_excel;  
        $objWriter = PHPExcel_IOFactory::createWriter($this, 'Excel5');  
        $objWriter->save($file_excel); //保存xls  
          
        $path_parts = pathinfo($file_excel);  
        $dir_bak = $path_parts["dirname"].'/'.$this->xls_bak_dir; //备份  
        if(is_dir($dir_bak)){  
            $basenameWE = substr($path_parts["basename"],0,strlen($path_parts["basename"]) - (strlen($path_parts["extension"]) + 1) );  
            $file_excel_bak = dirname(__FILE__).'/xls/backup/'.$basenameWE.' '.str_replace(':','_',date('Y-m-d H:i:s')).'.xls';  
            copy($file_excel,$file_excel_bak);  
            echo date('H:i:s') . " copy($file_excel,$file_excel_bak); ",'<br>'.PHP_EOL;  
        }  
        
        if($output){//导出excel
        	header('Content-Type: application/vnd.ms-excel');
        	header('Content-Disposition: attachment;filename="'.$file_excel.'"');
        	header('Cache-Control: max-age=0');
        
        	$objWriter->save('php://output');
        }
        
    }  
}  