<?php
/**
 * @desc 利润计算器类
 * @author Fun
 * @since 2017-02-09
 */
class ProfitCalculator {
    /**
     * @desc 货币CODE
     * @var string
     */
    protected $currencyCode = null;
    
    /**
     * @desc 总收入
     * @var string
     */
    protected $totalRevenue = null;
    
    /**
     * @desc 销售价格
     * @var float
     */
    protected $salePrice = null;

    /**
     * @desc 调整金额
     * @var float
     */
    protected $adjustAmount = null;    
    
    /**
     * @desc 收取运费
     * @var float
     */
    protected $shippingPrice = null;
    
    /**
     * @desc 采购成本
     * @var float
     */
    protected $purchaseCost = null;
    
    /**
     * @desc 运费成本
     * @var float
     */
    protected $shippingCost = null;
    
    /**
     * @desc 平台佣金
     * @var float
     */
    protected $platformCost = null;
    
    /**
     * @desc 产品上架费
     * @var float
     */
    protected $listingCost = null;
    
    /**
     * @desc 支付交易费
     * @var float
     */
    protected $payCost = null;
    
    /**
     * @desc 包材SKU
     * @var string
     */
    protected $packingSku = null;
    
    /**
     * @desc 包材成本
     * @var float
     */
    protected $packingCost = null;
    
    /**
     * @desc 包材重量
     * @var unknown
     */
    protected $packingWeight = null;
    
    /**
     * @desc 包装SKU
     * @var string
     */
    public $packageSku = null;
    
    /**
     * @desc 包装成本
     * @var float
     */
    protected $packageCost = null;
    
    /**
     * @desc 包装重量
     * @varfloat
     */
    protected $packageWeight = null;
    
    /**
     * @desc 仓储成本
     * @var float
     */
    protected $storageCost = null;
    
    /**
     * @desc 其他分摊成本
     * @var float
     */
    protected $otherCost = null;
    
    /**
     * @desc 利润
     * @var float
     */
    protected $profit = null;
    
    /**
     * @desc 利润率
     * @var float
     */
    protected $profitRate = null;

    /**
     * @desc 利润
     * @var float
     */
    protected $isOversea = null;
    
    /**
     * @desc 平台CODE
     * @var float
     */
    protected $platformCode = null;
    
    /**
     * @desc 平台类目名
     * @var string
     */
    public $platformCategory = null;
    
    /**
     * @desc 平台费率比例
     * @var float
     */
    public $platformRate = null;
    
    /**
     * @desc 运输方式CODE
     * @var string
     */
    protected $shippingCode = null;
    
    /**
     * @desc 仓库CODE
     * @var string
     */
    protected $warehouseCode = null;
    
    /**
     * @desc 产品或者订单重量
     * @var float
     */
    protected $totalWeight = null;
    
    /**
     * @desc 产品SKU
     * @var string
     */
    protected $sku = null;
    
    /**
     * @desc 产品重量
     * @var float
     */
    protected $productWeight = null;
    
    /**
     * @desc 产品体积
     * @var float
     */
    protected $productVolume = null;
    
    /**
     * @desc 总体积
     * @var float
     */
    protected $totalVolume = null;
    
    /**
     * @desc 发货国家
     * @var string
     */
    protected $shippingCountry = null;

    protected $currencyRate = null;
    
    /**
     * @desc 头程运费
     * @var unknown
     */
    protected $firstCarrierCost = null;
    
    /**
     * @desc 关税费用
     * @var unknown
     */
    protected $dutyCost = null;
    
    /**
     * @desc 站点
     * @var unknown
     */
    protected $accountId = null;
    
    /**
     * @desc 毛利率详细
     * @var unknown
     */
    protected $_summary = null;
    
    /**
     * @desc 退款金额
     * @var unknown
     */
    protected $_refundAmount = null;
    
    /**
     * @desc 重寄成本
     * @var unknown
     */
    protected $_redirectCost = null;
    
    /**
     * @desc 异常消息
     * @var string
     */
    protected $exceptionMessage = null;
    
    /**
     * @desc 结算货币
     */
    protected $calCurrencyCode = null;
    
/*     const PLATFORM_CODE_EBAY = 'EB';
    const PLATFORM_CODE_AMAZON = 'AMAZON';
    const PLATFORM_CODE_ALIEXPRESS = 'ALI';
    const PLATFORM_CODE_LAZADA = 'LAZADA';
    const PLATFORM_CODE_SHOPEE = 'SHOPEE';
    const PLATFORM_CODE_CDISCOUNT = 'CDISCOUNT';
    const PLATFORM_CODE_WISH = 'WISH';
    const PLATFORM_CODE_WALMART = 'WALMART'; */
    /**
     * @desc 利润计算模板
     * @var string
     */
/*     protected $profitTemplate = '({salePrice} + {shippingPrice}) - 
        {purchaseCost} - {shippingCost} - {platformCost} -
        {listingCost} - {payCost} - {packingCost} - {packageCost}-
        {storageCost} - {otherCost}'; */
    protected $profitTemplate = '{totalRevenue} - {purchaseCost} - {shippingCost} - {firstCarrierCost} - 
        {dutyCost} - {platformCost} - {payCost} - {refundAmount} - {redirectCost} - {packingCost} - {packageCost}';
    
    /**
     * @desc 产品缓存数据
     * @var array
     */
    protected static $_skuDataCache = array();
    
    /**
     * @desc 计算利润
     * @param unknown $params
     * @return boolean|unknown
     */
    public function calculateProfit($params) {
        try {
            //根据传递进来的参数设置对于的属性
            $this->setParams($params);
    /*         if (is_null($this->salePrice)) {
                $this->exceptionMessage = Yii::t('profit_calculator', 'Sale Price is Required');
                return false;
            }
            if (is_null($this->shippingPrice)) {
                $this->exceptionMessage = Yii::t('profit_calculator', 'Shipping Price is Required');
                return false;
            }    */
            $profitPlatform = $this->profitTemplate;
            if (empty($profitPlatform)) {
                $this->exceptionMessage = Yii::t('profit_calculator', 'Profit Templat is Required');   
                return false;
            }
            $totalRevenue = $this->_calculateTotalRevenue();
            if ($totalRevenue === false)
                return false;
            $template = $this->profitTemplate;
            $pattern = '/{(\w+)}/';
            $matchs = array();
            $seachArr = array();
            $properties = array();
            $replaceArr = array();
            if (!preg_match_all($pattern, $template, $matchs)) {
                $this->exceptionMessage = Yii::t('profit_calculator', 'Profit Template is Invalid');
                return false;
            }
            $seachArr = $matchs[0];
            $properties = $matchs[1];
            foreach ($properties as $property) {
                $methodName = '_calculate' . ucfirst($property);
                if (!method_exists($this, $methodName)) {
                    $this->exceptionMessage = Yii::t('profit_calculator', 'Profit Template is Invalid');
                    return false;
                }
                $value = call_user_func(array($this, $methodName));
                if ($value === false)
                    return false;
                $value = floatval($value);
                $replaceArr[] = $value;
                $this->addSummary($property, $value . '(' . $this->calCurrencyCode . ')');
            }
            $profitStr = str_replace($seachArr, $replaceArr, $template);
            $profit = eval("return $profitStr;");
            if ($profit === false) {
                $this->exceptionMessage = Yii::t('profit_calculator', 'Calculate Profit Failed');
                return false;
            }
            $profitStr .= ' = ' . $profit . '(' . $this->calCurrencyCode . ')';
            $this->addSummary('profit', $profitStr);
            $profitRate = round($profit / $totalRevenue * 100, 2);
            $this->profit = $profit;
            $this->profitRate = $profitRate;
            return $profit;
        } catch (Exception $e) {
            $this->exceptionMessage = $e->getMessage();
            return false;
        }
    }
    
    /**
     * @desc 获取卖价
     * @return float
     */
    public function getSalePrice() {
        return $this->salePrice;
    }

    /**
     * @desc 获取退款金额
     * @return float
     */
    public function getRefundAmount() {
        return $this->_refundAmount;
    }

    /**
     * @desc 获取重寄费用
     * @return float
     */
    public function getRediectCost() {
        return $this->_redirectCost;
    }
    
    /**
     * @desc 获取收取运费
     * @return float
     */
    public function getShippingPrice() {
        return $this->shippingPrice;
    }
    
    /**
     * @desc 获取采购成本
     * @return float|Ambigous <boolean, float>
     */
    public function getPurchaseCost() {
        return $this->purchaseCost;
    }

    /**
     * @desc 获取货币CODE
     * @return float|Ambigous <boolean, float>
     */
    public function getCurrencyCode() {
        return $this->currencyCode;
    }

    /**
     * @desc 获取头程运费
     * @return float|Ambigous <boolean, float>
     */
    public function getFirstCarrierCost() {
        return $this->firstCarrierCost;
    }
    
    /**
     * @desc 获取关税
     * @return float|Ambigous <boolean, float>
     */
    public function getDutyCost() {
        return $this->dutyCost;
    }
    
    /**
     * @desc 获取调整金额
     * @return float|Ambigous <boolean, float>
     */
    public function getAdjustAmount() {
        return $this->adjustAmount;
    }    
    
    /**
     * @desc 获取货币汇率
     * @return float|Ambigous <boolean, float>
     */
    public function getCurrencyRate() {
        return $this->currencyRate;
    }

    /**
     * @desc 获取是否为海外仓订单
     * @return float|Ambigous <boolean, float>
     */
    public function getIsOversea() {
        return $this->isOversea;
    }
    
    /**
     * @desc 添加利润摘要
     * @param unknown $property
     * @param unknown $value
     */
    public function addSummary($property, $value) {
        $turnLineTag = "\n";
        $summaryLables = array(
            'salePrice' => Yii::t('profit_calculator', 'Sale Price'),
            'totalRevenue' => Yii::t('profit_calculator', 'Total Revenue'),
            'purchaseCost' => Yii::t('profit_calculator', 'Purchase Cost'),
            'shippingCost' => Yii::t('profit_calculator', 'Shipping Cost'),
            'platformCost' => Yii::t('profit_calculator', 'Platform Cost'),
            'packingCost' => Yii::t('profit_calculator', 'Packing Cost'),
            'packageCost' => Yii::t('profit_calculator', 'Package Cost'),
            'profit' => Yii::t('profit_calculator', 'Profit'),
            'payCost' => Yii::t('profit_calculator', 'Pay Cost'),
            'shippingPrice' => Yii::t('profit_calculator', 'Shipping Price'),
            'redirectCost' => Yii::t('profit_calculator', 'Redirect Cost'),
            'refundAmount' => Yii::t('profit_calculator', 'Refund Amount'),
            'adjustAmount' => Yii::t('profit_calculator', 'Adjust Amount'),
            'firstCarrierCost' => Yii::t('profit_calculator', 'First Carrier Cost'),
            'dutyCost' => Yii::t('profit_calculator', 'Duty Cost'),
        );
        $label = isset($summaryLables[$property]) ? $summaryLables[$property] : $property;
        $this->_summary .= $label . $value . $turnLineTag;
    }
    
    /**
     * @desc 获取利润摘要
     * @return unknown
     */
    public function getSummary() {
        return $this->_summary;
    }
    
    /**
     * @desc 获取运费成本
     * @return float
     */
    public function getShippingCost() {
        return $this->shippingCost;
    }
    
    /**
     * @desc 计算卖价
     * @return float
     */
    protected function _calculateSalePrice() {
        return $this->salePrice;
    }
    
    /**
     * @desc 计算运费
     * @return float
     */
    protected function _calculateShippingPrice() {
        return $this->shippingPrice;
    }

    /**
     * @desc 计算头程运费
     * @return float
     */
    protected function _calculateFirstCarrierCost() {
        return $this->firstCarrierCost;
    }
    
    /**
     * @desc 计算关税
     * @return float
     */
    protected function _calculateDutyCost() {
        return $this->dutyCost;
    }
    
    /**
     * @desc 计算退款金额
     * @return float
     */
    protected function _calculateRefundAmount() {
        return $this->refundAmount;
    }
    
    /**
     * @desc 计算重寄费用
     * @return float
     */
    protected function _calculateRedirectCost() {
        return $this->redirectCost;
    }
    
    /**
     * @desc 计算运费成本
     * @return boolean
     */
    protected function _calculateShippingCost() {
        if (!is_null($this->shippingCost))
            return $this->shippingCost;
        $totalWeight = $this->totalWeight;
        if (is_null($totalWeight)) {
            $this->exceptionMessage = Yii::t('profit_calculator', 'Total Weight is Required');
            return false;
        }
        $shippingCountry = $this->shippingCountry;
        if (empty($shippingCountry)) {
            $this->exceptionMessage = Yii::t('profit_calculator', 'Shipping Country is Required');
            return false;
        }
        $shippingCode = $this->shippingCode;
        if (empty($shippingCode)) {
            $this->exceptionMessage = Yii::t('profit_calculator', 'Shipping Code is Required');
            return false;
        }
        $totalVolume = $this->totalVolume ? $this->totalVolume : 0;
        $params = array(
            'weight' => $totalWeight,
            'countryname' => $shippingCountry,
            'ship_code' => $shippingCode,
            'volume' => $totalVolume,
        );
        //计算运费
        $shippingCostArr = Logistics::model()->getMinShippingInfo($totalWeight, $params);
        if (empty($shippingCostArr)) {
            $this->exceptionMessage = Yii::t('profit_calculateor', 'Calculate Shipping Cost Failed');
            return false;
        }
        
        //将运费转换成对应货币
        $shippingCost = $shippingCostArr['ship_cost'];
        $shippingCost = CurrencyConvertor::currencyConvert($shippingCost, CurrencyConvertor::CURRENCY_CNY, $this->currencyCode);
        $this->shippingCost = $shippingCost;
        return $this->shippingCost;
    }
    
    /**
     * @desc 计算采购成本
     * @return boolean|float
     */
    protected function _calculatePurchaseCost() {
        if (is_null($this->purchaseCost)) {
            $this->exceptionMessage = Yii::t('profit_calculator', 'Purchase Cost is Required');
            return false;
        }
        return $this->purchaseCost;
    }
    
    /**
     * @desc 获取平台佣金
     * @return float
     */
    public function getPlatformCost() {
        return $this->platformCost;
    }

    /**
     * @desc 获取平台CODE
     * @return float
     */
    public function getPlatformCode() {
        return $this->platformCode;
    }
    
    /**
     * @desc 获取上架费
     * @return float
     */
    public function getListingCost() {
        return $this->listingCost;
    }
    
    /**
     * @desc 获取支付交易费用
     * @return float|number
     */
    public function getPayCost() {
        return $this->payCost;
    }
    
    /**
     * @desc 获取包材成本
     * @return float
     */
    public function getPackingCost() {
        return $this->packingCost;
    }
    
    /**
     * @desc 获取包装成本
     * @return float
     */
    public function getPackageCost() {
        return $this->packageCost;
    }
    
    /**
     * @desc 获取库存成本
     * @return float|number
     */
    public function getStorageCost() {
        return $this->storageCost;
    }
    
    /**
     * @desc 获取其他成本
     * @return float
     */
    public function getOtherCost() {
        return $this->otherCost;
    }
    
    /**
     * @desc 计算包材成本
     * @return number
     */
    protected function _calculatePackingCost() {
        return $this->packingCost;
    }
    
    /**
     * @desc 计算包装成本
     * @return number
     */
    protected function _calculatePackageCost() {
        return $this->packageCost;
    }
    
    /**
     * @desc 计算库存成本
     * @return number
     */
    protected function _calculateStorageCost() {
        return 0.00;
    }
    
    /**
     * @desc 计算库存成本
     * @return number
     */
    protected function _calculateOtherCost() {
        return 0.00;
    }
    
    /**
     * @desc 计算平台费用
     * @return boolean
     */
    protected function _calculatePlatformCost() {
        //if (!empty($this->platformCost))
            return $this->platformCost;
/*         $platformCode = $this->platformCode;
        if (empty($platformCode)) {
            $this->exceptionMessage = Yii::t('profit_calculator', 'Platform Code is Required');
            return false;
        }
        switch ($platformCode) {
            case Platform::CODE_EBAY:
                return $this->_calculateEbayFees();
            case Platform::CODE_ALIEXPRESS:
                return $this->_calculateAliexpressFees();
            case Platform::CODE_AMAZON:
                return $this->_calculateAmazonFees();
            case Platform::CODE_LAZADA:
                return $this->_calculateLazadaFees();
            case Platform::CODE_WISH:
                return $this->_calculateWishFees();
            case Platform::CODE_SHOPEE:
                return $this->_calculateShopeeFees();
            case Platform::CODE_CDISCOUNT:
                return $this->_calculateCdiscountFees();
            case Platform::CODE_WALMART:
                return $this->_calculateWalmartFees();
            case Platform::CODE_WALMART:
                return $this->_calculateWalmartFees();                
            default:
                $this->exceptionMessage = Yii::t('profit_calculator', 'Platform Code is Invalid');
                return false; 
        }*/
    }
    
    /**
     * @desc 计算上架费
     * @return number
     */
    protected function _calculateListingCost() {
        return 0.00;
    }
    
    /**
     * @desc 计算支付费用
     * @return number
     */
    protected function _calculatePayCost() {
        return $this->payCost;
    }
    
    /**
     * @desc 设置所有传递进来的参数属性
     * @param unknown $params
     */
    public function setParams($params) {
        foreach ($params as $key => $value) {
            if (property_exists($this, $key)){
                $this->{$key} = trim($value);
            }
        }
        if (is_null($this->currencyCode)) {
            $this->exceptionMessage = Yii::t('profit_calculator', 'Currency Code is Required');
            return false;
        }
        if (is_null($this->calCurrencyCode))
            $this->calCurrencyCode = CurrencyConvertor::CURRENCY_CNY;
        return true;
    }
    
    /**
     * @desc 设置采购成本
     * @param unknown $cost
     */
    public function setPurchaseCost($cost) {
        $this->purchaseCost = $cost;
    }
    
    /**
     * @desc 计算ebay平台佣金
     * @return Ambigous <number, multitype:string>
     */
	protected function _calculateEbayFees(){
        $totalRevenue = $this->_calculateTotalRevenue();
        if ($totalRevenue === false)
            return false;
	    $currencyCode = $this->currencyCode;
	    $totalRevenueToUsd = CurrencyConvertor::currencyConvert($totalRevenue, $this->currencyCode, CurrencyConvertor::CURRENCY_USD);
	    $platformRateConfig = self::getEbayPlatformRateConfig();
	    $platformRate = 0.00;
	    $platformCost = 0.00;
	    foreach ($platformRateConfig as $config) {
	        $check = true;
	        $extra = 0;
	        if ($config['type'] != 'amount') continue;
	        if (isset($config['extra']))
	            $extra = floatval($config['extra']);
	        if (isset($config['max']))
	           $check = $totalRevenueToUsd < $config['max'];
	        if (isset($config['min']))
	            $check && $check = $totalRevenueToUsd >= $config['min'];
            if ($check) {
                $platformRate = $config['rate'];
                $platformCost = $totalRevenueToUsd * $platformRate + $extra;
            }
	    }
	    //转换回指定货币
	    $platformCost = CurrencyConvertor::currencyConvert($platformCost, CurrencyConvertor::CURRENCY_USD, $this->currencyCode);
	    $this->platformRate = $platformRate;
	    $this->platformCost = $platformCost;
	    return $platformCost;
        
	}
	
	/**
	 * @desc 获取订单利润率
	 * @return float
	 */
	public function getProfitRate() {
	    return $this->profitRate;
	}
	
	/**
	 * @desc 获取订单利润
	 * @return float
	 */
	public function getProfit() {
	    return $this->profit;
	}
	
	/**
	 * @desc 获取ebay平台费率配置
	 * @return multitype:multitype:string number
	 */
	public static function getEbayPlatformRateConfig() {
	    $rules = array(
	        array(
	            'type' => 'amount',
	            'max' => 8,
	            'min' => 0,
	            'rate' => 0.17
	        ),
	        array(
	            'type' => 'amount',
	            'min' => 8,
	            'rate' => 0.14,
	            'extra' => 0.3,    //额外添加0.3美金
	        ),
	    );
	    return $rules;
	}
	
	/**
	 * @desc 获取总收入
	 * @return string|boolean
	 */
	public function getTotalRevenue() {
	    return $this->_calculateTotalRevenue();
	}
	
	/**
	 * @desc 计算总收入
	 * @return boolean
	 */
	protected function _calculateTotalRevenue() {
	    if (!is_null($this->totalRevenue))
	        return $this->totalRevenue;
	    if (is_null($this->salePrice)) {
	        $this->exceptionMessage = Yii::t('profit_calculator', 'Sale Price is Required');
	        return false;
	    }
	    $salePrice = $this->salePrice;
	    if (is_null($this->shippingPrice)) {
	        $this->exceptionMessage = Yii::t('profit_calculator', 'Shipping Price is Required');
	        return false;
	    }
	    $adjustAmount = $this->adjustAmount;
	    if (empty($adjustAmount))
	        $adjustAmount = 0.00;
	    $shippingPrice = $this->shippingPrice;
	    $totalRevenue = $salePrice + $shippingPrice + $adjustAmount;
	    $this->totalRevenue = $totalRevenue;
	    return $totalRevenue;
	}
	
	/**
	 * @desc 计算aliexpress平台佣金
	 * @return boolean|number
	 */
	protected function _calculateAliexpressFees() {
	    $alexpressRate = 0.05;
        $totalRevenue = $this->_calculateTotalRevenue();
        if ($totalRevenue === false)
            return false;
        $platformCost = $totalRevenue * $alexpressRate;
	    $this->platformRate = $alexpressRate;
	    $this->platformCost = $platformCost;
	    return $platformCost;
	}
	
	/**
	 * @desc 计算AMAZON平台费用
	 * @return boolean|ProfitCalculator|Ambigous <number, float>
	 */
	protected function _calculateAmazonFees() {
        $totalRevenue = $this->_calculateTotalRevenue();
        if ($totalRevenue === false)
            return false;
        //将总收入转换为美元
        $totalRevenueToUsd = CurrencyConvertor::currencyConvert($totalRevenue, $this->calCurrencyCode, CurrencyConvertor::CURRENCY_USD);
        if (is_null($this->platformCategory)) {
            $this->exceptionMessage = Yii::t('profit_calculator', 'Platform Category is Required');
            return $this;
        }
        $platformCagegory = $this->platformCategory;
        $extraFees = 0.00;
        $platformRate = null;
        $platformRateConfig = self::getAmazonPlatformRateConfig();
        $rules = $platformRateConfig['rules'];
        $defaultRate = $platformRateConfig['default'];
        $platformCost = 0.00;
        $platformRate = 0;
        foreach ($rules as $rule) {
            $categoryList = $rule['categoryList'];
            if (!in_array($platformCagegory, $categoryList)) continue;
            if (isset($rule['ruleChains'])) {
                foreach ($rule['ruleChains'] as $row) {
                    $check = true;
                    $extraFees = 0;
                    $minFees = 0;
                    if ($row['type'] != 'amount') continue;
                    if (isset($row['extraFees']))
                        $extraFees = floatval($row['extraFees']);
                    if (isset($row['minFees']))
                        $minFees = floatval($row['minFees']);
                    if (isset($row['max']))
                        $check = $totalRevenueToUsd <= $row['max'];
                    if (isset($config['min']))
                        $check && $check = $totalRevenueToUsd > $row['min'];
                    if ($check) {
                        $platformRate = $row['rate'];
                        $platformCost = $totalRevenueToUsd * $platformRate + $extraFees;
                        $platformCost = $platformCost < $minFees ? $minFees : $platformCost;
                    }                    
                }
            }
        }
        //将平台费用转换回指定货币
        $platformCost = CurrencyConvertor::currencyConvert($platformCost, CurrencyConvertor::CURRENCY_USD, $this->currencyCode);
	    $this->platformRate = $platformRate;
	    $this->platformCost = $platformCost;
	    return $platformCost;
	}
	
	/**
	 * @desc 获取amzon平台费率配置
	 * @return array
	 */
	public static function getAmazonPlatformRateConfig() {
	    $rateConfig = array(
	        'rules' => array(
	            array(
    	            'categoryList' => array('Amazon Kindle', 'Baby Products', 
    	            'Books', 'Home & Garden', 'Music', 'Musical Instruments',
    	            'Office Products', 'Shoes', 'Handbags', 'Sunglasses', 
    	            'Software & Computer Games', 'Sporting Goods', 'Toys',
    	            'Video & DVD', 'Video Games', 'Video Game Consoles',
    	            'Watches'),
    	            'rate' => 0.15,
	            ),
    	        array(
    	            'categoryList' => array('Automotive Parts and Accessories', 
    	            'Industrial & Scientific', 'Tools & Home Improvement'),
    	            'rate' => 0.12,
    	        ),
    	        array(
    	            'categoryList' => array('Camera and Photo', 'Consumer Electronics',
    	            'Unlocked Cell Phones', ),
    	            'rate' => 0.08,	            
    	        ),
    	        array(
    	            'categoryList' => array('Electronics Accessories'),
    	            'ruleChains' => array(
    	                array(
    	                    'max' => 100,
    	                    'rate' => 0.15,
    	                    'minFees' => 1,
    	                ),
    	                array(
    	                    'min' => 100,
    	                    'rate' => 0.008,
    	                ),
    	            )
    	        ),
    	        array(
    	            'categoryList' => array('Entertainment Collectibles', 'Sports Collectibles'),
    	            'ruleChains' => array(
    	                array(
    	                    'max' => 100,
    	                    'rate' => 0.20,
    	                    'minFees' => 1,
    	                ),
    	                array(
    	                    'max' => 1000,
    	                    'min' => 100,
    	                    'rate' => 0.01,
    	                ),
    	                array(
    	                    'min' => 1000,
    	                    'rate' => 0.06,
    	                ),	                
    	            )
    	        ),
    	        array(
    	            'categoryList' => array('Kindle Accessories'),
    	            'rate' => 0.25,
    	        ),
    	        array(
    	            'categoryList' => array('Personal Computers'),
    	            'rate' => 0.06,
    	        ),
    	        array(
    	            'categoryList' => array('Tires & Wheels'),
    	            'rate' => 0.10,
    	        ),
    	        array(
                    'categoryList' => array('default'),
    	            'rate' => 0.15
    	        )
	        ),
	        'default' => 0.15
	    );
	    return $rateConfig;
	}
	
	/**
	 * @desc 计算wish平台费用
	 * @return boolean|number
	 */
	protected function _calculateWishFees() {
	    $platformRate = 0.15;
        $totalRevenue = $this->_calculateTotalRevenue();
        if ($totalRevenue === false)
            return false;
        $platformCost = $totalRevenue * $platformRate;
        $this->platformRate = $platformRate;
        $this->platformCost = $platformCost;
        return $platformCost;
	}
	
	/**
	 * @desc 计算lazada平台费用
	 * @return boolean|number
	 */
	protected function _calculateLazadaFees() {
	    $platformCost = 0.00;
	    $platformRate = 0;
	    $totalRevenue = $this->_calculateTotalRevenue();
	    if ($totalRevenue === false)
	        return false;
	    if (!is_null($this->accountId) && ($accountInfo = LazadaAccount::model()->findByPk($this->accountId)))
	        $platformRate = self::getLazadaPlatformCostRate($accountInfo->country_code);
	    $platformCost = round($totalRevenue * ($platformRate * 100) / 100, 2);
	    $this->platformRate = $platformRate;
	    $this->platformCost = $platformCost;
	    return $platformCost;	    
	}

	/**
	 * @desc 计算lazada平台费用
	 * @return boolean|number
	 */
	protected function _calculateShopeeFees() {
	    $platformCost = 0.00;
	    $platformRate = 0;
	    $totalRevenue = $this->_calculateTotalRevenue();
	    if ($totalRevenue === false)
	        return false;
	    $this->platformRate = $platformRate;
	    $this->platformCost = $platformCost;
	    return $platformCost;
	}

	/**
	 * @desc 计算lazada平台费用
	 * @return boolean|number
	 */
	protected function _calculateCdiscountFees() {
	    $platformCost = 0.00;
	    $platformRate = 0;
	    $totalRevenue = $this->_calculateTotalRevenue();
	    if ($totalRevenue === false)
	        return false;
	    $this->platformRate = $platformRate;
	    $this->platformCost = $platformCost;
	    return $platformCost;
	}
		
	/**
	 * @desc 计算lazada平台费用
	 * @return boolean|number
	 */
	protected function _calculateWalmartFees() {
	    $platformCost = 0.00;
	    $platformRate = 0;
	    $totalRevenue = $this->_calculateTotalRevenue();
	    if ($totalRevenue === false)
	        return false;
	    $this->platformRate = $platformRate;
	    $this->platformCost = $platformCost;
	    return $platformCost;
	}	
	
	/**
	 * @desc 获取异常信息
	 * @return string
	 */
	public function getExceptionMessage() {
	    return $this->exceptionMessage;
	}
	
	/**
	 * @desc 获取LAZADA平台佣金费率
	 * @param unknown $amount
	 * @param unknown $siteCode
	 * @return float
	 */
	public static function getLazadaPlatformCostRate($siteCode)
	{
	    $rate = 0.10;
	    $siteCode = strtoupper($siteCode);
	    switch ($siteCode)
	    {
	        case 'ID':
	        case 'MY':
	        case 'VN':
	           $rate = 0.10;
	           break;
	        case 'PH':
	        case 'TH':
	        case 'SG':
	            $rate = 0.06;
	            break;
	        default:
	            $rate = 0.10;
	    }
	    return $rate;
	}
}