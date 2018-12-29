<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/4/1 0001
 * Time: 下午 2:24
 */
class EbayGetStore extends EbayApiAbstract
{
    public $CategoryStructureOnly = null;
    public $LevelLimit = null ;
    public $RootCategoryID = null ;
    public $UserID = null ;

    protected $EbayModel;
    protected $sendXml;
    protected $categoryIds = array();

    public function __construct($account)
    {
        if(is_numeric($account) && $account > 0 && $account%1 === 0)
        {
            $account = UebModel::model('Ebay')->findByPk((int)$account);
        }
        if($account instanceof Ebay)
        {
            $this->EbayModel = $account;
        }
        else
        {
            throw new Exception('ebay账号数据未找到');
        }
    }

    public function requestXmlBody($newGenerate = 'false')
    {
        if(empty($this->sendXml) || $newGenerate)
        {
            $this->sendXml = '<?xml version="1.0" encoding="utf-8" ?>';
            $this->sendXml .= '<GetStoreRequest xmlns="urn:ebay:apis:eBLBaseComponents">';
            $this->sendXml .= '<RequesterCredentials><eBayAuthToken>'.$this->EbayModel->user_token.'</eBayAuthToken></RequesterCredentials>';
            if(isset($this->CategoryStructureOnly))
                $this->sendXml .= "<CategoryStructureOnly>{$this->CategoryStructureOnly}</CategoryStructureOnly>";
            if(isset($this->LevelLimit))
                $this->sendXml .= "<LevelLimit>{$this->LevelLimit}</LevelLimit>";
            if(isset($this->RootCategoryID))
                $this->sendXml .= "<RootCategoryID>{$this->RootCategoryID}</RootCategoryID>";
            $this->sendXml .= "<UserID>{$this->EbayModel->user_name}</UserID>";
            $this->sendXml .= '<ErrorLanguage>zh_CN</ErrorLanguage>';
            $this->sendXml .= '<WarningLevel>High</WarningLevel>';
            $this->sendXml .= '</GetStoreRequest>';
        }
        return $this->sendXml;
    }
    public function setRequest()
    {
        $this->setUserToken($this->EbayModel->user_token);
        $ebayKeys = ConfigFactory::getConfig('ebayKeys');
        $this->appID = $ebayKeys['appID'];
        $this->devID = $ebayKeys['devID'];
        $this->certID = $ebayKeys['certID'];
        $this->serverUrl = $ebayKeys['serverUrl'];
        $this->siteID = 0;
        $this->compatabilityLevel = 983;
        $this->verb = 'GetStore';
        return $this;
    }

    public function getContent()
    {
        $this->setRequest()->sendHttpRequest()->handleResponse();
    }

    public function handleResponse(){
//        findClass($this->response,1);
        //$this->response->Ack->__toString()
        switch($this->response->Ack->__toString())
        {
            case 'Success':
            case 'Warning':
                $store = $this->response->Store;
                $storeName = $store->Name->__toString();
                $transaction = UebModel::model('EbayStoreCategories')->getDbConnection()->beginTransaction();
                try{
                    $this->categoryIds = array();
                    $this->saveToEbayStoreCategories($store->CustomCategories->CustomCategory,$storeName);
                    if(!empty($this->categoryIds))
                    {
                        UebModel::model('EbayStoreCategories')->deleteAll('category_id not in ("'.implode('","',$this->categoryIds).'") and platform_code=:platform_code and store_name=:store_name',array(':platform_code'=>Platform::CODE_EBAY,':store_name'=>$storeName));
                    }
                    $transaction->commit();
                }catch(Exception $e){
                    $transaction->rollback();
                    throw $e;
                }
        }

    }

    public function saveToEbayStoreCategories($simCustomCategory,$storeName,$parentCategoryId = null)
    {
        foreach($simCustomCategory as $customCategory)
        {
//            findClass($customCategory,1,0);
            $saveEbayStoreCategories = UebModel::model('EbayStoreCategories')->find('platform_code=:platform_code and store_name=:store_name and category_id=:category_id',array(':platform_code'=>Platform::CODE_EBAY,':store_name'=>$storeName,':category_id'=>$customCategory->CategoryID->__toString()));
            if(empty($saveEbayStoreCategories))
            {
                $saveEbayStoreCategories = new EbayStoreCategories();
                $saveEbayStoreCategories->create_by = Yii::app()->user->id;
                $saveEbayStoreCategories->create_time = date('H-m-d H:i:s');
                $modifyFlag = false;
            }
            else
            {
                $modifyFlag = true;
            }
            $saveEbayStoreCategories->platform_code = Platform::CODE_EBAY;
            $saveEbayStoreCategories->store_name = $storeName;
            $saveEbayStoreCategories->category_id = $customCategory->CategoryID->__toString();
            $saveEbayStoreCategories->category_name = $customCategory->Name->__toString();
            $saveEbayStoreCategories->order = $customCategory->Order->__toString();
            $saveEbayStoreCategories->parent_category_id = $parentCategoryId;
            if($modifyFlag)
            {
                $saveEbayStoreCategories->modify_by = Yii::app()->user->id;
                $saveEbayStoreCategories->modify_time = date('H-m-d H:i:s');

            }
            $saveEbayStoreCategories->save();
            $this->categoryIds[] = $saveEbayStoreCategories->category_id;
            if(isset($customCategory->ChildCategory))
            {
                $this->saveToEbayStoreCategories($customCategory->ChildCategory,$storeName,$saveEbayStoreCategories->category_id);
            }
        }
    }
}