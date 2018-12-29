<?php
/**
 *  @category    aliexpress
 *  @package     aliexpress
 *  @auther Bob <Foxzeng>
 */
class WhereProductAnalysisData  {

    private $apiParas = array();

    private $access_token = null;

    private $productId = null;
    private $currentPage = null;
    private $endDate = null;
    private $startDate = null;

    public function setPage($page) {
        $this->currentPage = $page;
        $this->apiParas["currentPage"] = $page;
        return $this;
    }
    public function  getPage()
    {
        return $this->currentPage;
    }

    public function setNum($num = 0) {
        $this->apiParas["pageSize"] = $num;
        return $this;
    }
    public function getNum()
    {
        return $this->apiParas['pageSize'] > 20 ? $this->apiParas['pageSize'] : 50;
    }
    public function setAccessToken($accessToken) {
        $this->access_token = $accessToken;
        $this->apiParas["access_token"] = $accessToken;
        return $this;
    }

    public function setProductId($productId) {
        $this->productId = $productId;
        $this->apiParas["productId"] = $productId;
        return $this;
    }

    public function setEndDate($endDate) {
        $this->endDate = $endDate;
        $this->apiParas["endDate"] = $endDate;
        return $this;
    }
    public function setSartDate($startDate) {
        $this->startDate = $startDate;
        $this->apiParas["startDate"] = $startDate;
        return $this;
    }

    public function getApiMethodName() {
        return "api.queryProductBusinessInfoById";
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
