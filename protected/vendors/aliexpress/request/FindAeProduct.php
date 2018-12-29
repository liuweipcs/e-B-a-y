<?php
/** 
 *  
 *  @category    aliexpress
 *  @package     aliexpress
 *  @auther Bob <Foxzeng>
 */
class FindAeProduct  {
    
    private $apiParas = array();    
    
    private $access_token = null;
    
    private $productId = null;


    public function setAccessToken($accessToken) {
        $this->access_token = $accessToken;
		$this->apiParas["access_token"] = $accessToken;
        return $this;
    }
    
    public function setProductId($productId) {
        $this->productIds = $productId;
        $this->apiParas["productId"] = $productId;
        return $this;
    }

    public function getApiMethodName() { 
		return "api.findAeProductById";
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
