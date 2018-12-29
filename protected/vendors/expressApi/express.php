<?php
require dirname(__FILE__).DIRECTORY_SEPARATOR.'track.class.php';
class express{
	
		private $track;
		
		function __construct(){
			$this->track = new Trackingmore();
		}
		
// 		public function getCompanyCode($companyName){
// 			$company = array(
// 					'4px'=>'递四方',
// 					'sfb2c'=>'顺丰国际',
// 					'yanwen'=>'燕文',
// 					'yunexpress'=>'云途物流',
// 			);
// 			return array_search($companyName,$company);
// 		}

		public function getCompanyCode($companyName){
				$company = array(
						'香港小包平邮'=>'4px',
						'4PX微包服务'=>'4px',
						'泛欧平邮'=>'4px',
						'4PX-H小包平邮+'=>'4px',
						'4PX-H小包挂号'=>'4px',
						'4PX-S小包平邮+'=>'4px',
						'4PX-S小包挂号'=>'4px',
						'欧洲小包特惠'=>'4px',
						'DHL出口'=>'4px',
						'4PX专线优选'=>'4px',
						'中国EMS国际'=>'china-ems',
						'新加坡EMS'=>'4px',
						'4PX专线ARMX'=>'4px',
						'4PX标准'=>'4px',
						'香港联邦IE'=>'4px',
						'香港邮政EMS'=>'4px',
						'中国邮政平常小包+（深圳中邮仓）'=>'china-post',
						'4PX新邮经济小包(深圳)'=>'4px',
						'中国邮政挂号小包(深圳)'=>'china-post',
						'DLE'=>'wishpost',
						'Wish邮平邮'=>'wishpost',
						'Wish邮挂号'=>'wishpost',
						'Wish邮平邮广州仓'=>'wishpost',
						'Wish邮挂号广州仓'=>'wishpost',
						'Wish邮挂号深圳仓'=>'wishpost',
						'Wish邮平邮深圳仓'=>'wishpost',
						'顺丰国际小包平邮(无跟踪号)(不带电)'=>'sfb2c',
						'顺丰国际小包挂号'=>'sfb2c',
						'顺丰国际电商专递'=>'sfb2c',
						'中邮平邮福州'=>'yunexpress',
						'云途快速小包平邮'=>'yunexpress',
						'云途中欧专线平邮'=>'yunexpress',
						'英国专线平邮'=>'yunexpress',
						'中邮挂号福州'=>'yunexpress',
						'欧洲专线DDP挂号'=>'yunexpress',
						'英国专线签名'=>'yunexpress',
						'本地中邮平邮'=>'china-post',
						'本地中邮挂号'=>'china-post',
						'线下E邮宝'=>'china-post',
						'E邮宝'=>'china-post',
						'英国快线'=>'china-post',
						'CUE-出口易专线'=>'china-post',
						'美国E邮宝'=>'china-post',
						'澳洲E邮宝'=>'china-post',
						'加拿大e邮宝'=>'china-post',
						'E特快'=>'china-post',
						'澳大利亚E邮宝'=>'china-post',
						'英国E邮宝'=>'china-post',
						'可追踪小包'=>'dhlglobalmail',
						'普通小包'=>'dhlglobalmail',
						'土耳其邮政平邮小包(含电)'=>'yanwen',
						'申通'=>'sto',
						'顺丰'=>'sf-express',
				);
				return isset($company[$companyName])?$company[$companyName]:'';
		}
		
		public function getErrorMsg($errorCode){
			$code = array(
					'pending' => '新增包裹正在查询中，请等待！',	
					'notfound' => '	包裹目前查询不到。',
					'transit' => '包裹正从发件国运往目的国。',
					'pickup' => '包裹正在派送中或已到达当地收发点，你可以继续派送或收件。',
					'delivered' => '	包裹已成功妥投。',
					'undelivered' => '快递员尝试过投递但失败，（这种情况下）通常会留有通知并且会再次试投！',
					'exception	' => '包裹出现异常，发生这种情况可能是：包裹已退回寄件人，清关失败，包裹已丢失或损坏等。',
					'expired	' => '包裹很长一段时间显示在运输途中，一直没有派送结果。',
			);
			return isset($code[$errorCode])?$code[$errorCode]:'';
		}
		//添加订单到物流平台
		public function addExpress($companyCode,$expressId,$orderId,$title,$customerName){
				$result =  $this->track->createTracking($companyCode,$expressId,array(
						'title'=>$title,
						'customer_name'=>$customerName,
						'order_id'=>$orderId
				));
				return $result['meta']['code']==200;
		}
		/*
		 * 批量添加
		 * $items = array(
				array(
						'tracking_number' => 'RM111516216CN',
						'carrier_code'    => 'china-post',
						'title'          => 'iphone6s',
						'customer_name'   => 'clooney chen',
						'customer_email'  => 'clooneychen@gmail.com',
						'order_id'      => '898874587'
				),
		);*/
		public function addExpressMulit($array){
				$result =  $this->track->createMultipleTracking($array);
				return $result['meta']['code']==200; 
		}
		//获取单个tracking
		public function getSingleTracking($companyCode,$expressId){
				$result =  $this->track->getSingleTrackingResult($companyCode, $expressId);
				if($result['meta']['code']==200){
					$list = $result['data']['origin_info']['trackinfo'];
					$text  = '';
					foreach ($list as $val){
						$text .= $val['Date'].'&nbsp;&nbsp;&nbsp;&nbsp;'.$val['StatusDescription'];
						if(!empty($val['Details'])){
							$text .='/'.$val['Details'];
						}
						$text .='<br/>';
					}
					return array(
						'code'=>$result['data']['status'],
						'msg'=>$text,
					);
				}
				return false;
		}
		
}