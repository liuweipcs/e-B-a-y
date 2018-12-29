<?php
/**
 *  Get TransactionDetails
 * 
 * @package Ueb.modules.services.components
 * @auther Tom 
 */
class GetTransactionDetails extends PaypalApiAbstract {  
	protected $_transactionId = '';
    /**
     * set request
     */
    public function setRequest(){
        $args = array(
            'Version'              => $this->version,
            'TransactionID'		   => $this->getTransactionId(),
        );

        $getBalanceRequest = new stdClass();
        
        $getBalanceRequest->GetTransactionDetailsRequest = new SoapVar($args, 
                SOAP_ENC_OBJECT, 'GetTransactionDetailsRequestType', 'urn:ebay:api:PayPalAPI');    

   
        $request = new SoapVar($getBalanceRequest, SOAP_ENC_OBJECT, 'GetTransactionDetailsRequest');
	
        $this->request = $request; 
        
        return $this;
   
    } 

    public function getTransactionId(){
    	return $this->_transactionId;
    }
    
    public function setTransactionId($transactionId){
    	$this->_transactionId = $transactionId;
    	return $this;
    }
    
    /**
     * to get detail for transaction
     * @param String $transactionId
     * @param String $paypalAccountId
     * @return Object
     */
    public function getDetailByTransactionId($transactionId,$paypalAccountId){
    	$respone = $this->setTransactionId($transactionId)
	    				->setEmail($paypalAccountId)
	    				->setRequest()
	    				->sendHttpRequest()
	    				->getResponse();
    	return $respone;
    }
    
}
?>