<?php
require_once Yii::app()->basePath.'/vendors/paypal/paypal_class.php';
require_once Yii::app()->basePath.'/vendors/paypal/paypal_adaptive_class.php';
require_once Yii::app()->basePath.'/vendors/paypal/CallerService.php';

class PaypalNvpService{
	
	public $_api_user_name = null;
	public $_api_password = null;
	public $_api_signature = null;
	public $_app_id = null;
	
	public $_error_message = '';
	
	const SANDBOX = false;
	
	public function __construct($accountId){
		$accountInfo = PaypalAccount::getById($accountId);
		if($accountInfo){
			$this->_api_user_name 	= $accountInfo['api_user_name'];
			$this->_api_password 	= $accountInfo['api_password'];
			$this->_api_signature 	= $accountInfo['api_signature'];
			$this->_app_id 			= $accountInfo['app_id'];
		}
	}
	
	public function _call($method, $dataArr=''){
		loadPaypalAccount($this->_api_user_name, $this->_api_password, $this->_api_signature, $this->_app_id);
		$result = hash_call($method, $this->_buildData($dataArr));
		$ack = strtoupper($result["ACK"]);
		if($ack != "SUCCESS" && $ack != "SUCCESSWITHWARNING"){
			$this->error_message = $result['L_LONGMESSAGE0'];
			return $this->error_message;
		}else{
			return $result;
		}
	}
	
	public function _buildData($dataArr){
		$nvpStr = '';
		foreach($dataArr as $col=>$val){
			$nvpStr .= '&'.$col.'='.$val;
		}
		return $nvpStr;
	}
	
	public function _bulildConfig(){
		return array(
				'APIUsername' 	=> $this->_api_user_name,
				'APIPassword'	=> $this->_api_password,
				'APISignature'	=> $this->_api_signature,
				'ApplicationID'	=> $this->_app_id,
				'Sandbox' 		=> self::SANDBOX,
		);
	}
	
	public function getError(){
		return $this->_error_message;
	}
}