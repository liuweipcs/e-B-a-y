<?php
/** 
 *  @category    aliexpress
 *  @package     aliexpress
 *  @auther Bob <Foxzeng>
 */
class OnlineAeProduct  {
    
    private $apiParas = array();    
    
    private $access_token = null;
    
    private $productIds = null;


    public function setAccessToken($accessToken) {
        $this->access_token = $accessToken;
		$this->apiParas["access_token"] = $accessToken;
        return $this;
    }
    
    public function setProductIds($productIds) {
        $this->productIds = $productIds;
        $this->apiParas["productIds"] = $productIds;
        return $this;
    }

    public function getApiMethodName() { 
		return "api.onlineAeProduct";
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
