<?php
/**
 * @desc 货币相关计算
 * @author Gordon
 * @since 2015-08-13
 */
class CurrencyCalculate{
    
    /**@var 销售平台*/
    public $plaformCode = null;
    
    /**@var 交易平台*/
    public $payPlatform = null;
    
    /**@var 卖价*/
    public $salePrice = null;
    
    /**@var 收取运费*/
    public $shippingPrice = null;
    
    /**@var 币种*/
    public $currency = null;
    
    /**@var SKU*/
    public $sku = null;
    
    /**@var 包材SKU*/
    public $skuPackageMaterial = null;
    
    /**@var 包装SKU*/
    public $skuPackageType = null;
    
    /**@var 产品成本*/
    public $productCost = null;
    
    /**@var 运费成本*/
    public $shipCost = null;
    
    /**@var 包材SKU成本*/
    public $skuPackageMaterialCost = null;
    
    /**@var 包装SKU成本*/
    public $skuPackageTypeCost = null;
    
    /**@var 产品重量(g)*/
    public $productWeight = null;
    
    /**@var 包材SKU重量*/
    public $skuPackageMaterialWeight = null;
    
    /**@var 包装SKU重量*/
    public $skuPackageTypeWeight = null;
    
    /**@var 销售平台费用*/
    public $platformCost = null;
    
    /**@var 销售平台费用比例*/
    public $platformRate = null;
    
    /**@var 交易平台费用*/
    public $payPlatformCost = null;
    
    /**@var 交易平台费用比例*/
    public $payplatformRate = null;
    
    /**@var 交易平台附加费用*/
    public $payplatformAddition = null;
    
    /**@var 利润*/
    public $profit = null;
    
    /**@var 利润率*/
    public $profitRate = null;
    
    /**@var 汇率*/
    public $rate = null;
    
    /**@var 账号*/
    public $accountID = null;
    
    /**@var sku信息*/
    public static $skuInfo = null;
    
    /**@var 报错信息*/
    public $errorMessage = null;
    
    /**@var 站点(只针对ebay)*/
    public $siteID = null;
    
    /**@var 分类名(与平台手续费挂钩)*/
    public $categoryName = null;
    
    /**@var 选择的运输方式code*/
    public $shipCode = null;
    
    /**@var 仓库ID*/
    public $warehouseID = null;
    
    /**@var 传进来的attributeid字符串，针对没有sku的利润计算 */
    public $attributeidArray = null;
    
    /**
     * @desc 获取利润
     */
    public function getProfit(){
        if( !$this->profit ){
            $flag = $this->calculateProfit();
        }
        return $flag===false ? false : $this->profit;
    }
    
    /**
     * @desc 获取利润率
     */
    public function getProfitRate(){
        if( !$this->profitRate ){
            $flag = $this->calculateProfit();
        }
        return $flag===false ? false : $this->profitRate;
    }
    
    /**
     * @desc 获取卖价
     */
    public function getSalePrice(){
        if( !$this->salePrice ){
            $flag = $this->calculateSalePrice();
        }
        return $flag===false ? false : $this->salePrice;
    }
    
    /**
     * @desc 获取收取运费
     */
    public function getShippingPrice(){
        return $this->shippingPrice ? $this->shippingPrice : 0;
    }
    
    /**
     * @desc 获取计算说明
     */
    public function getCalculateDescription(){
        $turnLine = '<br/>';
        $space = '&nbsp;&nbsp;';
        $openTagR = '<font style=color:red;font-weight:bold;>';
        $openTagG = '<font style=color:green;font-weight:bold;>';
        $closeTag = '</font>';
        return Yii::t('common', 'Sale Price').':'.$space.$openTagR.$this->getSalePrice().$closeTag.$turnLine
               .Yii::t('common', 'Shipping Price').':'.$space.$openTagR.$this->getShippingPrice().$closeTag.$turnLine
               .Yii::t('common', 'Profit').':'.$space.$this->getProfit().$turnLine
               .Yii::t('common', 'Profit Rate').':'.$space.$this->getProfitRate().$turnLine
               .Yii::t('common', 'Currency').':'.$space.$this->currency.$turnLine
               .Yii::t('common', 'Currency Rate').':'.$space.$this->getCurrencyRate().$turnLine
               .Yii::t('common', 'Logistics').':'.$space.Logistics::model()->getShipNameByShipCode($this->shipCode).$turnLine
               .Yii::t('common', 'Shipping Cost').':'.$space.$openTagG.$this->getShippingCost().$closeTag.$turnLine
               .Yii::t('common', 'Product Cost').':'.$space.$openTagG.$this->getProductCost().$closeTag.$turnLine
               .Yii::t('common', 'Platform Cost').':'.$space.$openTagG.$this->getPlatformCost().$closeTag.$turnLine
               .Yii::t('common', 'Pay Platform Cost').':'.$space.$openTagG.$this->getPayPlatformCost().$closeTag.$turnLine
               .Yii::t('common', 'Pay Platform Addition Cost').':'.$space.$openTagG.$this->getPayPlatformAdditionCost().$closeTag.$turnLine
               .Yii::t('common', 'Product Material Cost').':'.$space.$openTagG.$this->getProductPackageMaterialCost().$closeTag.$turnLine
               .Yii::t('common', 'Product Type Cost').':'.$space.$openTagG.$this->getProductPackageTypeCost().$closeTag.$turnLine;
    }
    
    
    /**
     * @desc 获取产品成本
     */
    public function getProductCost(){
        if( $this->productCost > 0 ){
            return $this->productCost;
        }elseif( $this->sku ){
            $skuInfo = $this->getSkuInfoBySku($this->sku);
            if( !empty($skuInfo) ){
                $this->productCost = $skuInfo['product_cost'];
                return $this->productCost;
            }
        }
        return false;
    }
    
    /**
     * @desc 获取产品重量
     * @return boolean
     */
    public function getProductWeight(){
        if( $this->productWeight > 0 ){
            return $this->productWeight;
        }elseif( $this->sku ){
            $skuInfo = $this->getSkuInfoBySku($this->sku);
            if( !empty($skuInfo) ){
                $this->productWeight = $skuInfo['gross_product_weight'] > 0 ? $skuInfo['gross_product_weight'] : $skuInfo['product_weight'];
                return $this->productWeight;
            }
        }
        return false;
    }
    
    /**
     * @desc 获取产品包材成本
     */
    public function getProductPackageMaterialCost(){
        if( $this->skuPackageMaterialCost===null ){
            $this->setProductPackageMaterial();
        }
        return $this->skuPackageMaterialCost;
    }
    
    /**
     * @desc 获取产品包材重量
     */
    public function getProductPackageMaterialWeight(){
        if( $this->skuPackageMaterialWeight===null ){
            $this->setProductPackageMaterial();
        }
        return $this->skuPackageMaterialWeight;
    }
    
    /**
     * @desc 获取产品包装成本
     */
    public function getProductPackageTypeCost(){
        if( $this->skuPackageTypeCost===null ){
            $this->setProductPackageType();
        }
        return $this->skuPackageTypeCost;
    }
    
    /**
     * @desc 获取产品包装重量
     */
    public function getProductPackageTypeWeight(){
        if( $this->skuPackageTypeWeight===null ){
            $this->setProductPackageType();
        }
        return $this->skuPackageTypeWeight;
    }
    
    /**
     * @desc 获取销售平台花费
     */
    public function getPlatformCost(){
        if( !$this->platformCost ){
            if($this->plaformCode){
                $this->getPlatformFee();
            }else{
                $this->platformCost = 0;
                $this->platformRate = 0;
            }
        }
        return $this->platformCost;
    }
    
    /**
     * @desc 获取销售平台收费比例
     */
    public function getPlatformCostRate($platformCode){
        if( !$this->platformRate ){
            if($this->plaformCode){
                $this->getPlatformFee();
            }else{
                $this->platformCost = 0;
                $this->platformRate = 0;
            }
        }
        return $this->platformRate;
    }
    
    /**
     * @desc 获取销售平台费用
     */
    public function getPlatformFee(){
        switch ($this->plaformCode){
            case Platform::CODE_EBAY:
                $fee = $this->getEbayFee(array(
                        'salePrice'     => $this->salePrice ? $this->salePrice : 0,
                        'shippingPrice' => $this->shippingPrice ? $this->shippingPrice : 0,
                        'categoryName'  => $this->categoryName,
                        'currency'      => $this->currency,
                        'rate'          => $this->getCurrencyRate(),
                ));
                $this->platformCost = $fee['fee'];
                $this->platformRate = $fee['rate'];
                break;
            case Platform::CODE_LAZADA:
                $fee = $this->getLazadaFee(array(
                        'salePrice'     => $this->salePrice ? $this->salePrice : 0,
                        'shippingPrice' => $this->shippingPrice ? $this->shippingPrice : 0,
                        'categoryName'  => $this->categoryName,
                        'currency'      => $this->currency,
                        'rate'          => $this->getCurrencyRate(),
                ));
                $this->platformCost = $fee['fee'];
                $this->platformRate = $fee['rate'];
                break;
            default:
                $this->platformCost = 0;
                $this->platformRate = 0;
                break;
        }
    }
    
    /**
     * @desc 获取支付平台费用比例
     */
    public function getPayPlatformCostRate(){
        if( $this->payPlatformRate===null ){
            if($this->payPlatform){
                $this->getPayPlatformFee();
            }else{
                $this->payPlatformCost = 0;
                $this->payplatformRate = 0;
                $this->payplatformAddition = 0;
            }
        }
        return $this->payplatformRate;
    }
    
    /**
     * @desc 获取支付平台费用
     */
    public function getPayPlatformCost(){
        if( $this->payPlatformCost===null ){
            if($this->payPlatform){
                $this->getPayPlatformFee();
            }else{
                $this->payPlatformCost = 0;
                $this->payplatformRate = 0;
                $this->payplatformAddition = 0;
            }
        }
        return $this->payPlatformCost;
    }
    
    /**
     * @desc 获取支付平台附加费用
     */
    public function getPayPlatformAdditionCost(){
        if( $this->payplatformAddition===null ){
            if($this->payPlatform){
                $this->getPayPlatformFee();
            }else{
                $this->payPlatformCost = 0;
                $this->payplatformRate = 0;
                $this->payplatformAddition = 0;
            }
        }
        return $this->payplatformAddition;
    }
    
    /**
     * @desc 获取交易平台费用
     */
    public function getPayPlatformFee(){
        switch ($this->payPlatform){
            case 'paypal':
                $fee = $this->getPaypalPayFee(array(
                        'totalAmount' 	=> $this->salePrice ? $this->salePrice : 0,
                        'rate' 			=> $this->getCurrencyRate(),
                        'price' 		=> $this->salePrice ? $this->salePrice : 0,
                ));
                $this->payPlatformCost = $fee['fee'];
                $this->payplatformRate = $fee['rate'];
                $this->payplatformAddition = $fee['addFee'];
                break;
            default:
                $this->payPlatformCost = 0;
                $this->payplatformRate = 0;
                $this->payplatformAddition = 0;
                break;
        }
    }
    
    /**
     * @desc 获取运输成本
     */
    public function getShippingCost(){
        //TODO 计算物流匹配规则
        //运行默认规则
        switch ($this->plaformCode){
            case Platform::CODE_LAZADA:
                $this->shipCost = $this->getLazadaShipCost();
                break;
            default:
                $this->shipCost = $this->getDefaultShipFee();
                break;
        }
        return $this->shipCost;
    }
    
    /**
     * @desc 获取默认规则运费
     */
    public function getDefaultShipFee(){
        $attributes = $this->sku ? ProductSelectAttribute::model()->getAttIdsBySku($this->sku,'') : array();
        $attributes = !empty($attributes) ? $attributes : ($this->attributeidArray ? $this->attributeidArray : array() );
        $weight = $this->productWeight + $this->skuPackageMaterialWeight + $this->skuPackageTypeWeight;
        return Logistics::model()->getShipFee(Logistics::CODE_COMMON, $weight, array(
                'countryname'   => 'United States',
                'attributeid'   => $attributes,
        ));
    }
    
    /**
     * @desc 获取默认规则运费
     */
    public function getLazadaShipCost(){
        $attributes = $this->sku ? Product::model()->getAttributeBySku($this->sku) : array();//属性
        $weight = $this->productWeight + $this->skuPackageMaterialWeight + $this->skuPackageTypeWeight;//重量
        $shipCodes = array(Logistics::CODE_GHXB_CN,Logistics::CODE_GHXB_SG,Logistics::CODE_GHXB);
        foreach($shipCodes as $shipCode){
            $shipFee = Logistics::model()->getShipFee($shipCode, $weight, array(
                'countryname'   => 'Malaysia',
                'attributeid'   => $attributes,
            ));
            if( $shipFee > 0 ){
                $this->shipCode = $shipCode;
                break;
            }
        }
        return $shipFee;
    }
    
    /**
     * @desc 获取转化为人民币的汇率
     */
    public function getCurrencyRate(){
        if(!$this->rate){
            $this->rate = CurrencyRate::model()->getRateToCny($this->currency);
        }
        return $this->rate;
    }
    
    /**
     * @desc 获取报错信息
     */
    public function getErrorMessage(){
        return $this->errorMessage;
    }
    
    /**
     * @desc 计算利润
     * @desc 计算公式: (卖价+运费-销售平台相关费用-支付平台相关费用-商品成本-运费成本-邮包成本)
     */
    private function calculateProfit(){
        $salePrice = $this->salePrice;//卖价
        if( !$salePrice ){ 
            $this->setErrorMessage('system', 'Sale Price Is Required');
            return false;
        }
        
        $shippingPrice = $this->shippingPrice ? $this->shippingPrice : 0;//收取运费
        $productCost = $this->getProductCost();//产品成本
        if( !$productCost ){
            $this->setErrorMessage('system', 'Product Cost OR SKU Is Required');
            return false;
        }
        $productPackageMaterialCost = $this->getProductPackageMaterialCost();//包材费用
        $productPackageMaterialCost = $productPackageMaterialCost ? $productPackageMaterialCost : 0;
        $productPackageTypeCost = $this->getProductPackageTypeCost();//包装费用
        $productPackageTypeCost = $productPackageTypeCost ? $productPackageTypeCost : 0;
        
        $shippingCost = $this->getShippingCost();
        if( !$shippingCost ){
            $this->setErrorMessage('system', 'Can Not Get Shipping Cost');
            return false;
        }
        $platformCost = $this->getPlatformCost();//销售平台费用
        $payPlatformCost = $this->getPayPlatformCost();//支付平台手续费
        $rateToCNY = $this->getCurrencyRate();
        //计算利润
        $this->profit = round(($salePrice*$rateToCNY + $shippingPrice*$rateToCNY - $platformCost - $payPlatformCost - $productCost - $productPackageMaterialCost - $productPackageTypeCost - $shippingCost), 2);
        $this->profitRate = round($this->profit / (($salePrice + $shippingPrice)*$rateToCNY), 3);
    }
    
    /**
     * @desc 计算卖价
     * 销量利润率 = (销售价-固定成本-销售价*(销售平台手续费比例+支付平台手续费比例))/销售价
	 * ----> 销售价 = 固定成本/((1-(销售平台手续费比例+支付平台手续费比例))-利润率))
	 * 固定成本 = 产品成本 + 运费成本 + 包装成本 + 包材成本
     */
    private function calculateSalePrice(){
        $profitRate = $this->profitRate;//利润率
        if( !$profitRate ){
            $this->setErrorMessage('system', 'Profit Rate Is Required');
            return false;
        }
        $shippingPrice = $this->shippingPrice ? $this->shippingPrice : 0;//收取运费
        $productCost = $this->getProductCost();//产品成本
        if( !$productCost ){
            $this->setErrorMessage('system', 'Product Cost OR SKU Is Required');
            return false;
        }
        $productPackageMaterialCost = $this->getProductPackageMaterialCost();//包材费用
        $productPackageMaterialCost = $productPackageMaterialCost ? $productPackageMaterialCost : 0;
        $productPackageTypeCost = $this->getProductPackageTypeCost();//包装费用
        $productPackageTypeCost = $productPackageTypeCost ? $productPackageTypeCost : 0;
        $shippingCost = $this->getShippingCost();
        if( !$shippingCost ){
            $this->setErrorMessage('system', 'Can Not Get Shipping Cost');
            return false;
        }
        $platformRate = $this->getPlatformCostRate();//销售平台费用比例
        $payPlatformCostRate = $this->getPayPlatformCostRate();//支付平台手续费比例
        $rateToCNY = $this->getCurrencyRate();
        $salePrice = ($productCost + $shippingCost + $productPackageMaterialCost + $productPackageTypeCost) / (1 - $platformRate - $payPlatformCostRate - $profitRate) / $rateToCNY;
        $this->salePrice = ceil($salePrice * 100) /100;
    }
    
    
    /**
     * @desc 设置产品包材信息
     * @return boolean
     */
    private function setProductPackageMaterial(){
        if( !$this->skuPackageMaterial ){
            if( $this->sku ){
                $skuInfo = $this->getSkuInfoBySku($this->sku);
                if( !empty($skuInfo) ){
                    $this->skuPackageMaterial = $skuInfo['product_pack_code'];
                }
            }
        }
        
        if( $this->skuPackageMaterial ){
            $productPackageMaterialInfo = $this->getSkuInfoBySku($this->skuPackageMaterial);
            if( !empty($productPackageMaterialInfo) ){
                $this->skuPackageMaterialCost = $productPackageMaterialInfo['product_cost'];
                $this->skuPackageMaterialWeight = $productPackageMaterialInfo['product_weight'];
            }
        }else{
            $this->skuPackageMaterialCost = 0;
            $this->skuPackageMaterialWeight = 0;
        }
        
    }
    
    /**
     * @desc 设置产品包装信息
     * @return boolean
     */
    private function setProductPackageType(){
        if( !$this->skuPackageType ){
            if( $this->sku ){
                $skuInfo = $this->getSkuInfoBySku($this->sku);
                if( !empty($skuInfo) ){
                    $this->skuPackageType = $skuInfo['product_package_code'];
                }
            }
        }
        if( $this->skuPackageType ){
            $productPackageTypeInfo = $this->getSkuInfoBySku($this->skuPackageType);
            if( !empty($productPackageTypeInfo) ){
                $this->skuPackageTypeCost = $productPackageTypeInfo['product_cost'];
                $this->skuPackageTypeWeight = $productPackageTypeInfo['product_weight'];
            }else{
                $this->skuPackageTypeCost = 0;
                $this->skuPackageTypeWeight = 0;
            }
        }
    }
    
    public function setErrorMessage($message){
        $this->errorMessage = $message;
    }
    
    /**
     * @desc 获取sku信息
     * @param string $sku
     */
    private function getSkuInfoBySku($sku){
        if( !isset(self::$skuInfo[$sku]) ){
            $skuInfo = Product::model()->getProductInfoBySku($sku);
            self::$skuInfo[$sku] = $skuInfo;
        }
        return self::$skuInfo[$sku];
    }
    
    /**
     * 获取ebay平台的交易费
     * @param array $params
     * $salePrice 卖价
     * $shippingCost 运费
     * $categoryName Listing分类
     * $currency 货币
     * $rate 汇率
     */
    public function getEbayFee( $params = array() ){
		extract($params);
		if( !isset($salePrice) )  throw new Exception( Yii::t('system','Sale Price').Yii::t('system','Is Required') );
		if( !isset($shippingPrice) )  throw new Exception( Yii::t('system','Shipping Cost').Yii::t('system','Is Required') );
		if( !isset($categoryName) )  throw new Exception( Yii::t('system','Category Name').Yii::t('system','Is Required') );
		if( !isset($currency) )  throw new Exception( Yii::t('system','Currency').Yii::t('system','Is Required') );
		$ebayFee = 0;
		$ebayFee = 0;
		$categoryRule = array();//成交费计算规则
		$categoryName = strtolower($categoryName);//将类名转化为小写
		$categoryArr = explode(':',$categoryName);//转化为数组
		
		switch ($currency){
			case 'USD':
				$ebayRate = 10/100;
				$ebayFee = ( floatval($salePrice) + floatval($shippingCost) ) * $ebayRate;
				break;
			case 'GBP':
				$categoryRule = array(
					'nofee' => array(
							'final_value_rate' => 0/100,
							'categories' => array(
									'property',
							),
					),
					'media' => array(
							'final_value_rate' => 9/100,
							'categories' => array(
									'books, comics & magazines',
									'dvd, film & tv',
									'music',
									'video games & consoles:games',
							),
					),
					'collectables' => array(
							'final_value_rate' => 9/100,
							'categories' => array(
									'antiques',
									'coins',
									'collectables',
									'sports memorabilia',
									'stamps',
									'art',
							),
					),
					'furniture, bath, holidays & travel' => array(
							'final_value_rate' => 10/100,
							'max_fee' => 40,
							'categories' => array(
									'home, furniture & diy:bath',
									'home, furniture & diy:furniture',
									'holidays & travel'
							),
					),
					'consumer electronics' => array(
							'final_value_rate' => 5/100,
							'max_fee' => 10,
							'categories' => array(
									'wholesale & job lots:consumer electronics',
							),
					),
					'vehicle parts & accessories' => array(
							'final_value_rate' => 8/100,
							'categories' => array(
									'vehicle parts & accessories'
							),
					),
					'watches' => array(
							'final_value_rate' => 11/100,
							'max_fee' => 50,
							'categories' => array(
									'jewellery & watches:watches',
							),
					),
					'clothes, shoes & accessories' => array(
							'final_value_rate' => 11/100,
							'categories' => array(
									'clothes, shoes & accessories',
									'jewellery & watches',
							),
					),
				);
				//计算成交费
				$hasCategoryRule = false;
				foreach ($categoryRule as $details){
					foreach ($details['categories'] as $detail){
						$detail = strtolower($detail);
						if( strpos($categoryName,$detail) === 0 ){//如果在最开头匹配到
							$hasCategoryRule = true;
							$ebayFee = $salePrice * $details['final_value_rate'];//通过此分类的成交费百分比算出成交费;
							if( $details['max_fee'] && $ebayFee > $details['max_fee'] ){
								$ebayFee = $details['max_fee'];
							}
							$ebayRate = $details['final_value_rate'];//成交费比例
							break 2;
						}
					}
				}
				if(!$hasCategoryRule && !$ebayFee){//没找到类型则为其它类型
					$ebayRate = 10/100;//成交费比例
					$ebayFee = $salePrice * $ebayRate;//其它类型的成交费百分比为10%
				}
				break;
			case 'AUD':
				$ebayRate = 9.5/100;
				$ebayFee = $salePrice * $ebayRate;
				break;
			case 'CAD':
				$ebayRate = 10/100;
				$ebayFee = $salePrice * $ebayRate;
				break;
			case 'EUR':
				$category_rule = array(
					array(
						'rule' => array(
							array(
									'start_fee' => 0.01,
									'end_fee' => 0,
									'final_value_rate' => 9/100,
							),
						),
						'categories' => array(
							'Filme & DVDs',
							'Musik',
							'PC- & Videospiele',
							'Tickets',
						),
					),
					array(
						'rule' => array(
							array(
								'start_fee' => 0.01,
								'end_fee' => 150.00,
								'final_value_rate' => 6/100,
							),
							array(
								'start_fee' => 150.01,
								'end_fee' => 0,
								'final_value_rate' => 0/100,
							),
						),
						'categories' => array(
							'Computer, Tablets & Netzwerk',
							'Haushaltsgeräte',
							'Foto & Camcorder',
							'Handys & Kommunikation',
							'Auto-Hi-Fi & Navigation',
							'TV, Video & Audio',
						),
					),
					array(
						'rule' => array(
							array(
								'start_fee' => 0.01,
								'end_fee' => 0,
								'final_value_rate' => 10/100,
							),
						),
						'categories' => array(
								'Auto & Motorrad: Teile',
						),
					),
					array(
						'rule' => array(
							array(
								'start_fee' => 0.01,
								'end_fee' => 0,
								'final_value_rate' => 5/100,
								'add_fee' => 19
							),
						),
						'categories' => array(
							'Auto & Motorrad: Fahrzeuge',
						),
					),
					array(
						'rule' => array(
							array(
								'start_fee' => 0.01,
								'end_fee' => 0,
								'final_value_rate' => 10/100,//拍卖为5%
							),
						),
						'categories' => array(
							'Antiquitäten & Kunst',
							'Sammeln & Seltenes',
							'Briefmarken',
							'Münzen',
						),
					),
					array(
						'rule' => array(
							array(
								'start_fee' => 0.01,
								'end_fee' => 0,
								'final_value_rate' => 11/100,
							),
						),
						'categories' => array(
							'Kleidung & Accessoires',
							'Uhren & Schmuck',
							'Bücher',
							'Spielzeug',
							'Baby',
							'Möbel & Wohnen',
							'Beauty & Gesundheit',
						),
					),
					array(
						'rule' => array(
							array(
								'start_fee' => 0.01,
								'end_fee' => 200,
								'final_value_rate' => 11/100,
							),
							array(
								'start_fee' => 200.01,
								'end_fee' => 0,
								'final_value_rate' => 0/100,
							),
						),
						'categories' => array(
								'Heimwerker'
						),
					),
					array(
						'rule' => array(
							array(
								'start_fee' => 0.01,
								'end_fee' => 0,
								'final_value_rate' => 11/100,
							),
							array(
								'start_fee' => 200.01,
								'end_fee' => 0,
								'final_value_rate' => 0/100,
							),
						),
						'categories' => array(
							'Garten & Terrasse'
						),
					),
					array(
						'rule' => array(
							array(
								'start_fee' => 0.01,
								'end_fee' => 0,
								'final_value_rate' => 12/100,
							),
							array(
								'start_fee' => 500.01,
								'end_fee' => 0,
								'final_value_rate' => 0/100,
							),
						),
						'categories' => array(
							'Uhren & Schmuck'
						),
					),
				);
				//其它分类的规则
				$otherRule = array(
					array(
						'start_fee' => 0.01,
						'end_fee' => 0,
						'final_value_rate' => 9/100,
					),
				);
				//计算成交费
				foreach ($categoryRule as $details){
					foreach ($details['categories'] as $detail){
						$detail = strtolower($detail);
						if(strpos($categoryName,$detail)===0){//如果在最开头匹配到
							$ebayFee = $this->calculateFinalValue($salePrice,$details['rule'],$ebayRate);//通过此分类的规则算出成交费;
							break 2;//跳出最外层
						}
					}
				}
				if( !$ebayFee ){//没找到类型则为其它类型
					$ebayFee = $this->calculateFinalValue($salePrice,$otherRule,$ebayRate);
				}
				break;
			default:
				$ebayRate = 10/100;
				$ebayFee = $salePrice * $ebayRate;
				break;
		}
		if( isset($rate) ){//如果有汇率
			$ebayFee = $ebayFee * $rate;//把最终的ebay成交费乘以汇率得到人民币
		}
		return array(
				'fee' 	=> $ebayFee,
				'rate' 	=> round($ebayRate, 2), 
		);	
	}
	
	/**
	 * @desc 获取lazada平台费用
	 * @param array $params
	 */
	public function getLazadaFee($params = array()){
	    extract($params);
	    if( $this->categoryName ){
	        
	    }else{
	        $lazadaRate = 0.1;
	    }
	    $lazadaFee = $salePrice * $lazadaRate;
	    if( isset($rate) ){//如果有汇率
	        $lazadaFee = $lazadaFee * $rate;//把最终的ebay成交费乘以汇率得到人民币
	    }
	    return array(
	        'fee' 	=> $lazadaFee,
	        'rate' 	=> round($lazadaRate, 2),
	    );
	}
	
	/**
	 * 获取paypal支付平台的交易费
	 * @param array $params
	 * $totalAmount 付款到paypal的金额(包括卖价和运费)
	 * $rate  当前的汇率(转人民币的)
	 * $price 该金额要付的手续费,默认不传则是total的值
	 */
	public function getPaypalPayFee( $params = array() ){
	    extract($params);
	    if( !isset($totalAmount) )  throw new Exception( Yii::t('system','Price').Yii::t('system','Is Required') );
	    if( !isset($price) ){
	        $price = $totalAmount;
	    }
	    if( $totalAmount < 10 ){
	        $paypalRate = 0.06;
	        $paypalAdd = 0.05;
	    }else{
	        $paypalRate = 0.032;
	        $paypalAdd = 0.3;
	    }
	    $fee = $price * $paypalRate + $paypalAdd;
	    if( isset($rate) ){
	        $fee = round($fee * $rate, 2);
	    }
	    return array(
	        'fee' 	=> $fee,
	        'rate' 	=> $paypalRate,
	        'addFee'=> $paypalAdd,
	    );
	}
	
	/**
	 * @desc 设置销售平台
	 * @param string $platformCode
	 */
	public function setPlatform($platformCode){
	    $this->plaformCode = $platformCode;
	}
	
	/**
	 * @desc 设置SKU
	 * @param string $platformCode
	 */
	public function setSku($sku){
	    $this->sku = $sku;
	    $skuInfo = $this->getSkuInfoBySku($sku);
	    if( !empty($skuInfo) ){
	        $this->productCost = $skuInfo['product_cost'];
	        $this->productWeight = $skuInfo['product_weight'];
	    }
	}
	
	/**
	 * @desc 设置币种
	 * @param char $currency
	 */
	public function setCurrency($currency){
	    $this->currency = $currency;
	}
	
	/**
	 * @desc 设置利润率
	 * @param unknown $profitRate
	 */
	public function setProfitRate($profitRate){
	    $this->profitRate = $profitRate;
	}
	
	/**
	 * @desc 设置国家名
	 * @param string $countryName
	 */
	public function setCountryName($countryName){
	    $this->countryName = $countryName;
	}
	
	/**
	 * @desc 用于获取头程运费
	 * @param int $warehouseID
	 */
	public function setWarehouseID($warehouseID){
	    $this->warehouseID = $warehouseID;
	}
	
	/**
	 * @desc 设置卖价
	 * @param float $salePrice
	 */
	public function setSalePrice($salePrice){
	    $this->salePrice = $salePrice;
	}
	
	/**
	 * @desc 设置产品成本
	 * @param float $productCost
	 */
	public function setProductCost($productCost){
		$this->productCost = $productCost;
	}
	
	/**
	 * @desc 设置产品重量
	 * @param float $productWeight
	 */
	public function setProductWeight($productWeight){
		$this->productWeight = $productWeight;
	}
	
	/**
	 * @desc 设置产品分类
	 * @param $categoryName
	 */
	public function setCategoryName($categoryName){
		$this->categoryName = $categoryName;
	}
	
	/**
	 * @desc 设置产品属性
	 * @param array $attributeidArray (e.g.: array(1,2,3,...) )
	 */
	public function setAttributeidArray($attributeidArray){
		$this->attributeidArray = $attributeidArray;
	}
	
	/**
	 * @desc 设置收取的运费
	 * @param float $shippingPrice
	 */
	public function setShippingPrice($shippingPrice){
		$this->shippingPrice = $shippingPrice;
	}
	
} 