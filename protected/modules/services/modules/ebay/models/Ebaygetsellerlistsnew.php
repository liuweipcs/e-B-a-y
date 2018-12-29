<?php

class Ebaygetsellerlistsnew {
    
    protected $accountid;
    
    protected $pagenum;
    
    protected $forcedflag=false;
    
    public function setaccount($accountid) {
        $this->accountid = $accountid;
    }
    
    public function setpagenum($pagenum) {
        $this->pagenum = $pagenum;
    }
    
    public function setforced($forcedflag) {
        $this->forcedflag = $forcedflag;
    }
    
    /**获取在线listing
     * 
     * @param  $startTime
     * @param  $endTime
     * @param  $type start is starttimefrom, end endtimefrom
     */
    public function getsellerlist($startTime,$endTime,$type='end') {
        //判断时间
        if(!$startTime || !$endTime) {
            return array('status'=>500, 'msg'=>'开始时间或结束时间不能为空');
        }
        //判断帐号
        if(empty($this->accountid)) {
            return array('status'=>500, 'msg'=>'eBay帐号不能为空');
        }
        
        $ApiObj = new Ebaygetsellerlistapi();
        $ApiObj->setPageNum($this->pagenum);
        
        if($type == 'end') {
            $ApiObj->setEndTimeFrom($startTime);
            $ApiObj->setEndTimeTo($endTime);
        } else {
            $ApiObj->setStartFrom($startTime);
            $ApiObj->setStartTo($endTime);
        }
        
        $response = $ApiObj->setShortName($this->accountid)
                ->setSiteId(0)               
                ->setVerb('GetSellerList')
                ->setRequest()
                ->sendHttpRequest()
                ->getResponse();
//        echo "<pre>";
//        var_dump($response);exit();
        $result = $this->updatelisting($response);
        return $result;
    }
    
    //写入数据
    public function updatelisting($data) {
        $ack = isset($data->Ack)?$data->Ack:'';

        if($ack == '' || $ack == 'Failure') {
            $refresh_status = '';
            $err_msg = empty($data->Errors->LongMessage)?$data->Errors['0']->LongMessage:$data->Errors->LongMessage;
            $errcode = $data->Errors->ErrorCode;
            if(is_object($data->Errors->ErrorCode)) {
                $errcode = $data->Errors->ErrorCode->__toString();
            }
            if('340' == $errcode) { //pagenum 超范围
                $refresh_status = 200;
            }
            
            return array('status'=>500, 'refresh_status'=>$refresh_status, 'msg'=>'API接口调用失败：'.$err_msg);
        }

        $HasMoreItems = $data->HasMoreItems;
        $Totalpagenum = $data->PaginationResult->TotalNumberOfPages;
        //item 信息
        $itemArray = $data->ItemArray->Item;
        if(empty($itemArray)) {
            
            return array('status'=>500, 'msg'=>'没有Listing数据');
        }
        
        foreach($itemArray as $itemvalue) {
            //判断item是否存在
            $itemid = $itemvalue->ItemID;
            if(empty($itemid)) {
                return array('status'=>500, 'msg'=>'API错误：ItemID 不存在');
            }
            
            //更新列表
            $storeName = $data->Seller->UserID;
            $listInfo = $this->updatelist($itemvalue, $storeName);
            if($listInfo['status'] == '300') { //listing 已修改等待更新，不用做同步数据
                continue;
            }
            
            if($listInfo['status'] == 500) {
                return $listInfo;
            }
            $listId = $listInfo['id'];
             
            //更新多属性
            $variationsInfo = $this->updatevariations($itemid, $itemvalue->Variations);                
            //更新描述
            $descrtionInfo = $this->updatedescrtion($itemid, $itemvalue->Description);               
            //更新运输方式
            $shippingInfo = $this->updateshipping($itemid, $itemvalue->ShippingDetails);               
            //更新图片列表
            $imageInfo = $this->updateimage($itemid, $itemvalue->PictureDetails,$listId);               
        }
        //echo "ok";
        //exit();
        return array('status'=>200, 'hasmore'=>$HasMoreItems,'totalpagenum'=>$Totalpagenum);
    }
    
    //更新listing列表
    public function updatelist($itemvalue, $storeName,$xmlDataTime) {
        set_time_limit(120);
        $listingObj = UebModel::model('Ebayonlinelisting')->find('itemid=:itemid',array(':itemid'=>$itemvalue->ItemID));
        if(empty($listingObj)) {
            $listingObj = new Ebayonlinelisting();
        } else {
           $id = $listingObj->id;
           if(!$this->forcedflag) { //判断是否手动更新
               if($listingObj->status == '1') { //listing 已修改等待更新，不用做同步数据 1
                   return array('status'=>'300'); 
               }
           }
        }
        
        //站点
        $siteInfo = array_flip(UebModel::model('Ebayonlinelisting')->getsite());
        $listingObj->xml_data_time = $xmlDataTime;
        $listingObj->account = $storeName;
        $listingObj->itemid = $itemvalue->ItemID;
        $listingObj->sku = EbayListing::decryptSku($itemvalue->SKU)['sku'];
        $listingObj->sell_sku = $itemvalue->SKU;

        $listingObj->start_price = $itemvalue->StartPrice;
        $listingObj->buy_it_now_price = $itemvalue->BuyItNowPrice;
        $listingObj->reserve_price = $itemvalue->ReservePrice;
        
        $listingObj->currency = $itemvalue->Currency;
        $listingObj->auto_pay = $itemvalue->AutoPay;
        $listingObj->buyer_protection = $itemvalue->BuyerProtection;
        $listingObj->start_time = strtotime($itemvalue->ListingDetails->StartTime);
        $listingObj->end_time = strtotime($itemvalue->ListingDetails->EndTime);
        $listingObj->view_item_url = $itemvalue->ListingDetails->ViewItemURL;
        
        $listingObj->ending_reason = isset($itemvalue->ListingDetails->EndingReason)?$itemvalue->ListingDetails->EndingReason:"";
        $listingObj->layoutid = $itemvalue->ListingDesigner->LayoutID;
        $listingObj->theme_id = $itemvalue->ListingDesigner->ThemeID;
        $listingObj->listing_duration = $itemvalue->ListingDuration; //gtc
        $listingObj->listing_type = $itemvalue->ListingType;  //chinese FixedPriceItem
        $listingObj->location = $itemvalue->Location;
        
        $listingObj->payment_methods = $itemvalue->PaymentMethods;
        $listingObj->paypal_email_address = $itemvalue->PayPalEmailAddress;
        $listingObj->primary_category_id = $itemvalue->PrimaryCategory->CategoryID;
        $listingObj->primary_category_name = $itemvalue->PrimaryCategory->CategoryName;
        $listingObj->second_category_id = isset($itemvalue->SecondaryCategory->CategoryID)?$itemvalue->SecondaryCategory->CategoryID:0;
        $listingObj->second_category_name = isset($itemvalue->SecondaryCategory->CategoryName)?$itemvalue->SecondaryCategory->CategoryName:'';
        
        $listingObj->private_listing = $itemvalue->PrivateListing;
        $listingObj->product_listing_details_upc = '';
        $listingObj->product_listing_details_brand = '';
        $listingObj->product_listing_details_mpn = '';
        $listingObj->product_listing_details_isbn = '';
        $listingObj->product_listing_include = '';
        
        $listingObj->quantity = ($itemvalue->Quantity - $itemvalue->SellingStatus->QuantitySold);
        $listingObj->site = $itemvalue->Site;
        $listingObj->store_category_id = $itemvalue->Storefront->StoreCategoryID;
        $listingObj->store_second_category_id = $itemvalue->Storefront->StoreCategory2ID;
        $listingObj->store_url = $itemvalue->Storefront->StoreURL;
        $listingObj->title = $itemvalue->Title;
        
        $listingObj->uuid = isset($itemvalue->UUID)?$itemvalue->UUID:'';
        $listingObj->bestoffer_enabled = isset($itemvalue->BestOfferDetails->BestOfferEnabled)?$itemvalue->BestOfferDetails->BestOfferEnabled:'';
        $listingObj->bestoffer_count = isset($itemvalue->BestOfferDetails->BestOfferCount)?$itemvalue->BestOfferDetails->BestOfferCount:'';
        $listingObj->new_bestoffer = isset($itemvalue->BestOfferDetails->NewBestOffer)?$itemvalue->BestOfferDetails->NewBestOffer:'';
        $listingObj->dispatch_time_max = isset($itemvalue->DispatchTimeMax)?$itemvalue->DispatchTimeMax:"1";
        $listingObj->quantity_sold = $itemvalue->SellingStatus->QuantitySold;
        
        $listingObj->hit_count = $itemvalue->HitCount;
        $listingObj->refund_option = $itemvalue->ReturnPolicy->RefundOption;
        $listingObj->refund = $itemvalue->ReturnPolicy->Refund;
        $listingObj->returns_within_option = $itemvalue->ReturnPolicy->ReturnsWithinOption;
        $listingObj->returns_accepted_option = $itemvalue->ReturnPolicy->ReturnsAcceptedOption;
        $listingObj->returns_description = $itemvalue->ReturnPolicy->Description;
        
        $listingObj->shipping_cost_paid_by_option = $itemvalue->ReturnPolicy->ShippingCostPaidByOption;
        $listingObj->restocking_fee_value_option = isset($itemvalue->ReturnPolicy->RestockingFeeValueOption)?$itemvalue->ReturnPolicy->RestockingFeeValueOption:'';
        $listingObj->siteid = $siteInfo[$itemvalue->Site];
        if(is_object($itemvalue->Site)) {
            $listingObj->siteid = $siteInfo[$itemvalue->Site->__toString()];
        }
        
        $listingObj->country = $itemvalue->Country;
        $listingObj->variation_multi = '0';
        
        $listingObj->sub_title = isset($itemvalue->SubTitle)?$itemvalue->SubTitle:'';
        $listingObj->bestoffer_accept_price = isset($itemvalue->ListingDetails->BestOfferAutoAcceptPrice)?$itemvalue->ListingDetails->BestOfferAutoAcceptPrice:'';
        $listingObj->bestoffer_decline_price = isset($itemvalue->ListingDetails->MinimumBestOfferPrice)?$itemvalue->ListingDetails->MinimumBestOfferPrice:'';
        $listingObj->listing_status = $itemvalue->SellingStatus->ListingStatus;
        $listingObj->status = 0;
        $listingObj->update_time = date('Y-m-d H:i:s');
        
        $listingObj->postcode = is_string($itemvalue->PostalCode)?$itemvalue->PostalCode:$itemvalue->PostalCode['0'];
        $listingObj->watch_num = $itemvalue->WatchCount;
        $listingObj->out_of_stock = isset($itemvalue->ReasonHideFromSearch)?$itemvalue->ReasonHideFromSearch:"";
        
        $flag_multi = empty($itemvalue->Variations);
        if(!$flag_multi) {
            $listingObj->variation_multi = '1';
        }
        //            $listingObj->conditon_id = ;
        //            $listingObj->condition_display_name = '';
        
        
        $result = $listingObj->save();
        if($result) {
            $id = $listingObj->attributes['id'];
            
            return array('status'=>200,'id'=>$id);
        } else {
            
            return array('status'=>500, 'msg'=>'Listing 列表更新失败：ItemID为 '.$itemvalue->ItemID);
        }
    }
    
    //更新多属性
    public function updatevariations($itemid, $variations) {
        $variation = $variations->Variation;
        $compareArr = array();
        UebModel::model('Ebayonlinelistingvariation')->deleteAll('item_id='.$itemid);
        foreach($variation as $variationvalue) {
            $sku = '';
            $sku = $variationvalue->SKU;
            $compareArr[] = $sku;
            $listingObj = UebModel::model('Ebayonlinelistingvariation')
                    ->find('item_id=:itemid AND sku=:sku',array(':itemid'=>$itemid, ':sku'=>$sku));
            if(empty($listingObj)) {
                $listingObj = new Ebayonlinelistingvariation();
            }
            //variation specifics
            $variationSpecifics = '';
            $variationSpecificsName = '';
            $variationSpecificsValue = '';
            $nameValueList = $variationvalue->VariationSpecifics->NameValueList;

            foreach($nameValueList as $namevalue) {
                $variationSpecifics .= '#@#'.$namevalue->Name.'|'.$namevalue->Value;
                $variationSpecificsName .= $namevalue->Name.'#@#';
                $variationSpecificsValue .= $namevalue->Value.'#@#';
            }
            if(substr($variationSpecifics,0,3) == '#@#') {
                $variationSpecifics = substr($variationSpecifics,3);
            }

            if(substr($variationSpecificsName,-3) == '#@#') {
                $variationSpecificsName = substr($variationSpecificsName,0,-3);
            }

            if(substr($variationSpecificsValue,-3) == '#@#') {
                $variationSpecificsValue = substr($variationSpecificsValue,0,-3);
            }
            
            $listingObj->item_id = $itemid;
            $listingObj->sku = EbayListing::decryptSku($sku)['sku'];
            $listingObj->sell_sku = $sku;
            $listingObj->start_price = $variationvalue->StartPrice;
            $listingObj->quantity = ($variationvalue->Quantity - $variationvalue->SellingStatus->QuantitySold);
            $listingObj->specifics_name = $variationSpecificsName;
            $listingObj->specifics_value = $variationSpecificsValue;
            $listingObj->specifics_name_value = $variationSpecifics;
            $listingObj->variation_listing_detail_upc = $variationvalue->VariationProductListingDetails->UPC;
            $listingObj->variation_listing_detail_mpn = $variationvalue->VariationProductListingDetails->MPN;
            $listingObj->variation_listing_detail_ean = $variationvalue->VariationProductListingDetails->EAN;
            $listingObj->variation_listing_detail_isbn = $variationvalue->VariationProductListingDetails->ISBN;
            $listingObj->quantity_sold = $variationvalue->SellingStatus->QuantitySold;
            $listingObj->status = 0;
            
            $listingObj->save();
        }
    }
    
    //更新描述
    public function updatedescrtion($itemid, $descrtion) {           
            $path = '';
            $path = implode('/',str_split($itemid,3));
            
            $url_path = dirname(Yii::app()->BasePath).'/upload/ebay_descrtion/'.$path;
            if(!is_dir($url_path)) {
                mkdir($url_path,0777,true);
            }
            $file_flag = file_put_contents($url_path.'/'.$itemid.'.txt', $descrtion);
    }
    
    //更新运输方式
    public function updateshipping($itemid, $shipping) {
        UebModel::model('Ebayonlinelistingshipping')->deleteAll('item_id='.$itemid);
        $ShippingServiceOptions = $shipping->ShippingServiceOptions;
        $InternationalShippingServiceOption = $shipping->InternationalShippingServiceOption;
        $ShippingType = $shipping->ShippingType;
        $ExcludeShipToLocation = (array)$shipping->ExcludeShipToLocation;
        $excludevalue = implode('##', $ExcludeShipToLocation);
        
        foreach($ShippingServiceOptions as $shippingservicevalue) {
            $listingObj = UebModel::model('Ebayonlinelistingshipping')
                ->find('item_id=:itemid AND shipping_service=:shippingservice and shipping_status=:s',
                        array(':itemid'=>$itemid,':shippingservice'=>$shippingservicevalue->ShippingService,':s'=>1));
            if(empty($listingObj)) {
                $listingObj = new Ebayonlinelistingshipping();
            }
            
            $listingObj->item_id = $itemid;
            $listingObj->shipping_service = $shippingservicevalue->ShippingService;
            $listingObj->shipping_service_cost = $shippingservicevalue->ShippingServiceCost;
            $listingObj->shipping_service_additional_cost = $shippingservicevalue->ShippingServiceAdditionalCost;
            $listingObj->shipping_service_priority = $shippingservicevalue->ShippingServicePriority;
            $listingObj->shipping_status = '1';
            $listingObj->expedited_service = $shippingservicevalue->ExpeditedService;
            $listingObj->shipping_time_min = $shippingservicevalue->ShippingTimeMin;
            $listingObj->shipping_time_max = $shippingservicevalue->ShippingTimeMax;
            $listingObj->shipping_type = $ShippingType;
            $listingObj->exclude_ship_to_location = $excludevalue;           

            $result = $listingObj->save();
        }
        
        if(!empty($InternationalShippingServiceOption)) {            
            foreach($InternationalShippingServiceOption as $internashippingvalue) {
                $intershippinglocation = '';
                $intershippinglocation = implode('##',(array)$internashippingvalue->ShipToLocation);
                $listingObj = UebModel::model('Ebayonlinelistingshipping')
                    ->find('item_id=:itemid AND shipping_service=:shippingservice AND shipping_status=:s  and ship_to_location=:intershippinglocation',
                        array(':itemid'=>$itemid, ':shippingservice'=>$internashippingvalue->ShippingService, ':s'=>2, ':intershippinglocation'=>$intershippinglocation));
                if(empty($listingObj)) {
                    $listingObj = new Ebayonlinelistingshipping();
                }
                
                $listingObj->item_id = $itemid;
                $listingObj->shipping_service = $internashippingvalue->ShippingService;
                $listingObj->shipping_service_cost = $internashippingvalue->ShippingServiceCost;
                $listingObj->shipping_service_additional_cost = $internashippingvalue->ShippingServiceAdditionalCost;
                $listingObj->shipping_service_priority = $internashippingvalue->ShippingServicePriority;
                $listingObj->shipping_status = '2';
    
                $listingObj->shipping_type = $ShippingType;
                $listingObj->exclude_ship_to_location = $excludevalue;           
                $listingObj->ship_to_location = $intershippinglocation;

                $result = $listingObj->save();
            }
        }    
    }
    
    /**更新图片列表
     * 
     * @param $itemid
     * @param $images image 对象
     * @param $id value 视type值结果，type=list,id为list_id. type=variation,id为variation_id
     * @param $type value等于list,variation
     */
    public function updateimage($itemid, $images, $id, $type='list') {
        
        $Galleryurl = $images->GalleryURL;
        $PictureURL = $images->PictureURL;
        $PhotoDisplay = $images->PhotoDisplay;
        
        //查询主图是否存在
        $listingObj = UebModel::model('Ebayonlinelistingimage')
            ->find('item_id=:itemid AND img_url=:imgurl AND img_status=:imgstatus',
                    array(':itemid'=>$itemid,':imgurl'=>$Galleryurl, ':imgstatus'=>1));
        if(empty($listingObj)) {
            $listingObj = new Ebayonlinelistingimage();
        }
        
        $listingObj->item_id = $itemid;
        $listingObj->img_url = $Galleryurl;
        $listingObj->img_status = 1;
        $listingObj->list_id = $id;
//        $listingObj->variation_id = '';
        $flag_source = '';
        $flag_source = strpos($Galleryurl, 'ebayimg.com');
        if($flag_source === false) {
            $listingObj->picture_source = 'Vendor';
        } else {
            $listingObj->picture_source = 'EPS';
        }
        $listingObj->save();
        
        //图片列表
        $tempArr = array();
        $tempArr[] = $Galleryurl;
        foreach($PictureURL as $picvalue) {
            $listingObj = UebModel::model('Ebayonlinelistingimage')
            ->find('item_id=:itemid AND img_url=:imgurl AND img_status=:imgstatus',
                array(':itemid'=>$itemid,':imgurl'=>$picvalue, ':imgstatus'=>0));
            if(empty($listingObj)) {
                $listingObj = new Ebayonlinelistingimage();
            }
            
            $tempArr[] = $picvalue;
            $listingObj->item_id = $itemid;
            $listingObj->img_url = $picvalue;
            $listingObj->img_status = 0;
            $listingObj->list_id = $id;
            //        $listingObj->variation_id = '';
            $flag_source = '';
            $flag_source = strpos($picvalue, 'ebayimg.com');
            if($flag_source === false) {
                $listingObj->picture_source = 'Vendor';
            } else {
                $listingObj->picture_source = 'EPS';
            }
            
            $listingObj->save();
        }
        
        //删除多余选项
        $realArr = array_column(Yii::app()->db->createCommand()->select('img_url')->from('ueb_product.ueb_ebay_online_listing_image')
                    ->where('item_id='.$itemid)->queryAll(true), 'img_url');
        $diffArr = array_diff($realArr, $tempArr);
        if(!empty($diffArr)) {
            $diffstr = implode('","', $diffArr);
            UebModel::model('Ebayonlinelistingimage')->deleteAll('item_id='.$itemid.' and img_url in ("'.$diffstr.'") ');
        }
        
    }
    
    
    
    //获取产品属性
    public function updateattributes($itmeid, $data) {
        $tempArr = array();
        if(!empty($data)) {
            foreach($data->NameValueList as $value) {
                $modelObj = UebModel::model('Ebayonlinelistingspecifics')
                    ->find('item_id=:itemid AND name=:name',array(':itemid'=>$itmeid, ':name'=>$value->Name));
                if(empty($modelObj)) {
                    $modelObj = new Ebayonlinelistingspecifics;
                }
                $tempArr[] = $value->Name;
                $modelObj->item_id = $itmeid;
                $modelObj->name = $value->Name;
                $modelObj->value = $value->Value;
                $modelObj->source = $value->Source;
                
                $modelObj->save();
            }
        }
        //
        $realArr = array_column(Yii::app()->db->createCommand()->select('name')->from('ueb_product.ueb_ebay_online_listing_specifics')
                    ->where('item_id='.$itmeid)->queryAll(true), 'name');
        $diffArr = array_diff($realArr, $tempArr);
        if(!empty($diffArr)) {
            $diffstr = implode('","', $diffArr);
            UebModel::model('Ebayonlinelistingspecifics')->deleteAll('item_id='.$itmeid.' and name in ("'.$diffstr.'")');
        }
        
    }
  
    
    //获取多属性图片
    public function updatevariationimg($itemid,$data) {
        $name = $data->VariationSpecificName;
        
        $tempArr = array();
        $picArr = $data->VariationSpecificPictureSet;
        foreach($picArr as $pic) {
            $value = $pic->VariationSpecificValue;
            $picUrl = $pic->PictureURL;

            foreach($picUrl as $url) {
                $model = UebModel::model('Ebayonlinelistingvariationimg')->find('item_id=:id and img_url=:url', array(':id'=>$itemid, ':url'=>$url));
                if(empty($model)) {
                    $model = new Ebayonlinelistingvariationimg;
                }
                
                $tempArr[] = $value;
                $model->item_id = $itemid;
                $model->variation_name = $name;
                $model->variation_value = $value;
                $model->img_url = $url;
                $model->status = 0;
                
                $result = $model->save();
            }
        }
        //
        $realArr = array_column(Yii::app()->db->createCommand()->select('variation_value')->from('ueb_product.ueb_ebay_online_listing_variation_img')
                ->where('item_id='.$itemid)->queryAll(true), 'variation_value');
        $diffArr = array_diff($realArr, $tempArr);
        if(!empty($diffArr)) {
            foreach($diffArr as $diffK=>$diffV) {
                $diffArr[$diffK] = addslashes($diffV);
            }
            
            $diffstr = implode('","', $diffArr);
            UebModel::model('Ebayonlinelistingvariationimg')->deleteAll('item_id='.$itemid.' and variation_value in ("'.$diffstr.'")  ');
        }
        
    }
    
    //获取买家要求
    public function updatebuyerrequire($item_id, $data) {
        $model = UebModel::model('Ebayonlinelistingbuyerrequire')->find('item_id='.$item_id);
        if(empty($model)) {
            $model = new Ebayonlinelistingbuyerrequire();
        }
        
        $model->item_id = $item_id;
        $model->linked_paypal_account = isset($data->LinkedPayPalAccount)?$data->LinkedPayPalAccount:'';
        $model->buyer_policy_violations_count = isset($data->MaximumBuyerPolicyViolations->Count)?$data->MaximumBuyerPolicyViolations->Count:'';
        $model->buyer_policy_violations_period = isset($data->MaximumBuyerPolicyViolations->Period)?$data->MaximumBuyerPolicyViolations->Period:'';
        $model->requirement_maximum_item_count = isset($data->MaximumItemRequirements->MaximumItemCount)?$data->MaximumItemRequirements->MaximumItemCount:'';
        $model->requirement_minimum_feedback_score = isset($data->MaximumItemRequirements->MinimumFeedbackScore)?$data->MaximumItemRequirements->MinimumFeedbackScore:'';
        $model->strikes_info_count = isset($data->MaximumUnpaidItemStrikesInfo->Count)?$data->MaximumUnpaidItemStrikesInfo->Count:'';
        $model->strikes_info_period = isset($data->MaximumUnpaidItemStrikesInfo->Period)?$data->MaximumUnpaidItemStrikesInfo->Period:'';
        $model->minimum_feedback_score = isset($data->MinimumFeedbackScore)?$data->MinimumFeedbackScore:'';
        $model->shipto_registration_country = isset($data->ShipToRegistrationCountry)?$data->ShipToRegistrationCountry:'';
        
        $model->save();
    }
    
}