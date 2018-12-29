<?php 
/**
 * oneworld api class
 * @author wx
 *
 */

class OwLogis extends Ilogis{
	
	private $api_url = "http://api.oneworldexpress.cn/";
	
	private $version = "v1";
	
	private $accountNO = 'OW00022';
	
	private $token = 'CpnATY4yqSitZHg'; //测试on3cymQ2qzCL4kZ
	
	private $nounce = '';
	
	public function __construct($config){
		foreach ($config as $key=>$v)
		{
			$this->{$key} = $v?$v:$this->{$key};
		}
		$this->nounce = date('YmdHis');
	}

	public function getHeaders(){
		return array(
            'Content-Type'=>'application/json',
            'Authorization'=>'Hc-OweDeveloper '.$this->accountNO.';'.$this->token.';'.$this->nounce,
			//'Accept-Language'=>'zh-cn',
			'Accept'=>'text/json'
		);
	}
	/**
	 * @desc api验证
	 */
	public function validateApi(){
		$validateUrl = $this->api_url.'/api/whoami';
		$response = $this->_exec($validateUrl, '', 'get');
		$ret = array();
		if( !empty($response) ){
			$response = json_decode($response);
			if( $response->Succeeded == true){
				$ret = array('flag'=>true,'msg'=>'');
			}else{
				$ret = array('flag'=>false,'msg'=>$response->Error->Message);
			}
		}
		return $ret;
	}
	
	/* public function curl_get($validateUrl){
		$headers = array(
            'Content-Type:application/json',
            'Authorization:Hc-OweDeveloper '.$this->accountNO.';'.$this->token.';'.$this->nounce,
			'Accept-Language: zh-cn',
			'Accept:text/json'
		);
		var_dump($headers);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$validateUrl);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 60);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	
		$data = curl_exec($ch);
		curl_close($ch);
		return $data;
	} */

	/**
	 * @desc 上传包裹获取跟踪号
	 * @param array $data
	 * @param int $packageid
	 */
	public function upload($json_data,$label_info,$packageid){
		$return = array();
		try 
		{
			$validateInfo = $this->validateApi();
			if( $validateInfo['flag'] ){
				$response = $this->_exec($this->api_url.'/api/parcels', $json_data, 'post');
				$response = json_decode($response);
				if ($response->Succeeded){
					$retData = $response->Data;
					$processCode = $retData->ProcessCode; //易时达 处理号
					$trackNum = $retData->TrackingNumber;
					$return = array('uploadflag'=>true,'uploadmsg'=>array('trackNum' => $trackNum,'processCode' => $processCode));
				}else{
					$return = array('uploadflag'=>false,'uploadmsg'=>'Code:'.$response->Error->Code.'  Message:'.$response->Error->Message);
				}
				return $return;
			}else{ //验证失败
				$return = array('uploadflag'=>false,'uploadmsg'=>'Authorization failure:'.$validateInfo['msg']);
				return $return;
			}
		} 
		catch (Exception $e) 
		{
			$return = array('uploadflag'=>false,'uploadmsg'=>'CatchException:'.$e->getMessage());
			return $return;
		}
	}
	
	
	/**
	 * @desc 上传包裹获取跟踪号
	 * @param array $data
	 * @param int $packageid
	 */
	public function confirm($processCode){
		$return = array();
		try
		{
			$validateInfo = $this->validateApi();
			if( $validateInfo['flag'] ){
				$response = $this->_exec($this->api_url.'/api/parcels/'.$processCode.'/confirmation','','post');
				$response = json_decode($response);
				if ($response->Succeeded){
					$return = array('uploadflag'=>true,'uploadmsg'=>'交运数据成功');
				}else{
					$return = array('uploadflag'=>false,'uploadmsg'=>'Code:'.$response->Error->Code.'  Message:'.$response->Error->Message);
				}
				return $return;
			}else{ //验证失败
				$return = array('uploadflag'=>false,'uploadmsg'=>'Authorization failure:'.$validateInfo['msg']);
				return $return;
			}
		}
		catch (Exception $e)
		{
			$return = array('uploadflag'=>false,'uploadmsg'=>'CatchException:'.$e->getMessage());
			return $return;
		}
	}
	
	/* (non-PHPdoc)
	 * @see Ilogis::getALabel()
	 */
	public function getowlabel($processCode,$packageId) {
		$return = array();
		try
		{
			$validateInfo = $this->validateApi();
			if( $validateInfo['flag'] ){
				$response = $this->_exec($this->api_url.'/api/parcels/'.$processCode.'/label','','get');
				$sub_dir = date('Ym');
				$curr_url = Yii::getPathOfAlias('webroot'). '/' .'upload/pdflabel/ow/'.$sub_dir.'/';
				$filename = $curr_url.$packageId.'.pdf';
				if(!is_dir($curr_url))
				{
					@mkdir($curr_url, 0777, true);
				}
				$fp = fopen($filename, 'w');
				fwrite($fp, $response);
				fclose($fp);
				$fsize = filesize($filename);
				if ( abs($fsize) > 0 ){
					$return = array( 'uploadflag'=>true,'label_url'=>substr( $filename,strpos($filename,'upload') ) );
				}else{
					$return = array( 'uploadflag'=>false,'label_url'=>substr( $filename,strpos($filename,'upload') ) );
				}
				return $return;
			}else{ //验证失败
				$return = array('uploadflag'=>false,'uploadmsg'=>'Authorization failure:'.$validateInfo['msg']);
				return $return;
			}
		}
		catch (Exception $e)
		{
			$return = array('uploadflag'=>false,'uploadmsg'=>'CatchException:'.$e->getMessage());
			return $return;
		}
	}
	
	
	/**
	 * @desc 上传包裹获取挂号跟踪号
	 * @param array $data
	 * @param int $packageid
	 */
	public function getPkInfo($processCode){
		$return = array();
		try
		{
			$validateInfo = $this->validateApi();
			if( $validateInfo['flag'] ){
				$response = $this->_exec($this->api_url.'/api/parcels/'.$processCode,'','get');
				$response = json_decode($response);
				
				if ($response->Succeeded){
					$retData = $response->Data;
					$trackingNumber = $retData->TrackingNumber;
					$realTrackingNumber = $retData->RealTrackingNumber;
					$trackNum = empty($realTrackingNumber)?$trackingNumber:$realTrackingNumber;
					if( empty($trackNum) ){
						$processResult = $retData->TrackingNoProcessResult;
						$return = array('uploadflag'=>false,'uploadmsg'=>'processResult:'.$processResult->Code.'  processMessage:'.$processResult->Message);
					}else{
						$return = array('uploadflag'=>true,'uploadmsg'=>array('trackNum'=>$trackNum));
					}
				}else{
					$return = array('uploadflag'=>false,'uploadmsg'=>'Code:'.$response->Error->Code.'  Message:'.$response->Error->Message);
				}
				return $return;
			}else{ //验证失败
				$return = array('uploadflag'=>false,'uploadmsg'=>'Authorization failure:'.$validateInfo['msg']);
				return $return;
			}
		}
		catch (Exception $e)
		{
			$return = array('uploadflag'=>false,'uploadmsg'=>'CatchException:'.$e->getMessage());
			return $return;
		}
	}
	
	/* (non-PHPdoc)
	 * @see Ilogis::getALabel()
	*/
	public function getALabel($nums) {
		// TODO Auto-generated method stub
		return false;
	}

	/* (non-PHPdoc)
	 * @see Ilogis::getLabels()
	 */
	public function getLabels($nums) {
		// TODO Auto-generated method stub
		return false;
	}

	/* (non-PHPdoc)
	 * @see Ilogis::trace()
	 */
	public function trace($no, $lang) {
		// TODO Auto-generated method stub
		return false;
	}

	/* (non-PHPdoc)
	 * @see Ilogis::cancel()
	 */
	public function cancel($no) {
		// TODO Auto-generated method stub
		return false;
	}

	/**
	 * request api
	 *
	 */
	private function _exec($url,$data,$method='get')
	{
		return parent::execute($url, $data, $method);
	}
	
}