<?php
/**
 * 顺丰快递---配置类
 * @author wx
 * @since  2015/05/25
 */

abstract class SfEuService{
	const SANDBOX = false;//是否为沙盒环境
	
	protected $_url 	= '';//对接地址
	protected $_code 	= '';//接入编码
	protected $_authCode= '';//检验码
	protected $_action 	= '';//行为动作

	private $_actionUrl = '';//行为操作，跟在请求地址后面
	
	public function __construct($action){
		if(self::SANDBOX){
			$this->_url = 'http://120.24.60.8:8003/CBTA/ws/sfexpressService?wsdl';
			$this->_code = '7550040115';
			$this->_authCode = 'B97743B14F9E869E0F903295BBEB2795';
		}else{//真实环境
			$this->_url 		= 'http://www.sfb2c.com:8003/CBTA/ws/sfexpressService?wsdl';//2号交互地址
			$this->_code 		= '7550056484';
			$this->_authCode 	= 'D4C938BAFDCA418EAF3435E31E8155EE';
		}
		$this->_action = $action;
	}

	/**
	 * Soap执行	
	 * @param array $data
	 * @return object
	 */
	public function _call($data){
		$xml = $this->_buildRequestData($data);
		//echo $xml;exit;
		$dxMd = strtoupper(md5($xml.$this->_authCode));
		$verifyCode = base64_encode($dxMd);
		
		$soapClient = new SoapClient($this->_url);
		$result = $soapClient->sfexpressService($xml,$verifyCode);
		$res = simplexml_load_string($result);
		return $res;
	}
	

	/**
	 * Curl执行
	 * @param array $data
	 * @param tinyInt $isPost
	 * @return object
	 */
	public function _excute($data=array(), $isPost = false){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$this->_url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 60);
		if( $isPost ){
			$data = $this->_buildRequestData($data);
			curl_setopt($ch, CURLOPT_POST, 1);	
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);	
		}
		
		$result = curl_exec($ch);	
		curl_close($ch);
		$result = simplexml_load_string($result);
		return $result;
	}

	/**
	 * 创建数据源
	 * @param array $data
	 * @return xml
	 */
	public function _buildRequestData($data){
		$xml = '<Request service="'.$this->_action.'" lang="zh-CN">';
		//$xml .= '<Head>'.$this->_code.','.$this->_authCode.'</Head>';
		$xml .= '<Head>'.$this->_code.'</Head>';
		$xml .= '<Body>';
		$xml .= $this->_buildXml($data);
		$xml .= '</Body>';
		$xml .= '</Request>';
		return $xml;
	}
	
	/**
	 * 创建提交的url
	 * @return string
	 */
	protected function _buildUrl(){
		if( $this->_action ){
			$urlArr = array(
				'OrderService' => $this->_url,//客户下单
				'OrderConfirm' => $this->_url,//确认订单
			);	
		}
		
		return $urlArr[$this->_action];
	}

	/**
	 * 将数组转化为xml
	 * @param array $data
	 * @return xml
	 */
	public function _buildXml($data){
		//var_dump($data);exit;
		if( $this->_action=='OrderService' ){
			//暂时只做下单的xml
			$xml = '<Order';
			foreach($data['Order'] as $key=>$order){
				$xml .= ' '.$key.'="'.$order.'"';
			}
			$xml .= '>';
			
			foreach($data['Cargo'] as $item){
				$xml .= '<Cargo';
				foreach($item as $k=>$itm){
					$xml .= ' '.$k.'="'.$itm.'"';
				}
				$xml .= "></Cargo>\n";
			}
			$xml .= '</Order>';
		}else if( $this->_action=='OrderConfirmService' ){
			$xml = '<OrderConfirm';
			foreach($data as $key=>$item){
				$xml .= ' '.$key.'="'.$item.'"';
			}
			$xml .= '>';
			$xml .= '</OrderConfirm>';
		}else if( $this->_action=='OrderSearchService' ){
			$xml = '<OrderSearch';
			foreach($data as $key=>$item){
				$xml .= ' '.$key.'="'.$item.'"';
			}
			$xml .= '>';
			$xml .= '</OrderSearch>';
		}
		return $xml;
	}
	/**
	 * 获取上传元素
	 * @return xml
	 */
	public function _getActionColumn(){
		$array = array(
				'OrderService' => array(
						'Order_0' => array(
								'Cargo_1',
						),
				),
		);
		return $array[$this->_action];
	}
}