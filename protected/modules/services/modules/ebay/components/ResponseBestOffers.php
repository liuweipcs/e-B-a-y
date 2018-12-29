<?php

class ResponseBestOffers extends EbayApiAbstract {
    
    protected $action;
    protected $bestofferid;
    protected $itemid;
    protected $price;
    protected $quantity;
    protected $currency;
    
    public function setAction($action) {
        $this->action = $action;
    }
    
    public function setBestOfferId($bestofferid) {
        $this->bestofferid = $bestofferid;
    }
    
    public function setItemid($itemid) {
        $this->itemid = $itemid;
    }
    
    public function setprice($price) {
        $this->price = $price;
    }
    
    public function setquantity($quantity) {
        $this->quantity = $quantity;
    }
    
    public function setCurrency($currency) {
        $this->currency = $currency;
    }
    
    public function setfujia($msg) {
        $this->msg = $msg;
    }
    
    
    public function setRequest() {
        $request = array(
            'RequesterCredentials'=>array(
                'eBayAuthToken'=>$this->getUserToken(),
            ),
            
            'ItemID'=>$this->itemid,
        );
        
        if($this->action == 'Accept') {
            $request['Action'] = 'Accept';
            $request['BestOfferID'] = $this->bestofferid['0'];
        }
        
        if($this->action == 'Counter') {
            $request['Action'] = 'Counter';
            $request['BestOfferID'] = $this->bestofferid['0'];
            $request['CounterOfferPrice'] = $this->price;
            $request['CounterOfferQuantity'] = $this->quantity;
            if(!empty($this->msg)) {
                $request['SellerResponse'] = $this->msg;
            }
        }
        
        if($this->action == 'Decline') {
            $request['Action'] = 'Decline';
            $request['BestOfferID'] = $this->bestofferid['0'];
        }
//        echo json_encode(array('status'=>500, 'msg'=>$request));exit();
        $this->request = $request;
        return $this;
    }
    
    public function requestXmlBody() {
        $xmlObj = parent::getXmlGeneratorObj();
        $xmlObj->XmlWriter()
            ->push('RespondToBestOffer', array( 'xmlns' => 'urn:ebay:apis:eBLBaseComponents'))
            ->buildXMLFilter($this->getRequest())
            ->pop();
        if($this->currency) {
           $xmlObj->XmlWriter()
            ->push('RespondToBestOffer', array( 'xmlns' => 'urn:ebay:apis:eBLBaseComponents'))
            ->buildXMLFilter($this->getRequest(), '', array('CounterOfferPrice'=>array('name'=>'currencyID', 'value'=>$this->currency)))
            ->pop();
        }
        return $xmlObj->getXml();
    }
}