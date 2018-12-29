<?php
/** 
 *  
 *  @category    aliexpress
 *  @package     aliexpress
 *  @auther Bob <Foxzeng>
 */
class FindAeProductDetailModuleList {
    
    private $apiParas = array();
    
    private $pageIndex = null;   
    
    private $access_token = null;
    
    private $type = null;
    
    private $moduleStatus = null;

    public function setPageIndex($pageIndex) {
        $this->pageIndex = $pageIndex;
		$this->apiParas["pageIndex"] = $pageIndex;
        return $this;
    }
    
    public function setType($type) {
        $this->type = $type;
		$this->apiParas["type"] = $type;
        return $this;
    }
    
    public function setMoudleStatus($moduleStatus) {
        $this->moduleStatus = $moduleStatus;
		$this->apiParas["moduleStatus"] = $moduleStatus;
        return $this;
    }
    
    public function setAccessToken($accessToken) {
        $this->access_token = $accessToken;
		$this->apiParas["access_token"] = $accessToken;
        return $this;
    }
    

    public function getApiMethodName() { 
		return "api.findAeProductDetailModuleListByQurey";
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
