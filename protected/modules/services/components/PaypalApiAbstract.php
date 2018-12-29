<?php
/**
 *  paypal api abstract
 * 
 * @package Ueb.modules.services.modules.components
 * @auther Bob <zunfengke@gmail.com>
 */
abstract class PaypalApiAbstract implements ApiInterface {
    /**
     * @var string email
     */   
    protected $_email = null;   
    
    /**
     * @var type set request
     */
    public $request = null;

    /**
     * @var object response  
     */
    public $response = null;   
    
    
    /**
     * @var string api user name  
     */
    protected $_apiUserName = null; 
    
   /**
    * api password
    *
    * @var string
    */
   protected $_apiPassword = null;
    
   /**
    * @var api signature
    */
   protected $_apiSignature = null;
   
   /**
     * application id
     *
     * @var string
     */
    protected $_appId = null;
    
   /**
    * platform code
    *
    * @var string
    */
    public $platformCode = null;
    
    /**
     * request url
     * 
     * @var string 
     */
    public $serverUrl = null;
    
    /**
     * soap client
     * @var object | null
     */
    public $soapClient = null;
    
    public $version = null;
    
    public function __construct() {
    	
    }
    
    /**
     * soap connect
     */
    public function soapConnect() {
        try {
            if ( empty($this->soapClient) ) {
                $this->soapClient = new soapclient($this->serverUrl, array(
                   // 'location' => 'https://api-3t.paypal.com/2.0/',
                 	'location' => 'https://api-3t.sandbox.paypal.com/nvp',
                    'soap_version' => SOAP_1_1
                ));
            }
        } catch (Exception $e) {
        	echo $e->getMessage();
            Yii::ulog($e->getMessage(), $e->getCode(),
                    get_class($this), 'ebay', ULogger::LEVEL_ERROR);
        }

        return $this;
    }
    
    /**
     * set the $email
     * 
     * @param string $email
     * @return \EbayModel
     */
    public function setEmail($account_id) {
        
      	$accountInfo = PaypalAccount::getById($account_id);
        $paypalKeys = ConfigFactory::getConfig('paypalKeys');
        
        
        $this->_email = $accountInfo['email'];
        $this->_apiUserName = $accountInfo['api_user_name'];
        $this->_apiPassword = $accountInfo['api_password'];
        $this->_apiSignature = $accountInfo['api_signature'];        
        $this->_appId = $accountInfo['app_id'];
        $this->serverUrl = $paypalKeys['serverUrl']; 
        $this->version = $paypalKeys['version'];
        return $this;
    }
    
    /**
     * send http request
     * 
     * @return object 
     */
    public function sendHttpRequest() {
        $this->soapConnect();
		if ( empty($this->soapClient) ){
            return false;            
        }
        $this->_setSoapHeaders();
		$response = false;		
        $callName = $this->getCallName();
		try {			
			$response = $this->soapClient->$callName($this->getRequest());
            $this->response = $response;
        } catch (SoapFault $e) { 
           Yii::apiDbLog($this->soapClient->__getLastRequest(),$e->getCode(), get_class($this), 'ebay', ULogger::LEVEL_ERROR);         
        } catch (Exception $e) {
            Yii::apiDbLog($e->getMessage(), $e->getCode(), get_class($this), 'ebay', ULogger::LEVEL_ERROR);
		}		    
        return $this;
    }
    
    /**
     * set the soap headers
     */
    private function _setSoapHeaders() {
        $credentials = array(
            'Username'  => $this->_apiUserName,
            'Password'  => $this->_apiPassword,
            'Signature' => $this->_apiSignature,  
            'Subject'   => $this->_email
        );
        $credentialsObj = new stdClass();
        $credentialsObj->Credentials = new SoapVar($credentials, SOAP_ENC_OBJECT, 'Credentials');
                
        $headers = new SoapVar($credentialsObj, SOAP_ENC_OBJECT,'CustomSecurityHeaderType', 'urn:ebay:apis:eBLBaseComponents');
        
        $soapHeaders = new SoapHeader('urn:ebay:api:PayPalAPI', 'RequesterCredentials', $headers);
        
        $this->soapClient->__setSoapHeaders($soapHeaders);
    }

    /**
     * request call name
     */
    public function getCallName() {
        return get_class($this);
    }
    
    /**
     * get request
     * 
     * @throws Exception
     */
    public function getRequest() {
        if ( empty($this->request) )  {
            throw new Exception('The request is not allowed to be empty');
        }
        
        return $this->request;
    }
    
    /**
     * @return object get response
     */
    public function getResponse() {
        return $this->response;
    }
    
}

?>