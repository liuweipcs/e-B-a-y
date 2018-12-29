<?php 
/**
 * oneworld api class
 * @author wx
 *
 */

class SyLogis extends Ilogis{
	
	private $api_url = "http://api.sunyou.hk/order";

	private $version = "v1";

	private $account = 'SZUNI';
	
	private $password = '123456';

	private $token = 'Jn50oIqc/tcRcicVbcLR5g==';//测试on3cymQ2qzCL4kZ
	
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
				 'Content-Type'=>'application/json;charset=\"utf-8\"',
				'Accept'=>'text/json' ,
				'SunYou-Token'=>'Jn50oIqc/tcRcicVbcLR5g=='
		);
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
			$response = $this->_exec($this->api_url.'/create_order.htm', $json_data, 'post');
		
			$response = json_decode($response);
			$retData = $response->result;
			if ( $response->respStatus == 400){
				$return = array('uploadflag'=>false,'uploadmsg'=>'Code:'.$response->respStatus.'  Message:'.$retData);
				
			}
			elseif ($response->result->statusCode == 100){
				
				$trackNum = $retData->obj->trNum;
				
				$return = array('uploadflag'=>true,'uploadmsg'=>array('trackNum' => $trackNum));
			}else{
				$return = array('uploadflag'=>false,'uploadmsg'=>'Code:'.$retData->statusCode.'  Message:'.$retData->msg);
			}
			return $return;
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