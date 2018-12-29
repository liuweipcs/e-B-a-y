<?php

class Ebayreviseinventoryapi extends EbayApiAbstract {
    
    public $param;
    public function setParam($param) {
        $this->param = $param;
    }
    
    public function setRequest() {
        $request = array(
            'RequesterCredentials' => array(
                'eBayAuthToken' => $this->getUserToken(),
            ),
            'ErrorLanguage'=>'en_US',
            'WarningLevel'=>'High',
            'DetailLevel' => 'ReturnAll',
            'InventoryStatus'=>$this->param
        );

        $this->request = $request;
        return $this;
    }
    
    public function requestXmlBody() {
        $xmlObj = parent::getXmlGeneratorObj();
        $xmlObj->XmlWriter()
            ->push('ReviseInventoryStatus', array( 'xmlns' => 'urn:ebay:apis:eBLBaseComponents'))
            ->buildXMLFilter($this->getRequest())
            ->pop();
        return $xmlObj->getXml();
    }
}