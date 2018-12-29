<?php

class Ebayrelistingapi extends EbayApiAbstract {
    
    public $itemid;
    
    public $qty;
    
    public function setitemid($itemid) {
        $this->itemid = $itemid;
    }
    
    public function setQty($qty) {
        $this->qty = $qty;
    }
    
    public function setRequest() {
        $request = array(
            'RequesterCredentials' => array(
                'eBayAuthToken' => $this->getUserToken(),
            ),
            'ErrorLanguage'=>'en_US',
            'WarningLevel'=>'High',
            'DetailLevel' => 'ReturnAll',
            'Item'=>array(
                'ItemID'=>$this->itemid,
            ),
        );
        
        if($this->qty > 0) {
            $request['Item']['Quantity'] = $this->qty;
        }
        
        $this->request = $request;
        return $this;
    }
    
    public function requestXmlBody() {
        $xmlObj = parent::getXmlGeneratorObj();
        $xmlObj->XmlWriter()
            ->push('RelistFixedPriceItem', array( 'xmlns' => 'urn:ebay:apis:eBLBaseComponents'))
            ->buildXMLFilter($this->getRequest())
            ->pop();
        return $xmlObj->getXml();
    }
}