<?php


class EbayCategorySpecificsController extends UebController {
    /**
     * Specifies the access control rules.
     * This method is used by the 'accessControl' filter.
     * @return array access control rules
     */
    public function accessRules() {
        return array();
    }
    
    /**
     * Ebay获取产品Specifics
     */
    public function actionGetcategorySpecifics() {
        set_time_limit(7200);
        $getSiteId = Yii::app()->request->getParam('siteid');
        $ebayCategoriesSpecifics = new EbayCategorySpecifics();
        $ebayCategoriesSpecifics->GetCategorySpecifics($getSiteId);
    }
        
 
   
    
}