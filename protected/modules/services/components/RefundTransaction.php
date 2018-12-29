<?php
/**
 *  Get RefundTransaction
 * 
 * @package Ueb.modules.services.components
 * @auther Ethan 
 */
class RefundTransaction extends PaypalApiAbstract {
	const REFUND_STATUS_PART 	= 'Partial';//部分退款
	const REFUND_STATUS_ALL 	= 'Full';//全额退款
	
	protected $_transactionId = '';//交易号
	protected $_refundType = '';//全退or部分退,详细参数见上面定义常量
	//protected $_currency = '';
	protected $_amt = '';//部分退时退款额,全退时不用传
	protected $_note = '';//备注说明
	

    /**
     * set request
     */
    public function setRequest($param = array()){
    	if (empty($param)) return false;
    	$args = array(
            'Version'              => $this->version,
        );
    	foreach ($param as $key=>$val){
    		$args[$key] = $val;
    	}
        $getRefundRequest = new stdClass();
        
        $getRefundRequest->RefundTransactionRequest = new SoapVar($args,SOAP_ENC_OBJECT, 'RefundTransactionRequestType', 'urn:ebay:api:PayPalAPI');           
        $request = new SoapVar($getRefundRequest, SOAP_ENC_OBJECT, 'RefundTransactionRequest');
	
        $this->request = $request; 
        
        return $this;
   
    } 

    public function getTransactionId(){
    	return $this->_transactionId;
    }
    
    public function setTransactionId($transactionId){
    	$this->_transactionId = $transactionId;
    }
    /**
     * 
     * @param string $email:account id
     * @param array $refundArr
     * @return object
     */
    public function refundTransactions($account_id,$refundArr){
    	$arr =  $this->setEmail($account_id)
			    	->setRequest($refundArr)
			    	->sendHttpRequest()
    				->getResponse();
    	return $arr;
    }
    /**
	 * get returnType
     */
    public function getRefundType(){
    	return $this->_refundType;
    }
    /**
	*	set  refundType
	*@param string $returnType
     */
    public function setRefundType($returnType){
    	$this->_refundType = $returnType;
    }
    
}
?>