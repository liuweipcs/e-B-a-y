<?php

class Ebaygetsellerlistapi extends EbayApiAbstract {
    
    protected $endTimeFrom;
    
    protected $endTimeTo;
    
    protected $startTimefrom;
    
    protected $startTimeto;
    
    protected $pagenum;
    
    public function setEndTimeFrom($endTimeFrom) {
        $this->endTimeFrom = $endTimeFrom;
    }
    
    public function setEndTimeTo($endTimeTo) {
        $this->endTimeTo = $endTimeTo;
    }
    
    public function setStartFrom($startTimefrom) {
        $this->startTimefrom = $startTimefrom;
    }
    
    public function setStartTo($startTimeto) {
        $this->startTimeto = $startTimeto;
    }
    
    public function setPageNum($pagenum) {
        $this->pagenum = $pagenum;
    }
    
    public function setRequest() {
        $request = array(
            'RequesterCredentials' => array(
                'eBayAuthToken' => $this->getUserToken(),
            ),
            'ErrorLanguage'=>'en_US',
            'WarningLevel'=>'High',
            'DetailLevel' => 'ReturnAll',
            'IncludeVariations'=>'true',
            'Pagination'=>array(
                    'EntriesPerPage'=>'100',
                    'PageNumber'=>empty($this->pagenum)?1:$this->pagenum,
            ),
        );
        
        if($this->endTimeFrom) {
            $request['EndTimeFrom'] = $this->endTimeFrom;
            
            if($this->endTimeTo) {
                $request['EndTimeTo'] = $this->endTimeTo;
            } else {
                $request['EndTimeTo'] = date('Y-m-d H:i:s', strtotime('+32day'));
            }   
        }
        
        if($this->startTimefrom) {
            $request['StartTimeFrom'] = $this->startTimefrom;
        }
        
        if($this->startTimeto) {
            $request['StartTimeTo'] = $this->startTimeto;
        }
        
        $this->request = $request;
        return $this;
    }
    
    
    public function requestXmlBody() {
        $xmlObj = parent::getXmlGeneratorObj();
        $xmlObj->XmlWriter()
            ->push('GetSellerList', array( 'xmlns' => 'urn:ebay:apis:eBLBaseComponents'))
            ->buildXMLFilter($this->getRequest())
            ->pop();
        return $xmlObj->getXml();
    }
    
    
}