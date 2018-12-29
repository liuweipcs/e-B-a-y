<?php

/**
 *  @category    aliexpress
 *  @package     aliexpress
 *  @auther Bob <Foxzeng>
 */
class GetAlisOrder {

    private $apiParas = array();
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

    public function setOrderStatus($status) {
        $this->apiParas['orderStatus'] = $status;
        return $this;
    }

    public function setTime($timeStart, $timeEnd) {
        $this->apiParas['createDateStart'] = $timeStart;
        $this->apiParas['createDateEnd'] = $timeEnd;
        return $this;
    }

    private function transactionTime($time) {
        $data = explode('-', $time);
        $time = $data[1] . '/' . $data[2] . '/' . $data[0];
        return $time;
    }

    public function setAccessToken($accessToken) {
        $this->access_token = $accessToken;
        $this->apiParas["access_token"] = $accessToken;
        return $this;
    }

    public function getApiMethodName() {

        return "api.findOrderListQuery";
    }

    public function getApiParas() {
        return $this->apiParas;
    }

    public function check() {
        
    }

    public function putOtherTextParam($key, $value) {
        $this->apiParas[$key] = $value;
        $this->$key = $value;
        return $this;
    }

}

?>