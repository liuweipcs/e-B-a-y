<?php
/** 
 *  @category    aliexpress
 *  @package     aliexpress
 *  @auther Bob <Foxzeng>
 */
class GetAccessToken {
    
    private $apiParas = array();
    
    private $grant_type = null;
    
    private $code = null;

    private $need_refresh_token = false;
    
    public function setCode($code) {
        $this->code = $code;
        $this->apiParas["code"] = $code;
        return $this;
    }
    
    public function getCode() {
        return $this->code;
    }

    public function setGrantType($grantType) {
        $this->grant_type = $grantType;
		$this->apiParas["grant_type"] = $grantType;
        return $this;
    }
    
    public function setNeedRefreshToken($needRefreshToken) {
        $this->need_refresh_token = $needRefreshToken;
		$this->apiParas["need_refresh_token"] = $needRefreshToken;
        return $this;
    }

    public function getApiMethodName() { 
		return "getToken";
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
