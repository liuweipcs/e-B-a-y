<?php
/*
 *API接口
 * 出口易：实现了查询的方法
 * */
class ChuKouYi implements expressUtil{
	private $url = 'http://api.chukou1.cn/v3/';
	private $token = '99D460E62459700957D180D67D8BA5FE';
	private $userKey = '87zct6m54i';
	private $userId = 'ebayst_2011@163.com';
	
	private function getAction($name){
		$array = array(
				'Tracking'=>'system/tracking/get-tracking?'
		);
		return isset($array[$name])?$array[$name]:false;
	}
	
	private function getUrl($name,$params = array()){
		if($action = $this->getAction($name)){
			$url = $this->url.$action.'token='.$this->token.'&user_key='.$this->userKey.'&user_id='.$this->userId;
			if(!empty($params)){
				foreach ($params as $key=>$val){
					$url .='&'.$key.'='.$val;
				}
			}
			return $url;
		}
		return false;
	}
	
	public function checkResult($transId){
		$url = $this->getUrl('Tracking',array('Track_no'=>$transId));
		$data = call_user_func(array('HttpUtil','curlGet'),$url);
		$data = json_decode($data);
		if($data->meta->code==200){
				$array = array();
				foreach ($data->body->details as $val){
					$key = str_replace('/','-', $val->date).' '.$val->time;
					$array[$key] = $val->description.'/'.$val->location;	
				}
				return $array;
		}
		return false;
	}
	
	
}