<?php
/**
 * 操作原因配置
 */
class reasonList{
	
	const REASON_TYPE = 'refund';
	const RESEND_TYPE = 'resend';
	
	public $_type = '';
	
 	public function __construct($type){
 		$this->_type = $type;
 	}
	
	public function getResonList(){
		$list = $this->reasonConfig();	
		if( isset($list[$this->_type]) ){
			return $list[$this->_type];
		}else{
			return array();
		}
	}
	//退款原因
	private function reasonConfig(){
		return array(
			self::REASON_TYPE => array(
					'a'	=> 'a:未收到物品',
					'b' => 'b:物品本身质量问题',
					'c' => 'c:物品与描述不符（如广告有问题或寄的根本不是同一产品）',
					'd' => 'd:颜色错误',
					'e' => 'e:没有客户所需的颜色，客户要求退款',
					'f' => 'f:少寄物品',
					'g' => 'g:错寄物品',
					'h' => 'h:物流慢',
					'i' => 'i:迟发货',
					'g' => 'j:产品缺货',
					'k' => 'k:产品下线',
					'l' => 'l:在运送途中出现的损毁问题',
					'm' => 'm:其它',
					'n' => 'n:订单取消',
					'o' => 'o:多付、折扣',
					'p' => 'p:退回来的包裹',
					'q' => 'q:价格错误',
					'r' => 'r:帐号受限',
			),
			self::RESEND_TYPE => array(
					'a' =>'a: 未收到物品',
					'b' =>'b: 物品本身质量问题',
					'c' =>'c: 少寄物品',
					'd' =>'d: 颜色错误',
					'f' =>'f: 错寄物品',
					'g' =>'g: 在运送途中出现的损毁问题',
					'h' =>'h: 退回来的包裹',
					'i' =>'i: 物流公司丢包',
					'j' =>'j: 其它',
			),
			
		);
	}
}