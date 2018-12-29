<?php

class Ebayenditems extends EbayApiAbstract {
    
    public $itemid;
    
    public function setitemid($itemid) {
        $this->itemid = $itemid;
    } 
    
    
    public function setRequest() {
        $request = array(
            'RequesterCredentials' => array(
                'eBayAuthToken' => $this->getUserToken(),
            ),
            'DetailLevel' => 'ReturnAll',
            'EndingReason'=>'OtherListingError',
            'ItemID' => $this->itemid
        );
    
        $this->request = $request;
        return $this;
    }
    
    public function requestXmlBody() {
        $xmlObj = parent::getXmlGeneratorObj();
        $xmlObj->XmlWriter()
        ->push('EndFixedPriceItem', array( 'xmlns' => 'urn:ebay:apis:eBLBaseComponents'))
        ->buildXMLFilter($this->getRequest())
        ->pop();
        return $xmlObj->getXml();
    }
}