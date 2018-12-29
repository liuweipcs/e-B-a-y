<?php
/**
 * 云途---配置类
 * @author gk
 * @since 2014/11/09
 */

abstract class YtService{
	const SANDBOX = false;//是否为沙盒环境
	
	protected $_url 	= '';//对接地址
	protected $_num 	= '';//客户编号
	protected $_secret= '';//ApiSecret
	protected $_action 	= '';//行为动作

	private $_actionUrl = '';//行为操作，跟在请求地址后面
	
	public function __construct(){
		if(self::SANDBOX){//沙盒环境
			$this->_url = 'http://test.tinydx.com:901/LMS.API/api';
			$this->_num = 'C22221';
			$this->_secret = 'tLa5QkFoIy0=';
		}else{//真实环境
			$this->_url = 'http://api.yunexpress.com/LMS.API/api';
			$this->_num = 'C91513';
			$this->_secret = 'UKb2UuyTLpQ=';
		}
	}

	/**
	 * curl post操作
	 * @param post数据  $post_data
	 * @param 进行的操作 $action
	 * @return object
	 */
	protected function curl_post($post_data,$action){
		$headers = $this->get_curl_header();
		$action_url = $this->get_action_url($action);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$this->_url.$action_url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 60);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
	
		$data = curl_exec($ch);
		curl_close($ch);
		return $data;
	}
	/**
	 * curl get 操作
	 * @param 进行的操作 $action
	 * @return object
	 */
	protected function curl_get($action,$get_data=array()){
		$headers = $this->get_curl_header();
		$action_url = $this->get_action_url($action,$get_data);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$this->_url.$action_url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 60);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

		$data = curl_exec($ch);
		curl_close($ch);
		return $data;
	}
	
	protected function get_curl_header(){
		$header = array(
				"Content-Type: application/json",
				"Authorization: basic ".base64_encode($this->_num.'&'.$this->_secret),
				"Accept-Language: zh-cn",
				"Accept: text/json",
		);
		return $header;
	}
	
	protected function get_action_url($action,$data_arr=array()){
		$arr = array(
			'create' 		=> '/WayBill/BatchAdd',
			'getShipInfo' 	=> '/lms/Get'.'?countryCode='.$data_arr['countryCode'],
			'GetTrackNumber'=> '/WayBill/GetTrackNumber?orderId='.$data_arr['orderId'],
		);	
		return $arr[$action];
	}

}