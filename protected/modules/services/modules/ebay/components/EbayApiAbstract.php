<?php
/**
 *  ebay api abstract
 * 
 * @package Ueb.modules.services.modules.aliexpress.components
 * @auther Bob <zunfengke@gmail.com>
 */
abstract class EbayApiAbstract implements ApiInterface {
    /**
     * @var string short name 
     */   
    protected $_shortName = null;
    /**
     * @var string usertoken    
     */
    protected $_userToken = null;
    /**
     * @var object request xml body 
     */
    public $requestXmlBody = null;  
    
    /**
     * @var type set request
     */
    public $request = null;

    /**
     * @var object response  
     */
    public $response = null;   
    
   /**
    * developer ID
    *
    * If you do not already have one, please
    * apply for a developer ID at http://developer.ebay.com
    *
    * @var string
    */
    public $devID = null;
    
   /**
    * application id
    *
    * @var string
    */
    public $appID = null;
    
   /**
    * application id
    *
    * @var certificate ID
    */
    public $certID = null;
    
   /**
    * site id
    *
    * @var integer
    */
    public $siteID = 0;
    
    /**
     * compatibility level
     *
     * @var integer
     */
    public $compatabilityLevel = null;
    
    
    public $verb = null;
    
    /**
     * request url
     * 
     * @var string 
     */
    public $serverUrl = null;

    public function __construct() {}
    
    /**
     * set the short name
     * 
     * @param string $shortName
     * @return \EbayModel
     */
    public function setShortName($shortName) {
        $this->_shortName = $shortName;
        $accountInfo = EbayAccount::getByShortName($shortName);
        $this->setUserToken($accountInfo['user_token']);
        $ebayKeys = ConfigFactory::getConfig('ebayKeys');
        $this->appID = $ebayKeys['appID'];
        $this->devID = $ebayKeys['devID'];
        $this->certID = $ebayKeys['certID'];
        $this->serverUrl = $ebayKeys['serverUrl'];
        $this->compatabilityLevel = $ebayKeys['compatabilityLevel'];

        return $this;
    }

    /**
     * set user token
     * 
     * @param string $userToken
     * @return \EbayModel
     */
    public function setUserToken($userToken) {
        $this->_userToken = $userToken;
        
        return $this;
    }
    
    /**
     * Set Site ID
     */
    public function setSiteId($id){
    	$this->siteID = $id;	
    	return $this;
    }
    
    /**
     * get user token 
     * 
     * @return type
     */
    public function getUserToken() {
        if ( empty($this->_userToken) ) {
            throw  new Exception('User token is not allowed to be empty');
        }
        return $this->_userToken;
    }
    
    
    public function setVerb($verb) {
        $this->verb = $verb;
        return $this;
    }

    /**
     * send http request
     * 
     * @return object 
     */
    public function sendHttpRequest() {
    	require_once $_SERVER['DOCUMENT_ROOT'].'/protected/vendors/ebay/EbaySession.php';
        try {
            $userToken = $userToken = $this->getUserToken();
            $devID = $this->devID;
            $appID = $this->appID;
            $certID = $this->certID;
            $compatabilityLevel = $this->compatabilityLevel;
            $siteID = $this->siteID;
            $serverUrl = $this->serverUrl;
            $verb = $this->verb;

            $session = new eBaySession($userToken, $devID, $appID, $certID, $serverUrl, $compatabilityLevel, $siteID, $verb);
            //send the request and get response
            $requestXmlBody = $this->requestXmlBody();
            $response = $session->sendHttpRequest($requestXmlBody);                 
            $this->response = $response;          
            
        } catch (Exception $e ) {
            Yii::apiDbLog($e->getMessage(), $e->getCode(), get_class($this));          
        }               
        
        return $this;
    }

    public function getSendAllData()
    {
        require_once $_SERVER['DOCUMENT_ROOT'].'/protected/vendors/ebay/EbaySession.php';
        try {
            $userToken = $userToken = $this->getUserToken();
            $devID = $this->devID;
            $appID = $this->appID;
            $certID = $this->certID;
            $compatabilityLevel = $this->compatabilityLevel;
            $siteID = $this->siteID;
            $serverUrl = $this->serverUrl;
            $verb = $this->verb;
            $session = new eBaySession($userToken, $devID, $appID, $certID, $serverUrl, $compatabilityLevel, $siteID, $verb);
            //send the request and get response
            return ['xml'=>$this->requestXmlBody(),'header'=>$session->buildEbayHeaders()];
            $this->response = $response;
        } catch (Exception $e ) {
            Yii::apiDbLog($e->getMessage(), $e->getCode(), get_class($this));
            return false;
        }
    }

    /**
     * request xml body
     */
    abstract function requestXmlBody();	
    
    /**
     * get xml generator obj 
     * 
     * @return \XmlGenerator
     */
    public static function getXmlGeneratorObj() {
        return new XmlGenerator();
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
    
    public function getIfSuccess(){
    	if( isset($this->response->Ack) && ($this->response->Ack=='Success' || $this->response->Ack=='Warning') ){
    		return true;
    	}else{
    		return false;
    	}
    }
    
    /**
     * 获取错误信息
     * @return string
     */
    public function getErrorMsg(){
    	$msg = '';
    	if( isset($this->response->Errors) ){
	    	foreach($this->response->Errors as $error) {
	    		$msg .= $error->LongMessage.".";
	    	}
    	}
    	return $msg;
    }
}

?>