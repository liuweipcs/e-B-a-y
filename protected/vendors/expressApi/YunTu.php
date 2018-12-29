<?php
/*
 * 云途
 * */
class YunTu implements expressUtil{
	//设置api服务器和用户名&token
// 	private $url = 'http://t.tinydx.com:901/LMS.API/api/';
// 	private $num = 'C88888';
// 	private $secret = 'JCJaDQ68amA=';
	private $num = 'C08156';
	private $secret = 'B8WNqPrkTXs=';
	private $url = 'http://api.yunexpress.com/LMS.API/api/';
	private $actionUrl='';
	private $headers;
	//设置header认证
	public function __construct(){
		$this->headers = array(
				"Content-Type: application/json",
				"Authorization: basic ".base64_encode($this->num.'&'.$this->secret),
				"Accept-Language: zh-cn",
				"Accept: text/json",
		);
	}
	//获取相应的url
	protected function getActionUrl($action,$data=array()){
		$arr = array(
				'getShipType'=>'lms/Get'.(isset($data['countryCode'])?'?countryCode='.$data['countryCode']:''),
				'getTrackNumber'=>'WayBill/GetTrackNumber?orderId='.(isset($data['orderId'])?$data['orderId']:''),
				'getTracking'=>'WayBill/GetTrackingNumber?trackingNumber='.(isset($data['orderId'])?$data['orderId']:''),
				'getCountry'=>'lms/GetCountry',
				'getShipInfo'=>'WayBill/GetWayBill?wayBillNumber='.(isset($data['wayBillNumber'])?$data['wayBillNumber']:''),
		);
		return $this->url.$arr[$action];
	}
	//get方式访问api，$data需指定获取url时的参数
	protected function curlGet($action,$data=array()){
		$url = $this->getActionUrl($action,$data);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 60);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);
		$data  = curl_exec($ch);
		curl_close($ch);
		return $data;
	}
	//post方式访问api接口
	protected function curlPost($action,$array=array()){
		$url = $this->getActionUrl($action,$array);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 60);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $array);
		$data  = curl_exec($ch);
		curl_close($ch);
		return $data;
	}
	
	//获取城市列表
	public function getCity(){
		return $this->curlGet('getCountry');
	}
	//查询运送方式
	public function getShipType($countryCode=''){
		!empty($type) && $data['countryCode'] = $countryCode;
		return $this->curlGet('getShipType',$data);
	}
	//查询跟踪号
	public function getTrackNumber($oId){
		return $this->curlGet('getTrackNumber',array('orderId'=>$oId));
	}
	//查询运单信息
	public function getShipInfo($wayBillNumber){
		return $this->curlPost('getShipInfo',array('wayBillNumber'=>$wayBillNumber));
	}
	
	public function getTracking($oId){
		return $this->curlGet('getTracking',array('orderId'=>$oId));
	}
	public function checkResult($oId){
		$result = json_decode($this->getTracking($oId));
		if($result->ResultCode==0){
			$array = array();
			$trackInfo =array_reverse($result->Item->OrderTrackingDetails); 
			foreach ($trackInfo as $val){
				$key = str_replace('T', ' ', $val->ProcessDate);
				$array[$key] = $val->ProcessContent;
				if(!empty($val->ProcessLocation)){
					$array[$key].='/'.$val->ProcessLocation;
				}
			}
			return $array;
		}else{
			return false;
		}
	}
	
}