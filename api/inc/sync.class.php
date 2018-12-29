<?php

/****************************************************************************************************************************
 * 更改日志：

 * 
 */

class syncModel{
	function __construct(){
		$this->config =  new config($mysql=false);
		$this->config_new = new config(1);
		
	}
	function get_time_thread($action,$lastStr){
		$data = $this->config_new->getRowBySimple("function_model='$action' and status=1 and content='$lastStr'","*","id desc",$this->config->UDB.".sync_function_log");
		$endtime = isset($data['end_time'])?$data['end_time']:'2008-07-06 01:00:00';
		$endtime = date('Y-m-d H:i:s',strtotime($endtime)-200);
		
		return $endtime;
	}
	function get_time($action){
		$data = $this->config_new->getRowBySimple("function_model='$action' and status=1","*","id desc",$this->config->UDB.".sync_function_log");
		$endtime = isset($data['end_time'])?$data['end_time']:'2008-07-07 01:00:00';
		$endtime = date('Y-m-d H:i:s',strtotime($endtime)-200);
		return $endtime;
	}

	
	
		
	function synchron_main_product($lastStr,$starttime,$limit=0,$num = 1000){
		$data = array();
		try{
 			$data = $this->config->getCollectionBySimple("substr(trim(pro_code),-1,1)='$lastStr' and opration_date>='$starttime'","*","opration_date asc",$limit.','.$num,$this->config->OLDDB.".main_product");			
			if($data){
				foreach($data as $key=>$val){				
					$newdata = $desc['cn'] = $desc['en'] =  array();
					$newdata['product_cost'] = $val['cost'];					
					if($val['use_state']==1){
						$use_state = 4;
					}elseif($val['use_state']==2){
						$use_state = 6;
					}elseif($val['use_state']==0){
						$use_state = 7;
					}
					$newdata['product_status'] =  $use_state;
					$newdata['product_weight'] = $val['weight'];
					$newdata['product_length'] = $val['length']*10;
					$newdata['product_width']  = $val['width']*10;
					$newdata['product_height'] = $val['height']*10;
					$isbak = 0;
					if($val['prodattr']=='BAK'){
						$isbak = 1;
					}
					$newdata['product_is_bak'] = $isbak;
					$newdata['product_bak_days'] = $val['cg_period'];
					$newdata['product_prearrival_days'] = $val['bakdays'];
					$newdata['product_package_max_nums'] = $val['pkgmaxnums'];
					$newdata['product_is_storage'] = $isbak;
					$newdata['product_original_package'] = $val['original_package'];
					$newdata['product_pack_code'] =$this->getPmaterialSku($val['pmaterial_code']);
					$newdata['product_package_code'] = $this->getPmaterialSku($val['packagetype']);
					$newdata['product_is_new'] = $val['isnew'];
					$newdata['product_is_multi'] = $val['pro_code_level'];
					$newdata['modify_time'] = $val['opration_date'];
					$newdata['provider_type'] = 1;
					$newdata['drop_shipping'] = $val['drop_shipping'];
					$newdata['drop_shipping_sku'] = $val['drop_shipping_sku'];
					$newdata['create_user_id'] = $this->getUserId($val['create_id']);
					$newdata['modify_user_id'] = $this->getUserId($val['opration_id']);
					$newdata['create_time'] = $val['create_date'];
					$newdata['product_combine_code'] = !empty($val['related_product']) ? $val['related_product'].'*'.$val['multi'] :'';
					$newdata['product_combine_num'] = $val['multi'] ? $val['multi'] : 0;//旧系统是否只一对一捆绑
					$newdata['last_provider_id'] = $this->getProviderIdBySupId($val['defaultsupply']);//两边供应商id并不对应，需根据名称关联起来
					$newdata['product_cn_link'] = $val['ref_cn_url'];
					$newdata['product_en_link'] = $val['ref_en_url'];										
					$row = $this->config_new->getRow(array('where'=>"sku='".$val['pro_code']."'"),$this->config->UPRO.".ueb_product");									
					if($row){
						$r = $this->config_new->update($newdata,"sku='".$val['pro_code']."'",$this->config->UPRO.".ueb_product");
						if($r){						
							//修改产品绑定供应商,还需要同步sku与供应商的对应关系 ueb_product_provider
							$providerId = $this->getProviderIdBySupId($val['selsupply']);
							$realation = $this->config_new->getRow(array('where'=>"product_id='".$row['id']."' and provider_id='".$providerId."'"),$this->config->UPRO.".ueb_product_provider");
							if(empty($realation)){
								$a = array('product_id'=>$row['id'],'provider_id'=>$providerId);
								$this->config_new->insert($a,$this->config->UPRO.".ueb_product_provider",true);
							}							
// 							if(!empty($val['related_product'])){
// 								$this->saveSkuCombine($row['id'],$val); //修改产品捆绑表
// 							}																				
							$this->updatedesc($row['id'],$val);			//insert 中英文描述																	
							$this->saveSecurityLevel($val['pro_code']);	//save 侵权												
							$this->saveSkuAssign($val);					//角色分配
						}
					}else{
						$newdata['sku'] =  $val['pro_code'];
						$newdata['currency'] =  'CNY';
						$newdata['product_type'] = 1;
						$r = $this->config_new->insert($newdata,$this->config->UPRO.".ueb_product",true);
						if($r){							
							//修改产品绑定供应商,还需要同步sku与供应商的对应关系 ueb_product_provider
							$providerId = $this->getProviderIdBySupId($val['selsupply']);
							$a = array('product_id'=>$r,'provider_id'=>$providerId);
							$this->config_new->insert($a,$this->config->UPRO.".ueb_product_provider",true);							
// 							if(!empty($val['related_product'])){
// 								$this->saveSkuCombine($r,$val);			//修改产品捆绑表
// 							}
							$this->updatedesc($r,$val);
							$this->saveSecurityLevel($val['pro_code']);
							$this->saveSkuAssign($val);														
						}
					}
					$limit++;
					$endtime = $val['opration_date'];
				}
				$this->config_new->insert(array('function_model'=>'mainproduct','start_time'=>$starttime,'end_time'=>$endtime,'status'=>1,'content'=>$lastStr),$this->config->UDB.".sync_function_log");
				$this->synchron_main_product($lastStr,$starttime,$limit,$num = 1000);

			}else{
				return true;
			}
		}catch(Exception $e){
			echo 'error';
		}
	}
	
	

	//同步产品表里最后采购供应商
	public function productProvider($lastStr,$starttime, $limit=0,$num=100){
		$data = array();
		try{//and opration_date>='$starttime'
			$data = $this->config->getCollectionBySimple("substr(trim(pro_code),-1,1)='$lastStr' and defaultsupply !='' ","*","opration_date asc",$limit.','.$num,$this->config->OLDDB.".main_product");
			if($data){
				foreach($data as $key=>$val){
					$newdata = array();
					//两边供应商id并不对应，需根据名称关联起来
					$oldSupply = $this->config->getRow(array('where'=>"sup_id='".$val['defaultsupply']."'"),$this->config->OLDDB.".bs_supply");
					$newProvider = $this->config_new->getRow(array('where'=>"provider_code='".$oldSupply['sup_abbr']."'"),$this->config->UPUR.".ueb_provider");
					if($newProvider){
						$newdata['last_provider_id'] = $newProvider['id'];
						$r = $this->config_new->update($newdata,"sku='".$val['pro_code']."'",$this->config->UPRO.".ueb_product");
					}
					$limit++;
					$endtime = $val['opration_date'];
				}
				$this->config_new->insert(array('function_model'=>'productProvider','start_time'=>$starttime,'end_time'=>$endtime,'status'=>1,'content'=>$lastStr),$this->config->UDB.".sync_function_log");
				$this->productProvider($lastStr,$starttime,$limit,$num);
			}else{
				return true;
			}
		}catch(Exception $e){
			echo 'error';
		}
	}
	
	public function runThread($url,$hostname='',$port=80) {
		
		if(!$hostname){
			$hostname=$_SERVER['HTTP_HOST'];
		}
		$fp=fsockopen($hostname,$port,&$errno,&$errstr,600);
		fputs($fp,"GET ".$url."\r\n");
// 		while (!feof($fp)){
// 			echo fgets($fp,2048);
// 		}
		fclose($fp);
    }
    
	
	
	public function synchron_product_attribute($num,$limit=0 ,$end=1000){					
		try{
			$data = $this->config->getCollectionBySimple("attributeid='".$num."'","*","",$limit.','.$end,$this->config->OLDDB.".product_attribute");			
			if($data){
				foreach ($data as $val){	
					$str='';				
					$skuInfo = $this->config_new->getRow(array('where'=>"sku='".$val['pro_code']."'"),$this->config->UPRO.".ueb_product");					
					$oldSkuInfo= $this->config->getRow(array('where'=>"pro_code='".$val['pro_code']."'"),$this->config->OLDDB.".main_product");		
					$isMulti=$oldSkuInfo['pro_code_level']!=0?1:0;
					$mainSku=!empty($oldSkuInfo['main_pro_code'])?$oldSkuInfo['main_pro_code']:'';																						
					$productId = $this->config_new->getRow(array('where'=>"sku='".$mainSku."'"),$this->config->UPRO.".ueb_product");				
					if($skuInfo['sku']){
						$info = array(
							'product_id' 			=> $skuInfo['id'],
							'attribute_id' 			=> $this->getAttributeId($num),
							'attribute_value_id' 	=> $val['attributeid'],
							'attribute_is_multi' 	=> $isMulti,
							'multi_product_id' 		=> !empty($productId)?$productId['id']:'',
							'sku' 					=> $skuInfo['sku'],
						);
						
						$attributeNew = $this->config_new->getRow(array('where'=>"sku='".$val['pro_code']."' and attribute_value_id='".$val['attributeid']."'"),$this->config->UPRO.".ueb_product_select_attribute");
						if($attributeNew){
							$this->config_new->update($info,"sku='".$val['pro_code']."' and attribute_value_id='".$val['attributeid']."'",$this->config->UPRO.".ueb_product_select_attribute");
						}else{
							$this->config_new->insert($info,$this->config->UPRO.".ueb_product_select_attribute");
						}	
						unset($productId);		
					}					
					$limit++;		
 				}
 				$this->synchron_product_attribute($num,$limit,$end);							
			}
		}catch(Exception $e){
			echo 'error';
		}
	}
	
	/*老到新  非公共属性 与 非公共属性值对应的关系*/
	public function synchron_son_product_attribute($attr,$limit=0 ,$end=500){
		try{
			$attributeInfo = $this->config_new->getRow(array('where'=>"attribute_code='".$attr."'"),$this->config->UPRO.".ueb_product_attribute");
			$data = $this->config->getCollectionBySimple("multi_name='".$attr."'","*","",$limit.','.$end,$this->config->OLDDB.".product_multi_detail","multi_name,multi_value");
 			if($data){
 				foreach ($data as $val){
 					if(empty($val['main_sku']) || empty($val['son_sku']) || empty($val['multi_name']) || empty($val['multi_value'])){
 						continue;
 					}									
 					$info=array(
 						'attribute_value_name'	=>stripslashes($val['multi_value']),
 						'modify_user_id'		=>1,
 						'modify_time'			=>date("Y-m-d H:i:s",time())
 					); 										
 					$attrValNew = $this->config_new->getRow(array('where'=>"attribute_value_name='".$val['multi_value']."'"),$this->config->UPRO.".ueb_product_attribute_value"); 					 					 					
 					if(empty($attrValNew)){
 						$id=$this->config_new->insert($info,$this->config->UPRO.".ueb_product_attribute_value",true); 						
 						if($id>0){							
		 					$attrInfo=array(
		 							'attribute_id'		=> $attributeInfo['id'],
		 							'attribute_value_id'=> $id
		 					);
		 					$attributData = $this->config_new->getRow(array('where'=>"attribute_id='".$attributeInfo['id']."' and attribute_value_id='".$id."'"),$this->config->UPRO.".ueb_product_attribute_map");
		 					if(empty($attributData)){
		 						$this->config_new->insert($attrInfo,$this->config->UPRO.".ueb_product_attribute_map",true);
		 					}
 						}
 					}else{
 						$attrInfo=array(
 							'attribute_id'		=> $attributeInfo['id'],
 							'attribute_value_id'=> $attrValNew['id']
 						);
 						$attributData = $this->config_new->getRow(array('where'=>"attribute_id='".$attributeInfo['id']."' and attribute_value_id='".$attrValNew['id']."'"),$this->config->UPRO.".ueb_product_attribute_map");
 						if(empty($attributData)){
 							$this->config_new->insert($attrInfo,$this->config->UPRO.".ueb_product_attribute_map");
 						}
 					} 
 					$limit++;		
 				}
 				$this->synchron_son_product_attribute($attr,$limit,$end);
 			}else{
 				return true;
 			}
		}catch(Exception $e){
			echo 'error';
		}
	}
	
	public function getAttributeId($num){
		$arr=array("1","2","3","4","5","10","11","12","13");
		if(in_array($num,$arr)){
			return 3;
		}else{
			return 18;
		}
	}

	
	public function updatedesc($productId,$val=array()){
		$desc['cn'] = $desc['en'] = $desc['de'] =$desc['fr']=  array();
		//descript data
		if(!empty($val['cname'])){
			$desc['cn'] = array(
					'product_id' 		=> $productId,
					'sku' 				=> $val['pro_code'],
					'title' 			=> $val['cname'],
					'customs_name'  	=> $val['hs_code'],
					'language_code' 	=> 'Chinese',
					'description' 		=> $val['cnote'],
					'included' 			=> $val['includes'],
			);
			$this->_insertSkuLanguage($desc['cn'],$val['pro_code'],'Chinese');
		}
		if(!empty($val['ename'])){
			$desc['en'] = array(
					'product_id' 		=> $productId,
					'sku' 				=> $val['pro_code'],
					'title' 			=> $val['ename'],
					'customs_name' 		=> $val['hs_code'],
					'language_code' 	=> 'english',
					'description' 		=> $val['enote'],
					'included' 			=> $val['attach'],
			);
			$this->_insertSkuLanguage($desc['en'],$val['pro_code'],'english');
		}
		//German and French
		$otherDesc = $this->config->getCollectionBySimple("pro_code='".$val['pro_code']."'","*","id asc",'',$this->config->OLDDB.".product_multilingual_describe");
	
		if($otherDesc){
			foreach ($otherDesc as $key=>$v){
				$data = array(
						'product_id' 		=> $productId,
						'sku' 				=> $val['pro_code'],
						'title' 			=> $v['title'],
						'customs_name' 		=> $val['hs_code'],
						'language_code' 	=> $v['language_code'],
						'description' 		=> $v['description'],
						'included' 			=> $v['includes'],
				);
				$this->_insertSkuLanguage($data,$val['pro_code'],$v['language_code']);
			}
		}
	}
	public function _insertSkuLanguage($data,$sku,$language){
		$where = "sku='{$sku}' and language_code='{$language}'";
		//$this->config_new->delete($where,$this->config->UPRO.".ueb_product_description");
		$row = $this->config_new->getRow($where,$this->config->UPRO.".ueb_product_description");
		if($row){
			$this->config_new->update($data,$where,$this->config->UPRO.".ueb_product_description");
		}else{
			$this->config_new->insert($data,$this->config->UPRO.".ueb_product_description",true);
		}
	
	}
	public function saveSecurityLevel($sku){	
		$where = "sku='{$sku}'";
		$data = $this->config->getRow(array('where'=>"pro_code='".$sku."'"),$this->config->OLDDB.".product_infringe");
		if($data){
			$arr = array(
					'sku' 					=> $sku,
					'security_level' 		=> $data['infringe_level']=='not_assign'?'E':$data['infringe_level'],
					'infringement' 			=> $this->getChangeNun($data['is_infringe']),
					'infringement_reason' 	=> $data['infringe_reson'],
					'operating_id' 			=> $this->getUserId($data['opration_id']),
					'operating_time' 		=> $data['opration_time'],
			);
			
			$data = $this->config_new->getRow(array('where'=>$where),$this->config->UPRO.".ueb_product_infringement");
			if($data){
				$this->config_new->update($arr,"sku='".$sku."'",$this->config->UPRO.".ueb_product_infringement");
			}else{
				$this->config_new->insert($arr,$this->config->UPRO.".ueb_product_infringement",true);
			}
		}
	}
		
	public function getChangeNun($newId){
		switch($newId){
			case 0:
				$oldId = 1;
				break;
			case 1:
				$oldId = 2;
				break;
			case 2:
				$oldId = 3;
				break;
		}
		return $oldId;
	}
	public function saveSkuAssign($val){
		$arr = array('purchaser','product_developers','ebay_user');
		foreach ($arr as $roleCode){
			$roledata = array();
			if($roleCode =='product_developers'){
				$roledata['user_id'] 	= $this->getUserId($val['create_id']);
				$roledata['user_name']  = $this->getUserFullName($val['create_id']);
			} 
			if($roleCode =='purchaser'){
				$roledata['user_id'] = $this->getUserId($val['pur_name']);
				$roledata['user_name'] = $this->getUserFullName($val['pur_name']);
			}
			if($roleCode =='ebay_user'){
				if(!empty($val['sc_name'])){
					$roledata['user_id'] = $this->getUserId($val['sc_name']);
					$roledata['user_name'] = $this->getUserFullName($val['sc_name']);
				}
			}
			$roledata['role_code'] = $roleCode;
			$roledata['sku'] 	   = $val['pro_code'];
			$roledata['pro_id']    = $this->getSkuId($val['pro_code']);
			$whe = "sku='".$val['pro_code']."' and role_code='".$roleCode."'";		
			$roleRow = $this->config_new->getRow($whe,$this->config->UPRO.".ueb_product_role_assign");				
			if($roleRow){
				$this->config_new->update($roledata,$whe,$this->config->UPRO.".ueb_product_role_assign");
			}else{
				if(!empty($roledata['user_name'])){
					$this->config_new->insert($roledata,$this->config->UPRO.".ueb_product_role_assign");
				}
			}
			unset($roledata);
		}
	}
	
	

	public function getPmaterialSku($pmaterial){
		
		if(!empty($pmaterial)){
			$info=$this->config_new->getRow(array('where'=>"title = '".$pmaterial."'"),$this->config->UPRO.".ueb_product_description");
			return $info['sku'];
		}else{
			return '';
		}
		
		
	}
	public function getSkuById($id){		
		$data=$this->config_new->getRow(array('where'=>"id = '".$id."'"),$this->config->UPRO.".ueb_product");
		return $data['sku'];
	}
	
}
?>