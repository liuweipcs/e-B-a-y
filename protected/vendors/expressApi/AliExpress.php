<?php
class AliExpress implements expressUtil{
	private $url = 'http://global.cainiao.com/detail.htm?mailNoList=';
	
	public function checkResult($expressId){
		$url = $this->url.$expressId;
		$content = call_user_func(array('HttpUtil','curlPost'), $url);
		$path = '/<textarea.*?id=\"waybill_list_val_box\">(.*?)<\/textarea>/s';
		preg_match_all($path, $content,$matches);
		$obj = json_decode($matches[1][0]);
		$str = $matches[1][0];
		$str = str_replace('&quot;','"',$str);
		$obj = json_decode($str);
		$array = $obj->data[0]->section2->detailList;
		if(empty($array)) return false;
		$result = array();
		foreach ($array as $val){
			$result[$val->time] = $val->desc;
			!empty($val->status) && $result[$val->time] .= '/'.$val->status;
		}
		return $result;
	}
	
}