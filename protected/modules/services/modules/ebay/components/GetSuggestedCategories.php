<?php
class GetSuggestedCategories extends EbayApiAbstract {
    protected $_keyWord;
    
    public function setCategoryKeyword($keyWord){
        $this->_keyWord = $keyWord;
    }
    
    public function setRequest() {
        $request = array(
            'RequesterCredentials' => array(
                'eBayAuthToken' => $this->getUserToken(),
            ),
                 
            'Query'=>$this->_keyWord,
            'DetailLevel' => 'ReturnAll',
        );
    
        $this->request = $request;
        return $this;
    }
    
    public function requestXmlBody() {
        $xmlObj = parent::getXmlGeneratorObj();
        $xmlObj->XmlWriter()
        ->push('GetSuggestedCategories', array( 'xmlns' => 'urn:ebay:apis:eBLBaseComponents'))
        ->buildXMLFilter($this->getRequest())
        ->pop();
        return $xmlObj->getXml();
    }
}