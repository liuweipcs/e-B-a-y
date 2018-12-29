<?php
class EbaySuggestedCategoryController extends UebController {
    public function accessRules() {
        return array();
    }
    
    /**
     * Ebay获取产品Specifics
     */
    public function actionSuggestedGetcategory() {
        $siteID = Yii::app()->request->getParam('siteid');
        $keyword = Yii::app()->request->getParam('keyword');
    
        $ebayCategoriesSpecifics = new EbayGetSuggestedCategory();
        $response = $ebayCategoriesSpecifics->GetSuggestedCategories($keyword,$siteID);
        return $response;
    }
}