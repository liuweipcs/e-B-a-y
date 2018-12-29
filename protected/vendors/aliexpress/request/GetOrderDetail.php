<?php

/**
 *  @category    aliexpress
 *  @package     aliexpress
 *  @auther Bob <Foxzeng>
 */
class GetOrderDetail {

    private $apiParas = array();
    private $page = null;
    private $access_token = null;

    public function setOrderId($orderId) {
        $this->apiParas['orderId'] = $orderId;
        return $this;
    }

    public function setAccessToken($accessToken) {
        $this->access_token = $accessToken;
        $this->apiParas["access_token"] = $accessToken;
        return $this;
    }

    public function getApiMethodName() {
        return "api.findOrderById";
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