<?php
/**
 * @package Ueb.modules.services.controllers
 * @author Gordon 
 */

class EbaycategoryController extends UebController {

	/**
	 * Specifies the access control rules.
	 * This method is used by the 'accessControl' filter.
	 * @return array access control rules
	 */
	public function accessRules() {
		return array();
	}
    
	/**
	 * Ebay获取分类
	 */
    public function actionGetcategory(){ 
    	$ebayCategories = new EbayCategories();
    	$ebayCategories->updateCategories();
    }
    
    
}