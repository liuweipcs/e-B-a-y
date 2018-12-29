<?php
/**
 * 深圳邮政---配置类
 * @author Michael
 * @since 2015/08/29
 */

abstract class SYouService{
	const SANDBOX = true;//是否为沙盒环境
	protected $_url 	= '';//对接地址
	protected $_code 	= '';//接入编码
	protected $_authCode= '';//检验码
	protected $_action 	= '';//行为动作
	private $_actionUrl = '';//行为操作，跟在请求地址后面
	public function __construct($action){
		if(self::SANDBOX){//沙盒环境
			//$this->_url = 'http://test01.routdata.com/selfsys/services/mailSearch';
			$this->_url = 'http://xb.shenzhenpost.com.cn:7003/xbzz/services/mailSearch';
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
	 * @param tinyInt $isPost
	 * @return object
	 */
	public function _excute($data=array(), $isPost = false){
		try{
			$soap = new SoapClient(null,array('location'=>'http://xb.shenzhenpost.com.cn:7003/xbzz/services/mailSearch','uri'=>'http://xb.shenzhenpost.com.cn:7003/'));
			 //$soap = new SoapClient(null,array('location'=>'http://test01.routdata.com/selfsys/services/mailSearch','uri'=>'http://test01.routdata.com/'));
			$action = $this->_action;
			$result = $soap->$action($data);
		}catch(Exception $e){
			$e->getMessage();
			exit;
		}
		return $result;
	}
	
	/**
	 * Curl执行
	 * @param array $data
	 * @param int $isPost
	 * @return object
	 */
	public function execute($data = array(), $isPost = false)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$this->_url."/".$this->_action);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 60);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		$result = curl_exec($ch);
		curl_close($ch);
		return $result;
	}
}