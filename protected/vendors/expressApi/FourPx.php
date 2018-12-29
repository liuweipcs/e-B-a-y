<?php
class FourPx implements expressUtil{
	private $authToken = '1066B645F331E3CFBE85A70A55D94C9C';
	private $orderOperation = 'http://api.4px.com/OrderOnlineService.dll?wsdl';
	private $orderTools = 'http://api.4px.com/OrderOnlineToolService.dll?wsdl';
	
	private  $orderConn = null;
	private  $toolConn = null;

	private function getToolConn(){
		if(is_null($this->toolConn)){
			try {
				$this->toolConn = new SoapClient($this->orderTools);
			} catch (Exception $e) {
// 				$e->getMessage();
			}
		}
	}
	
	//获取订单的物流信息
	public function checkResult($oId){
		$this->getToolConn();
		$result =  $this->toolConn->cargoTrackingService(array(
			'arg0'=>$this->authToken,
			'arg1'=>$oId
		));
		$array =  $result->return->tracks->trackInfo;
		if(!empty($array)){
			$result = array();
			//可能返回数组或者对象
			if($array instanceof  stdClass){
				$result[$array->occurDate] = $array->trackContent;
			}else{
				foreach ($array as $val){
					$result[$val->occurDate] = $val->trackContent;
					if(isset($val->occurAddress)) $result[$val->occurDate] .= $val->occurAddress;
				}
			}
			return $result;
		}
		return false;
	}
	
}