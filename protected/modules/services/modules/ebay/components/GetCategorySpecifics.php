<?php
class GetCategorySpecifics extends EbayApiAbstract {
    protected $_categoryID;
    
    public function setCategoryId($id){
        $this->_categoryID = $id;
    }
    
    public function setRequest() {
        $request = array(
            'RequesterCredentials' => array(
                'eBayAuthToken' => $this->getUserToken(),
            ), 
            'CategoryID'=>$this->_categoryID,
            'DetailLevel' => 'ReturnAll',
//            'CategorySpecificsFileInfo'=>true
        );
    
        $this->request = $request;
        return $this;
    }
    
    public function requestXmlBody() {
        $xmlObj = parent::getXmlGeneratorObj();
        $xmlObj->XmlWriter()
        ->push('GetCategorySpecifics', array( 'xmlns' => 'urn:ebay:apis:eBLBaseComponents'))
        ->buildXMLFilter($this->getRequest())
        ->pop();
        return $xmlObj->getXml();
    }
}