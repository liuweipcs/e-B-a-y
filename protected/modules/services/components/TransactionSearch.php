<?php
/**
 *  TransactionSearch
 * 
 * @package Ueb.modules.services.components
 * @auther Tom 
 */
class TransactionSearch extends PaypalApiAbstract {  

    /**
     * set request
     */
    public function setRequest() {
        $args = array(
            'Version'              => $this->version,
        );
        
        $getTransactionRequest = new stdClass();
        
        $getTransactionRequest->TransactionSearchRequest = new SoapVar($args, 
                SOAP_ENC_OBJECT, 'TransactionSearchRequestType', 'urn:ebay:api:PayPalAPI');    
                 
        $request = new SoapVar($getTransactionRequest, SOAP_ENC_OBJECT,'TransactionSearchRequest');
        
        $this->request = $request; 
        
        return $this;
   
    }   
}
?>