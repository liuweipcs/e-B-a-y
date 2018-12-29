<?php
class ProductController extends CController{
	
	function beforeAction(){
		parent::beforeAction();
// 		$token = Yii::app()->request->getParam('token');
// 		if(empty($token)) exit('无访问权限');
		$this->loadModules();
		return true;
	}
	
	protected function loadModules(){
		Yii::import('application.modules.products.models.*');
		Yii::import('application.modules.purchases.models.*');
	}
	
	public function actionIndex(){
		$sku = Yii::app()->request->getParam('sku');
		$model = new Product();
		$model = $model->getBySku($sku);
		$this->render('index',array(
				'model'=>$model
		));
	}
	
	//产品的基础信息
	public function actionBaseu(){
		$id = Yii::app()->request->getParam('id');
		$model = $this->loadModel($id);
		$productJob = UebModel::model('Productjob')->find('sku = :sku',array(':sku' => $model->sku));
		$providerIds 			= UebModel::model('ProductProvider')->getProviderIdByProductId($id);
		$model->combine 		= UebModel::model('ProductCombine')->getCombineList($id);
		$model->bind  			= UebModel::model('Productbind')->getBindSkuByBaseSku($model->sku);
		$providerCode 			= UebModel::model('Provider')->getCodeById($providerIds);
		$productSecurityList	= UebModel::model('Product')->getProductSecurityList();//产品侵权List
		$productInfringement	= UebModel::model('Product')->getProductInfringementList();
		$productBrand 			= UebModel::model('Productbrand')->getListOptions();
		$bindProvider 			= UebModel::model('ProductProvider')->getBindSkuProvider($id);
		//$proCatOldModel 		= $proCatOldModel ? $proCatOldModel : $productCatOldModel;
		$model->provider_code 	= empty($providerCode) ? '' : implode(",", $providerCode);
		$model->provider_code 	= trim( $model->provider_code,',');
		$model->security_level 	= $mops->security_level ? $productSecurityList[$mops->security_level] : '-';
		$model->infringement 	= $mops->infringement>=1 ? $productInfringement[$mops->infringement] : $productInfringement[1];
		if($model->drop_shipping){
			$model->provider_type = $model->dropshipping;
		}else{
			$model->provider_type = $model->provider;
		}
		$category = UebModel::model('ProductCategory')->getCat(0);
		$categories = ProductCategory::model()->getAllParentByCategoryId($model->product_category_id);
		$catetoryArr =  UebModel::model('ProductCategory')->getCategoryArr(CN);
		$newCategory=$model->getCategoryBySku($model->sku);
		empty($newCategory)?'':$newCategory;
		$catArr = array();
		$catetoryView = '';
		foreach($category as $cat){
			$catArr[$cat['id']] = $cat['category_en_name'].' '.$cat['category_cn_name'];
		}
		foreach($categories as $key=>$value){
			$catetoryView .= $catetoryArr[$value].">>";
		}
		$catetoryView = trim($catetoryView,">>");
		$infringePlatform=UebModel::model('platform')->getPlatformList();
		$productPackage=UebModel::model('ProductToWayPackage')->getProductAllPackage();//所有包装 方式
		$this->render('baseu', array(
					'ft'=>$ft,
					'nologo'=>$nologo,
					'model' => $model,
					'do' => $do,
					'baocai'=>$baocai,
					'mops'=>$mops,
					'catArr'=>$catArr,
					'catetoryView'=>$newCategory,
					'bindProvider'=>$bindProvider,
					'productBrand'=>$productBrand,
			)
		);
	}
	
	//产品的属性
	public function actionAttrs(){
		$model = new ProductSelectAttribute();
		$productId = Yii::app()->request->getParam('product_id');
		$attributeIdsInfo=$model->getAttributeIdByProductId($productId);
		$selectedId=$model->getSelectedIdByProduct($productId);
		$productInfo = UebModel::model('Product')->getById($productId);
		$categoryAttributeList = UebModel::model('ProductCategoryAttribute')->getAttributeList($productInfo['product_category_id']);
		$publicAttributeList = UebModel::model('ProductAttribute')->getPublicAttributeList();
		foreach ($publicAttributeList as $key => $val) {
			$categoryAttributeList[$key] = $val;
		}
		unset($publicAttributeList);
		$attributeIds = array_keys($categoryAttributeList);
		$attributeListData = UebModel::model('ProductAttributeMap')
		->getListValueData($attributeIds);
		$selectAttrPairs = $model->getAttrList($productId);
		if ( $productInfo['product_is_multi'] == 2 ) {
			$selectMutiIds = $model->getMultiAttIdsByMultiId($productId);
			$multiSku = $productInfo['sku'];
		} else {
			$selectMutiIds = $model->getMultiAttIds($productId);
			$multiSku = $model->getMultiSku($productId);
			if ( empty($multiSku) && strpos($productInfo['sku'],'.') !== false ) {
				$multiSku = substr($productInfo['sku'], 0, strpos($productInfo['sku'],'.'));
			}
		}
		$model->setAttribute('multi_sku', $multiSku);
		$isNopublicAttr=UebModel::model('ProductAttribute')->getNopublicAttr();
		$singleProduct=UebModel::model('Product')->findByPk($productId);
		if($singleProduct->product_is_multi==0){
			$model->multi_sku='';
		}
		$this->render('attrs', array(
				'isNopublicAttr'		=> $isNopublicAttr,
				'categoryAttributeList' => $categoryAttributeList,
				'attributeListData'     => $attributeListData,
				'categoryId'            => $productInfo['product_category_id'],
				'selectAttrPairs'       => $selectAttrPairs,
				'productId'             => $productId,
				'attributeIdsInfo'		=> $attributeIdsInfo,
				'selectedId'			=> $selectedId,
				'product_is_multi'      => $productInfo['product_is_multi'],
				'selectMutiIds'         => json_encode($selectMutiIds),
				'model'                 => $model,
		));
	}
	
	public function actionQualityreport(){
		$id = Yii::app()->request->getParam('id');
		$model = $this->loadModel($id);
		$this->render('qualityreport', array('quality_standard' => $model->quality_standard,'quality_remark' => $model->quality_remark,'quality_lable'=>$model->quality_lable));
	}
	
	public function loadModel($id) {
		$model = UebModel::model('Product')->findByPk((int) $id);
		if ( $model === null )
			throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
			return $model;
	}
}