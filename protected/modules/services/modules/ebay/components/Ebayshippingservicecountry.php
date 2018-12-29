<?php

class Ebayshippingservicecountry extends EbayApiAbstract {
    
    public function setRequest() {
        $request = array(
            'RequesterCredentials'=>array(
                'eBayAuthToken'=>$this->getUserToken(),
            ),
            'DetailLevel'=>'ReturnAll',
            'DetailName'=>'ShippingLocationDetails'
        );
    
        $this->request = $request;
        return $this;
    }
    
    public function requestXmlBody() {
        $xmlObj = parent::getXmlGeneratorObj();
        $xmlObj->XmlWriter()
        ->push('GeteBayDetails', array( 'xmlns' => 'urn:ebay:apis:eBLBaseComponents'))
        ->buildXMLFilter($this->getRequest())
        ->pop();
        return $xmlObj->getXml();
    }
}