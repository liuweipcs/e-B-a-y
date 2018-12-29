<?php
class GetCategorySpecificsBatch extends EbayApiAbstract {
    
    
    public function setRequest() {
        $request = array(
            'RequesterCredentials' => array(
                'eBayAuthToken' => $this->getUserToken(),
            ),
            
            'fileReferenceId' => '6468752087',
            'taskReferenceId'=> '6321271257'
        );
    
        $this->request = $request;
        return $this;
    }
    
    public function requestXmlBody() {
        $xmlObj = parent::getXmlGeneratorObj();
        $xmlObj->XmlWriter()
        ->push('downloadFile', array( 'xmlns' => 'http://www.ebay.com/marketplace/services'))
        ->buildXMLFilter($this->getRequest())
        ->pop();
        return $xmlObj->getXml();
    }
}