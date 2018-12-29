<?php 


/**
 * eub api class
 * @author Mark lin
 *
 */


class EubLogis extends Ilogis
{
	
	//private $api_url = "http://www.ems.com.cn:8091";
	private $api_url = "http://www.ems.com.cn";
	
	private $label_domain = "http://labels.ems.com.cn";
	
	private $version = "international_eub_us_1.1";
	
	private $authenticate = "gjtoumingjiao_13fb1de0d24f3a36b818189850e53f98";
	
	public $ShipTypeKey = 'UebShipFromAdd';

	public $PickupTypeKey = 'UebPickupAdd';
	
	public $ReturnTypeKey = 'EbayUebReturnAdd';
	
	public $OnlineShipTypeKey = 'OnlineUebShipFromAdd';

	public $OnlinePickupTypeKey = 'OnlineUebPickupAdd';
	
	public $OnlineReturnTypeKey = 'OnlineEbayUebReturnAdd';
	
	public $print_code = '01';
	
	public function __construct($config)
	{	
		foreach ($config as $key=>$v)
		{
			$this->{$key} = $v?$v:$this->{$key};
		}
		
	}

	public function getHeaders()
	{
		return array(
            'version' => $this->version,
            'authenticate' => $this->authenticate
		);
	}
	public function uploadOnLine($data,$label_info,$packageid){
		$model = new eub();
		$track_code = $model->upload($data,$label);
		if ( is_string($track_code) ){	
			if($label){
				$filePath = Yii::getPathOfAlias('webroot'). '/' .'upload/pdflabel';
				$pdf_path = $filePath.'/'.date("Ymd");
				mkdir($pdf_path, 0777, true);
				$fp = fopen($pdf_path.'/'.$packageid.'.pdf',"w+");
				fwrite($fp,$label);	
				fclose($fp);	
				$arr['pdf_path'] = 'upload/pdflabel/'.date("Ymd").'/'.$packageid.'.pdf';
			}
			$arr['result'] = 'success-%%'.$track_code;
		}else{
			$arr['result'] = 'error-%%'.$track_code[0];
		}
		return $arr;
	}
	
	public function confirm($data){
		$model = new eub();
		$arr = $model->confirm($data);
		return $arr;
	}
	
	public function cancelOnLine($data){
		$model = new eub();
		$arr = $model->cancel($data);
		return $arr;
	}
	
	public function recreateOnLine($data,$packageid){
		$model = new eub();
		$track_code = $model->reupload($data,$label);
		if ( is_string($track_code) ){
			if($label){
				$filePath = Yii::getPathOfAlias('webroot'). '/' .'upload/pdflabel';
				$pdf_path = $filePath.'/'.date("Ymd");
				mkdir($pdf_path, 0777, true);
				$fp = fopen($pdf_path.'/'.$packageid.'.pdf',"w+");
				fwrite($fp,$label);
				fclose($fp);
				$arr['pdf_path'] = 'upload/pdflabel/'.date("Ymd").'/'.$packageid.'.pdf';
			}
			$arr['result'] = 'success-%%'.$track_code;
		}else{
			$arr['result'] = 'error-%%'.$track_code['recreatemsg'];
		}
		return $arr;
	}
	
	
	/**
	 * upload message to eub
	 * @see Ilogis::upload()
	 */
	public function upload($data,$label_info,$packageid)
	{	
		try 
		{
			$url = $this->api_url . '/partner/api/public/p/order/';
			$data['packageid'] = $packageid;
			$data['OrderDetail']['ShipFromAddress']  = $this->getAddress('from');
			$data['OrderDetail']['PickUpAddress']    = $this->getAddress('pickup');
			$data['OrderDetail']['RetrunAddress']    = $this->getAddress('return');
			$data['OrderDetail']['EMSPickUpType']    = 1;  //卖家发货 配置
			$data['customerCode'] = 'cn_shenzhen_vakind_ueb'; //配置
			$xml = $this->buildUploadXml($data);
			$response = $this->_exec($url, $xml, 'post');
			if (isset($response->mailnum))
			{	
				$r = $this->getLabels($response->mailnum);
				if($r){
					$filePath = Yii::getPathOfAlias('webroot'). '/' .'upload/pdflabel';
					$pdf_path = $filePath.'/'.date("Ymd");
					mkdir($pdf_path, 0777, true);
					$fp = fopen($pdf_path.'/'.$packageid.'.pdf',"w+");
					fwrite($fp,$r);	
					fclose($fp);	
//					$this->addEubLabelInfo($label_info,$pdf_path.'/'.$packageid.'.pdf',$packageid);
					$arr['pdf_path'] = 'upload/pdflabel/'.date("Ymd").'/'.$packageid.'.pdf';
				}
				$arr['result'] = 'success-%%'.$response->mailnum;
				return $arr;
			}else{
				if (isset($response->description))
				{
					Yii::ulog($response->description, 'upload', null, 'LogisApi');
				}
				return $arr['result'] = 'error-%%'.$response->description;
			}
		}
		catch (Exception $e){
			Yii::ulog($e->getMessage(), 'upload', null, 'LogisApi');
			return false;
		}
		
	}
	
	
	//添加pdf打印信息
	function addEubLabelInfo($label_info,$pdfile,$packageid){

		require_once(Yii::app()->request->baseUrl.'/protected/vendors/FPDI141/fpdi.php');
		//Yii::import('application.vendors.PDFMerger.*');
		$maxRows = 4; //设定一个E邮宝的标签上最多打多少行库位. 
		if(!is_array($label_info)){return false;}
		
		$retfile = substr($pdfile,0,-4).'-new.pdf'; //用于保存到数据库表中.
		//$pdfile  = $_SERVER["DOCUMENT_ROOT"].''.$pdfile;
		$outfile = substr($pdfile,0,-4).'-new.pdf';
		$qc  = $label_info['qc'];
		$products = $label_info['products'];
		$packagetype = $label_info['packagetype']; //邮包类型.
		if($label_info['print_remark']){
			$print_remark = iconv('UTF-8','GB2312','★'.$label_info['print_remark']);
		}
		// initiate FPDI
		
		$pdf =& new FPDI("P","cm",array("10","10"));
		
		

		//导入第1页
		$pdf->AddPage();
		$pdf->setSourceFile($pdfile);
		$tp1 = $pdf->importPage(1);
		$pdf->useTemplate($tp1, 0, 0, 0,0,true);
		//导入第2页
		$pdf->AddPage();
		$pdf->setSourceFile($pdfile);
		$tplIdx = $pdf->importPage(2);
		// use the imported page and place it at point 10,10 with a width of 100 mm
		$pdf->useTemplate($tplIdx, 0, 0, 0,0,true);
		
		// now write some text above the imported page
		$pdf->SetFont('Arial','',9);
		$pdf->SetTextColor(0,0,0);
		//$pdf->Text(1.2,7.7,'QC: '.$qc);
		
		$pdf->SetFillColor(255,255,255);//画一个矩形遮盖下面的文字.以便添加DN号与SKU.
		$pdf->Rect(0,6.7,10,1.96,'F');
		
		$pdf->SetLineWidth(0.002);		//设置线宽
		$pdf->Line(0.45,6.7,0.45,8.7);	//第1条线
//		$pdf->Line(1.06,6.7,1.06,8.7);	//第2条线
//		$pdf->Line(6.45,6.7,6.45,8.7);	//第3条线
		$pdf->Line(7.55,6.7,7.55,8.7);	//第4条线
		$pdf->Line(8.66,6.7,8.66,8.7);	//第5条线
	
		$pdf->SetXY(8.7,0.1);
		$pdf->MultiCell(1,0.5,$qc,1,'C',0);
		
		//模拟GET提交,以便生成DN号的条码图片文件.

    	$ch = curl_init("http://".$_SERVER['HTTP_HOST']."/image.php?code=code128&o=1&t=12&r=1&text=".$packageid."&f1=-1&f2=8&a1=&a2=B&a3=&filename=".$packageid);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,true); 
		curl_setopt($ch,CURLOPT_BINARYTRANSFER,true); 
		curl_exec($ch);

		$img = substr(Yii::app()->basePath,0,-9).'upload/barcode/'.$packageid.'.png';
		$pdf->Image($img,0.5,6.85,0,0);	//包裹条码
		$pdf->Text(5.8,7.2,$packageid);	//包裹号
		$pdf->Text(8.8,7.2,"P:".$packagetype); //邮包类型
//字体样式设置		
		$pdf->AddGBFont('simhei','黑体');    
//		$pdf->SetFont('simhei','B',9); 
		$pdf->SetTextColor(0,0,0);
		//note.
		$pdf->Text(1,0.4,$print_remark);
		
		//换行输入产品库位.
		$product_arr = explode('^^',$products);
		$pdf->Text(0.3,7.6,$product_arr[0]);
		$k = 0;$setY = 7.6; //设置Y轴位置
		for($i=1;$i<count($product_arr);$i++){
			$k++;
			if($i == $maxRows){ //超过设定的最大行数则另换一页.Rubil/120103
				$k = 2;$setY = 1.2;
				$pdf->AddPage();
				$title = iconv('UTF-8','GB2312',"包裹号: $packageid 的标签,接上页");
				$pdf->Text(2,1,$title);
				$pdf->Ln();
			}
			$txtH = $k*0.35+$setY;
			$pdf->Text(0.48,$txtH,$product_arr[$i]);
		}
		
		$pdf->Output($outfile, 'F');
		return $retfile;
	}
	/**
	 * download a file
	 * @see Ilogis::download()
	 */
	
	public function getLabels($nums)
	{	
		try 
		{
			$url = $this->api_url . '/partner/api/public/p/print/batch';
			$xml = $this->buildDownloadXml($nums);
			$response = $this->_exec($url, $xml, 'post');
// 			var_dump($response);
			if (isset($response->status) && $response->status == 'success')
			{
				$zipUrl = $response->description;
           	 	$fileName = time() .'-'. rand(1, 1000).'.zip';
            	$filePath = Yii::getPathOfAlias('webroot'). '/' .'upload/pdflabel';
				
				file_put_contents($filePath . '/' . $fileName, file_get_contents($zipUrl));				
				$zip = new ZipArchive;
				$rs = $zip->open($filePath . '/' . $fileName);
				if ($rs) {
					$zip->extractTo($filePath);
					$zip->close();
					$pdffile = $filePath . '/4_4/'.$nums.'.pdf';
					if (file_exists($pdffile)) {
						$result = file_get_contents($pdffile);
						unlink($filePath.'/'.$fileName);
					}
				}
				return $result;
			}
			else 
			{
				if (isset($response->description))
				{
					Yii::ulog($response->description, 'getLabels', null, 'LogisApi');
				}
				return false;
			}
		} 
		catch (Exception $e){
			Yii::ulog($e->getMessage(), 'getLabels', null, 'LogisApi');
			return false;
		}	
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Ilogis::getALabel()
	 */
	
	public function getALabel($num)
	{
		$url = $this->label_domain . '/partner/api/public/p/static/label/download/' . md5($this->authenticate . $num) . '/' . $num . '.pdf';
		return $this->_exec($url, $data, 'get');
	}
	
	
	/**
	 * (non-PHPdoc)
	 * @see Ilogis::trace()
	 */
	
	public function trace($no,$lang='cn')
	{
		$url = $this->api_url . "/partner/api/public/p/track/query/$lang/" . $no;
		return $this->_exec($url, array(),'get');
	}
	
	
	/**
	 * (non-PHPdoc)
	 * @see Ilogis::cancel()
	 */
	
	public function cancel($no)
	{
		try 
		{
			$url = $this->api_url . '/partner/api/public/p/order/' . $no ;
			$response = $this->_exec($url, array(),'delete');
			if (isset($response->status) && $response->status == 'success')
			{
				return true;
			}
			else 
			{
				if (isset($response->description))
				{
					Yii::ulog($response->description, 'cancel', null, 'LogisApi');
				}
				return false;
			}
		} 
		catch (Exception $e) 
		{
			Yii::ulog($e->getMessage(), 'cancel', null, 'LogisApi');
			return false;
		}	
	}
	
	
	/**
	 * 确认发货
	 * @param array $data
	 * @param int $packageid
	 */
	
	public function validate($data,$packageid)
	{
		$return = array();
		try 
		{
			$url = $this->api_url . '/partner/api/public/p/validate';
			$data['packageid'] = $packageid;
			//$data['OrderDetail']['ShipToAddress'] = $data['ShipToAddress'];
			$data['OrderDetail']['ShipFromAddress'] = $this->getAddress('from');
			$data['OrderDetail']['PickUpAddress'] 	= $this->getAddress('pickup');
			$data['OrderDetail']['ReturnAddress']  	= $this->getAddress('return');
			$data['customerCode']  	= 'cn_shenzhen_vakind_ueb';
			//$data['OrderDetail']['ItemList']['Item'] = $data['Item'];
			$xml = $this->buildUploadXml($data);
			
			$response = $this->_exec($url, $xml, 'post');
			if (isset($response->status) && $response->status == 'success')
			{
				$return = array('confirmflag'=>true,'confirmmsg'=>'success');
				return $return;
			}
			else 
			{
				if (isset($response->description))
				{
					Yii::ulog($response->description, 'validate', null, 'LogisApi');
					$return = array('confirmflag'=>false,'confirmmsg'=>$response->description);
				}
				return $return;
			}
		} 
		catch (Exception $e) 
		{
			Yii::ulog($e->getMessage(), 'validate', null, 'LogisApi');
			$return = array('confirmflag'=>false,'confirmmsg'=>$e->getMessage());
			return $return;
		}
	}
	
	
	
	/**
	 * 运单信息
	 * @param string $mailnum
	 * 
	 */
	
	public function order($mailnum)
	{
		$url = $this->api_url . '/partner/api/public/p/order/' . $mailnum;
		return $this->_exec($url, array(),'get');
	}
	/**
	 * geren xml
	 * @param array $order
	 */
	//上传线下E邮宝 - XML格式请求
	public function buildUploadXml($data)
	{
        $xml =  '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<orders xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">';
        $xml .= "\n<order><orderid>{$data['packageid']}</orderid>";             
        $xml .= "<customercode>{$data['customerCode']}</customercode> 
        <operationtype>0</operationtype>     
        <producttype>0</producttype>       
        <clcttype>0</clcttype>      
        <pod>false</pod>       
        <untread>Returned</untread>                  
        <printcode>01</printcode>";        
        //shipping from address
        $shipFromAddressInfo = $data['OrderDetail']['ShipFromAddress'];
        if (! empty($shipFromAddressInfo)) {
        $xml .= "<sender>
            <name>{$shipFromAddressInfo['Contact']}</name>
            <postcode>{$shipFromAddressInfo['Postcode']}</postcode>     
            <mobile>{$shipFromAddressInfo['Mobile']}</mobile>
            <country>{$shipFromAddressInfo['Country']}</country>
            <province>{$shipFromAddressInfo['Province']}</province>
            <city>{$shipFromAddressInfo['City']}</city>  
            <county>{$shipFromAddressInfo['District']}</county>
            <company>{$shipFromAddressInfo['Company']}</company>
            <street>{$shipFromAddressInfo['Street']}</street>
            <email>{$shipFromAddressInfo['Email']}</email>
            </sender>";       
        }
        
        //shipping to address
        $shipToAddressInfo = $data['OrderDetail']['ShipToAddress'];
        if (! empty($shipToAddressInfo)) {
            $shipToCountry = strtoupper(trim($shipToAddressInfo['Country']));
            if ( $shipToCountry == 'UNITED STATES') {
                $shipToCountry = 'UNITED STATES OF AMERICA';
            }
            $xml .= "<receiver>
            <name>{$shipToAddressInfo['Contact']}</name>
            <postcode>{$shipToAddressInfo['Postcode']}</postcode>
            <phone>{$shipToAddressInfo['Phone']}</phone>           
            <country>{$shipToCountry}</country>
            <province>{$shipToAddressInfo['Province']}</province>
            <city>{$shipToAddressInfo['City']}</city>          
            <street>{$shipToAddressInfo['Street']}</street>
            </receiver>";        
        }
        
        //pick up address
        $pickUpAddressInfo = $data['OrderDetail']['PickUpAddress'];
        if (! empty($pickUpAddressInfo)) {
            $xml .= "<collect>
            <name>{$pickUpAddressInfo['Contact']}</name>
            <postcode>{$pickUpAddressInfo['Postcode']}</postcode>
            <phone>{$pickUpAddressInfo['Mobile']}</phone>
            <mobile>{$pickUpAddressInfo['Mobile']}</mobile>
            <country>{$pickUpAddressInfo['Country']}</country>
            <province>{$pickUpAddressInfo['Province']}</province>
            <city>{$pickUpAddressInfo['City']}</city>
            <county>{$pickUpAddressInfo['District']}</county>
            <company>{$pickUpAddressInfo['Company']}</company>
            <street>{$pickUpAddressInfo['Street']}</street>
            <email>{$pickUpAddressInfo['Email']}</email>
            </collect>";               
        }
        if (! empty($data['OrderDetail']['ItemList']['Item'])) {
           $itemList = $data['OrderDetail']['ItemList']['Item']; 
            $xml .= "<items>";
            foreach ( $itemList as $itemInfo ) {
            	$skuinfo = $itemInfo['SKU'];
                $weight = round($skuinfo['Weight'], 3);
                $count = $skuinfo['Count']?$skuinfo['Count']:1;
                $xml .= "<item>
                <cnname>{$skuinfo['CustomsTitleCN']}</cnname>
                <enname>{$skuinfo['CustomsTitleEN']}</enname>
                <count>{$count}</count>
                <weight>{$weight}</weight>
                <delcarevalue>5.000</delcarevalue>
                <origin>CN</origin>
                </item>";            
            }
            $xml .= "</items>";      
        }
        $xml .= "</order></orders>";   

        return $xml;
	}
	
	
	/**
	 * build download xml
	 * @param array $arr
	 */
	
	private function buildDownloadXml($data)
	{         
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
                <orders xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">';
        if ( is_array($data) ) {
            foreach ($data as $key => $val ) {
                $xml .= "<order><mailnum>$val</mailnum></order>";
            } 
        } else {
            $xml .= "<order><mailnum>$data</mailnum></order>";
        }                       
        $xml .= '</orders>';
       
        return $xml;
	}
	
	
	
    /**
     * request api
     * 
     */
    
    private function _exec($url,$data,$method='get')
    {
    	return simplexml_load_string(parent::execute($url, $data, $method));
    }
	

	
}