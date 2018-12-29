<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/12/14 0014
 * Time: 下午 2:31
 * 自动识别AddItem和AddFixedPriceItem接口
 */
class EbayAddItem extends EbayApiAbstract
{
    public $listingAction = 'queue';
    protected $EbayListingModel;
    protected $EbayPublishConfigModel;
    protected $EbayBuyerRequirementModel;
    protected $EbayReturnPolicyModel;
    protected $EbayPaymentMethodTypeModel;
    protected $EbayShippingModel;
    protected $EbayAccountModel;
    protected $sendXml;
    public $test = false; //是否测试，测试设置刊登中状态,true测试，false非测试

    public function __construct()
    {
        $num = func_num_args();
        switch ($num)
        {
            case 1:
                $this->init(func_get_arg(0));
                break;
            case 2:
                $this->init(func_get_arg(0),func_get_arg(1));
        }
    }

    public function init($idOrModel)
    {
        if($idOrModel instanceof EbayListing)
        {
            $this->EbayListingModel = $idOrModel;
        }
        else
        {
            if($this->test)
                $this->EbayListingModel = UebModel::model('EbayListing')->findByPk((int)$idOrModel,'is_delete=0');
            else
                $this->EbayListingModel = UebModel::model('EbayListing')->findByPk((int)$idOrModel,'is_delete=0 and status in (2,11)');
        }
        if(empty($this->EbayListingModel))
            throw new Exception('待刊登数据未找到');
        if(!$this->test)
        {
            $this->EbayListingModel->status = 9;   //修改状态 刊登中
            $this->EbayListingModel->commit_time = time();
            $this->EbayListingModel->save();
        }
        $this->EbayListingModel->long_message = '';
        $this->EbayPublishConfigModel = UebModel::model('EbayPublishConfig')->findByPk((int)$this->EbayListingModel->ebay_publish_config_id);
        if(empty($this->EbayPublishConfigModel))
        {
            $this->handleResponse('配置数据未找到');
            throw new Exception('配置数据未找到');
        }
        $this->EbayBuyerRequirementModel = UebModel::model('EbayBuyerRequirement')->findByPk((int)$this->EbayPublishConfigModel->Buyer_Requir);
        if(empty($this->EbayBuyerRequirementModel))
        {
            $this->handleResponse('买家要求数据未找到');
            throw new Exception('买家要求数据未找到');
        }
        $this->EbayReturnPolicyModel = UebModel::model('EbayReturnPolicy')->findByPk((int)$this->EbayPublishConfigModel->refund_policy);
        if(empty($this->EbayReturnPolicyModel))
        {
            $this->handleResponse('退货政策数据未找到');
            throw new Exception('退货政策数据未找到');
        }
        $this->EbayPaymentMethodTypeModel = UebModel::model('EbayPaymentMethodType')->findByPk((int)$this->EbayPublishConfigModel->payment_method_type_id);
        if(empty($this->EbayPaymentMethodTypeModel))
        {
            $this->handleResponse('付款方式数据未找到');
            throw new Exception('付款方式数据未找到');
        }

        if(!empty($this->EbayListingModel->ebay_shipping_info))
        {
            $ebayShippingInfo = unserialize($this->EbayListingModel->ebay_shipping_info);
            if(!empty($ebayShippingInfo))
                $this->EbayShippingModel = (object)$ebayShippingInfo;
        }
        if(empty($this->EbayShippingModel) && !empty($this->EbayListingModel->ebay_shipping_id))
        {
            $this->EbayShippingModel = UebModel::model('EbayShipping')->findByPk((int)$this->EbayListingModel->ebay_shipping_id);
        }
        if(empty($this->EbayShippingModel))
        {
            $this->handleResponse('货运方式数据未找到');
            throw new Exception('货运方式数据未找到');
        }

        $this->EbayAccountModel = UebModel::model('EbayAccount')->findByPk((int)$this->EbayListingModel->ebay_account_id);
        if(empty($this->EbayAccountModel))
        {
            $this->handleResponse('ebay账号数据未找到');
            throw new Exception('ebay账号数据未找到');
        }
        if($this->EbayPublishConfigModel->selling_price_type == 'FixedPriceItem')
            $this->setVerb('AddFixedPriceItem');
        else
            $this->setVerb('AddItem');
        if(func_num_args() > 1)
        {
            $this->listingAction = strtolower(func_get_arg(1));
        }
    }

    public function requestXmlBody($newGenerate = 'false')
    {

        if(empty($this->sendXml) || $newGenerate)
        {
            $this->generateSendXml();
        }
        return $this->sendXml;
    }

    public function setRequest($production = true)
    {
        $ebayKeys = array(
            //彭偲
            // 'devID' => '79d6d117-5bf3-4091-832d-6b568250715d',
            // 'appID' => 'sipeng-php-SBX-cbff3fe44-7647d73d',
            // 'certID' => 'SBX-bff3fe44de68-7013-49b5-bd8f-5d59',

            //hs
            'devID' => 'caca4707-213c-4bc2-8e0c-1f9113a7760c',
            'appID' => 'shihuang-ybebay-SBX-a45ed6035-43bfef57',
            'certID' => 'SBX-45ed60357d16-e3a1-4458-a330-6d52',
            'serverUrl' => 'https://api.sandbox.ebay.com/ws/api.dll',
        );
        $this->setUserToken($this->EbayAccountModel->user_token);
        if($production){
            $ebayKeys = ConfigFactory::getConfig('ebayKeys');
        }
        $this->appID = $ebayKeys['appID'];
        $this->devID = $ebayKeys['devID'];
        $this->certID = $ebayKeys['certID'];
        $this->serverUrl = $ebayKeys['serverUrl'];
        $this->siteID = $this->EbayListingModel->siteid;
        $this->compatabilityLevel = 983;
        return $this;
    }

    public function listing($production = true)
    {
       return $this->setRequest($production)
            ->sendHttpRequest()
            ->handleResponse();
    }

    public function sendHttpRequest()
    {
        $newStatus = UebModel::model('EbayListing')->findByPk($this->EbayListingModel->id)->status;
        if($newStatus > 3 && $newStatus < 8)
            throw new Exception('提交前检查出重复刊登。');
        else
            return parent::sendHttpRequest();
    }

    protected function handleResponse($longMessage = ''){
        $return =  array('Ack'=>'Failure','update'=>'Failure');
        switch($this->response->Ack)
        {
            case 'Success':
                $this->EbayListingModel->status = $this->listingMethod == 'immediatelylisting' ? 6:7;
                $return['Ack'] = 'Success';
                break;
            case 'Warning':
                $return['Ack'] = 'Warning';
                $this->EbayListingModel->status = $this->listingMethod == 'immediatelylisting' ? 4:5;
                break;
            case 'Failure':
                $newStatus = UebModel::model('EbayListing')->findByPk($this->EbayListingModel->id)->status;
                if($newStatus > 3 && $newStatus < 8)
                    throw new Exception('提交后检查出重复刊登');
                else
                    $this->EbayListingModel->status = 3;
                break;
            default:
                if(empty($longMessage))
                {
                    $this->EbayListingModel->long_message .= '[无返回值]';
                    $this->EbayListingModel->status = 2;
                    $this->EbayListingModel->listing_status = 0;   //不锁定，下一次重新刊登
                }
                else
                {
                    $this->EbayListingModel->long_message .= '['.$longMessage.']';
                    $this->EbayListingModel->status = 3;
                }

        }
        if(isset($this->response->Fees->Fee))
        {
            $this->EbayListingModel->listing_fee = 0;
            foreach($this->response->Fees->Fee as $feeV)
                $this->EbayListingModel->listing_fee += ($feeV->Fee->__toString()-0);
            $this->EbayListingModel->listing_fee .= $this->response->Fees->Fee[0]->Fee->attributes()->currencyID->__toString();
        }
        if(isset($this->response->Errors))
        {
            $errorCount = $this->response->Errors->count();
            $this->EbayListingModel->error_classification = '';
            $this->EbayListingModel->error_code = '';
            $this->EbayListingModel->long_message .= '';
            for($i=0;$i<$errorCount;$i++)
            {
                if(isset($this->response->Errors->ErrorClassification))
                    $this->EbayListingModel->error_classification .= '['.$this->response->Errors[$i]->ErrorClassification->__toString().']';
                if(isset($this->response->Errors->ErrorCode))
                    $this->EbayListingModel->error_code .= '['.$this->response->Errors[$i]->ErrorCode->__toString().']';
                if(isset($this->response->Errors->LongMessage))
                    $this->EbayListingModel->long_message .= '['.$this->response->Errors[$i]->LongMessage->__toString().']';
            }
        }
        elseif($this->response->Ack == 'Success')
        {
            $this->EbayListingModel->error_classification = '';
            $this->EbayListingModel->long_message .= '';
            $this->EbayListingModel->error_code;
        }
        if(isset($this->response->StartTime))
            $this->EbayListingModel->start_time = $this->response->StartTime->__toString();
        if(isset($this->response->EndTime))
            $this->EbayListingModel->end_time = $this->response->EndTime->__toString();
        if(isset($this->response->ItemID))
        {
            $this->EbayListingModel->item_id = $this->response->ItemID->__toString();
            UebModel::model('EbayListingMapSalesScheme')->updateAll(array('item_id'=>$this->EbayListingModel->item_id),'listing_id='.$this->EbayListingModel->id);
        }

        if(isset($this->response->Build))
            $this->EbayListingModel->build = $this->response->Build->__toString();
        if(isset($this->response->Timestamp))
            $this->EbayListingModel->timestamp = $this->response->Timestamp->__toString();
        if(isset($this->response->Message))
            $this->EbayListingModel->message = strip_tags($this->response->Message->__toString());
        // echo "<pre>";
        // print_r($this->EbayListingModel->attributes);
        // echo "------<br>";
        // var_dump($this->EbayListingModel->save());
        //  echo "------<br>";
        // var_dump($this->EbayListingModel->getErrors());
        // exit;
        if($this->EbayListingModel->save()){
            $return['update'] = 'Success';
        }

        return $return;
    }

    protected function generateSendXml()
    {
        $this->sendXml = '<?xml version="1.0" encoding="utf-8" ?>';
        switch($this->verb)
        {
            case 'AddFixedPriceItem' :
                $this->sendXml .= '<AddFixedPriceItemRequest xmlns="urn:ebay:apis:eBLBaseComponents">';
                $endSendXml =  '</AddFixedPriceItemRequest>';
                break;
            case 'AddItem' :
                $this->sendXml .= '<AddItemRequest xmlns="urn:ebay:apis:eBLBaseComponents">';
                $endSendXml = '</AddItemRequest>';
        }
        $this->sendXml .= '<RequesterCredentials><eBayAuthToken>'.$this->EbayAccountModel->user_token.'</eBayAuthToken></RequesterCredentials>';
        $this->sendXml .= '<ErrorLanguage>zh_CN</ErrorLanguage>';
        $this->sendXml .= '<WarningLevel>High</WarningLevel>';
        $this->sendXml .= '<ErrorHandling>BestEffort</ErrorHandling>';
        $this->sendXml .= '<Item>';
        if($this->EbayPaymentMethodTypeModel->auto_pay == 1){
            $this->sendXml .= '<AutoPay>true</AutoPay>';
        }
        if($this->EbayBuyerRequirementModel->refuse == 1)
        {
            $this->sendXml .= '<BuyerRequirementDetails>';
            if($this->EbayBuyerRequirementModel->linked_paypal_account == 1)
            {
                $this->sendXml .= '<LinkedPayPalAccount>true</LinkedPayPalAccount>';
            }
            if($this->EbayBuyerRequirementModel->maximum_buyer_policy_violations == 1){
                $this->sendXml .= '<MaximumBuyerPolicyViolations>';
                $this->sendXml .= '<Count>'.$this->EbayBuyerRequirementModel->maximum_buyer_policy_violations_count.'</Count>';
                $this->sendXml .= '<Period>'.$this->EbayBuyerRequirementModel->maximum_buyer_policy_violations_period.'</Period>';
                $this->sendXml .= '</MaximumBuyerPolicyViolations>';
            }
            if($this->EbayBuyerRequirementModel->maximum_unpaid_item_strikes_info == 1)
            {
                $this->sendXml .= '<MaximumUnpaidItemStrikesInfo>';
                $this->sendXml .= '<Count>'.$this->EbayBuyerRequirementModel->maximum_unpaid_item_strikes_info_count.'</Count>';
                $this->sendXml .= '<Period>'.$this->EbayBuyerRequirementModel->maximum_unpaid_item_strikes_info_period.'</Period>';
                $this->sendXml .= '</MaximumUnpaidItemStrikesInfo>';
            }
            if($this->EbayBuyerRequirementModel->minimum_feedback_score != -1000)
            {
                $this->sendXml .= '<MinimumFeedbackScore>'.$this->EbayBuyerRequirementModel->minimum_feedback_score.'</MinimumFeedbackScore>';
            }
            if($this->EbayBuyerRequirementModel->ship_to_registration_country == 1)
            {
                $this->sendXml .= '<ShipToRegistrationCountry>true</ShipToRegistrationCountry>';
            }
            if($this->EbayBuyerRequirementModel->verified_user_requirements_verifieduser == 1 && !in_array($this->EbayListingModel->siteid,array(0)))
            {
                $this->sendXml .= '<VerifiedUserRequirements><VerifiedUser>true</VerifiedUser></VerifiedUserRequirements>';
            }
            $this->sendXml .= '</BuyerRequirementDetails>';
        }
        if($this->EbayPublishConfigModel->selling_price_type == 'Chinese' && !empty($this->EbayListingModel->buy_it_now_price))
        {
            $this->sendXml .= '<BuyItNowPrice currencyID="'.$this->EbayListingModel->currency.'">'.$this->EbayListingModel->buy_it_now_price.'</BuyItNowPrice>';
        }
        if(!empty($this->EbayListingModel->store_first_category_id) || !empty($this->EbayListingModel->store_second_category_id))
        {
            $this->sendXml .= '<Storefront>';
            if(!empty($this->EbayListingModel->store_first_category_id))
                $this->sendXml .= "<StoreCategoryID>{$this->EbayListingModel->store_first_category_id}</StoreCategoryID>";
            if(!empty($this->EbayListingModel->store_second_category_id))
                $this->sendXml .= "<StoreCategory2ID>{$this->EbayListingModel->store_second_category_id}</StoreCategory2ID>";
            $this->sendXml .= '</Storefront>';
        }
        $this->sendXml .= '<CategoryBasedAttributesPrefill>true</CategoryBasedAttributesPrefill>';
        $this->sendXml .= '<CategoryMappingAllowed>true</CategoryMappingAllowed>';
        $this->sendXml .= '<ConditionID>1000</ConditionID>';
        $this->sendXml .= '<Country>'.trim($this->EbayPublishConfigModel->nation).'</Country>';
        $this->sendXml .= '<Currency>'.$this->EbayListingModel->currency.'</Currency>';
//        $this->EbayListingModel->template_describe = VHelper::resourceLinkTransformHttps($this->EbayListingModel->template_describe);
        if(empty($this->EbayListingModel->template_describe))
        {
            $this->handleResponse('描述不能为空');
            throw new Exception('描述不能为空');
        }
        $replaceObj = new ReplaceThirdResource();
        $replaceObj->subject = $this->EbayListingModel->template_describe;
        $this->EbayListingModel->template_describe = $replaceObj->replace();
        $this->sendXml .= '<Description><![CDATA[<div style="max-width: 755px;margin: 0 auto;">'.$this->EbayListingModel->template_describe.'</div>]]></Description>';
        $this->sendXml .= '<DispatchTimeMax>'.$this->EbayShippingModel->dispatch_time_max.'</DispatchTimeMax>';
        $this->sendXml .= '<ListingDuration>'.trim($this->EbayPublishConfigModel->listing_duration).'</ListingDuration>';
        $this->sendXml .= '<ListingType>'.$this->EbayPublishConfigModel->selling_price_type.'</ListingType>';
        $this->sendXml .= '<Location>'.$this->EbayPublishConfigModel->goods_site.'</Location>';
        $this->sendXml .= '<PaymentMethods>PayPal</PaymentMethods>';
        if(!empty($this->EbayPaymentMethodTypeModel->payment_methods_value_ids))
        {
            $EbayPaymentMethodValueModels = UebModel::model('EbayPaymentMethodValue')->findAllByPk(explode('|',$this->EbayPaymentMethodTypeModel->payment_methods_value_ids));
            foreach($EbayPaymentMethodValueModels as $EbayPaymentMethodValueModel)
            {
                if($EbayPaymentMethodValueModel->value != 'PayPal')
                {
                    $this->sendXml .= '<PaymentMethods>'.$EbayPaymentMethodValueModel->value.'</PaymentMethods>';
                }
            }
        }
        if($this->EbayPaymentMethodTypeModel->use_paypal_scheme)
        {
            $EbayPaymentPaypalSchemeModel = UebModel::model('EbayPaymentPaypalScheme')->find('ebay_account_id=:ebay_account_id',array(':ebay_account_id'=>$this->EbayAccountModel->id));
            if(empty($EbayPaymentPaypalSchemeModel))
            {
                $this->handleResponse('收款方案找不到ebay账号数据');
                throw new Exception('收款方案找不到ebay账号数据');
            }
            if(!array_key_exists($this->EbayListingModel->currency,EbayProductsAssign::$currencyRate))
            {
                $this->handleResponse('单价计算方案汇率未提供，不能使用收款方案');
                throw new Exception('单价计算方案汇率未提供，不能使用收款方案');
            }
            if($this->EbayListingModel->currency == 'USD')
                $dividingMoney = $EbayPaymentPaypalSchemeModel->dividing_money;
            else
                $dividingMoney = (($EbayPaymentPaypalSchemeModel->dividing_money)/(EbayProductsAssign::$currencyRate['USD']))*(EbayProductsAssign::$currencyRate[$this->EbayListingModel->currency]);
            if($this->EbayListingModel->start_price < $dividingMoney)
            {
                $this->sendXml .= '<PayPalEmailAddress>'.$EbayPaymentPaypalSchemeModel->sub_papay_account.'</PayPalEmailAddress>';
            }
            else
            {
                $this->sendXml .= '<PayPalEmailAddress>'.$EbayPaymentPaypalSchemeModel->sup_papay_account.'</PayPalEmailAddress>';
            }
        }
        else
            $this->sendXml .= '<PayPalEmailAddress>'.$this->EbayPaymentMethodTypeModel->paypal_email.'</PayPalEmailAddress>';
        $this->sendXml .= '<PictureDetails>';
        $this->sendXml .= '<GalleryType>Gallery</GalleryType>';
        $imageModel = new Productimage();
//        $hostUrl = empty($this->EbayAccountModel->image_host) ? Yii::app()->request->getHostInfo():$this->EbayAccountModel->image_host;
        $hostUrl = Yii::app()->request->getHostInfo();
        $imageList = empty($this->EbayListingModel->image_galleries) ? '':unserialize($this->EbayListingModel->image_galleries);
        if(!is_array($imageList) || empty($imageList))
        {
            $imageList = $imageModel->getFtLists($this->EbayListingModel->sku);
        }
        if(!empty($imageList))
        {
            $this->sendXml .= '<PhotoDisplay>PicturePack</PhotoDisplay>';
            $imageListCount = 0;
            foreach($imageList as $imageListUrl)
            {
                if($imageListCount > 11)
                    break;
                $imageListUrl = VHelper::imageLinkTransformHttps($imageListUrl);
                if(!empty($imageListUrl))
                {
                    /*if($imageListCount === 0)
                        $imageListUrl = str_replace('https://image-us.bigbuy.win','http://image-us.bigbuy.win',$imageListUrl);*/
                    file_get_contents($imageListUrl);
                    $this->sendXml .= "<PictureURL>$imageListUrl</PictureURL>";
                    $imageListCount++;
                }
            }
            unset($imageListCount,$imageList);
//            if($this->verb == 'AddFixedPriceItem')
//            {
                $this->sendXml .= '<PictureSource>EPS</PictureSource>';
//            }
        }
        $this->sendXml .= '</PictureDetails>';
        $this->sendXml .= empty($this->EbayPublishConfigModel->postcodes) ? '':'<PostalCode>'.$this->EbayPublishConfigModel->postcodes.'</PostalCode>';
        $this->sendXml .= '<PrimaryCategory><CategoryID>'.$this->EbayListingModel->primary_category.'</CategoryID></PrimaryCategory>';
        if(!empty($this->EbayListingModel->secondary_category))
        {
            $this->sendXml .= '<SecondaryCategory><CategoryID>'.$this->EbayListingModel->secondary_category.'</CategoryID></SecondaryCategory>';
        }
        //$childrenEbayListingModel = UebModel::model('EbayListing')->findAll('status=0 and parent_sku="'.$this->EbayListingModel->sku.'" and siteid='.$this->EbayListingModel->siteid.' and ebay_account_id='.$this->EbayListingModel->ebay_account_id);
        $childrenEbayListingModel = UebModel::model('EbayListing')->findAll('status=0 and parent_id="'.$this->EbayListingModel->id.'" and siteid='.$this->EbayListingModel->siteid.' and ebay_account_id='.$this->EbayListingModel->ebay_account_id);
        $this->sendXml .= '<ProductListingDetails>';
        /*if($this->EbayPublishConfigModel->selling_price_type != 'AddFixedPriceItem' && !empty($this->EbayListingModel->brand) && !empty($this->EbayListingModel->mpn))
        {
            $this->sendXml .= '<BrandMPN>';
            if(!empty($this->EbayListingModel->brand))
                $this->sendXml .= '<Brand>'.$this->EbayListingModel->brand.'</Brand>';
            if(!empty($this->EbayListingModel->mpn))
                $this->sendXml .= '<MPN>'.$this->EbayListingModel->mpn.'</MPN>';
            $this->sendXml .= '</BrandMPN>';

        }*/
        if(empty($childrenEbayListingModel) || $this->EbayPublishConfigModel->selling_price_type == 'AddItem')
        {
            if(!empty($this->EbayListingModel->ean))
                $this->sendXml .= '<EAN>'.$this->EbayListingModel->ean.'</EAN>';
            if(!empty($this->EbayListingModel->isbn))
            {
                $this->sendXml .= '<ISBN>'.$this->EbayListingModel->isbn.'</ISBN>';
            }
            if(!empty($this->EbayListingModel->upc))
                $this->sendXml .= '<UPC>'.$this->EbayListingModel->upc.'</UPC>';
        }
        $this->sendXml .= '<ReturnSearchResultOnDuplicates>true</ReturnSearchResultOnDuplicates>';
        $this->sendXml .= '<UseFirstProduct>true</UseFirstProduct>';
        $this->sendXml .= '<IncludeeBayProductDetails>true</IncludeeBayProductDetails>';
        $this->sendXml .= '<IncludeStockPhotoURL>false</IncludeStockPhotoURL>';
        $this->sendXml .= '</ProductListingDetails>';
        $this->sendXml .= '<ShippingDetails>';
        $this->sendXml .= '<ShippingType>'.$this->EbayShippingModel->shipping_type.'</ShippingType>';
        if($this->EbayShippingModel->shipping_type != 'Flat')
        {
            $this->sendXml .= '<CalculatedShippingRate>';
            if(in_array($this->EbayShippingModel->shipping_type,array('Calculated','FlatDomesticCalculatedInternational')))
            {
                $this->sendXml .= '<InternationalPackagingHandlingCosts currencyID="'.$this->EbayListingModel->currency.'">'.$this->EbayShippingModel->international_packaging_handling_costs.'</InternationalPackagingHandlingCosts>';
            }
            $this->sendXml .= '<OriginatingPostalCode>'.$this->EbayShippingModel->originating_postal_code.'</OriginatingPostalCode>';
            if(in_array($this->EbayShippingModel->shipping_type,array('Calculated','CalculatedDomesticFlatInternational')))
            {
                $this->sendXml .= '<PackagingHandlingCosts currencyID="'.$this->EbayListingModel->currency.'">'.$this->EbayShippingModel->packaging_handling_costs.'</PackagingHandlingCosts>';
            }
            $this->sendXml .= '</CalculatedShippingRate>';
        }
        if(!empty($this->EbayShippingModel->exclude_ship_to_location))
        {
            $excludeShipToLocations = explode('|',$this->EbayShippingModel->exclude_ship_to_location);
            foreach($excludeShipToLocations as $excludeShipToLocation)
            {
                $this->sendXml .=  "<ExcludeShipToLocation>$excludeShipToLocation</ExcludeShipToLocation>";
            }
        }
        if(empty($this->EbayShippingModel->EbayShippingServiceFee))
        {
            //不是存序列化值来的数据
            $InternationalShippingServiceOptions = UebModel::model('EbayShippingServiceFee')->findAll('shipping_id=:shipping_id and domestic_or_international=2 order by shipping_service_priority',array(':shipping_id'=>$this->EbayShippingModel->id));
            $DomesticShippingServiceOptions = UebModel::model('EbayShippingServiceFee')->findAll('shipping_id=:shipping_id and domestic_or_international=1 order by shipping_service_priority',array(':shipping_id'=>$this->EbayShippingModel->id));
        }
        else
        {
            //存序列化值来的数据
            foreach($this->EbayShippingModel->EbayShippingServiceFee as $EbayShippingServiceFeeK=>$EbayShippingServiceFeeV)
            {
                $EbayShippingServiceFeeV = (object)$EbayShippingServiceFeeV;
                switch($EbayShippingServiceFeeV->domestic_or_international)
                {
                    case '2':
                        $InternationalShippingServiceOptions[$EbayShippingServiceFeeV->shipping_service_priority] = $EbayShippingServiceFeeV;
                        break;
                    case '1':
                        $DomesticShippingServiceOptions[$EbayShippingServiceFeeV->shipping_service_priority] = $EbayShippingServiceFeeV;
                }
            }
            ksort($InternationalShippingServiceOptions);
            ksort($DomesticShippingServiceOptions);
        }
        foreach($InternationalShippingServiceOptions as $InternationalShippingServiceOption)
        {
            $this->sendXml .= '<InternationalShippingServiceOption>';
            $this->sendXml .= '<ShippingService>'.$InternationalShippingServiceOption->shipping_service.'</ShippingService>';
            if(in_array($this->EbayShippingModel->shipping_type,array('Flat','CalculatedDomesticFlatInternational')))
            {
                $this->sendXml .= '<ShippingServiceAdditionalCost currencyID="'.$this->EbayListingModel->currency.'">'.$InternationalShippingServiceOption->service_additional_cost.'</ShippingServiceAdditionalCost>';
                $this->sendXml .= '<ShippingServiceCost currencyID="'.$this->EbayListingModel->currency.'">'.$InternationalShippingServiceOption->service_cost.'</ShippingServiceCost>';
            }
            $this->sendXml .= '<ShippingServicePriority>'.$InternationalShippingServiceOption->shipping_service_priority.'</ShippingServicePriority>';
            $InternationalShippingToLocations = explode('|',$InternationalShippingServiceOption->ship_to_location);
            foreach($InternationalShippingToLocations as $InternationalShippingToLocation)
            {
                if(!empty($InternationalShippingToLocation))
                    $this->sendXml .= "<ShipToLocation>$InternationalShippingToLocation</ShipToLocation>";
            }
            $this->sendXml .= '</InternationalShippingServiceOption>';
        }

        foreach($DomesticShippingServiceOptions as $DomesticShippingServiceOption)
        {
            $this->sendXml .= '<ShippingServiceOptions>';
            $this->sendXml .= '<ShippingService>'.$DomesticShippingServiceOption->shipping_service.'</ShippingService>';
            if(in_array($this->EbayShippingModel->shipping_type,array('Flat','FlatDomesticCalculatedInternational')))
            {
                if($DomesticShippingServiceOption->free_shipping == 1)
                {
                    $this->sendXml .= '<FreeShipping>true</FreeShipping>';
                }
                $this->sendXml .= '<ShippingServiceAdditionalCost currencyID="'.$this->EbayListingModel->currency.'">'.$DomesticShippingServiceOption->service_additional_cost.'</ShippingServiceAdditionalCost>';
                $this->sendXml .= '<ShippingServiceCost currencyID="'.$this->EbayListingModel->currency.'">'.$DomesticShippingServiceOption->service_cost.'</ShippingServiceCost>';
            }
            $this->sendXml .= '<ShippingServicePriority>'.$DomesticShippingServiceOption->shipping_service_priority.'</ShippingServicePriority>';
            $this->sendXml .= '</ShippingServiceOptions>';
        }
        $this->sendXml .= '</ShippingDetails>';
        if($this->EbayShippingModel->shipping_type != 'Flat')
        {
            $this->sendXml .= '<ShippingPackageDetails>';
            $this->sendXml .= '<MeasurementUnit>'.$this->EbayShippingModel->measurement_unit.'</MeasurementUnit>';
            $this->sendXml .= '<PackageDepth>'.$this->EbayShippingModel->package_depth.'</PackageDepth>';
            $this->sendXml .= '<PackageLength>'.$this->EbayShippingModel->package_length.'</PackageLength>';
            $this->sendXml .= '<PackageWidth>'.$this->EbayShippingModel->package_width.'</PackageWidth>';
            if($this->EbayShippingModel->shipping_irregular == 1)
            {
                $this->sendXml .= '<ShippingIrregular>true</ShippingIrregular>';
            }
            $this->sendXml .= '<ShippingPackage>'.$this->EbayShippingModel->shipping_package.'</ShippingPackage>';
            $this->sendXml .= '<WeightMajor>'.$this->EbayShippingModel->weight_major.'</WeightMajor>';
            $this->sendXml .= '<WeightMinor>'.$this->EbayShippingModel->weight_minor.'</WeightMinor>';
            $this->sendXml .= '</ShippingPackageDetails>';
        }
        $this->sendXml .= '<ReturnPolicy>';
        if(!empty($this->EbayReturnPolicyModel->description))
        {
            $this->sendXml .= '<Description>'.$this->EbayReturnPolicyModel->description.'</Description>';
        }
        if(!empty($this->EbayReturnPolicyModel->refund_option))
            $this->sendXml .= '<RefundOption>'.$this->EbayReturnPolicyModel->refund_option.'</RefundOption>';
        if(!empty($this->EbayReturnPolicyModel->restocking_fee_value_option))
            $this->sendXml .= '<RestockingFeeValueOption>'.$this->EbayReturnPolicyModel->restocking_fee_value_option.'</RestockingFeeValueOption>';
        if(!empty($this->EbayReturnPolicyModel->returns_accepted_option))
            $this->sendXml .= '<ReturnsAcceptedOption>'.$this->EbayReturnPolicyModel->returns_accepted_option.'</ReturnsAcceptedOption>';
        if(!empty($this->EbayReturnPolicyModel->returns_within_option))
            $this->sendXml .= '<ReturnsWithinOption>'.$this->EbayReturnPolicyModel->returns_within_option.'</ReturnsWithinOption>';
        if(!empty($this->EbayReturnPolicyModel->shipping_cost_paid_by_option))
            $this->sendXml .= '<ShippingCostPaidByOption>'.$this->EbayReturnPolicyModel->shipping_cost_paid_by_option.'</ShippingCostPaidByOption>';
        $this->sendXml .= '</ReturnPolicy>';
        //$itemAttributes = UebModel::model('EbayItemAttribute')->selectAllAsArray('key,value','ebay_listing_id='.$this->EbayListingModel->id);
        $itemAttributes = VHelper::selectAsArray('EbayItemAttribute','`key`,GROUP_CONCAT(value SEPARATOR "{$$}") `values`','ebay_listing_id='.$this->EbayListingModel->id,false,'key');
        if(!empty($itemAttributes) || !empty($this->EbayListingModel->brand))
        {
            $this->sendXml .= '<ItemSpecifics>';
            if(!empty($this->EbayListingModel->brand))
            {
                $this->sendXml .= '<NameValueList>';
                $this->sendXml .= '<Name>Brand</Name>';
                $this->sendXml .= '<Value>'.htmlentities($this->EbayListingModel->brand,ENT_XML1).'</Value>';
                $this->sendXml .= '</NameValueList>';
                if(!empty($this->EbayListingModel->mpn))
                {
                    $this->sendXml .= '<NameValueList>';
                    $this->sendXml .= '<Name>MPN</Name>';
                    $this->sendXml .= "<Value>{$this->EbayListingModel->mpn}</Value>";
                    $this->sendXml .= '</NameValueList>';
                }
            }
            if(!empty($itemAttributes))
            {
                foreach ($itemAttributes as $itemAttributeV)
                {
                    $this->sendXml .= '<NameValueList>';
                    $this->sendXml .= '<Name>'.htmlentities($itemAttributeV['key'],ENT_XML1).'</Name>';
                    $itemAttributeValues = explode('{$$}',$itemAttributeV['values']);
                    foreach ($itemAttributeValues as $itemAttributeValue)
                    {
                        $this->sendXml .= '<Value>'.htmlentities($itemAttributeValue,ENT_XML1).'</Value>';
                    }
                    $this->sendXml .= '</NameValueList>';
                }
            }
            $this->sendXml .= '</ItemSpecifics>';
        }
        $this->sendXml .='<Site>'.UebModel::model('EbaySites')->find("siteid={$this->EbayListingModel->siteid}")->value.'</Site>';
        $this->sendXml .= '<SKU>'.$this->EbayListingModel->sell_sku.'</SKU>';
        if(!empty($this->EbayListingModel->reserve_price) && $this->EbayPublishConfigModel->selling_price_type == 'Chinese')
            $this->sendXml .= '<ReservePrice currencyID="'.$this->EbayListingModel->currency.'">'.$this->EbayListingModel->reserve_price.'</ReservePrice>';
        $this->sendXml .= '<Title>'.htmlentities($this->EbayListingModel->sell_title,ENT_XML1).'</Title>';
        if(!empty($this->EbayListingModel->sell_sub_title))
        {
            $this->sendXml .= '<SubTitle>'.$this->EbayListingModel->sell_sub_title.'</SubTitle>';
        }
        $UUID = range('A','F');
        $fillChars = 32-strlen($this->EbayListingModel->id);
        $mergeCount = ceil($fillChars/6);
        for($i = 1;$i < $mergeCount;$i++)
            $UUID = array_merge($UUID,$UUID);
        shuffle($UUID);
        $UUID = $this->EbayListingModel->id.implode('',array_slice($UUID,0,$fillChars));
        $this->sendXml .= '<UUID>'.$UUID.'</UUID>';
        $deleteItemLevelPrice = false;
        if($this->verb == 'AddFixedPriceItem' && $childrenEbayListingModel)
        {
                $deleteItemLevelPrice = true;
                $this->sendXml .= '<Variations>';
                $variationSpecificsSet = array(); //存标签VariationSpecificsSet中的内容
                foreach($childrenEbayListingModel as $childEbayListingModel)
                {
                    //子sku中
                    $this->sendXml .= '<Variation>';
                    if(!empty($childEbayListingModel->ean) || !empty($childEbayListingModel->isbn) || !empty($childEbayListingModel->upc))
                    {
                        $this->sendXml .= '<VariationProductListingDetails>';
                        if(!empty($childEbayListingModel->ean))
                            $this->sendXml .= '<EAN>'.$childEbayListingModel->ean.'</EAN>';
                        if(!empty($childEbayListingModel->isbn))
                            $this->sendXml .= '<ISBN>'.$childEbayListingModel->isbn.'</ISBN>';
                        if(!empty($childEbayListingModel->upc))
                            $this->sendXml .= '<UPC>'.$childEbayListingModel->upc.'</UPC>';
                        $this->sendXml .= '</VariationProductListingDetails>';
                    }
                    $this->sendXml .= '<Quantity>'.$childEbayListingModel->quantity.'</Quantity>';
                    if($childEbayListingModel->sell_sku === null || $childEbayListingModel->sell_sku === '')
                    {
                        $this->sendXml .= '<SKU>'.$childEbayListingModel->sku.'</SKU>';
                    }
                    else
                    {
                        $this->sendXml .= '<SKU>'.$childEbayListingModel->sell_sku.'</SKU>';
                    }
                    $this->sendXml .= '<StartPrice>'.$childEbayListingModel->start_price.'</StartPrice>';
                    $childrenItemAttributes = UebModel::model('EbayItemAttribute')->selectAllAsArray('key,value','ebay_listing_id='.$childEbayListingModel->id);
                    if(!empty($childrenItemAttributes))
                    {
                        $childrenItemAttributes = UebModel::uniqueArrayField($childrenItemAttributes,'key');
                        $this->sendXml .= '<VariationSpecifics>';
                        $countVariationNameValueList = 1;
                        foreach($childrenItemAttributes as $childrenItemAttributeK=>$childrenItemAttributeV)
                        {
                            //同属性名中
                            if($countVariationNameValueList >5)
                                break;
                            if(!isset($variationSpecificsSet[$childrenItemAttributeK]))
                            {
                                $variationSpecificsSet[$childrenItemAttributeK]['xml'] = '<Name>'.htmlentities($childrenItemAttributeK,ENT_XML1).'</Name>';
                                $variationSpecificsSet[$childrenItemAttributeK]['value'] = array();
                            }
                            $this->sendXml .= '<NameValueList>';
                            $this->sendXml .= '<Name>'.htmlentities($childrenItemAttributeK,ENT_XML1).'</Name>';
                            foreach($childrenItemAttributeV as $childrenAttributeValue)
                            {
                                //同属性名不同属性值
                                if(!in_array($childrenAttributeValue['value'],$variationSpecificsSet[$childrenItemAttributeK]['value']))
                                {
                                    $variationSpecificsSet[$childrenItemAttributeK]['value'][] = $childrenAttributeValue['value'];
                                    $attributeValueRelativeModel[$childrenAttributeValue['value']] = $childEbayListingModel; //子sku的图片关联的属性值，后面需要用到
                                    $variationSpecificsSet[$childrenItemAttributeK]['xml'] .= '<Value>'.htmlentities($childrenAttributeValue['value'],ENT_XML1).'</Value>';
                                }
                                $this->sendXml .= '<Value>'.htmlentities($childrenAttributeValue['value'],ENT_XML1).'</Value>';
                            }
                            $this->sendXml .= '</NameValueList>';
                            $countVariationNameValueList++;
                        }
                        $this->sendXml .= '</VariationSpecifics>';
                    }
                    $this->sendXml .= '</Variation>';
                }
                if(!empty($variationSpecificsSet))
                {
                    $this->sendXml .= '<VariationSpecificsSet>';
                    foreach ($variationSpecificsSet as $variationSpecificsSetV)
                    {
                        $this->sendXml .= '<NameValueList>';
                        $this->sendXml .= $variationSpecificsSetV['xml'];
                        $this->sendXml .= '</NameValueList>';
                    }
                    $this->sendXml .= '</VariationSpecificsSet>';
                    $this->sendXml .= '<Pictures>';
                    $variationsPicturesAttributes = reset($variationSpecificsSet);
                    $this->sendXml .= '<VariationSpecificName>'.key($variationSpecificsSet).'</VariationSpecificName>';
                    foreach($variationsPicturesAttributes['value'] as $variationsPicturesAttributeValue)
                    {
                        $this->sendXml .= '<VariationSpecificPictureSet>';
                        $this->sendXml .= '<VariationSpecificValue>'.htmlentities($variationsPicturesAttributeValue,ENT_XML1).'</VariationSpecificValue>';
                        if(empty($attributeValueRelativeModel[$variationsPicturesAttributeValue]->image_galleries))
                        {
                            $childImageList = $imageModel->getFtLists($attributeValueRelativeModel[$variationsPicturesAttributeValue]->sku);
                        }
                        else
                        {
                            $childImageList = unserialize($attributeValueRelativeModel[$variationsPicturesAttributeValue]->image_galleries);
                        }
                        $imageListCount = 0;
                        foreach($childImageList as $childImageListUrl)
                        {
                            if($imageListCount > 11)
                                break;
                            $childImageListUrl = VHelper::imageLinkTransformHttps($childImageListUrl);
                            if(!empty($childImageListUrl))
                            {
                                $this->sendXml .= "<PictureURL>$childImageListUrl</PictureURL>";
                                $imageListCount++;
                            }
                        }
                        unset($imageListCount,$childImageList);
                        $this->sendXml .= '</VariationSpecificPictureSet>';
                    }
                    $this->sendXml .= '</Pictures>';
                }
                $this->sendXml .= '</Variations>';
        }
        if(!$deleteItemLevelPrice)
        {
            $this->sendXml .= '<StartPrice currencyID="'.$this->EbayListingModel->currency.'">'.$this->EbayListingModel->start_price.'</StartPrice>';
            $this->sendXml .= '<Quantity>'.$this->EbayListingModel->quantity.'</Quantity>';
        }
//         if(trim($this->EbayListingModel->schedule_time) && $this->EbayListingModel->schedule_time != '') {
//             $this->sendXml .= '<ScheduleTime>'.$this->EbayListingModel->schedule_time.'</ScheduleTime>';
//         }
        if($this->EbayListingModel->best_offer_enabled == 1) {
            $this->sendXml .= '<BestOfferDetails><BestOfferEnabled>true</BestOfferEnabled></BestOfferDetails>';
        }
        if($this->EbayListingModel->best_offer_accept_price > 0 || $this->EbayListingModel->best_offer_deline_price > 0) {
            $this->sendXml .= '<ListingDetails>';
            if($this->EbayListingModel->best_offer_accept_price > 0) {
                $this->sendXml .= '<BestOfferAutoAcceptPrice>'.$this->EbayListingModel->best_offer_accept_price.'</BestOfferAutoAcceptPrice>';
            }
            if($this->EbayListingModel->best_offer_deline_price > 0) {
                $this->sendXml .= '<MinimumBestOfferPrice>'.$this->EbayListingModel->best_offer_deline_price.'</MinimumBestOfferPrice>';
            }
            $this->sendXml .= '</ListingDetails>';
        }

        
        $this->sendXml .= '</Item>';
        $this->sendXml .= $endSendXml;
    }
}