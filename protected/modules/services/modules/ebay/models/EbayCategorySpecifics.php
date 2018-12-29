<?php
class EbayCategorySpecifics {
    
    /**
     * 查询eBay产品刊登 Specifics
     */
    public function GetCategorySpecifics($siteID = '') {
        $allSite = UebModel::model('EbaySite')->getSiteList();
        $siteIds = array_keys($allSite);
        
        if(isset($siteID)) {
            $siteIds = [];
            $siteIds[] = $siteID;
        }
        
        foreach($siteIds as $siteId) {
            $categoryInfo = array_column(VHelper::selectAsArray('Ebaycategory','category_id','site_id='.$siteId.' and leaf_category = "true" '), 'category_id');           
            $existCategory = array_column(VHelper::selectAsArray('EbayCategorySpecificsName','category_id','site_id='.$siteId,true), category_id);
            $diffCategory = array_diff($categoryInfo, $existCategory);
            
            foreach($diffCategory as $categoryID) {
                $this->CheckCategorySpecifics($categoryID,$siteId);
            }
        }
        
        echo "ok";exit();
    }
    
    /**
     * 检查产品 Specifics 更新或新增
     */
    public function CheckCategorySpecifics($categoryID,$siteId) {
        //取一个可用账号
        $accountInfo = EbayAccount::getOneEnableAccount();
        if( !$accountInfo ){
            throw new CException('No Account!');
        }
        $categorySpecificsApiObj = new GetCategorySpecifics();
        $categorySpecificsApiObj->setCategoryId($categoryID);
        //获取分类
        $response = $categorySpecificsApiObj->setShortName($accountInfo['short_name'])
        ->setSiteId($siteId)
        ->setVerb('GetCategorySpecifics')
        ->setRequest()
        ->sendHttpRequest()
        ->getResponse();

        if($categorySpecificsApiObj->getIfSuccess()) {
            foreach($response->Recommendations->NameRecommendation as $specificsValue) {
                $specificsName = addslashes($specificsValue->Name);
                $checkInfo = VHelper::selectAsArray('EbayCategorySpecificsName','id','site_id='.$siteId.' and name="'.$specificsName.'" and category_id="'.$categoryID.'"');
                $infoArr = array_column($checkInfo,'id');
                
                if(!empty($checkInfo)) { //更新
                    $infoId = $infoArr['0'];
                    $model = EbayCategorySpecificsName::model()->findByPk($infoId);
                } else { //添加
                    $model = new EbayCategorySpecificsName();
                }
                
                $model->site_id = $siteId;
                $model->category_id = $categoryID;
                $model->name = $specificsValue->Name;
                $model->value_type = $specificsValue->ValidationRules->ValueType;
                $model->min_values = $specificsValue->ValidationRules->MinValues;
                $model->max_values = $specificsValue->ValidationRules->MaxValues;
                $model->selection_mode = $specificsValue->ValidationRules->SelectionMode;
                $model->variation_specifics = $specificsValue->ValidationRules->VariationSpecifics;
                $model->update_time = date('Y-m-d H:i:s');
                
                $result = $model->save();
                $id = $model->attributes['id'];
                //更新属性值
                $ValueRecommendation = $specificsValue->ValueRecommendation;
                foreach($ValueRecommendation as $value) {
                    $val = addslashes($value->Value);
                    $checkInfos = VHelper::selectAsArray('EbayCategorySpecificsValue','id','specifics_name_id='.$id.' and value="'.$val.'"');
                    $infosArr = array_column($checkInfos,'id');
                    
                    if(!empty($checkInfos)) {
                        $infosId = $infosArr['0'];
                        $modelobj = EbayCategorySpecificsValue::model()->findByPk($infosId);
                    } else {
                        $modelobj = new EbayCategorySpecificsValue();
                    }
                    
                    $modelobj->specifics_name_id = $id;
                    $modelobj->value = $value->Value;
                    
                    $result = $modelobj->save();
                }
            }
            
            return $result;
        } else {
            echo "<pre>";var_dump($response);exit();
            throw new CException('Can Not Get The CategoriesSpecifics,Msg:'.$categorySpecificsApiObj->getErrorMsg());
        }
    }
    
    
    public function checkebaynewcategory($siteid,$categoryid) {
        set_time_limit(180);
        $categoryApiObj = new GetCategories();
        $categoryApiObj->setCategorySiteId($siteid);
        $categoryApiObj->setCategoryParent($categoryid);
        $allSite = UebModel::model('EbaySite')->getSiteList();
        
        //获取分类
        $accountInfo = EbayAccount::getOneEnableAccount();
        if( !$accountInfo ){
            throw new CException('No Account!');
        }
        
        $response = $categoryApiObj->setShortName($accountInfo['short_name'])->setSiteId($siteid)
                    ->setVerb('GetCategories')
                    ->setRequest()
                    ->sendHttpRequest()
                    ->getResponse();
        
        if(isset($response->Ack) && $response->Ack == 'Success') {
            $checkInfo = VHelper::selectAsArray('Ebaycategory','id','site_id='.$siteid.' and category_id="'.$categoryid.'"');
            
            if(empty($checkInfo)) {
                $CategoryObj = $response->CategoryArray->Category;
                if(empty($CategoryObj)) {
                    return array('status'=>500,'msg'=>'没有同步到类目信息，请确认输入的类目ID是否正确');
                }
                
                $CategoryLevel = $CategoryObj->CategoryLevel;
                $BestOfferEnabled = $CategoryObj->BestOfferEnabled;
                $AutoPayEnabled = $CategoryObj->AutoPayEnabled;
                $CategoryID = $CategoryObj->CategoryID;
                $CategoryName = $CategoryObj->CategoryName;
                $categoryParentid = $CategoryObj->CategoryParentID;
                $LeafCategory = $CategoryObj->LeafCategory;
                
                $sql = "INSERT INTO ueb_product.ueb_ebay_category(category_name,best_offer_enabled,auto_pay_enabled,category_id,category_level,
                            category_parent_id,leaf_category,site_id,site) 
                    VALUES ('".$CategoryName."','".$BestOfferEnabled."','".$AutoPayEnabled."','".$CategoryID."','".$CategoryLevel."','".$categoryParentid."',
                            '".$LeafCategory."','".$siteid."','".$allSite[$siteid]."')";
                $result = Yii::app()->db->createCommand($sql)->execute();
                
                if($CategoryLevel == 1) {
                    return array('status'=>200);
                } else {
                    return $this->checkebaynewcategory($siteid, $categoryParentid);
                }
            } else {
               return array('status'=>200);
            }
            
           
        } else {
            return array('status'=>500,'msg'=>'同步失败，请稍后再试');
        }
        
    }
    
    
    
    
    
    
    
    
    
    
}