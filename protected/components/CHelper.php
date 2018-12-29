<?php
/**
 * Controller Helper Class
 * @package Application.components
 * @auther Bob <Foxzeng>
 */
class CHelper {

    /**
     * timing profiling log
     *
     * @return string $timing;
     */
    public static function profilingTimeLog() {
        $url = Yii::app()->request->getUrl();
        $uri = explode("?_=", $url);
        $level = ULogger::LEVEL_SUCCESS;
        $begin = Yii::app()->session['timings_' . session_id(). $uri[1]];
        unset(Yii::app()->session['timings_' . session_id(). $uri[1]]);
        $timing = self::diffSeconds($begin);
        $systemConfig = SysConfig::getConfigCacheByType('system');

        if ( $timing > $systemConfig['profileTimingLimit'] ) {
            Yii::ulog($timing, $uri[1], 'profile', $level, Yii::app()->request->getPathInfo(), $url);
        }
    }

    /**
     * profiling Time
     * @return string $timing
     */
    public static function profilingTime() {
        $timing = 0;
        $url = Yii::app()->request->getUrl();
        if ( strpos($url, "?_=") !== false ) {
            $uri = explode("?_=", $url);
            $uri[1] = substr($uri[1], 0, 13);
            $begin = Yii::app()->session['timings_' . session_id(). $uri[1]];
            unset(Yii::app()->session['timings_' . session_id(). $uri[1]]);
            $timing = self::diffSeconds($begin);
        }

        return $timing;
    }

    /**
     * diff seconds
     * @param string $beginTime
     * @param string $endTime
     * @return float
     */
    public static function diffSeconds($beginTime, $endTime = null) {
        if ( $endTime === null ) {
            $endTime = microtime();
        }
        $beginTime = array_sum(preg_split('/[\s]+/', $beginTime));
        $timing = sprintf("%s", number_format(array_sum(preg_split('/[\s]+/', $endTime)) - $beginTime, 3));

        return $timing;
    }

    /**
     *
     * @param $headArr 标题
     * @param $alist 数据
     * @param string $imgindexs 是否导出图片
     * @param string $filename 文件名（默认的为时间命名）
     * @param string $position 导出图片的位置：bottom，是图片在数据的下面
     * @param int $width excel表格格子的宽
     * @param string $rowHeight excel表格格子的高
     * @param int $pwidth 导出的图片宽
     * @param int $pheight 导出的图片高
     */
    public static function ExportExcel($headArr,$alist,$filename="",$imgindexs=false,$position='',$width=25,$rowHeight='',$pwidth=50,$pheight=50){
        set_time_limit(0);
        ini_set('memory_limit', '-1');

        $dir = $_SERVER['DOCUMENT_ROOT']; //定义网站根目录

        /** 设置报错级别 */
        //error_reporting(E_ALL);
        //ini_set('display_errors', 0);
        //ini_set('display_startup_errors', 0);
        if (PHP_SAPI == 'cli')
            die('This example should only be run from a Web Browser');

        /** Include PHPExcel */
        require_once $dir.'/themes/Classes/PHPExcel.php';

        $cacheMethod = PHPExcel_CachedObjectStorageFactory::cache_to_phpTemp;
        $cacheSettings = array( 'memoryCacheSize' => '512MB');
        PHPExcel_Settings::setCacheStorageMethod($cacheMethod,$cacheSettings);
        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();

        // Set document properties
        $objPHPExcel->getProperties()->setCreator("Maarten Balliauw")
            ->setLastModifiedBy("Maarten Balliauw")
            ->setTitle("Office 2007 XLSX Test Document")
            ->setSubject("Office 2007 XLSX Test Document")
            ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
            ->setKeywords("office 2007 openxml php")
            ->setCategory("Test result file");

        /*实例化excel图片处理类*/
        $objDrawing = new PHPExcel_Worksheet_Drawing();

        /*$colnums = count($headArr);
        for($i = 0,$startA = "A"; $i < $colnums; $i++) {
            // 设置列数
            if(is_array($width)){
                if($width[$i]){
                    $cellwidth=$width[$i];
                }else{
                    $cellwidth=25;
                }
            }else{
                $cellwidth=$width;
            }
            if($i > 25) {
                $j = $i -26;
                $temp = $startA.chr(intval(ord($startA))+$j);
            } else {
                $temp = chr(intval(ord($startA))+$i);
            }
            $objPHPExcel->getActiveSheet()->getColumnDimension($temp)->setWidth($cellwidth);
        }

        //设置标题
        for($i = 0,$startA = "A"; $i < $colnums; $i++) {
            // 设置列数
            if($i > 25) {
                $j = $i - 26;
                $temp = $startA.chr(intval(ord($startA))+$j).'1';
            } else {
                $temp = chr(intval(ord($startA))+$i).'1';
            }
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($temp, $headArr[$i]);
        }*/
//        $objPHPExcel->createSheet(0);
        $objPHPExcel->setActiveSheetIndex(0);
        // 重命名 worksheet
        if($filename){
            $objPHPExcel->getActiveSheet()->setTitle($filename);
        }
        self::setExcelHead($objPHPExcel,$headArr,$width);
        // Miscellaneous glyphs, UTF-8
        $row=2;         //sheet中第第几行的判断
        $count = 1;   //分页判断统计数
        $page = 1;      //第几个sheet
        $pushCount = 0;  //刷出判断
        foreach($alist as $key=>$val){
            if($count > 50000 * $page)
            {
                $objPHPExcel->createSheet($page);
                $objPHPExcel->setActiveSheetIndex($page);
                if($filename){
                    $objPHPExcel->getActiveSheet()->setTitle($filename);
                }
                $page += 1;
                $row = 2;
                self::setExcelHead($objPHPExcel,$headArr,$width);
            }
            if($pushCount > 500)
            {
                ob_flush();
                flush();
                $pushCount = 0;
            }
            //设置行高
            if($rowHeight){
                $objPHPExcel->getActiveSheet()->getRowDimension($row)->setRowHeight($rowHeight);
            }

            $span = 0;
            $startA = 'A';

            //填充每一行的内容
            foreach($val as $factkey=>$factval){
                if($span > 25) {
                    $j = $span -26;
                    $temp = $startA.chr(intval(ord($startA))+$j).$row;
                } else {
                    $temp = chr(intval(ord($startA))+$span).$row;
                }
                //1.图片填充列
                if($position=='bottom'){//view bottom
                    $num=$span*2;
                    if($val['img']=='pic'){
                        $row=$key;
                    }

                    $ptemp = chr(intval(ord($startA))+$num).$row;
                }else{
                    $ptemp = $temp;
                }

                if(is_file($factval) && $imgindexs){
                    /*实例化插入图片类*/
                    $objDrawing = new PHPExcel_Worksheet_Drawing();
                    /*设置图片路径 切记：只能是本地图片*/
                    $objDrawing->setPath($factval);
                    /*设置图片高度*/
                    $objDrawing->setHeight($pheight);
                    $objDrawing->setWidth($pwidth);
                    // 图片偏移距离
                    $objDrawing->setOffsetX(10);
                    $objDrawing->setOffsetY(10);
                    /*设置图片要插入的单元格*/
                    $objDrawing->setCoordinates($ptemp);
                    $objDrawing->setWorksheet($objPHPExcel->getActiveSheet());
                }else{
                    //2.非图片填充列
                    $objPHPExcel->getActiveSheet()->setCellValue($temp, $factval);
                }
                $span++;
            }
            $row++;
            $count++;
        }


        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);

        $filename = iconv("utf-8", "gb2312", $filename.date('Y-m-dHis').rand(100,999));

        header('Content-Type: application/vnd.ms-excel;');
        header('Content-Disposition: attachment;filename='.$filename.'.xls');
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');

        // If you're serving to IE over SSL, then the following may be needed
        header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
        header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header ('Pragma: public'); // HTTP/1.0

        //注意这里 第二个参数写成 'Excel2007' 会避免特殊字符或中文乱码
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');

        exit;
    }

    public static function setExcelHead(&$objPHPExcel,$headArr,$width)
    {
        $colnums = count($headArr);
        for($i = 0,$startA = "A"; $i < $colnums; $i++) {
            // 设置列数
            if(is_array($width)){
                if($width[$i]){
                    $cellwidth=$width[$i];
                }else{
                    $cellwidth=25;
                }
            }else{
                $cellwidth=$width;
            }
            if($i > 25) {
                $j = $i -26;
                $temp = $startA.chr(intval(ord($startA))+$j);
            } else {
                $temp = chr(intval(ord($startA))+$i);
            }
            $objPHPExcel->getActiveSheet()->getColumnDimension($temp)->setWidth($cellwidth);
        }

        //设置标题
        for($i = 0,$startA = "A"; $i < $colnums; $i++) {
            // 设置列数
            if($i > 25) {
                $j = $i - 26;
                $temp = $startA.chr(intval(ord($startA))+$j).'1';
            } else {
                $temp = chr(intval(ord($startA))+$i).'1';
            }
//            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($temp, $headArr[$i]);
            $objPHPExcel->getActiveSheet()->setCellValue($temp, $headArr[$i]);
        }
    }

    /**
     * 用户名、邮箱、手机账号中间字符串以*隐藏
     * @param $str
     * @return mixed|string
     */
    public static function hideStar($str) {
        if(!empty($str)){
            $pattern = '/(1[3458]{1}[0-9])[0-9]{4}([0-9]{4})/i';
            if (strpos($str, '@')) {
                $email_array = explode("@", $str);
                //$prevfix = (strlen($email_array[0]) < 4) ? "" : substr($str, 0, 3); //邮箱前缀
                $prevfix = (strlen($email_array[0]) < 3) ? $email_array[0] : mb_substr($str, 0, 2,'utf-8'); //邮箱前缀
                //$count = 0;
                //$str = preg_replace('/([\d\w+_-]{0,100})@/', '***@', $str, -1, $count);
                $str='***@'.$email_array[1];
                $rs = $prevfix . $str;
            } else if (preg_match($pattern, $str)) {
                $rs = preg_replace($pattern, '$1****$2', $str); // substr_replace($name,'****',3,4);
            }else {
                $rs = mb_substr($str, 0, 2,'utf-8') . "***" . mb_substr($str, -1, 1,'utf-8');
            }
            return $rs;
        }
        return '';
    }

    /*
	*站点货币映射
	*return string
	*/
    public static function rateMap($site)
    {
        $rate = array(
            'us' => 'USD',
            'uk' => 'GBP',
            'sp' => 'EUR',
            'jp' => 'JPY',
            'ca' => 'CAD',
            'mx' => 'MXN',
            'in' => 'IDR',
            'it' => 'EUR',
            'de' => 'EUR',
            'fr' => 'EUR',
            'au' => 'AUD'
        );
        if($rate[$site]){
            return $rate[$site];
        } else {
            return '';
        }
    }

    /**
     * @test
     *
     * @noreturn
     */
    public static function sendEmail($to, $title, $content,$file='')
    {
        $email = new \Mail();

        $email->initialize(array(
            'protocol'  => 'smtp',
            'mailtype'  => 'html',
            'smtp_host' => 'smtp.163.com',
            'smtp_user' => '***@163.com',
            'smtp_pass' => '***',
            'smtp_port' => '25',
            'charset'   => 'utf-8',
            'wordwrap'  => TRUE,
        ));

        $r = $email->clear();
        if(!empty($file)){
            $r->attach($file);
        }
        $r->to($to)
        ->from('***@163.com', 'Me QQ')
        ->subject($title)
        ->message($content)
        ->send();

        //echo $email->print_debugger();

        return $r;

    }

    /**
     * 图片上传处理
     * @param $files
     * @param string $filepath
     * @param string $filenames
     * @param int $size
     * @return array|string]
     */
    public static function upload_files($files,$filepath='',$filenames='',$size=2){

        if(empty($filepath)){
            $filepath='upload/image/'.date('Ymd').'/';
        }

        if (!file_exists($filepath))
        {
            mkdir($filepath, 0777,true);
        }

        $path=date('YmdHis',time()).rand(100,999);
        $path_arr = @end(explode('.',$files['name']));

        //大小判断
        $filessize=$files['size']/1024/1024;
        if($filessize>$size){
            return ['code'=>'error','msg'=>"文件大小不能大于".$size."M"];
        }

        if(empty($filenames)){
            $filenames=['jpg','jpeg','png','gif','JPG','JPEG','PNG','GIF','xls','xlsx','txt','rar','zip','pdf','doc','csv'];
        }

        if(in_array($path_arr,$filenames)){
            $file_path = $filepath.$path.'.'.$path_arr;
            move_uploaded_file($files['tmp_name'],$file_path);
            return $path.'.'.$path_arr;
        }else{
            return ['code'=>'error','msg'=>'不允许上传的文件格式！'];
        }

        if($files['error']>1){
            switch($files['error']) {
                case 2:
                    // 要上传的文件大小超出浏览器限制
                    return ['code'=>'error','msg'=>"The file is too large (form)."];
                case 3:
                    // 文件仅部分被上传
                    return ['code'=>'error','msg'=>"The file was only partially uploaded."];
                case 4:
                    // 没有找到要上传的文件
                    return ['code'=>'error','msg'=>"No file was uploaded."];
                case 5:
                    // 服务器临时文件夹丢失
                    return ['code'=>'error','msg'=>"The servers temporary folder is missing."];
                case 6:
                    // 文件写入到临时文件夹出错
                    return ['code'=>'error','msg'=>"Failed to write to the temporary folder."];
                case 7:
                    // 文件写入失败
                    return ['code'=>'error','msg'=>"File write failed."];
                case 8:
                    // php文件上传扩展没有打开
                    return ['code'=>'error','msg'=>"Php file upload extension does not open."];
            }

        }
        exit;
    }

    /**
     * 生成账号树数据
     * @param null $activeRecord
     * @return array
     */
    public static function getAccountTreeList($activeRecord = null)
    {
        $criteria            = new CDbCriteria();
        $criteria->select    = '*';
        $criteria->condition = "status=:status";
        $criteria->params    = [':status' => 1];
        $criteria->order     = 'sort asc, group_id asc';

        $rs = UebModel::model('Amazon')->findAll($criteria);

        $data = [];
        foreach ($rs as $val) {
            if ($val->group_id) {
                $data[$val->group_id][] = [
                    'id'   => $val->id,
                    'gid'  => $val->group_id,
                    'name' => $val->account_name,
                ];
            }
        }
        return $data;
    }

    /**
     * 格式化excel读取的日期
     * @param $date
     * @param string $type
     * @return false|string
     */
    public static function excelTime($date, $type=''){
        date_default_timezone_set('UTC');
        if(is_numeric($date)){
            if($type){
                $date = date($type,($date - 25569) * 86400);
            }else{
                $date = date('Y-m-d H:i:s',($date - 25569) * 86400);
            }

            return $date;
        }
        return $date;
    }

    /**
     * Amazon site->country map
     * @return array
     */
    public static function getAmazonSiteCountry(){
       return array(
            'us' => '美国',
            'uk' => '英国',
            'de' => '德国',
            'ca' => '加拿大',
            'fr' => '法国',
            'sp' => '西班牙',
            'it' => '意大利',
            'jp' => '日本',
            'br' => '巴西',
            'in' => '印度',
            'mx' => '墨西哥',
            'nl' => '荷兰',
            'au' => '澳大利亚'
        );
    }

    /**
     * 获取对应的最新的汇率
     * @param $fcurrcode 要兑换的币种
     * @param $tcurrcode 兑换后的币种
     * @param string $type
     * @return bool
     */
    public static function getFccByTcc($fcurrcode,$tcurrcode,$type=''){
        if(!empty($fcurrcode) && !empty($tcurrcode)){
            $rates=CurrencyRate::model()->findAll('from_currency_code=:fcc and to_currency_code=:tcc',[':fcc'=>$fcurrcode,':tcc'=>$tcurrcode]);
            $rate=end($rates);
            return $rate['rate'];
        }
        return false;
    }

    /**
     * 前导值的设置
     * @param $input
     * @param int $pad_length
     * @param string $pad_string
     * @param int $pad_type
     * @return string
     */
    public static function getStrPad($input, $pad_length=2, $pad_string = '0', $pad_type = STR_PAD_LEFT){
        return str_pad($input, $pad_length, $pad_string, $pad_type);
    }

    /**
     * 最新汇率映射
     * @param bool $type
     * @return array
     */
    public static function getRates($type=false){
        if($type){
            $maprate = [
                'USD' => 1,
                'CAD' => CHelper::getFccByTcc('CAD', 'USD'),
                'MXN' => CHelper::getFccByTcc('MXN', 'USD'),
                'GBP' => CHelper::getFccByTcc('GBP', 'USD'),
                'EUR' => CHelper::getFccByTcc('EUR', 'USD'),
                'JPY' => CHelper::getFccByTcc('JPY', 'USD'),
                'AUD' => CHelper::getFccByTcc('AUD', 'USD'),
            ];

            return array(
                'uk'=>$maprate['GBP'],
                'de'=>$maprate['EUR'],
                'fr'=>$maprate['EUR'],
                'sp'=>$maprate['EUR'],
                'it'=>$maprate['EUR'],
                'us'=>$maprate['USD'],
                'ca'=>$maprate['CAD'],
                'mx'=>$maprate['MXN'],
                'jp'=>$maprate['JPY'],
                'au'=>$maprate['AUD'],
            );
        }else{
            return [
                'USD' => 1,
                'CAD' => CHelper::getFccByTcc('CAD', 'USD'),
                'MXN' => CHelper::getFccByTcc('MXN', 'USD'),
                'GBP' => CHelper::getFccByTcc('GBP', 'USD'),
                'EUR' => CHelper::getFccByTcc('EUR', 'USD'),
                'JPY' => CHelper::getFccByTcc('JPY', 'USD'),
                'AUD' => CHelper::getFccByTcc('AUD', 'USD'),
            ];
        }

    }

}
?>
