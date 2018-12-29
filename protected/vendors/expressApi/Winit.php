<?php
class Winit implements expressUtil{
	
	private $hasCode = '259fd3cad23f493c7ae59c6ac80aae45_08b64506f122d4aed20788ac7328fd1e';
	private $url = 'http://track.winit.com.cn/tracking/Index/result';
	
// 	trackingNo:ID18010257395740CN
// 	trackingNoString:ID18010257395740CN
// 	__hash__:259fd3cad23f493c7ae59c6ac80aae45_74dea5639b9b73cc1b117c16b390912b
	public function checkResult($data){
		$content = call_user_func(array('HttpUtil','curlPost'),$this->url,array(
				'trackingNo'=>$data,
				'trackingNoString'=>$data,
				'__hash__'=>$this->hasCode
		));
		Yii::import('application.vendors.simple_html_dom.simple_html_dom');
		$html = new simple_html_dom();
		$html->load($content);
		$hasCode = $html->find('input[name=__hash__]');
		$this->hasCode = $hasCode[0]->attr['value'];
		$string = $html->find('#'.$data);
		if(empty($string)){
			return false;
		}
		$string = $string[0]->attr['data-id'];
		$html->load($string);
		$spans = $html->find("span");
		if(empty($spans)) return false;
		$result = array();
		$count = count($spans);
		for ($i=0;$i<$count;$i+=3){
			$result[$spans[$i]->plaintext] = $spans[$i+1]->plaintext.'/'.$spans[$i+2]->plaintext;
		}
		return $result;
	}
}