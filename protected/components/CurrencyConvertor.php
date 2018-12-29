<?php
/**
 * @desc 货币转换器
 * @author Fun
 *
 */
class CurrencyConvertor {
    
    const CURRENCY_CNY = 'CNY';
    const CURRENCY_USD = 'USD';
    
    /**
     * @desc 货币汇率值
     * @var unknown
     */
    protected static $_currencyRates = array();
    
    /**
     * @desc 添加货币汇率值
     * @param string $fromCurrencyCode
     * @param string $toCurrencyCode
     * @param unknown $rate
     */
    public static function addCurrencyRate($fromCurrency = 'CYN', $toCurrency = 'CYN', $exchageRate) {
        $rate = floatval($rate);
        $key = self::getKey($fromCurrency, $toCurrency);
        if (!empty($key))
            self::$_currencyRates[$key] = $exchageRate;
    }
    
    /**
     * @desc 转换货币
     * @param unknown $amount
     * @param unknown $fromCurrency
     * @param unknown $toCurrency
     * @param number $precision
     * @return float
     */
    public static function currencyConvert($amount, $fromCurrency, $toCurrency, $precision = 4) {
        $amount = floatval($amount);
        $exchageRate = self::getExchageRate($fromCurrency, $toCurrency);
        $precision = (int)$precision;
        if ($precision > 4 || $precision < 0)
            $precision = 4;
        $exchageAmount = $amount * $exchageRate;
        return round($exchageAmount, $precision);
    }
    
    /**
     * @desc 获取货币间的兑换汇率
     * @param unknown $fromCurrency
     * @param unknown $toCurrency
     * @throws Exception
     * @return unknown|string
     */
    public static function getExchageRate($fromCurrency, $toCurrency) {
        $key = self::getKey($fromCurrency, $toCurrency);
        $exchageRate = '';
        if (array_key_exists($key, self::$_currencyRates)) {
            return self::$_currencyRates[$key];
        } else {
            if ($fromCurrency == $toCurrency)
                $exchageRate = 1;
            else {
                $exchageRate = UebModel::model('currencyRate')->getRateByCondition($fromCurrency, $toCurrency);
                if ($exchageRate === false)
                    throw new Exception(Yii::t('systems', 'Currency ' . $fromCurrency . '=>' . $toCurrency . ' Exchange Rate Not Found'));
            }                
            self::addCurrencyRate($exchageRate, $fromCurrency, $toCurrency);
        }
        return $exchageRate;
    }
    
    /**
     * @desc 获取缓存KEY
     * @param unknown $fromCurrency
     * @param unknown $toCurrency
     * @return Ambigous <string, string>
     */
    public static function getKey($fromCurrency, $toCurrency) {
        $key = '';
        if (!empty($fromCurrency) && empty($fromCurrency)) {
            $key = md5($fromCurrency . $fromCurrency);
        }
        return $key;          
    }
    
}