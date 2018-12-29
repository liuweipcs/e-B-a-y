<?php
class OneWorld implements expressUtil{
// 	private $url = 'https://oneworldexpress.co.uk/mulit_tracking/demo/results.php';
	private $url = 'https://oneworldexpress.co.uk/mulit_tracking/demo/api/tracking.php';
	public function checkResult($tranId){
		$data = array(
				'func'=>'api',
				'number'=>$tranId,
				'count'=>1,
		);
		$content = call_user_func(array('HttpUtil','curlPost'),$this->url,$data);
		$content = json_decode($content);
		if(!empty($content->History)){
			$array = array();
			$dataTime = array_reverse($content->History->Date_Time);
			$trackPoint = array_reverse($content->History->Track_Point);
			$eventContent = array_reverse($content->History->Event_Content);
			$other = array_reverse($content->History->Other);
			foreach($dataTime as $key=>$val){
				$array[$val] = $trackPoint[$key].'/'.$eventContent[$key].'/'.$other[$key];
			}
			return $array;
		}
		return '';
	}
}