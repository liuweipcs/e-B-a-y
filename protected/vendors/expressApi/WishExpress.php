<?php
/*
 * 网页APi访问
 * */
class WishExpress implements expressUtil{
	private $url = 'http://www.shpostwish.com/api/tracking/search';
	
	//根据wish网站的快递接口查询相关内容
	//后续可能存在接口被封禁的可能  2016-10-18
	public function checkResult($transId){
			$data  = array(
					'ids[]'=>$transId,
			);
			$content = call_user_func(array('HttpUtil','curlPost'), $this->url,$data);
			$obj = json_decode($content);
			if($obj->code==0){
				$array = array();
				$objs = array_reverse($obj->data->$transId->checkpoints);
				foreach ($objs as $val){
						$array[$val->date] = $val->status_desc;
				}
				return $array;
			}else{
				return false;	
			}
	}
}