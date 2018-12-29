<?php
class Ems implements expressUtil{
	private $url ='http://intmail.183.com.cn/zdxt/gjyjqcgzcx/gjyjqcgzcx_gjyjqcgzcxLzxxQueryPage.action';
	public function checkResult($data){
		$xml = call_user_func(array("HttpUtil","curlPost"),$this->url,array(
				'submitType'=>1,
				'ajax'=>$this->getXmlData($data)
		));
		$obj = simplexml_load_string($xml);
		if((int)$obj->data->page->size>0){
			$result = array();
			foreach ($obj->data->rdata as $val){
				$result[(string)$val->D_SJSJ] = (string)$val->V_ZT;
			}
			return array_reverse($result);
		}
		return false;
	}
	
	public function getXmlData($data){
		return '<?xml version="1.0" encoding="utf-8"?><root><params><param><key>vYjhm</key><value>'.$data.'</value></param><param><key>FROM_FLAG</key><value>0</value></param><param><key>gngjFlag</key><value>1</value></param><param><key>ntdbz</key><value>0</value></param><param><key>validres</key><value>success</value></param></params><data></data></root>';
	}
}