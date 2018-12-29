<?php
class EyouBao implements expressUtil{
	private $url = 'http://www.ckd.cn/query.php';
	public function checkResult($data){
		$content = call_user_func(array('HttpUtil','curlPost'),$this->url,array(
				'corp_code'=>'ems',
				'waybill'=>$data
		));
		$obj = json_decode($content);
		if($obj->errno==0){
			$result = array();
			if(!empty($obj->data)){
				foreach ($obj->data as $val){
					$result[$val->time] = $val->note;
				}
			}
			return array_reverse($result);
		}
		return false;
	}
}