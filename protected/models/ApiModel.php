<?php
/**
 * @package Api.models
 * @author Gordon
 * @since 2015-01-05
 */
class ApiModel extends UebModel {
	
	public $client = '';//请求端
	public $key = '';//请求验证key
	public $method = '';//请求接口
	public $_error = 0;
	public $_requestResult = 0;
	
	public $_attribute = array();
	
	const WMS = 'wms';
	const OLDERP = 'olderp';
	const MARKET = 'erp_market';
	
	const NOT_SHIP	=0;//不可以发货
	const CAN_SHIP	=1;//可以发货
	const NO_STATUS =0;//出库回传:失败
	const CAN_STATUS=1;//出库回传:成功
	
	/**
	 * 初始化验证信息
	 */
	public function initApiParam($attribute){
		$this->_attribute = $attribute;
		$this->clientConfig();
		$this->keyConfig();
		if( isset($this->_attribute->method) ){
			$this->method = $this->_attribute->method;
		}
	}
	
	public function run(){
		$callResult = $this->_call();
		if( isset($callResult['errorCode']) ){
			if($callResult['errorCode']==0){
				$this->_requestResult = 1;
			}else{
				$this->_requestResult = 0;
				$this->_error = $callResult['errorCode'];
			}
		}else{
			$this->_requestResult = 1;
		}
		return $this->_buildReturnData($callResult);
	}
	
	/**
	 * 运行查询逻辑
	 */
	private function _call(){
		$model = null;
		switch ($this->client){
			case self::WMS:				
				$model = new Wms();
				break;
			case self::OLDERP:
				$model = new Olderp();
				break;
			case self::MARKET:
				$model = new Erpmarket();
				break;
			default:
				
		}
		$model->initParam($this->method);
		$result = $model->_call($this->_attribute);
		return $result;
	}
	
	/**
	 * 请求客户端配置
	 * @return boolean
	 */
	public function clientConfig(){
		$config = array(self::WMS, self::OLDERP, self::MARKET);
		if( isset($this->_attribute->client) ){
			if( in_array($this->_attribute->client, $config) ){				
				$this->client = $this->_attribute->client;
				return true;
			}
		}
		return false;
	}
	
	/**
	 * key值配置
	 * @return boolean
	 */
	public function keyConfig(){
		$config = array(
			self::WMS 		=> 'vakind_wmsClient2015',
			self::OLDERP 	=> 'vakind_olderpClient2015',
			self::MARKET 	=> 'vakind_marketClient2015',
		);
		if( isset($this->_attribute->key) ){
			if( $this->_attribute->key==$config[$this->client] ){
				$this->key = $this->_attribute->key;
				return true;
			}
		}
		return false;
	}
	
	/**
	 * 记录交互日志
	 */
	public function connectLog(){
		//TODO
	}
	
	/**
	 * 错误信息配置
	 */
	public function errorCodeConfig(){
		$config = array(
				1 	=> '请求异常',
				2 	=> '请求验证失败',
				3 	=> '请求方法不存在',
				4 	=> '传入包裹号、重量或物流公司不存在',
				5 	=> '包裹订单利润亏损',
				6 	=> '出库订单回传传入ERP包裹号、WMS仓库ID、货主、WMS订单编号、快递单号或重量不存在',
				7 	=> 'ERP更新出库信息失败',
				8 	=> '入库单取消(ERP->WMS)传入仓库、采购单号、订单号、货主不存在',
				9 	=> '运费计算结果为空',
				10 	=> '重量必须传递', 
		);
		return $config[$this->_error];
	}
	
	/**
	 * 组建返回信息
	 */
	public function _buildReturnData($data = array()){
		$returnArr = array(
				'Ack' 		=> $this->_requestResult==0 ? 'FAILURE' : 'SUCCESS',
				'CallName'	=> $this->method,
				'Time'		=> date('Y-m-d H:i:s'),
		);
		if( $this->_requestResult==0 ){
			$returnArr['ErrorMsg'] = $this->errorCodeConfig();
		}
		if( !empty($data) ){
			$returnArr['Data'] = $data;
		}	
		return $returnArr;
	}
	
	/**
	 * 验证交互
	 */
	public function authenticate(){
		if( $this->client && $this->key && $this->method ){
			return true;
		}else{
			if( !$this->client || !$this->key ){
				$this->_error = 2;//失败代码
				$this->_requestResult = 0;//请求失败
			}else{
				$this->_error = 3;//失败代码
				$this->_requestResult = 0;//请求失败
			}
		}
	}
	
	public function getDbKey() {
		return 'db_order';
	}
	
	public function tableName() {
		return 'ueb_order';
	}
	
}