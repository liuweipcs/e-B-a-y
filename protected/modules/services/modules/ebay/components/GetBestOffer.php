<?php

class GetBestOffer extends EbayApiAbstract {
    
    protected $pagenum;
    protected $status;
    protected $itemid;
    
    public function setPageNum($pagenum) {
        $this->pagenum = $pagenum;
    }
    
    public function setStatus($status) {
        $this->status = $status;
    }
    
    public function setItemid($itemid) {
        $this->itemid = $itemid;
    }
    
    public function setRequest() {
        if(empty($this->pagenum)) {
            $this->pagenum = 1;
        }
        
        $request = array(
                    'RequesterCredentials'=>array(
                            'eBayAuthToken'=>$this->getUserToken(),
                     ),
                    'DetailLevel'=>'ReturnAll',
                    'BestOfferStatus'=>'Active',
                    'Pagination'=>array(
                                'EntriesPerPage'=>'199',
                                'PageNumber'=>$this->pagenum
                    ),
        );
        
        if($this->status) {
            $request['BestOfferStatus'] = $this->status;
        }
        
        if($this->itemid) {
            $request['ItemID'] = $this->itemid;
            $request['BestOfferStatus'] = 'All';
        }
        
        $this->request = $request;
        return $this;
    }
    
    public function requestXmlBody() {
        $xmlObj = parent::getXmlGeneratorObj();
        $xmlObj->XmlWriter()
        ->push('GetBestOffers', array( 'xmlns' => 'urn:ebay:apis:eBLBaseComponents'))
        ->buildXMLFilter($this->getRequest())
        ->pop();
        return $xmlObj->getXml();
    }
}