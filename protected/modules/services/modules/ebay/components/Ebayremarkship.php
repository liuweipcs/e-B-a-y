<?php

class Ebayremarkship extends EbayApiAbstract {
    
    protected $itemid;
    protected $transactionid;
    protected $tracknumber;
    protected $carrier;
    
    public function setItemId($itemid) {
        $this->itemid = $itemid;
    }
    
    public function setTransaction($transactionid) {
        $this->transactionid = $transactionid;
    }
    
    public function setTrackNumber($tracknumber) {
        $this->tracknumber = $tracknumber;
    }
    
    public function setCarrier($carrier) {
        $this->carrier = $carrier;
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
            'ItemID' => $this->itemid,
            'TransactionID' => $this->transactionid,
            'Shipped'=>'true',
        );
        if($this->tracknumber && $this->carrier) {
            $request['Shipment'] = array(
                        'ShipmentTrackingDetails' => array(
                            'ShipmentTrackingNumber' => $this->tracknumber,
                            'ShippingCarrierUsed' => $this->carrier
                        )
            );
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
        ->push('CompleteSale', array( 'xmlns' => 'urn:ebay:apis:eBLBaseComponents'))
        ->buildXMLFilter($this->getRequest())
        ->pop();
        return $xmlObj->getXml();
    }
}