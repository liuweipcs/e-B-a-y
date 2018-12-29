<?php
/** 
 *  aliexpress client
 * 
 *  @category    aliexpress
 *  @package     aliexpress
 *  @auther Bob <Foxzeng>
 */
class AlibabaClient
{

    
	public $gatewayUrl = "http://gw.open.1688.com/openapi/";
    
    public $redirectUri = "";

    public $sysQuery = 'param2/2/cn.alibaba.open/';

	public $format = "json";
    
    public $request = null;
    
    public $response = null;

	public $checkRequest = true;

	protected $signMethod = "sha1";
    
    public function __construct() {
        $this->setRedirectUri();   
    }
    
    /**
     * set request obj
     * 
     * @param object $request
     * @return \Client
     */
    public function setRequest($request){

        $this->request = $request;
        return $this;
    }
    
    /**
     * curl execute
     * 
     * @return type
     */
    public function exec() {
        if ($result = $this->_beforeExec()) {
            return $result;
        }
        //配置参数
        $apiParams = $this->request->getApiParas();

        $requestUrl = $this->gatewayUrl . $this->sysQuery;
        $requestUrl .= $this->request->getApiMethodName() . '/' . $this->request->app_key;
        $apiParams['_aop_signature']= $this->_generateSign($apiParams);
        $this->response = Yii::app()->curl->post($requestUrl, $apiParams);
        $response = $this->_afterExec();

        return $response;
    }

    /**
     * get code url
     * 
     * @return string
     */
    public function getCodeUrl() {
       $params = array(
            'client_id'     => $this->request->app_key,
            'redirect_uri'  => $this->redirectUri.'alibabaToken/GetToken',
            'site'          => 'alibaba'
       );
       $codeSign = $this->_generateSign($params);
       $params['_aop_signature'] = $codeSign;
       $queryStr = http_build_query($params);
       $codeUrl = $this->gatewayUrl .'?'.$queryStr;
       
       return $codeUrl;
    } 
    
    
   /**
    *  set redirect uri
    * 
    * @return \Client
    */
    public function setRedirectUri() {
        $host = Yii::app()->request->getHostInfo();     
        $this->redirectUri = $host . '/services/alibaba/';
        
        return $this;
    }

    /**
     * getnerate sign
     * 
     * @param array $params
     * @return string
     */
    protected function _generateSign($params) {

        $apiInfo = $this->sysQuery.$this->request->getApiMethodName().'/'.$this->request->app_key;
        $aliParams =[];
        foreach ($params as $key=>$val) {
            $aliParams[]= $key . $val;
        }
        sort($aliParams);
        $sign_str = join('', $aliParams);
        $sign_str = $apiInfo.$sign_str;
        return strtoupper(bin2hex(hash_hmac('sha1', $sign_str, $this->request->secret_key, true)));
	}
    
    /**
     *  before execute
     * 
     * @return type
     */
    protected function _beforeExec() {
        return $this->_check();
    }
    
    protected function _afterExec() {
        return $this->_parseResponse();
    }
    
    /**
     * parse response
     * 
     * @return object $result
     */
    protected function _parseResponse() {       
		$responseWellFormed = false;
        $result = new stdClass();
		if ("json" == $this->format) {
			//print_r($this->response);exit;
			$responsenew = preg_replace('/,"orderId":(\d{1,})./', ',"orderId":"\\1"}', $this->response);
			if(empty(json_decode($responsenew,true))){
				$responsenew = $this->response;
			}
			$responsenew1 = preg_replace('/,"orderId":(\d{1,})./', ',"orderId":"\\1",', $responsenew);
			if(empty(json_decode($responsenew1,true))){
				$responsenew1 = $responsenew;
			}
			$responsenew2 = preg_replace('/,"id":(\d{1,})./', ',"id":"\\1",', $responsenew1);
			if(empty(json_decode($responsenew2,true))){
				$responsenew2 = $responsenew1;
			}
			$responsenew3 = preg_replace('/^\{"id":(\d{1,})./', ',"{id":"\\1",', $responsenew2);
			if(empty(json_decode($responsenew3,true))){
				$responsenew3 = $responsenew2;
			}
			$responsenew0 = preg_replace('/,"childId":(\d{1,})./', ',"childId":"\\1",', $responsenew3);
			
			$response = json_decode($responsenew0);
            if (false !== $response) {
				$responseWellFormed = true;	
			}          
		} else if ( "xml" == $this->format ) {
			$response = @simplexml_load_string($this->response);
            
			if ( false !== $response ) {
				$responseWellFormed = true;
			}
		}
        
		if ( false === $responseWellFormed ) {			
			$result->code = 0;
			$result->msg = "HTTP_RESPONSE_NOT_WELL_FORMED";
            
			return $result;
		}
		            
		if ( isset($response->code) ) {}
        
        return $response;
    }

    /**
     * check request
     * @return \stdClass|null
     */
    protected function _check() {
        $result = new stdClass();
        if ($this->checkRequest) {
            try {
                $this->request->check();
            } catch (Exception $e) {
                $result->code = $e->getCode();
                $result->msg = $e->getMessage();

                return $result;
            }
        }
        
        return null;
    }
    
}