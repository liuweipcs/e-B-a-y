<?php
/**
 * 顺丰快递---配置类
 * @author gk
 * @since 2014/11/04
 */

abstract class SfService{
	const SANDBOX = false;//是否为沙盒环境
	
	protected $_url 	= '';//对接地址
	protected $_code 	= '';//接入编码
	protected $_authCode= '';//检验码
	protected $_action 	= '';//行为动作

	private $_actionUrl = '';//行为操作，跟在请求地址后面
	
	public function __construct($action){
		if(self::SANDBOX){//沙盒环境
			//$this->_url = 'http://bsp-test.sf-express.com:6080/bsp-ois/ws/expressService?wsdl';
			$this->_url = 'http://219.134.187.131:6080/bsp-oisp/ws/expressService?wsdl';
			$this->_code = 'szglkj';
			$this->_authCode = 'MoZYb2Lenno3N3TLfI[;';
		}else{//真实环境
			$this->_url 		= 'http://bsp-oisp.sf-express.com/bsp-oisp/ws/expressService?wsdl';
			$this->_code 		= '7556778556';
			$this->_authCode 	= '8n21fpgQIGTCuYbPaMdkfF8QZNLsJvNf';
		}
		$this->_action = $action;
	}

	/**
	 * Soap执行	
	 * @param array $data
	 * @return object
	 */
/* 	public function _call($data){
		$url = $this->_url;
		$soapClient = new SoapClient($url);
		$xml = $this->_buildRequestData($data);
		$result = $soapClient->sfexpressService(array(
			'arg0'=>$xml
		));
		$res = simplexml_load_string($result->return);
		return $res;
	} */
	
	public function _call($data){
		$xml = $this->_buildRequestData($data);
		//echo $xml;exit;
		$soapClient = new SoapClient($this->_url);
		$result = $soapClient->sfexpressService(array(
				'arg0'=>$xml
		));
		//var_dump($result); exit;
		$res = simplexml_load_string($result->return);
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
		$xml .= '<Head>'.$this->_code.','.$this->_authCode.'</Head>';
		$xml .= '<body>';
		$xml .= $this->_buildXml($data);
		$xml .= '</body>';
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
		}elseif( $this->_action=='OrderConfirmService' ){
			$xml = '<OrderConfirm ';
			foreach($data['Order'] as $key=>$item){
				$xml .= ' '.$key.'="'.$item.'"';
			}
			$xml .= '>';
			foreach($data['Option'] as $k=>$itm){
				$xml .= '<OrderConfirmOption ';
				$xml .= ' '.$k.'="'.$itm.'"';
				$xml .= "></OrderConfirmOption>\n";
			}
			$xml .= '</OrderConfirm>';
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