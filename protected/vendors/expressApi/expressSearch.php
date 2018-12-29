<?php
class expressSearch{
		/*
		 * 策略：1k条track，4k条其他API
		 * 邮政公司名称=>邮寄地址
		 * 
		 * */
		public static $expressCode = array(
				//递四方 
				'新加坡EMS'=>'FourPx',
				'中国EMS国际'=>'FourPx',
				'DHL出口'=>'FourPx',
				'4PX专线优选'=>'FourPx',
				'香港联邦IE'=>'FourPx',
				'欧洲小包特惠'=>'FourPx',
				'4PX-H小包平邮+'=>'FourPx',
				'4PX-H小包挂号'=>'FourPx',
				'4PX-S小包平邮+'=>'FourPx',
				'4PX-S小包挂号'=>'FourPx',
				'泛欧平邮'=>'FourPx',
				'4PX微包服务'=>'FourPx',
				'4PX新邮经济小包(深圳)'=>'FourPx',
				//云途物流
				'欧洲专线DDP挂号'=>'YunTu',
				'英国专线平邮'=>'YunTu',
				'英国专线签名'=>'YunTu',
				'云途中欧专线平邮'=>'YunTu',
				'中邮平邮福州'=>'YunTu',
				'云途快速小包平邮'=>'YunTu',
				'云途中欧专线平邮'=>'YunTu',
				//wish邮 	
				'Wish邮挂号'=>'WishExpress',
				'Wish邮平邮'=>'WishExpress',
				'Wish邮平邮深圳仓'=>'WishExpress',
				'Wish邮平邮广州仓'=>'WishExpress',
				'Wish邮挂号深圳仓'=>'WishExpress',
				'美国渠道DLE'=>'WishExpress',
				'wish邮广州仓挂号'=>'WishExpress',
				'wish邮广州仓平邮'=>'WishExpress',
				'深圳仓wish邮平邮'=>'WishExpress',
				'深圳仓wish邮挂号'=>'WishExpress',
				'DLE'=>'WishExpress',	
				//出口易货代
				'本地中邮平邮'=>'ChuKouYi',
				'本地中邮挂号'=>'ChuKouYi',
				'CUE出口易专线'=>'ChuKouYi',
				'英国快线'=>'ChuKouYi',
				'E邮宝'=>'ChuKouYi',
				'线下E邮宝'=>'ChuKouYi',
				'Wish邮平邮广州仓'=>'ChuKouYi',
				//顺友
				'K邮宝'=>'ShunYou',
				'顺邮宝平邮'=>'ShunYou',
				'顺邮宝挂号'=>'ShunYou',
				'顺友航空经济小包(深圳)'=>'ShunYou',
				//万欧国际
				"RM欧洲专线（不带签收）"=>'OneWorld',
				"英国经济专线"=>'OneWorld',
				"英国经济专线RM"=>'OneWorld',
				//燕文物流
				'土耳其邮政平邮小包(含电)'=>'YanWen',
				//EMS物流
				'中国邮政平常小包+（深圳中邮仓）'=>'Ems',
				'中国邮政挂号小包(深圳)'=>'Ems',
				'中国邮政平常小包+(佛山中邮仓)'=>'Ems',
				//E邮宝
				'美国E邮宝'=>'EyouBao',
				'澳洲E邮宝'=>'EyouBao',
				'加拿大e邮宝'=>'EyouBao',
				'澳大利亚E邮宝'=>'EyouBao',
				'英国E邮宝'=>'EyouBao',
				//万邑邮
				'万邑邮选-香港渠道（平邮）-ebay易递'=>'Winit',
				'万邑邮选-香港渠道（平邮）-ebay易递宝'=>'Winit',
				'万邑邮选-马来西亚渠道（平邮）-ebay易递宝'=>'Winit',
				'万邑邮选-DHL eCommerce (香港)-ebay易递宝'=>'Winit',
				'万邑邮选-荷兰渠道（平邮）-ebay易递宝-含电'=>'Winit',
				'万邑邮选-DHL eCommerce ( 香港）-ebay易递宝'=>'Winit',
				'万邑邮选-香港渠道（平邮）-ebay易递宝|深圳ISP仓'=>'Winit',
				//速卖通线上  
				'中国邮政平常小包+（深圳中邮仓）'=>'AliExpress',
				'4PX新邮经济小包(深圳)'=>'AliExpress',
				'顺友航空经济小包(深圳)'=>'AliExpress',
				'中国邮政挂号小包(深圳)'=>'AliExpress',
				'俄邮通1'=>'AliExpress', 
				'新小包挂号'=>'WenHui',
		);
		
		public static function loadExpressCode($companyName){
			return isset(self::$expressCode[$companyName])?self::$expressCode[$companyName]:'';
		}
		//包含并返回一个快递方式
		public static function loadExpress($name){
			static $expressCompany = array();
			if(isset($expressCompany[$name]) && is_null($expressCompany[$name])){
				throw new Exception("运输方式不存在");
			}
			if(isset($expressCompany[$name])) return $expressCompany[$name];
			$file = dirname(__FILE__).DIRECTORY_SEPARATOR.$name.'.php';
			if(file_exists($file)){
				require $file;
				$expressCompany[$name] = new $name;
				return $expressCompany[$name];
			}else{
				$expressCompany[$name] = null;
				throw new Exception("可查询运输方式不存在");
			}
		}
		
		public static function getTracking($companyName,$transId){
				try {
					$company = self::loadExpressCode($companyName);
					$express = self::loadExpress($company);
					if($express instanceof expressUtil){
						$result =  $express->checkResult($transId);
						return self::manageExpress($result);
					}else{
						throw new Exception("调用对象需实现接口");
					}
				} catch (Exception $e) {
						return false;
				}
		}
		
		protected static function manageExpress($result){
				if(!empty($result)){
					$array =  array();
					$key = key($result);
					$array['status'] = $key.'	'.$result[$key];
					$array['info'] = '';
					foreach ($result as $key=>$val){
						$array['info'].=$key.'	'.$val.'<br/>';
					}
					return $array;
				}
				return false;
		}
}

interface expressUtil{
	function checkResult($data);
}

class HttpUtil{
	public static function curlPost($url,$array=array()){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 200);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $array);
		$data = curl_exec($ch);
		curl_close($ch);
		return $data;
	}
	
	public static function curlGet($url){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_REFERER, $url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 100);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		$data = curl_exec($ch);
		curl_close($ch);
		return $data;
	}
}