<?php
/**
 * @author  super
 * @package products.components
 * @since   2014-07-08
 */
class PackageType{
	
	public $packageTypes = array();
	public $commonPackageTypes = array();
	public $blebPackageTypes = array();
	
	public $commonPackage = '';
	public $blebPackage   = '';
	
	const PACKAGE_TYPE = 'package_type';//包裹的attribute_code
	
	function __construct(){
		$this->commonPackage = Yii::t('orderpackage','Common Package');
		$this->blebPackage   = Yii::t('orderpackage','Bubble Package');
		$this->getPackageTypes();
	}
	
	/**
	 * get attribute code
	 */
	public function getAttributeCode(){
		return ProductAttribute::PACKAGE_TYPE;
	}
	
	/**
	 * get all package type
	 * @param none
	 * @return array $packageTypes
	 */
	public function getPackageTypes(){
		if(empty($this->packageTypes)){
			$attributeCode = $this->getAttributeCode();			
			$packageAttributeId = UebModel::model('ProductAttribute')-> getAttributeIdByCode($attributeCode);
			$productIds = UebModel::model('ProductSelectAttribute')-> getProductIdByAttributeId($packageAttributeId);
			if($productIds){
				$packageTypeInfo = UebModel::model('Product')->getListPairsByIdArr($productIds);
				foreach($packageTypeInfo as $value){
					$packageAttributeValueId = UebModel::model('ProductSelectAttribute')->getAttributeBySku($value['sku'],$packageAttributeId);
					$packageAttributeValueId = isset($packageAttributeValueId[0]) ? $packageAttributeValueId[0] : '';
					$packageType = UebModel::model('Productdesc')->getProductCnTitleBySkuAndLanguageCode($value['sku']);
					$this->packageTypes[$packageType]['type']	 = $packageType; 
					$this->packageTypes[$packageType]['volume'] = $value['product_width']*$value['product_height']*$value['product_length'];
					$this->packageTypes[$packageType]['weight'] = $value['product_weight'];
					$this->packageTypes[$packageType]['cost']	 = $value['product_cost'];
					$this->packageTypes[$packageType]['attributeValueName'] = UebModel::model('ProductAttributeValue')->getAttributeValueNameById($packageAttributeValueId);
					if($this->packageTypes[$packageType]['attributeValueName'] == $this->commonPackage){
						$this->commonPackageTypes[$packageType] = $this->packageTypes[$packageType];
					}elseif($this->packageTypes[$packageType]['attributeValueName'] == $this->blebPackage){
						$this->blebPackageTypes[$packageType] = $this->packageTypes[$packageType];
					}
				}
			}
		}
		return $this->packageTypes;
	}
	
	/**
	 * 获取所有普通邮包类型
	 * @param NONE
	 * @return array $commonPackageTypes
	 */
	public function getCommonPackageTypes(){
		return $this->commonPackageTypes;
	}
	
	/**
	 * 获取所有气泡袋邮包类型
	 * @param NONE
	 * @return array @blebPackageTypes;
	 */
	public function getBlebPackageTypes(){
		return $this->blebPackageTypes;
	
	}
	
	/**
	 * get The relative volume for the package
	 * @param string $type
	 * @return number
	 */
	public function getPackageVolumeByType($type){
		return floatval($this->packageTypes[$type]['volume']);
	}
	
	/**
	 * get The weight for the package
	 * @param string $type
	 * @return number
	 */
	public function getPackageWeightByType($type){
		return $this->packageTypes[$type]['weight'];
	}
	
	/**
	 * get cost for the package
	 * @param string $type
	 * @return float
	 */
	public function getPackageCostByType($type){
		return $this->packageTypes[$type]['cost'];
	}
	
	/**
	 * 是否为气泡袋邮包
	 * @param string $type
	 * @return boolean
	 */
	public function isBlebPackge($type){
		return $this->packageTypes[$type]['attributeValueName'] == $this->blebPackage ? true : false;
	}
	/**
	 * get the best Bubble package By the Volume
	 * @param int $cubage
	 * @return string $packagetype
	 */
	public function getBestBlebPackageTypeByVolume($Volume){
		$packagetype = '';
		foreach ($this->getBlebPackageTypes() as $detail){
			if($detail['volume'] >= $Volume){
				$packagetype = $detail['type'];
				break;
			}
		}
		return $packagetype;
	}
	
	/**
	 * get the best common package By the Volume
	 * @param int $cubage
	 * @return string $packagetype
	 */
	public function getBestCommonPackageTypeByVolume($Volume){
		$packagetype = '';
		foreach ($this->getCommonPackageTypes() as $detail){
			if($detail['volume'] >= $Volume){
				$packagetype = $detail['type'];
				break;
			}
		}
		return $packagetype;
	}
	
	/**
	 * get the max package by the package type
	 * @param string $type
	 * @return string $packagetype
	 */
	public function getMaxPackageTypeByType($type){
		$packagetype = '';
		$Volume = 0;
		if($type == $this->commonPackage){
			$packageTypes = $this->getCommonPackageTypes();
		}elseif($type == $this->blebPackage){
			$packageTypes = $this->getBlebPackageTypes();
		}
		foreach ($packageTypes as $detail){
			if($detail['volume'] > $Volume){
				$packagetype = $detail['type'];
			}
		}
		return $packagetype;
	}

	
	/**
	 * get package type by sku
	 * @param string $sku
	 * @return string
	 */
	public function getProductPackageType($sku){
		if(isset($sku) && $sku){
			$productInfo =  UebModel::model('product')->getInfoBySku($sku);
			$packageType = UebModel::model('Productdesc')->getProductCnTitleBySkuAndLanguageCode($productInfo['product_package_code']);
			return $packageType;
		}else{
			return '';
		}
	}
	
	/**
	 * 通过产品列表获取邮包类型
	 * @param array $info
	 * $info = array(
	 *	  array('sku'=>'1001','quantity'=>2),
	 *    array('sku'=>'1002','quantity'=>5),
	 *);
	 *@return
	 */
function microtime_float()
{
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
}
public function getListPackageType($info){
		$arr1 = $this->microtime_float();
		static $type_arr = array();
		if(count($info) == 1){
			if(!empty($type_arr[$info[0]['sku'].$info[0]['quantity']])){
				return $type_arr[$info[0]['sku'].$info[0]['quantity']];
			}else{
				if($info[0]['quantity'] == 1){
					$packageType = $this->getProductPackageType($info[0]['sku']);	
				}else{
					$productInfo =  UebModel::model('product')->getInfoBySku($info[0]['sku']);
					if($productInfo['product_package_max_nums'] >= 1){
						$packageType = '';
					}else{
						$packageType = $this->getProductPackageType($info[0]['sku']);
						$packageType = $info[0]['quantity'].$packageType;
					}
				}
				$type_arr[$info[0]['sku'].$info[0]['quantity']] = $packageType;
			}
		}
		
		if($packageType == ''){//多个计算
			$volume = 0;
			$hasBleb = false;
			foreach ($info as $key=>$detail){
				$productInfo =  UebModel::model('product')->getInfoBySku($info[$key]['sku']);
				if(intval($productInfo['product_package_max_nums'])<=0){
					$volume = 0; break;
				}
				$type = $this->getProductPackageType($detail['sku']);
				$volume += $this->getPackageVolumeByType($type)/intval($productInfo['product_package_max_nums'])*intval($detail['quantity']);
				if($hasBleb === false){
					$hasBleb = $this->isBlebPackge($type);
				}
			}
			if($volume>0){
				if($hasBleb){
					$packageType = $this->getBestBlebPackageTypeByVolume($volume);
					if($packageType == ''){
						$type = $this->blebPackage;
						$packageType = $this->getMaxPackageTypeByType($type);
					}
					
				}else{
					$packageType = $this->getBestCommonPackageTypeByVolume($volume);
					if($packageType == ''){
						$type = $this->commonPackage;
						$packageType = $this->getMaxPackageTypeByType($type);
					}
				}
			}
		}
		$arr2 = $this->microtime_float();
		//echo '所用时间'.($arr2-$arr1).'<br/>';
		
		return $packageType;
	}
	
}