 <?php 

/**
 * Byb api class
 * @author Darren
 *
 */
/*贝邮宝
 * how to use
 * Yii::import('ext.xlogis.byb.*');
 * $a=new BybLogis();
 * $truckNum=$a->uploadBybs($packageids); 
 */

class BybLogis extends Ilogis{
	
	public $_apiKey  = 'dd5d52830588a11b98265a23c0021a3f97';
	public $_apiSite = 'http://www.ppbyb.com/api.asp';
	
	public function __construct($config)
	{
	
// 		foreach ($config as $key=>$v)
// 		{
// 			$this->{$key} = $v?$v:$this->{$key};
// 		}
	
	}
	
	public function getHeaders()
	{
		return array(
				"Authorization" =>  "basic ".$this->token,
				"Content-Type" => "text/xml;charset=utf-8",
					
		);
	}
	  
	    
	
		public function upload($data,$packageid,$abs = '')
		{
			$url = $this->apiSite;
			$xml = $this->buildXml($data);			
			return $this->execute($this->_apiSite, $xml);
		}
		
		
		/**
		 * 创建xml
		 * @param array $data
		 * @return string
		 */
		
		public function buildXml($filterarray)
		{
			$xmlfilter = "<ExpressType>";
		
			foreach ($filterarray as $key=>$value) {
				if(is_array($value)) {
					$xmlfilter .= " <$key>\n".$this->buildXMLFilter($value)."</$key>\n";
				}else {
					$xmlfilter .= " <$key>$value</$key>\n";
				}
			}
			$xmlfilter .= "</ExpressType>";
			//		echo $xmlfilter;
			//		exit();
			return $xmlfilter;
		}
		
		function buildXMLFilter ($filterarray) {
			$xmlfilter = "";
		
			foreach ($filterarray as $key=>$value) {
				if(is_array($value)) {
					$xmlfilter .= " <$key>\n".buildXMLFilter($value)."</$key>\n";
				}else {
				  
					$xmlfilter .= " <$key>$value</$key>\n";
				  
				}
			}
			return $xmlfilter;
		}
	//	API需要的包裹参数信息 (上传信息)
// 		public function upload($packageData){
// 			$trackNum = '';
// 			try{
// 				$postData = array_merge($this->getSenderInfo(),$packageData);
// 				$result = $this->getApiResult($postData);
	
// 				if(isset($result->status)&&$result->status!='0'){//有错误
// 					throw new Exception($result->error_message);
// 				}else{
// 					$this->label_url = $result->PDF_10_EN_URL;
// 					return $this->trackNum = $result->barcode;
// 				}
// 			} catch (Exception $e) {
// 				$result = array();
// 				$result['error_message'] = $e->getMessage();
// 				self::writeError('uploadToBub', $e->getMessage(),json_encode($request_data));
// 				return $result;
// 			}
// 			return trim($trackNum);
// 		}
	
	
		
// 		protected function getSenderInfo(){
// 			$sendData = array(
// 					'api_key' => $this->_apiKey,
// 					//			'from' => getSysPara('eub_shipfrom_contact'),
// 			//			'sender_province' => getSysPara('byb_shipfrom_province'),
// 			//			'sender_city' => getSysPara('byb_shipfrom_city'),
// 			//			'sender_addres' => getSysPara('byb_shipfrom_street'),
// 			//			'sender_phone' => getSysPara('byb_shipfrom_mobile'),
// 			);
// 			return $sendData;
// 		}
	
// 		protected  function getApiResult(Array $data){
// 			$data = http_build_query($data);
// 			$ch = curl_init();
// 			$timeout = 5;
// 			curl_setopt($ch, CURLOPT_URL, $this->_apiSite);
// 			curl_setopt($ch, CURLOPT_POST, true);//启用POST提交
// 			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);	
// 			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);	
// 			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
// 			$result = curl_exec($ch);
// 			curl_close($ch);
// 			if (! empty($result)) {
// 				$result = simplexml_load_string($result);
// 			}
// 			return $result;
// 		}
	
	
// 		public static function writeError($callName, $message,$requestBody=null){
// 			$str = date("Y-m-d H:i:s")."  ".$callName." "."\n";	
// 			if($requestBody!=null){
// 				$str .= "Request:".$requestBody."\n";
// 			}	
// 			$str .= "ErrorMessage:".$message."\n\n";	
// 			writeEbayError($str, $callName);
// 		}
	
	

	
	

	
	/**
	 * upload message to eub
	 * @see Ilogis::upload()
	 */
	

	
	/**
	 * download a file
	 * @see Ilogis::download()
	 */
	
	public function getLabels($nums)
	{	
		return true;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Ilogis::getALabel()
	 */
	
	public function getALabel($num)
	{
		return true;
	}
	
	
	/**
	 * (non-PHPdoc)
	 * @see Ilogis::trace()
	 */
	
	public function trace($no,$lang='cn')
	{
		return true;
	}
	
	
	/**
	 * (non-PHPdoc)
	 * @see Ilogis::cancel()
	 */
	
	public function cancel($no)
	{
		return true;
	}
	
	
	/**
	 * 确认发货
	 * @param array $data
	 * @param int $packageid
	 */
	
	public function validate($data,$packageid)
	{
		return true;
	}
	
	
	
	/**
	 * 运单信息
	 * @param string $mailnum
	 * 
	 */
	
	public function order($mailnum)
	{
		return true;
	}
	
	
	

	
	
	
    /**
     * request api
     * 
     */
    
    private function _exec($url,$data,$method='get')
    {
    	return json_decode(parent::execute($url, $data, $method));
    }
	

	
}