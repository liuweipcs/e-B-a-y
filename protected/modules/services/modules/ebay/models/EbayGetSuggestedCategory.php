<?php
class EbayGetSuggestedCategory {
    public function GetSuggestedCategories($keyWord,$siteID='0') {
        $result = $this->CheckSuggestedCategory($keyWord,$siteID);
        return $result;
    }
    
    public function CheckSuggestedCategory($keyWord,$siteID) {
        //取一个可用账号
        $accountInfo = EbayAccount::getOneEnableAccount();
        if( !$accountInfo ){
            throw new CException('No Account!');
        }
        $suggestedCategoryApiObj = new GetSuggestedCategories();
        $suggestedCategoryApiObj->setCategoryKeyword($keyWord);
        //获取建议分类
        $response = $suggestedCategoryApiObj->setShortName($accountInfo['short_name'])
        ->setSiteId($siteID)
        ->setVerb('GetSuggestedCategories')
        ->setRequest()
        ->sendHttpRequest()
        ->getResponse();
        
        $data = array();
        if($suggestedCategoryApiObj->getIfSuccess()) {
            $response = json_decode(json_encode($response),true);
            foreach($response['SuggestedCategoryArray']['SuggestedCategory'] as $key=>$suggestedValue) {
                $data[$key]['categoryID'] = $suggestedValue['Category']['CategoryID'];
                $data[$key]['CategoryName'] = $suggestedValue['Category']['CategoryName'];
                
                $data[$key]['PercentItemFound'] = $suggestedValue['PercentItemFound'];
                if(!is_array($suggestedValue['Category']['CategoryParentName'])) {
                    $suggestedValue['Category']['CategoryParentName'] = array($suggestedValue['Category']['CategoryParentName']);
                }
                    
                $data[$key]['showCategory'] = implode(' > ',$suggestedValue['Category']['CategoryParentName']).' > '.$data[$key]['CategoryName'];
                
            }
            $data['status'] = 200;
        } else {
            $data['status'] = 500;
            $data['msg'] = $suggestedCategoryApiObj->getErrorMsg();
        }
        return $data;
    }
}