<?php
/*
 * 网页API访问
 * */
class YanWen implements expressUtil{
		private $url = 'http://track.yw56.com.cn/zh-CN/';
		
		public function checkResult($transId){
			$data  = array(
					'InputTrackNumbers'=>$transId,
			);
			$content = call_user_func(array('HttpUtil','curlPost'), $this->url,$data);
			//容错处理
			Yii::import('application.vendors.simple_html_dom.simple_html_dom');
			$html = new simple_html_dom();
			$html->load(content);
			$table = $html->find('#accordion> .panel>table');
			if(count($table)==0){
				return false;
			}
			$tds = $html->find('#accordion> .panel>table>td');
			$count = count($tds);
			if($count==0){
				return false;
			}
			$array = array();
			for ($i=0;$i<=$count;$i+=2){
				$array[$tds[$i]->plaintext] = $tds[$i+1]->plaintext;
			}
			return $array;
		}
		
}