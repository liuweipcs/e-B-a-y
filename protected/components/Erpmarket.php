<?php
/**
 * @package Ueb.modules.api.models
 * @author Gordon
 * @author 2015-07-29
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
class Erpmarket extends UebModel { 		
	
	public $_method = '';//请求接口
	public $_error  = 0;
	public function initParam($method){
		$this->_method = $method;
	}
	
	public function _call($data=array()){
		$ps = explode(':', $this->_method);
		$moduleName = $ps[0]; $modelName = $ps[1]; $functionName = $ps[2];
		$paramArr = MHelper::objectToArray($data->data);
		$model = UebModel::model($modelName);
		$result = call_user_func_array(array($model, $functionName),$paramArr);
		return array(
				'errorCode' => $this->_error,
				'data'		=> $result,
		);
	}

	public function getDbKey() {
		return 'db_order';
	}
	
	public function tableName() {
		return 'ueb_order';
	}
}