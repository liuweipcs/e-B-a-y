<?php
class warningModel{
	function __construct(){
		//$this->config =  new config();
		$this->config_new = new config(1);
	}
	
	public function synchron_main_warning(){
		
		$result = $this->config_new->getCollectionBySimple('status=1',"*","id",'',$this->config_new->UPRO.".ueb_product_warning");
		$warnings = array();
		foreach($result as $detail){
			$product_status = $detail['product_status'];//W状态
			$row = $this->config_new->getRow(array('where'=>"sku='".$detail['sku']."'",'select'=>'sku,product_status'),$this->config_new->UPRO.".ueb_product");
			$main_product_status = $row['product_status'];//M状态
			$sku = $row['sku'];//SKU
			if($main_product_status != $product_status){
				$this->config_new->update(array('status'=>2,'updatetime'=>date('Y-m-d H:i:s')),'id = "'.$detail['id'].'"',$this->config_new->UPRO.".ueb_product_warning");
			}else{
				$newtime = strtotime(date('Y-m-d H:i:s'));
				if($detail['product_status']==1){//刚开发
					$mtine = $newtime-strtotime($detail['checktime']);
					//$date = round($mtine/3600/24);//超时？
					if($mtine>60*180){
					//if($date > 1){  180分钟
						$this->config_new->update(array('time_out'=>2,'updatetime'=>date('Y-m-d H:i:s')),'id = "'.$detail['id'].'"',$this->config_new->UPRO.".ueb_product_warning");
					}
				}elseif($detail['product_status']==8){
					$mtine = $newtime-strtotime($detail['checktime']);
					$date = round($mtine/3600/24);
					if($date > 5){
						$this->config_new->update(array('time_out'=>2,'updatetime'=>date('Y-m-d H:i:s')),'id = "'.$detail['id'].'"',$this->config_new->UPRO.".ueb_product_warning");
					}
				}elseif($detail['product_status']==9){
					$mtine = $newtime-strtotime($detail['checktime']);
					//$date = round($mtine/3600/24);
					//if($date > 1){
					if($mtine >1800){//品检
						$this->config_new->update(array('time_out'=>2,'updatetime'=>date('Y-m-d H:i:s')),'id = "'.$detail['id'].'"',$this->config_new->UPRO.".ueb_product_warning");
					}
				}elseif($detail['product_status']==10){
					$mtine = $newtime-strtotime($detail['checktime']);
					$date = round($mtine/3600/24);
					$prefix = explode('-',$sku);
					$warehouse = $this->getWarehouseConfig();
					$warehouse = array_flip($warehouse);
					if(in_array($prefix[0],$warehouse)){
						if($date > 12){
							$this->config_new->update(array('time_out'=>2,'updatetime'=>date('Y-m-d H:i:s')),'id = "'.$detail['id'].'"',$this->config_new->UPRO.".ueb_product_warning");
						}
					}else{
						//5==>4
						//if($date > 4){
						if($mtine>7200){//摄影两个小时
							$this->config_new->update(array('time_out'=>2,'updatetime'=>date('Y-m-d H:i:s')),'id = "'.$detail['id'].'"',$this->config_new->UPRO.".ueb_product_warning");
						}
					}
					
				}elseif($detail['product_status']==2){//文案编辑
					$mtine = $newtime-strtotime($detail['checktime']);
					//$date = round($mtine/3600/24);
					//if($date > 3){
					if($mtine>1800){//30分钟
						$this->config_new->update(array('time_out'=>2,'updatetime'=>date('Y-m-d H:i:s')),'id = "'.$detail['id'].'"',$this->config_new->UPRO.".ueb_product_warning");
					}
				}
			}
		}
		
	}
	
	public function getWarehouseConfig($status=null) {
		$typeBakInfo	= array(
			'US'  => '美国仓',
			'UK'  => '英国仓',
			'GB'  => '英仓',
			'DE'  => '德国仓',
			'AU'  => '澳洲仓',
			'FR'  => '法国仓',
			'IT'  => '意大利仓',
			'ES'  => '西班牙仓',
			'CA'  => '加拿大仓',
			'JP'  => '日本仓',
			'RU'  => '俄罗斯仓',
		);
		if($status!==null){
			return $typeBakInfo[$status];
		}else{
			return $typeBakInfo;
		}
	}
	

}   

?>