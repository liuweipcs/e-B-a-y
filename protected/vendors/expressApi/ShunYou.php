<?php
/*
 * 顺友：网页api访问
 * */
class ShunYou implements expressUtil{
	private $url = 'http://www.sypost.net/query';
	
	public function checkResult($transId){
		$data  = array(
				'connotNo'=>$transId,
		);
		$content = call_user_func(array('HttpUtil','curlPost'), $this->url,$data);
		$content = json_decode($content);
		$message = $content->data[0]->result->origin->items;
		if(!empty($message)){
			$array = array();
			$message = array_reverse($message);
			foreach ($message as $val){
				$key = date('Y-m-d H:i:s',$val->createTime/1000);
				$array[$key]=$val->content.'/'.$val->office;
			}
			return $array;
		}
		return false;
	}
	
}