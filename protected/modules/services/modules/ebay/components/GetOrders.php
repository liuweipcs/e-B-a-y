<?php
/**
 *  get orders 
 * 
 * @package Ueb.modules.services.modules.ebay.components
 * @auther Bob <zunfengke@gmail.com>
 */
class GetOrders extends EbayApiAbstract {
	protected $_entriesPerPage = 10; 
  	protected $_pageNumber = 1; 
  	protected $_moveTimeFrom = 0;
  	protected $_moveTimeTo = 0; 
  	protected $_orderIDArray = array();
  	/**
  	 * 
  	 * set the orders' num  of per catched 
  	 * @param $entriesPerPage
  	 */
  	public function setEntriesPerPage($entriesPerPage){
  		$this->_entriesPerPage = $entriesPerPage;
  	}

  	
  	/**
  	 * 
  	 * set current page to catch
  	 * @param $pageNumber
  	 */
  	public function setPageNumber($pageNumber){
  		$this->_pageNumber = $pageNumber;
  	}
  	  	
  	/**
  	 * 
  	 * get current page to catch
  	 */
	public function getPageNumber(){
		return $this->_pageNumber;
	}
  	
	/**
	 * 
	 * get advance time of move time from
	 */
	public function getMoveTimeFrom(){
		return $this->_moveTimeFrom;
	}
	
	/**
	 * 
	 * get advance time of move time to
	 */
	public function getMoveTimeTo(){
		return $this->_moveTimeTo;
	}

	public function getOrderIDArray(){
		return $this->_orderIDArray;
	}
	
	
	/**
	 * 
	 * set advance time of move time from
	 * @param String $time
	 */	
	public function setMoveTimeFrom($time){
		$this->_moveTimeFrom = date('Y-m-d\TH:i:s\Z',$time);	
	}
	
	/**
	 * 
	 * set advance time of move time to
	 * @param String $time
	 */
	public function setMoveTimeTo($time){
		$this->_moveTimeTo = date('Y-m-d\TH:i:s\Z',$time);
	}
	
	public function setOrderIDArray($orderIdArr){
		//buildXMLFilter
		$xmlObj = parent::getXmlGeneratorObj();
		$this->_orderIDArray = $xmlObj->_buildXMLFilter($orderIdArr, 'OrderID');
	}
	
	
    /**
     * set request
     */
    public function setRequest() {
        $request = array(
            'RequesterCredentials' => array(
                'eBayAuthToken' => $this->getUserToken(),
            ),
            'DetailLevel' => 'ReturnAll',
            'IncludeFinalValueFee' => 'true',
            'OrderRole' => 'Seller',
            'Pagination' => array(
                'EntriesPerPage' => $this->_entriesPerPage,
                'PageNumber' => $this->_pageNumber,
            ),
        );
		if(!($this->getMoveTimeFrom())){
			$request['OrderIDArray'] = $this->getOrderIDArray();
		}else{
			$request['ModTimeFrom']   = $this->getMoveTimeFrom();
			$request['ModTimeTo'] = $this->getMoveTimeTo();
		}        

        $this->request = $request;
	
        return $this;
    }
    
    /**
     * request xml body
     */
    public function requestXmlBody() {        
        $xmlObj = parent::getXmlGeneratorObj();
        $xmlObj->XmlWriter()
            ->push('GetOrdersRequest', array( 'xmlns' => 'urn:ebay:apis:eBLBaseComponents'))       
            ->buildXMLFilter($this->getRequest()) 	  		
            ->pop();

            
		return $xmlObj->getXml(); 
    }
}
?>