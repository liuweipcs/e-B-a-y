<?php
/** 
 *  @category    aliexpress
 *  @package     aliexpress
 *  @auther Bob <Foxzeng>
 */
class WhereCancelOrder {
    
    private $apiParas = array();
    private $fileName= null;
    private $page = null;
    private $access_token = null;
    public function setPage($page) {
        $this->page = $page;
        $this->apiParas["page"] = $page;
        return $this;
    }
    public function  getPage()
    {
        return $this->page;
    }

    public function setNum($num = 0) {
        $this->apiParas["pageSize"] = $num;
        return $this;
    }
    public function getNum()
    {
        return $this->apiParas['pageSize'] > 20 ? $this->apiParas['pageSize'] : 50;
    }
    public function setFileName($fileName) {
        $this->fileName = $fileName;
		$this->apiParas["fileName"] = $fileName;
        return $this;
    }
    
    public function setAccessToken($accessToken) {
        $this->access_token = $accessToken;
		$this->apiParas["access_token"] = $accessToken;
        return $this;
    }

    public function getApiMethodName() {
        return "api.findOrderListSimpleQuery";
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
