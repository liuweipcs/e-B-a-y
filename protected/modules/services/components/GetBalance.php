<?php
/**
 *  get balance
 * 
 * @package Ueb.modules.services.components
 * @auther Bob <zunfengke@gmail.com>
 */
class GetBalance extends PaypalApiAbstract {  

    /**
     * set request
     */
    public function setRequest() {
        $args = array(
            'Version'              => $this->version,
            'ReturnAllCurrencies'  => '1'
        );
        
        $getBalanceRequest = new stdClass();
        
        $getBalanceRequest->GetBalanceRequest = new SoapVar($args, 
                SOAP_ENC_OBJECT, 'GetBalanceRequestType', 'urn:ebay:api:PayPalAPI');    
                 
        $request = new SoapVar($getBalanceRequest, SOAP_ENC_OBJECT, 'GetBalanceRequest');
        $this->request = $request; 
        
        return $this;
   
    }   
}
?>