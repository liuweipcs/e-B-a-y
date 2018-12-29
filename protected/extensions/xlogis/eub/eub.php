<?php
/*
 * 		线上易邮宝
 * */
class eub{
	
	public $requestUrl = 'https://api.apacshipping.ebay.com.hk/aspapi/v4/ApacShippingService?WSDL';
	public $version = '4.0.0';
	
	public $devID = '433b57c3-cc37-4d73-a28d-8cc33791bb40';  
	public $appID = 'vakindd80-38d6-46c2-9b38-14d6cfd4c64';
	public $certID = '97ee6168-6492-4e95-844b-1e15afdf907e';
	
	private $soapclient;
	
	function connect(){
		$this->soapclient = new soapclient($this->requestUrl,array('trace'=>true,'location' => $this->requestUrl));
	}
	function upload($apiRequest,&$label_file){  
		$this->connect();
		if(empty($this->soapclient)){ return false; }
		$result = false;
		$callName = 'AddAPACShippingPackage';   
		$ack = '';
		//准备提交数据
		$apiRequest["PageSize"]  = 1;
		$apiRequest['Carrier'] = 'CNPOST';
		$apiRequest['Version'] = $this->version;
		$apiRequest['APIDevUserID'] = $this->devID;
		$apiRequest['AppID'] = $this->appID;
		$apiRequest['AppCert'] = $this->certID;
// 		try{   
			$para = array("AddAPACShippingPackageRequest"=>$apiRequest); 
			$retInfo = $this->soapclient->$callName($para);
			$ack = trim($retInfo->AddAPACShippingPackageResult->Ack);
			if(is_array($retInfo->AddAPACShippingPackageResult->NotificationList->Notification)){
				$message = $retInfo->AddAPACShippingPackageResult->NotificationList->Notification[0]->Message;
			}else{
				$message = $retInfo->AddAPACShippingPackageResult->NotificationList->Notification->Message;
			}
			if($ack == 'Success' || $ack == 'Warning'){
				$result = $retInfo->AddAPACShippingPackageResult->TrackCode;
				$apiRequest['TrackCode'] = $result;
				$label_file = $this->getLabel($apiRequest);
				return $apiRequest['TrackCode'];
			}elseif(substr_count($message,'TransactionId') && substr_count($message,'Already exists')){
                    $apiRequestTrackCode = array(
                           'Version'             => $apiRequest['Version'],
                           'APIDevUserID'   	 => $apiRequest['APIDevUserID'],
                           'APISellerUserToken'  => $apiRequest['APISellerUserToken'],
                           'APISellerUserID'	 => $apiRequest['APISellerUserID'],
                           'AppID'               => $apiRequest['AppID'],
                           'AppCert'             => $apiRequest['AppCert'],
                    	   'Carrier'			 => 'CNPOST',
					);//构造取得trackcode的参数.
					for($i = 0; $i < count($apiRequest['OrderDetail']['ItemList']['Item']); $i++){//循环取track code.
						$apiRequestTrackCode['EBayItemID'] 			= $apiRequest['OrderDetail']['ItemList']['Item'][$i]['EBayItemID'];
						$apiRequestTrackCode['EBayTransactionID']   = $apiRequest['OrderDetail']['ItemList']['Item'][$i]['EBayTransactionID'];
						if($trackcode = $this->getTrackCode($apiRequestTrackCode)){//只要一个取到则退出.
							$apiRequest['TrackCode'] = $trackcode;
							$label_file = $this->getLabel($apiRequest);
							return $trackcode;
						}
					}
			}else{
				return array($message);
			}
// 		}catch(Exception $e){
// 			$message = $retInfo->AddAPACShippingPackageResult->NotificationList->Notification->Message;
// 			$time = $retInfo->AddAPACShippingPackageResult->Timestamp;
// 			$invocationID = $retInfo->AddAPACShippingPackageResult->InvocationID;
// 		}
		return $result;
	}
	public function getTrackCode($apiRequestTrackCode){
		$this->connect();
		if(empty($this->soapclient)){ return false; }
		if(!is_array($apiRequestTrackCode)){return false;}
		$callName = 'GetAPACShippingTrackCode';
		try{
			$para = array("GetAPACShippingTrackCodeRequest"=>$apiRequestTrackCode);

			$retInfo = $this->soapclient->$callName($para);
			
			$ack = trim($retInfo->GetAPACShippingTrackCodeResult->Ack);

			if($ack == 'Success' || $ack == 'Warning'){
				return $retInfo->GetAPACShippingTrackCodeResult->TrackCode;
			}else{
				$message = $retInfo->GetAPACShippingTrackCodeResult->Message;
				$time = $retInfo->GetAPACShippingTrackCodeResult->Timestamp;
				$invocationID = $retInfo->GetAPACShippingTrackCodeResult->InvocationID;		
				throw new Exception($this->_getErrorMessage($message, $time, $invocationID));
			}
		}catch(Exception $e){
				$message = $retInfo->GetAPACShippingTrackCodeResult->Message;
				$time = $retInfo->GetAPACShippingTrackCodeResult->Timestamp;
				$invocationID = $retInfo->GetAPACShippingTrackCodeResult->InvocationID;
// 			    $this->writeError($this->soapclient->__getLastRequest(),$callName,$ack,$this->_getErrorMessage($message, $time, $invocationID));
		}//end try
	}
	
	
	function getLabel($apiRequest){
		$this->connect();
		if(empty($this->soapclient)){ return false; }
		$result = false;
		$callName = 'GetAPACShippingLabel';
		$ack = '';
		try{
			$para = array("GetAPACShippingLabelRequest"=>$apiRequest);
			
			$retInfo = $this->soapclient->$callName($para);
			$ack = trim($retInfo->GetAPACShippingLabelResult->Ack);
			if($ack == 'Success' || $ack == 'Warning'){
				$result = $retInfo->GetAPACShippingLabelResult->Label;
			}else{
				throw new Exception(trim($retInfo->GetAPACShippingLabelResult->Message));
			}
		}catch(Exception $e){
		
			$message = $retInfo->GetAPACShippingLabelResult->Message;
			$time = $retInfo->GetAPACShippingLabelResult->Timestamp;
			$invocationID = $retInfo->GetAPACShippingLabelResult->InvocationID;
			$errorMessage =  $this->_getErrorMessage($message, $time, $invocationID);

			$this->writeError($this->soapclient->__getLastRequest(),$callName,$ack,$errorMessage);
		}//end try
		return $result;
	}
	
	/**
	 * @desc 交运eub
	 * @param unknown $apiRequest
	 * @throws Exception
	 * @return boolean
	 */
	function confirm($apiRequest){
		if(!$apiRequest['Carrier']){
			$apiRequest['Carrier'] = 'CNPOST';
		}
		$apiRequest['Version'] = $this->version;
		$apiRequest['APIDevUserID'] = $this->devID;
		$apiRequest['AppID'] = $this->appID;
		$apiRequest['AppCert'] = $this->certID;
		$this->connect();
		if(empty($this->soapclient)){ return false; }
		$result = array();
		$callName = 'ConfirmAPACShippingPackage';
		$ack = '';
		try{
			$para = array("ConfirmAPACShippingPackageRequest"=>$apiRequest);
			$retInfo = $this->soapclient->$callName($para);
			$ack = trim($retInfo->ConfirmAPACShippingPackageResult->Ack);
			if($ack == 'Success' || $ack == 'Warning'){
				$result['confirmflag'] = true;
			}else{
				$message = $retInfo->ConfirmAPACShippingPackageResult->NotificationList->Notification->Message;
				$time = $retInfo->ConfirmAPACShippingPackageResult->Timestamp;
				$invocationID = $retInfo->ConfirmAPACShippingPackageResult->InvocationID;
				//Yii::ulog('[time='.$time.']'.$message.'[invocationId='.$invocationID.']', 'upload', null, 'LogisApi');
				$result['confirmmsg'] = '[time='.$time.']'.$message.'[invocationId='.$invocationID.']';
			}
		}catch(Exception $e){
			$message = $retInfo->ConfirmAPACShippingPackageResult->NotificationList->Notification->Message;
			$time = $retInfo->ConfirmAPACShippingPackageResult->Timestamp;
			$invocationID = $retInfo->ConfirmAPACShippingPackageResult->InvocationID;
			//Yii::ulog('[time='.$time.']'.$message.'[invocationId='.$invocationID.']', 'upload', null, 'LogisApi');
			$result['confirmmsg'] = '[time='.$time.']'.$message.'[invocationId='.$invocationID.']';
		}
		return $result;
	}
	
	
	/**
	 * @desc 取消eub包裹
	 * @param unknown $apiRequest
	 * @throws Exception
	 * @return boolean
	 */
	function cancel($apiRequest){
		if(!$apiRequest['Carrier']){
			$apiRequest['Carrier'] = 'CNPOST';
		}
		$this->connect();
		if(empty($this->soapclient)){ return false; }
		$result = array();
		$callName = 'CancelAPACShippingPackage';
		$ack = '';
		
		//准备提交数据
		$apiRequest['Version'] = $this->version;
		$apiRequest['APIDevUserID'] = $this->devID;
		$apiRequest['AppID'] = $this->appID;
		$apiRequest['AppCert'] = $this->certID;
		
		try{
			$para = array("CancelAPACShippingPackageRequest"=>$apiRequest);
			$retInfo = $this->soapclient->$callName($para);
			//print_r($retInfo);
			
			$ack = trim($retInfo->CancelAPACShippingPackageResult->Ack);
			if($ack == 'Success' || $ack == 'Warning'){
				$result['cancelflag'] = true;
			}else{
				$messageArr = $retInfo->CancelAPACShippingPackageResult->NotificationList->Notification;
				//var_dump($messageArr);
				$messageArr = (array)$messageArr;
				//var_dump($messageArr);
				$message = '';
				foreach( $messageArr as $item ){
					$message .= $item->Message.' ';
				}
				if( trim($message) == '' ){
					$message .= $messageArr['Message'];
				}
	
				$time = $retInfo->CancelAPACShippingPackageResult->Timestamp;
				$invocationID = $retInfo->CancelAPACShippingPackageResult->InvocationID;
				$result['cancelmsg'] = '[time='.$time.']'.$message.'-[invocationId='.$invocationID.']';
			}
		
		}catch(Exception $e){
			$messageArr = $retInfo->CancelAPACShippingPackageResult->NotificationList->Notification;
			//var_dump($messageArr);
			$messageArr = (array)$messageArr;
			//var_dump($messageArr);
			$message = '';
			foreach( $messageArr as $item ){
				$message .= $item->Message.' ';
			}
			if( trim($message) == '' ){
				$message .= $messageArr['Message'];
			}
			$time = $retInfo->CancelAPACShippingPackageResult->Timestamp;
			$invocationID = $retInfo->CancelAPACShippingPackageResult->InvocationID;
			$result['cancelmsg'] = '[time='.$time.']'.$message.'[invocationId='.$invocationID.']';
		}
		return $result;
	}
	
	/**
	 * @desc 重新发货eub包裹，只针对原包裹已经交运过的
	 * @param unknown $apiRequest
	 * @throws Exception
	 * @return boolean
	 */
	function reupload($apiRequest,&$label_file){
		if(!$apiRequest['Carrier']){
			$apiRequest['Carrier'] = 'CNPOST';
		}
		$this->connect();
		if(empty($this->soapclient)){ return false; }
		$result = array();
		$callName = 'RecreateAPACShippingPackage';
		$ack = '';
		
		//准备提交数据
		$apiRequest["PageSize"]  = 1;
		$apiRequest['Version'] = $this->version;
		$apiRequest['APIDevUserID'] = $this->devID;
		$apiRequest['AppID'] = $this->appID;
		$apiRequest['AppCert'] = $this->certID;
		
		try{
			$para = array("RecreateAPACShippingPackageRequest"=>$apiRequest);
			$retInfo = $this->soapclient->$callName($para);
			
			$ack = trim($retInfo->RecreateAPACShippingPackageResult->Ack);
			if($ack == 'Success' || $ack == 'Warning'){
				$apiRequest['TrackCode'] = $retInfo->RecreateAPACShippingPackageResult->TrackCode;
				$label_file = $this->getLabel($apiRequest);
				return $apiRequest['TrackCode'];
			}else{
				$message = $retInfo->RecreateAPACShippingPackageResult->NotificationList->Notification->Message;
				$time = $retInfo->RecreateAPACShippingPackageResult->Timestamp;
				$invocationID = $retInfo->RecreateAPACShippingPackageResult->InvocationID;
				
				$result['recreatemsg'] = '[time='.$time.']'.$message.'-[invocationId='.$invocationID.']';
			}
		}catch(Exception $e){
			$message = $retInfo->RecreateAPACShippingPackageResult->NotificationList->Notification->Message;
			$time = $retInfo->RecreateAPACShippingPackageResult->Timestamp;
			$invocationID = $retInfo->RecreateAPACShippingPackageResult->InvocationID;
			$result['recreatemsg'] = '[time='.$time.']'.$message.'[invocationId='.$invocationID.']';
		}
		return $result;
	}
	
}


?>