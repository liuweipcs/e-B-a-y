<?php
/** 
 *  @category    aliexpress
 *  @package     aliexpress
 *  @auther Bob <Foxzeng>
 */
class QueryPromiseTemplate {
    
    private $apiParas = array();
    
    private $templateId= null;   
    
    private $access_token = null;

    public function setTemplateId($templateId) {
        $this->templateId = $templateId;
		$this->apiParas["templateId"] = $templateId;
        return $this;
    }
    
    public function setAccessToken($accessToken) {
        $this->access_token = $accessToken;
		$this->apiParas["access_token"] = $accessToken;
        return $this;
    }

    public function getApiMethodName() { 
		return "api.queryPromiseTemplateById";
	}   
	
	public function getApiParas() {
		return $this->apiParas;
	}
	
	public function check(){}
	
	public function putOtherTextParam($key, $value) {
		$this->apiParas[$key] = $value;
		$this->$key = $value;
        return $this;
	}
}
?>
