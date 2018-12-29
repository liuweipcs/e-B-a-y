<?php

/**
 *  @category    aliexpress
 *  @package     aliexpress
 *  @auther Bob <Foxzeng>
 */
class GetPostCategory {

    private $apiParas = array();
    
    private $cat_id = null;
    
    private $access_token = null;

    public function setCateId($cateId) {
        $this->cat_id = $cateId;
        $this->apiParas["cateId"] = $cateId;
        return $this;
    }

    public function setAccessToken($accessToken) {
        $this->access_token = $accessToken;
        $this->apiParas["access_token"] = $accessToken;
        return $this;
    }

    public function getApiMethodName() {
        return "api.getPostCategoryById";
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
