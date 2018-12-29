<?php
/** 
 *  @category    aliexpress
 *  @package     aliexpress
 *  @auther Bob <Foxzeng>
 */
class FindAeProductProhibitedWords {
    
    private $apiParas = array();  
    
    private $access_token = null;   
    
    private $categoryId = null;

    
    public function setMoudleId($categoryId) {
        $this->categoryId = $categoryId;
		$this->apiParas["categoryId"] = $categoryId;
        return $this;
    }
    
    public function setAccessToken($accessToken) {
        $this->access_token = $accessToken;
		$this->apiParas["access_token"] = $accessToken;
        return $this;
    }
    

    public function getApiMethodName() { 
		return "api.findAeProductProhibitedWords";
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
