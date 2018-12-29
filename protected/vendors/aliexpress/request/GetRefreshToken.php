<?php
/** 
 *  @category    aliexpress
 *  @package     aliexpress
 *  @auther Bob <Foxzeng>
 */
class GetRefreshToken {
    
    private $apiParas = array();
    
    private $grant_type = null;   

    private $refresh_token = null;  

    public function setGrantType($grantType) {
        $this->grant_type = $grantType;
		$this->apiParas["grant_type"] = $grantType;
        return $this;
    }
    
    public function setRefreshToken($refreshToken) {
        $this->refresh_token = $refreshToken;
		$this->apiParas["refresh_token"] = $refreshToken;
        return $this;
    }

    public function getApiMethodName() { 
		return "refreshToken";
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
