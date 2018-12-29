<?php
/**
 * @package Ueb.modules.api.models
 * @author Gordon
 * @author 2015-01-05
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
class Wms extends UebModel { 		
// 	const CUSTOMERID= 'HQJM'; //项目客户名(改成了配置)
	
	public $_method = '';//请求接口
	public $_error  = 0;
	const SHIP_STATUS_END = 1;
// 	public $_url= 'http://172.16.1.17:9080/datahubWeb/WMSSOAP/HQJM?wsdl';(改成了配置)
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
	
// 	private function getTest($data){
// 		return array(
// 				'a' => '111',
// 		);
// 	}
	/**
	 * 集拼箱(WMS->ERP)
	 * 说明：
	 * 1.出库订单集拼时需要判断该票订单是否达到预期利润，若存在不符合的情况，则不做发货操作。
	 * @param unknown $data
	 * @return boolean
	 */
	private function checkCanShip($data){		
		if(!isset($data->data->SOReference1) || !isset($data->data->Weight) || !isset($data->data->CarrierID) ){
			$this->_error=4;//传入包裹号、重量或物流公司不存在
			return false;
		}
// 		$Orderno	  =	$data->data->Orderno;      //WMS订单号 
 		$SOReference1 = $data->data->SOReference1; //ERP包裹号 
 		$Weight		  = $data->data->Weight;       //重量
		$Carrierid	  = $data->data->CarrierID;    //物流公司			
    	//获取系统设定的亏算变量现在数据库中添加数据 设定流程未完成 gk
 		$profit_loss_value = UebModel::model('SysConfig')->find('config_key = :config_key',array(':config_key'=>'order_profit_limit'))->config_value;
		//查询临时表检测利润信息		
    	$orderProfitInfo=UebModel::model('orderProfitMomentData')->findAll('package_id = :package_id',array(':package_id'=>$SOReference1));	
		//计算运费	
		$calWeight=0; 		
		$shipCostArr=UebModel::model('Logistics')->getMinShippingInfo($Weight,array('country'=>$orderProfitInfo[0]->ship_country_name,'ship_code'=>$Carrierid));
		$shipCost=$shipCostArr['ship_cost'];				
		//订单利润 = 总收入 - 平台交易费 - paypal手续费  - 成本 - 运费
		$flag=false;
		$packageProfit=0;//包裹利润;
		if(!empty($orderProfitInfo)){
			$flag=true;
			foreach($orderProfitInfo as $key=>$orderNoInfo){			
				$orderNoShipCost = UebModel::model('OrderProfit')->findByPk($orderNoInfo->order_id);//查询订单运费						
				$orderNoProfit           = $orderNoInfo->amout-$orderNoInfo->final_value_fee-$orderNoInfo->paypal_fee-$orderNoInfo->cost-$orderNoShipCost['ship_cost'];//订单利润				
				$packageProfitNoShipCost = $orderNoInfo->amout-$orderNoInfo->final_value_fee-$orderNoInfo->paypal_fee-$orderNoInfo->cost;//包裹利润（未扣运费）			
				if($orderNoProfit<$profit_loss_value){
					$flag=false;					
				}
				$packageProfit+=$packageProfitNoShipCost;//包裹利润;
			}
		}	
		if($shipCost==0){
			$this->_error=9;//运费计算结果为空
			$arr->Profit  =$packageProfit-$shipCost;
			$arr->CanShip =ApiModel::NOT_SHIP;
			return $arr;
		}		
		if($flag){
			$this->_error=0;
			$arr->Profit  =$packageProfit-$shipCost;
			$arr->CanShip =ApiModel::CAN_SHIP;
			return $arr;
		}else{
			$this->_error=5;//包裹订单利润亏损
			$arr->Profit  =$packageProfit-$shipCost;
			$arr->CanShip =ApiModel::NOT_SHIP;
			return $arr;
		}		
	}
	/**
	 *  出库订单（B2C出库订单）(WMS->ERP)
	 *  说明：
	 *  1、WMS出库完成后，根据接口字段将出库信息反馈到ERP，ERP接收信息完成出库。
	 */
	private function completeOrder($data){
		$WarehouseID     = $data->data->WarehouseID;     //WMS仓库ID
 		$CustomerID      = $data->data->CustomerID;      //货主
// 		$OrderNo         = $data->data->OrderNo;         //WMS订单编号
		$package_id      = $data->data->SOReference1;    //ERP包裹号
		$track_num       = $data->data->SOReference5;    //快递单号	
		$weight          = $data->data->Weight;          //重量
		$Carrierid       = $data->data->CarrierID;       //物流公司
 		$OrderShippedtime= $data->data->OrderShippedtime;//订单发运时间
 		$packageShipName = $data->data->Shipper;         //发货人	
 		
 		$arr->PackageId = $package_id;
 		
		if(!isset($data->data->SOReference1)|| !isset($data->data->Weight) || !isset($data->data->WarehouseID)|| !isset($data->data->CustomerID)){
			$this->_error=6;//出库订单回传传入ERP包裹号、WMS仓库ID、货主、WMS订单编号、快递单号或重量不存在
			$arr->Status=ApiModel::NO_STATUS; //失败
			return $arr;
		}
		$packageInfo = UebModel::model('OrderPackage')->getAttributesByPackageId($package_id);
		if(empty($packageInfo))	{
			$this->_error=7; //ERP更新出库信息失败
			$arr->Status=ApiModel::NO_STATUS; //失败
			return $arr;
		}
		//$WmsApiLog = UebModel::model('WmsApiLog')->findAll('package_id = :package_id',array(':package_id'=> $package_id));
		$WmsApiLog = UebModel::model('WmsApiLog')->find('package_id = :package_id',array(':package_id'=> $package_id));
		if(!empty($WmsApiLog)){
			$this->_error= 0;
			$arr->Status = ApiModel::CAN_STATUS;//成功
			return $arr;
		}
		//传入参数添加到日志表
		$arrLog=array(
				'package_id'	  => $package_id,
				'weight'     	  => $weight,
				'real_ship_code'  => $Carrierid,
				'track_num'       => $track_num,
				'ship_user_enname'=> $packageShipName,
				'create_time'     => $OrderShippedtime,
				'update_ship_status'=> WmsApiLog::UPDATE_SHIP_STATUS_NOT,
				'add_time'		  => date('Y-m-d H:i:s')
				
		);
		$result = UebModel::model('WmsApiLog')->getDbConnection()->createCommand()->insert(WmsApiLog::tableName(), $arrLog);
    	if($result){    
    		$this->completeOrderResult();				
    		$this->_error=0;
    		$arr->Status=ApiModel::CAN_STATUS;//成功   				
    		return $arr;
    	}else{
    		$this->_error=7; //ERP更新出库信息失败
    		$arr->Status=ApiModel::NO_STATUS; //失败
    		return $arr;
    	}
		
	}
	
	/**
	 * @desc 将wms回传的包裹未更新order_package状态的更新出货状态
	 * @since 2015-04-24
	 * @author Super
	 */
	public function completeOrderResult(){
		$wmsPackageInfoArr = UebModel::model('WmsApiLog')->getNotUpdateShipPackage();
				
		$model = new OrderPackage();
		
		foreach ($wmsPackageInfoArr as $wmsPackageInfo){
			
			$transaction = $model->getDbConnection()->beginTransaction();
			try {
				
				$flag = false;
				$package_id      = $wmsPackageInfo['package_id'];    //ERP包裹号
				$track_num       = $wmsPackageInfo['track_num'];    //快递单号
				$weight          = $wmsPackageInfo['weight'];          //重量
				$shipCode        = $wmsPackageInfo['real_ship_code'];       //物流公司
				$OrderShippedtime= $wmsPackageInfo['ship_time'];//订单发运时间
				$packageShipName = $wmsPackageInfo['ship_user_enname'];         //发货人
				
				//查询临时表检测利润信息
				$orderProfit = UebModel::model('OrderProfitMomentData')->findAll('package_id = :package_id',array(':package_id'=> $package_id));
				//取包裹成本
				$packageInfo = UebModel::model('OrderPackage')->getAttributesByPackageId($package_id);
				$shipUserId = UebModel::model('User')->getIdByUserEnName($packageShipName);//查询发货人id
				
				//计算运费
				$logisticsModel = UebModel::model('Logistics');
				$calWeight=0;
				$conditions = array('country'=>$orderProfit[0]->ship_country_name,'ship_code'=>$shipCode,'include_disable' => true);
				$shipCost = $logisticsModel->getMinShippingInfo($weight,$conditions);
				//未得到运费，从缓存的计价方案中计算
				if (empty($shipCost['ship_code'])) {
					$conditions['before_price_plan'] = true;
					$recordPricingSolution = LogisticsPricingSolution::readPricingSolutionRecord($package_id);
					$recordPricingSolution && $logisticsModel->setBeforePricePlanData($recordPricingSolution);
					$shipCost = $logisticsModel->getMinShippingInfo($weight,$conditions);
				}
				//未得到运费，从生成包裹时取　[此情况很少]
				if (empty($shipCost['ship_code'])) {
					$shipCost['ship_cost'] = $packageInfo['ship_cost_infer'];
					$shipCost['ship_id'] = $logisticsModel->getLogisticsIdByCode2($shipCode);
				}
				LogisticsPricingSolution::delPricingSolutionRecord($package_id);
				
				$package_data = array(
						'package_id'		=> $package_id,
						'total_weight'		=> $weight,
						'net_weight'		=> $weight,	//$_POST['totalQty'] == 1 ? $weight - $_POST['packageWeight'] : '',
						'real_ship_type'	=> $shipCode,
						'real_ship_type_id'	=> $shipCost['ship_id'],
						'ship_cost'			=> $shipCost['ship_cost'],
						'package_cost'		=> $packageInfo['package_cost'],
						'track_num'			=> $track_num,
						'ship_date'         => $OrderShippedtime,
						'ship_user_id'      => $shipUserId,
				);
				
				$updateLog = array('update_ship_time'  => date('Y-m-d H:i:s',time()));
				
				if ($packageInfo) {
						
					$flagSave = $model->WmsApisaveShip($package_data);
						
					$flag = false;
					$flagDeduct = false;//是否需要扣除库存标记
					if($packageInfo['ship_status'] == OrderPackage::SHIP_STATUS_END){//已发货直接返回
						$updateLog['update_ship_note'] = '包裹已交运!';
						$flag = true;
					}elseif($packageInfo['ship_status'] == OrderPackage::SHIP_STATUS_CANCEL) {
						$updateLog['update_ship_note'] = '包裹已取消!';
						$flag = true;
					}else{
						$flag = true;
						$flagDeduct = true;
					}
						
					$updateLog['update_ship_status'] = WmsApiLog::UPDATE_SHIP_STATUS_YES;
				}else {
					$updateLog['update_ship_status'] = WmsApiLog::UPDATE_SHIP_STATUS_ABN;
					$updateLog['update_ship_note'] = '包裹不存在!';
					$flag = true;
				}
				
				if($flag){
					$result = UebModel::model('WmsApiLog')->saveWmsApiLog($updateLog,$wmsPackageInfo['id']);
					
					//扣除库存放到这里，前期放在上面存在问题
					if ($result && $flagDeduct) {
						$packageDetailInfo=OrderPackageDetail::model()->getDetailByPackageId($package_id);//根据包裹查询包裹详情信息
						foreach($packageDetailInfo as $key=>$val){
							$stockQtyArr = array('true_qty'=>-$val['quantity']);
							UebModel::model('WarehouseRecord')->warehouseRecordSave($val['warehouse_id'],$val['sku'],$val['quantity'],InAndOutStock::STOCK_OUT,$note=$package_id.'WMS包裹发货扣实际库存',$isFirst=false,$recordType=array(WarehouseRecord::STOCKINANDOUT_TYPE_PHYSICAL));
							UebModel::model('WarehouseSkuMap')->updateStockQty($stockQtyArr,$val['warehouse_id'], $val['sku']);//出库，扣实际
						}
					}
				}
			
				$transaction->commit();
				echo 'ok';
					
			}catch (Exception $e){
				echo 'err';
				$transaction->rollback();
			}
		
		}
		
	}
	
	
	
	/**************************************erp出货信息异常包裹处理api******************************************************/
	/**
	 *  出库订单（B2C出库订单）(WMS->ERP)
	 	 *  说明：
	 *  1、WMS出库完成后，根据接口字段将出库信息反馈到ERP，ERP接收信息完成出库。
	 */
	private function checkCompleteOrder(){
		$wmsPackageInfoArr = UebModel::model('WmsApiLog')->wmsApiExceptionPackageidRepair();
		foreach ($wmsPackageInfoArr as $wmsPackageInfo){
			$package_id      = $wmsPackageInfo['package_id'];    //ERP包裹号
			$track_num       = $wmsPackageInfo['track_num'];    //快递单号
			$weight          = $wmsPackageInfo['total_weight'];          //重量
			$shipCode        = $wmsPackageInfo['real_ship_type'];       //物流公司
			$OrderShippedtime= $wmsPackageInfo['ship_date'];//订单发运时间
			$shipUserId		 = $wmsPackageInfo['ship_user_id'];         //发货人					
			//查询临时表检测利润信息
			$orderProfit = UebModel::model('OrderProfitMomentData')->findAll('package_id = :package_id',array(':package_id'=> $package_id));
			//计算运费
			$calWeight=0;
			$shipCost=UebModel::model('Logistics')->getMinShippingInfo($weight,array('country'=>$orderProfit[0]->ship_country_name,'ship_code'=>$shipCode));
			//取包裹成本
			$packageInfo = UebModel::model('OrderPackage')->getAttributesByPackageId($package_id);
			if(empty($packageInfo))	{
				continue;
			}
			$model = new OrderPackage();
			$package_data = array(
					'package_id'		=> $package_id,
					'total_weight'		=> $weight,
					'net_weight'		=> $weight,	//$_POST['totalQty'] == 1 ? $weight - $_POST['packageWeight'] : '',
					'real_ship_type'	=> $shipCode,
					'real_ship_type_id'	=> $shipCost['ship_id'],
					'ship_cost'			=> $shipCost['ship_cost'],
					'package_cost'		=> $packageInfo['package_cost'],
					'track_num'			=> $track_num,
					'ship_date'         => $OrderShippedtime,
					'ship_user_id'      => $shipUserId,
			);
			$flagSave=OrderPackage::model()->WmsApisaveShip($package_data);
			if($packageInfo['ship_status']==self::SHIP_STATUS_END){//已发货直接返回
				$flag=true;
			}else{
				$transaction = $model->getDbConnection()->beginTransaction();
				try {
					$packageDetailInfo=OrderPackageDetail::model()->getDetailByPackageId($package_id);//根据包裹查询包裹详情信息
					foreach($packageDetailInfo as $key=>$val){
						$stockQtyArr = array('true_qty'=>-$val['quantity']);
						UebModel::model('WarehouseRecord')->warehouseRecordSave($val['warehouse_id'],$val['sku'],$val['quantity'],InAndOutStock::STOCK_OUT,$note=$package_id.'WMS包裹发货扣实际库存',$isFirst=false,$recordType=array(WarehouseRecord::STOCKINANDOUT_TYPE_PHYSICAL));
						UebModel::model('WarehouseSkuMap')->updateStockQty($stockQtyArr,$val['warehouse_id'], $val['sku']);//出库，扣实际
					}
					$flag=true;
					$transaction->commit();
				}catch (Exception $e){
					$flag = false;
					$transaction->rollback();
				}
			}

		}
	}
	/*****************************************************************************************************************/
	
	/**
	 * 入库单取消(ERP->WMS)
	 * 说明:
	 * 1.ERP推送入库单给WMS后，因某种原因需要取消该订单
	 * @param unknown $data
	 */
	private function cancelStockIn($data){
		if(empty($data)){
			$this->errors=8;
			return false;
		}
		$model=new Wmsapiset();
		$wmsConfigs=$model->getConfig();
		$arr=array();
		$arr['wmsSecurityInfo']['username']= $wmsConfigs['wms_username'];
		$arr['wmsSecurityInfo']['password']= $wmsConfigs['wms_password'];
		$arr['wmsParam']['warehouseid']    = $data->data->warehouse_id;       //仓库
		$arr['wmsParam']['customerid']     = $wmsConfigs['wms_customerid'];   //项目客户名
		$arr['wmsParam']['messageid']     = '104_Canl';             	  	  //接口代码
		$arr['wmsParam']['stdno']          = 'CANCEL';                        //接口业务类型,
		$arr['wmsParam']['param'][0]	   = $data->data->purchase_order_no;  //采购单号
		$functions=processSP;
		$flag=$this->wmsCurl($arr,$functions);
		 return $flag;		
	}
	
	/**
	 * 出库单取消(ERP->WMS)	
	 * 说明：
	 * 1、ERP订单推送至WMS后，因某种原因需要取消该订单
	 * 2、WMS订单发货前可以取消，发货完成后不允许取消。
	 * @param unknown $data
	 */
	private function cancelOutStock($data){
		if(empty($data)){
			$this->errors=9;
			return false;
		}
		 $model=new Wmsapiset();
		 $wmsConfigs=$model->getConfig();
		 $arr=array();
		 $arr['wmsSecurityInfo']['username']= $wmsConfigs['wms_username'];
		 $arr['wmsSecurityInfo']['password']= $wmsConfigs['wms_password'];
		 $arr['wmsParam']['warehouseid']    = $data->data->warehouse_id;     //仓库		
		 $arr['wmsParam']['customerid']     = $wmsConfigs['wms_customerid']; //项目客户名
		 $arr['wmsParam']['messageid']     = '106_Canl';	                 //接口代码          
		 $arr['wmsParam']['stdno']          = 'CANCEL';                      //接口业务类型,
		 $arr['wmsParam']['param'][0]	    = $data->data->package_id;       //订单号
		 $functions=processSP;		
		 $flag=$this->wmsCurl($arr,$functions);
		 return $flag;
	} 
	
	/**
	 * 交互wms
	 * @param unknown $data
	 * @return unknown
	 * $arr:传入参数,$functions:方法名
	 */
	public function wmsCurl($arr,$functions){	//http://172.16.1.17:9080/datahubWeb/WMSSOAP/HQJM?wsdl
		$model=new Wmsapiset();
		$wmsConfigs=$model->getConfig();
		$soapClient = new SoapClient($wmsConfigs['wms_url']);
		$result=$soapClient->$functions($arr);		
		return $result->return;
		
	}

	public function getDbKey() {
		return 'db_order';
	}
	
	public function tableName() {
		return 'ueb_order';
	}
}