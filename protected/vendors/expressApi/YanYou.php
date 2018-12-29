<?php
class YanYou{
		private $url = 'http://track.yw56.com.cn/zh-CN/';
		
		public function getContent($oId){
			//容错处理
			Yii::import('application.vendors.simple_html_dom.simple_html_dom');
			$html = new simple_html_dom();
			$html->load($this->spider($oId));
			$table = $html->find('#accordion> .panel>table');
			if(count($table)==0){
				return false;
			}
			$tds = $html->find('#accordion> .panel>table>td');
			$text = '';
			$i=0;
			
			foreach ($tds as $val){
				$text .=$val->plaintext.'&nbsp;&nbsp;&nbsp;&nbsp;';
				$text .= (($i++)%2==1)?'<br/>':'';
			}
			return $text;
		}
			
		private function spider($oId){
			$array = array(
				'InputTrackNumbers'=>$oId
			);
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $this->url);
			curl_setopt($ch, CURLOPT_REFERER, $this->url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_TIMEOUT, 100);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $array);
			$data = curl_exec($ch);
			curl_close($ch);
			return $data;
		}
}