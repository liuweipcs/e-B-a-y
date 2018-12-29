<?php
/**
 * 云途API接口
 * @author gk
 * @since 2014/11/09
 */
require_once Yii::app()->basePath.'/extensions/xlogis/yuntu/config.php';
class YtServiceAction extends YtService{
	private $_error = '';

	public function __construct(){
		parent::__construct();
	}
	
	/**
	 * 新建快件信息
	 * @param string $packageId
	 * @param tinyInt $expressType
	 * @return object
	 */
	public function createPackage($packageData){
		$data = json_encode(array($packageData));
		$result = parent::curl_post($data, 'create');//执行上传
		$resultObj = json_decode($result);
		if( $resultObj->ResultCode=='0000' ){//成功
			$obj = $resultObj->Item['0'];
			$tracknum = $obj->OrderId;
			return 'success-%%'.$tracknum;
		}else{//成功
			$obj = $resultObj->Item['0'];
			if( $obj->Feedback == '订单号已存在' ){//已上传过但没拿到tracknum的包裹重新获取
				$check = $this->getTrackNum($packageData['OrderNumber']);
				$object = json_decode($check);
				if($object->ResultCode=='0000'){
					$trackInfo = $object->Item['0'];
					if($trackInfo->TrackingNumber){
						return 'success-%%'.$trackInfo->TrackingNumber;
					}else{
						return 'error-%%获取失败';
					}
				}
			}else{ 
				return 'error-%%'.$obj->Feedback;
			}
		}
		return false;			
	}
	
	/**
	 * 获取tracknum
	 * @param string $packageId
	 * @return
	 */
	public function getTrackNum($packageId){
		$check = parent::curl_get('GetTrackNumber',array('orderId'=>$packageId));
		return $check;
	}
	
	public function getShipInfo($country){
		$result = parent::curl_get('getShipInfo',array('countryCode'=>$country));
		return $result;
	}
	
	/**
	 * 获取错误信息
	 * @return string
	 */
	public function getErrorMsg(){
		return $this->_error;
	}
}