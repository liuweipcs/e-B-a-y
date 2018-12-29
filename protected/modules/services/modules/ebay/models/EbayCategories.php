<?php
/**
 * @package Ueb.modules.EbayOrderApiTask.models
 *
 * @author Gordon
 * @since 2014-07-24
 */
class EbayCategories{
	
	/**
	 * 更新ebey在线分类
	 */
	public function updateCategories(){
		$allSite = UebModel::model('EbaySite')->getSiteList();
		$siteIds = array_keys($allSite);
		foreach($siteIds as $siteId){
			$this->updateCategoriesBySiteId($siteId);
		}
		//获取特殊site下的分类
		$specialSites = UebModel::model('EbaySite')->getSpecialSite();
		foreach($specialSites as $siteId=>$site){
			foreach($site as $realSiteId){
				$this->updateCategoriesBySiteId($realSiteId, $siteId);
			}
		}
	}
	
	/**
	 * 根据siteId获取站点分类
	 * @param smallInt $realSiteId
	 * @param smallInt $siteId
	 */
	public function updateCategoriesBySiteId($realSiteId, $siteId=''){
		//取一个可用账号
		$accountInfo = EbayAccount::getOneEnableAccount();
		if( !$accountInfo ){
			throw new CException('No Account!');
		}
		$categoryApiObj = new GetCategories();
		$categoryApiObj->setCategorySiteId($realSiteId);
		//获取分类
		$response = $categoryApiObj->setShortName($accountInfo['short_name'])
					->setSiteId($siteId ? $siteId : $realSiteId)
					->setVerb('GetCategories')
					->setRequest()
					->sendHttpRequest()
					->getResponse();
		if( $categoryApiObj->getIfSuccess() ){
			foreach( $response->CategoryArray->Category as $category ){
				$params = array(
						'site_id' 		=> $siteId ? $siteId : $realSiteId,
						'real_site_id' 	=> $realSiteId,
						'category_id' 	=> $category->CategoryID,
						'parent_id' 	=> $category->CategoryParentID,
						'category_name' => $category->CategoryName,
						'level' 		=> $category->CategoryLevel,
						'auto_pay' 		=> $category->AutoPayEnabled == 'true' ? 1 : 0,
						'best_offer' 	=> $category->BestOfferEnabled == 'true' ? 1 : 0,
				);	
				UebModel::model('EbayCategory')->saveData($params);
				UebModel::model('ProductCategory')->updateProductCategory();
			}
		}else{
			throw new CException('Can Not Get The Categories,Msg:'.$categoryApiObj->getErrorMsg());
		}
	}
}