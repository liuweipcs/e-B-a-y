<?php

class Ebaygetpromotionalsaleapi extends EbayApiAbstract {
    
    protected $param;
    
    public function setParam($param) {
        $this->param = $param;
    }
    
    public function setRequest() {
        $request = array(
            'RequesterCredentials' => array(
                'eBayAuthToken' => $this->getUserToken(),
            ),
            'ErrorLanguage'=>'en_US',
        );       
        
        if(!empty($this->param)) {
            $request = array_merge($request, $this->param);
        }
        
        $this->request = $request;
        return $this;
    }
    
    /**
     * Request XML Body
     */
    public function requestXmlBody() {
        
        $xmlObj = parent::getXmlGeneratorObj();
        $xmlObj->XmlWriter()
        ->push('GetPromotionalSaleDetails', array( 'xmlns' => 'urn:ebay:apis:eBLBaseComponents'))
        ->buildXMLFilter($this->getRequest())
        ->pop();
        
        return $xmlObj->getXml();
        
        
    }
}