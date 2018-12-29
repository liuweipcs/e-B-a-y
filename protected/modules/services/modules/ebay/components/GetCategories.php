<?php
/**
 * Get Categories
 * 
 * @package Ueb.modules.services.modules.ebay.components
 * @author Gordon
 * @since 2014-07-24
 */
class GetCategories extends EbayApiAbstract {
	protected $_categorySiteID = 0; 
	protected $_categoryParent = 0;
	
    public function setCategoryParent($_categoryParent)
    {
        $this->_categoryParent = $_categoryParent;
    }

    public function setCategorySiteId($id){
		$this->_categorySiteID = $id;
	}
	
	
	
    /**
     * Send Request
     * @see ApiInterface::setRequest()
     */
    public function setRequest() {
        $request = array(
            'RequesterCredentials' => array(
                'eBayAuthToken' => $this->getUserToken(),
            ),
            'DetailLevel' => 'ReturnAll',
            'CategorySiteID' => $this->_categorySiteID,
        );
        
        if($this->_categoryParent) {
            $request['CategoryParent'] = $this->_categoryParent;
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
            ->push('GetCategoriesRequest', array( 'xmlns' => 'urn:ebay:apis:eBLBaseComponents'))       
            ->buildXMLFilter($this->getRequest()) 	  		
            ->pop();
		return $xmlObj->getXml(); 
    }
}
?>