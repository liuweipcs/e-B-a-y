<?php
/**
 * @package Ueb.modules.api.models
 * @author Gordon
 * @author 2015-05-07
 */
Yii::import('application.modules.logistics.models.*');
Yii::import('application.modules.orders.models.*');
Yii::import('application.modules.orders.components.*');
Yii::import('application.modules.systems.models.*');
Yii::import('application.modules.systems.components.*');
Yii::import('application.modules.users.models.*');
Yii::import('application.modules.users.components.*');
Yii::import('application.modules.products.models.*');
Yii::import('application.modules.purchases.models.*');
Yii::import('application.modules.warehouses.models.*');
Yii::import('application.modules.warehouses.components.*');
class Olderp extends UebModel { 		
	
	public $_method = '';//请求接口
	public $_error  = 0;
	public function initParam($method){
		$this->_method = $method;
	}
	
	public function _call($data=array()){
		$result = call_user_func_array(array('self', $this->_method),array($data));
		return array(
				'errorCode' => $this->_error,
				'data'		=> $result,
		);
	}
	
	/**
	 * @desc 老系统请求计算OMS运费
	 * @author Gordon
	 * @since 2015-05-07
	 */
	private function getMinShippingInfo($data){
		if( !isset($data->data->weight) ){
			$this->_error = 10;
			return false;
		}
		$param = array(
				'country'				=> isset($data->data->country) ? $data->data->country : '',
				'ship_code'				=> isset($data->data->ship_code) ? $data->data->ship_code : '', 
				'discount'				=> isset($data->data->discount) ? $data->data->discount : 0,
				'attributeid'			=> isset($data->data->attributeid) ? $data->data->attributeid : array(),
				'volume'				=> isset($data->data->volume) ? $data->data->volume : 0,
				'warehouse'				=> isset($data->data->warehouse) ? $data->data->warehouse : 1,
				'exclude_ship_code'		=> isset($data->data->exclude_ship_code) ? $data->data->exclude_ship_code : array(),
		);
		$return = Logistics::model()->getMinShippingInfo($data->data->weight, $param);
		return $return;
	}
	
	/**
	 * @desc 老系统请求取消订单
	 * @author Gordon
	 * @since 2015-06-27
	 */
	private function cancelOrders($data){
		$return = Order::model()->cancelOrders($data->data->orders);
		return $return;
	}

	public function getDbKey() {
		return 'db_order';
	}
	
	public function tableName() {
		return 'ueb_order';
	}
}