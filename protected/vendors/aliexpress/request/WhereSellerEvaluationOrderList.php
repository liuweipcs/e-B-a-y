<?php
header("content-type:text/html;charset=utf-8");
/** 
 *  @category    aliexpress
 *  @package     aliexpress
 *  @auther Bob <Foxzeng>
 */
class WhereSellerEvaluationOrderList {

    private $apiParas = array();

    private $access_token = null;

    private $productId = null;
    private $currentPage = null;
    private $productStatusType = null;

    public function setPage($page) {
        $this->currentPage = $page;
        $this->apiParas["currentPage"] = $page;
        return $this;
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
        return "api.evaluation.querySellerEvaluationOrderList";
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
