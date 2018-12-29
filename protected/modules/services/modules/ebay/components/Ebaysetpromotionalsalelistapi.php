<?php

class Ebaysetpromotionalsalelistapi extends EbayApiAbstract {
    
    protected $param;
    
    public function setParam($param) {
        $this->param = $param;
    }
    
    public function setRequest() {
        $request = array(
            'RequesterCredentials' => array(
                'eBayAuthToken' => $this->getUserToken(),
            ),
        );
    
        $request = array_merge($request, $this->param);
    
        $this->request = $request;
        return $this;
    }
    
    /**
     * Request XML Body
     */
    public function requestXmlBody() {
        $xmlObj = parent::getXmlGeneratorObj();
        $xmlObj->XmlWriter()
        ->push('SetPromotionalSaleListings', array('xmlns' => 'urn:ebay:apis:eBLBaseComponents'))
        ->buildXMLFilter($this->getRequest())
        ->pop();
        return $xmlObj->getXml();
    }
}