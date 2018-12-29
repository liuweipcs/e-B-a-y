<?php
/** 
 *  @category    aliexpress
 *  @package     aliexpress
 *  @auther Bob <Foxzeng>
 */
class GetSellShip {

    private $apiParas = array();
    private $access_token = null;

    public function setAccessToken($accessToken) {
        $this->access_token = $accessToken;
        $this->apiParas["access_token"] = $accessToken;
        return $this;
    }

    public function getApiMethodName() {
        return "api.sellerShipment";
    }

    public function check() {
        
    }

    public function getApiParas() {
        return $this->apiParas;
    }

    public function setParams(Array $params) {
        if (!empty($params)) {
            foreach ($params as $key => $value) {
                $this->apiParas[$key] = $value;
            }
        }
        return $this;
    }

}

?>