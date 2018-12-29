<?php

class Ebaygetitemapi extends EbayApiAbstract {
    
    protected $itemid;
    
    protected $sku;
    
    public function setItemid($itemid) {
        $this->itemid = $itemid;
    }
    
    public function setsku($sku) {
        $this->sku = $sku;
    }
    
    public function setRequest() {
        if(empty($this->itemid) && empty($this->sku)) {
            return array('status'=>500, 'msg'=>'没有输入ItemID或SKU');    
        }
        
        $request = array(
            'RequesterCredentials' => array(
                'eBayAuthToken' => $this->getUserToken(),
            ),
            'ErrorLanguage'=>'en_US',
            'WarningLevel'=>'High',
            'DetailLevel' => 'ReturnAll',
            'IncludeItemSpecifics'=>'true',
        );
        
        if($this->itemid) {
            $request['ItemID'] = $this->itemid;
        }
           
        if($this->sku) {
            $request['SKU'] = $this->sku;
        }

        $this->request = $request;
        return $this;
    }
    
    public function requestXmlBody() {
        $xmlObj = parent::getXmlGeneratorObj();
        $xmlObj->XmlWriter()
        ->push('GetItem', array( 'xmlns' => 'urn:ebay:apis:eBLBaseComponents'))
        ->buildXMLFilter($this->getRequest())
        ->pop();
        return $xmlObj->getXml();
    }
    
}