<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/8/18 0018
 * Time: 下午 5:27
 * 必需设置 $xmlTagArray、$siteID（在父类中）、TradingAPI::setUserTokenByAccount()|TradingAPI::setUserToken(),可选设置$callback、$customDate
 */
class TradingAPI extends EbayApiAbstract
{
    public $xmlTagArray;        //组装xml的数组
    public $callback;          //发送请求后，回调函数。
    public $customDate;       //自定义数据，类不会使用，可以供$callback调用时使用
    protected $sendXml;
    public $ebayAccountModel;

    public function setUserTokenByAccount($id)
    {
        if(is_numeric($id) && $id > 0 && $id%1 === 0)
            $this->ebayAccountModel = UebModel::model('Ebay')->findByPk((int)$id);
        elseif (is_string($id))
            $this->ebayAccountModel = UebModel::model('Ebay')->find('user_name=:user_name',array(':user_name'=>$id));
        if(empty($this->ebayAccountModel))
            throw new Exception('未查到ebay账号.');
        $this->setUserToken($this->ebayAccountModel->user_token);
    }

    public function requestXmlBody()
    {
        $this->sendXml = '<?xml version="1.0" encoding="utf-8" ?>'.$this->arrayTransformXml($this->xmlTagArray);
        return $this->sendXml;
    }

    protected function arrayTransformXml($array,$level = 0)
    {
        $xml = '';
        $nextLevel = $level + 1;
        foreach ($array as $tagName=>$tagContent)
        {
            if(isset($tagContent['value_not_tag']))
            {
                $attributes = '';
                $hasXmlns = false;
                if(isset($tagContent['attributes_not_tag']))
                {
                    foreach($tagContent['attributes_not_tag'] as $attributeK => $attributeV)
                    {
                        if($attributeK == 'xmlns')
                        {
                            $hasXmlns = true;
                        }
                        $attributes .= "{$attributeK}='{$attributeV}' ";
                    }
                }
                if(is_array($tagContent['value_not_tag']))
                {
                    if(self::isAssociativeArray($tagContent['value_not_tag']))
                    {
                        if($level === 0)
                        {
                            if(!$hasXmlns)
                                $attributes .= 'xmlns="urn:ebay:apis:eBLBaseComponents"';
                            $xml .= "<{$tagName} $attributes><RequesterCredentials><eBayAuthToken>{$this->_userToken}</eBayAuthToken></RequesterCredentials>".$this->arrayTransformXml($tagContent['value_not_tag'],$nextLevel)."</{$tagName}>";
                        }
                        else
                            $xml .= "<{$tagName} $attributes>".$this->arrayTransformXml($tagContent['value_not_tag'],$nextLevel)."</{$tagName}>";
                    }
                    else
                    {
                        foreach ($tagContent['value_not_tag'] as $contentValue)
                        {
                            if(is_array($contentValue))
                            {
                                $xml .= "<{$tagName} $attributes>".$this->arrayTransformXml($contentValue,$nextLevel)."</{$tagName}>";
                            }
                            else
                            {
                                $xml .= "<{$tagName} $attributes>{$contentValue}</{$tagName}>";
                            }
                        }
                    }
                }
                else
                {
                    if($level === 0)
                    {
                        if(!$hasXmlns)
                            $attributes .= 'xmlns="urn:ebay:apis:eBLBaseComponents"';
                        $xml .= "<{$tagName} $attributes>{$tagContent['value_not_tag']}</{$tagName}>";
                    }
                    else
                        $xml .= "<{$tagName} $attributes>{$tagContent['value_not_tag']}</{$tagName}>";
                }
            }
            else
            {
                if(is_array($tagContent))
                {
                    if(self::isAssociativeArray($tagContent))
                    {
                        if($level === 0)
                            $xml .= "<{$tagName} xmlns=\"urn:ebay:apis:eBLBaseComponents\"><RequesterCredentials><eBayAuthToken>{$this->_userToken}</eBayAuthToken></RequesterCredentials>".$this->arrayTransformXml($tagContent,$nextLevel)."</{$tagName}>";
                        else
                            $xml .= "<{$tagName}>".$this->arrayTransformXml($tagContent,$nextLevel)."</{$tagName}>";

                    }
                    else
                    {
                        foreach ($tagContent as $contentValue)
                        {
                            if(is_array($contentValue))
                            {
                                $xml .= "<{$tagName}>".$this->arrayTransformXml($contentValue,$nextLevel)."</{$tagName}>";
                            }
                            else
                            {
                                $xml .= "<{$tagName}>{$contentValue}</{$tagName}>";
                            }
                        }
                    }
                }
                else
                {
                    if($level === 0)
                        $xml .= "<{$tagName} xmlns=\"urn:ebay:apis:eBLBaseComponents\">{$tagContent}</{$tagName}>";
                    else
                        $xml .= "<{$tagName}>{$tagContent}</{$tagName}>";
                }
            }
        }
        return $xml;
    }

    //是关联数组返回true,不是返回false
    public static function isAssociativeArray($array)
    {
        return !is_numeric(implode('',array_keys($array)));
    }


    public function send()
    {
        $this->setRequest()->sendHttpRequest();
        if(is_callable($this->callback))
            return call_user_func($this->callback,$this);
        else
            return $this;
    }

    public function sendViaHK()
    {
        $sendData = $this->setRequest()->getSendAllData();
        $sendData['header'] = serialize($sendData['header']);
        return json_decode(ReplaceThirdResource::pushData($sendData,'http://47.52.147.59/ebayTradingAPI.php'),true);
    }

    public function setRequest($production = true)
    {
        $ebayKeys = ConfigFactory::getConfig('ebayKeys');
        $this->appID = $ebayKeys['appID'];
        $this->devID = $ebayKeys['devID'];
        $this->certID = $ebayKeys['certID'];
        $this->serverUrl = $ebayKeys['serverUrl'];
        $this->compatabilityLevel = 983;
        $this->verb = substr(key($this->xmlTagArray),0,-7);
        return $this;
    }
}