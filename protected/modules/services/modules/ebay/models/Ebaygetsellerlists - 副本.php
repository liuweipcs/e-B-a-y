<?php

class Ebaygetsellerlists {
    
    protected $siteid;
    
    protected $accountid;
    
    protected $pagenum;
    
//    public function setSiteid($siteid) {
//        $this->siteid = $siteid;
//    }
    
    public function setaccount($accountid) {
        $this->accountid = $accountid;
    }
    
    public function setpagenum($pagenum) {
        $this->pagenum = $pagenum;
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
            //日志
            /* */      
            Ebayonlinelistinglog::model()->dbConnection->createCommand()->insert('ueb_ebay_online_listing_log',
                array('msg'=>'开始时间或结束时间不能为空,page:'.$this->pagenum,'account'=>$this->accountid,'type'=>'request','add_time'=>date('Y-m-d H:i:s')));
            
            return array('status'=>500, 'msg'=>'开始时间或结束时间不能为空');
        }
        //判断帐号
        if(empty($this->accountid)) {
            //日志
            /* */      
            Ebayonlinelistinglog::model()->dbConnection->createCommand()->insert('ueb_ebay_online_listing_log',
                array('msg'=>'eBay帐号不能为空,page:'.$this->pagenum,'account'=>$this->accountid,'type'=>'request','add_time'=>date('Y-m-d H:i:s')));
            return array('status'=>500, 'msg'=>'eBay帐号不能为空');
        }
        //判断站点
//        if(empty($this->siteid) && $this->siteid != '0') {
//            return array('status'=>500, 'msg'=>'eBay站点不能为空');    
//        }
        Ebayonlinelistinglog::model()->dbConnection->createCommand()->insert('ueb_ebay_online_listing_log',
            array('msg'=>'断开前是否到达测试,page:'.$this->pagenum,'account'=>$this->accountid,'type'=>'start','add_time'=>date('Y-m-d H:i:s')));
        
        $ApiObj = new Ebaygetsellerlistapi();
        $ApiObj->setPageNum($this->pagenum);
        
        Ebayonlinelistinglog::model()->dbConnection->createCommand()->insert('ueb_ebay_online_listing_log',
            array('msg'=>'断开前是否到达 测试222,page:'.$this->pagenum,'account'=>$this->accountid,'type'=>'start','add_time'=>date('Y-m-d H:i:s')));
        
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
        Ebayonlinelistinglog::model()->dbConnection->createCommand()->insert('ueb_ebay_online_listing_log',
            array('msg'=>'断开前是否到达测试2,page:'.$this->pagenum,'account'=>$this->accountid,'type'=>'start','add_time'=>date('Y-m-d H:i:s')));
         //日志
            /* */      Ebayonlinelistinglog::model()->dbConnection->createCommand()->insert('ueb_ebay_online_listing_log',
                array('msg'=>'返回结果：success,page:'.$this->pagenum,'account'=>$this->accountid,'type'=>'back result','add_time'=>date('Y-m-d H:i:s')));
        return $result;
    }
    
    //写入数据
    public function updatelisting($data) {
        $ack = isset($data->Ack)?$data->Ack:'';

        if($ack == '' || $ack == 'Failure') {
            $refresh_status = '';
            $err_msg = empty($data->Errors->LongMessage)?$data->Errors['0']->LongMessage:$data->Errors->LongMessage;
            $errcode = $data->Errors->ErrorCode->__toString();
            if('340' == $errcode) { //pagenum 超范围
                $refresh_status = 200;
            }
            
            //日志
            /* */      
            Ebayonlinelistinglog::model()->dbConnection->createCommand()->insert('ueb_ebay_online_listing_log',
                array('msg'=>'API接口调用失败：'.$err_msg.' ,page:'.$this->pagenum,'account'=>$this->accountid,'type'=>'back','add_time'=>date('Y-m-d H:i:s')));
            return array('status'=>500, 'refresh_status'=>$refresh_status, 'msg'=>'API接口调用失败：'.$err_msg);
        }

        $HasMoreItems = $data->HasMoreItems;
        $Totalpagenum = $data->PaginationResult->TotalNumberOfPages;
        //item 信息
        $itemArray = $data->ItemArray->Item;
        if(empty($itemArray)) {
            //日志
            /* */      
            Ebayonlinelistinglog::model()->dbConnection->createCommand()->insert('ueb_ebay_online_listing_log',
                array('msg'=>'返回数据：没有Listing数据,page:'.$this->pagenum,'account'=>$this->accountid,'type'=>'back','add_time'=>date('Y-m-d H:i:s')));
            
            return array('status'=>500, 'msg'=>'没有Listing数据');
        }
        
        foreach($itemArray as $itemvalue) {
            //判断item是否存在
            $itemid = $itemvalue->ItemID;
            if(empty($itemid)) {
                Ebayonlinelistinglog::model()->dbConnection->createCommand()->insert('ueb_ebay_online_listing_log',
                    array('msg'=>'返回数据：API错误：ItemID 不存在,page:'.$this->pagenum,'account'=>$this->accountid,'type'=>'back','add_time'=>date('Y-m-d H:i:s')));
                return array('status'=>500, 'msg'=>'API错误：ItemID 不存在');
            }
            
            //日志
/* */           Ebayonlinelistinglog::model()->dbConnection->createCommand()->insert('ueb_ebay_online_listing_log',
                array('msg'=>'更新列表开始,page:'.$this->pagenum,'account'=>$this->accountid,'type'=>'start','add_time'=>date('Y-m-d H:i:s')));
/* */                
            //更新列表
            $storeName = $data->Seller->UserID;
            $listInfo = $this->updatelist($itemvalue, $storeName);
            //日志
/* */           Ebayonlinelistinglog::model()->dbConnection->createCommand()->insert('ueb_ebay_online_listing_log',
                array('msg'=>'更新列表结束,page:'.$this->pagenum,'account'=>$this->accountid,'type'=>'end','add_time'=>date('Y-m-d H:i:s')));
/* */                
            if($listInfo['status'] == 500) {
                return $listInfo;
            }
            $listId = $listInfo['id'];
            //日志
/* */           Ebayonlinelistinglog::model()->dbConnection->createCommand()->insert('ueb_ebay_online_listing_log',
                array('msg'=>'更新多属性开始,page:'.$this->pagenum,'account'=>$this->accountid,'type'=>'start','add_time'=>date('Y-m-d H:i:s')));
/* */                
            //更新多属性
            $variationsInfo = $this->updatevariations($itemid, $itemvalue->Variations);
            //日志
/* */           Ebayonlinelistinglog::model()->dbConnection->createCommand()->insert('ueb_ebay_online_listing_log',
                array('msg'=>'更新多属性结束,page:'.$this->pagenum,'account'=>$this->accountid,'type'=>'end','add_time'=>date('Y-m-d H:i:s')));               
            //日志
            Ebayonlinelistinglog::model()->dbConnection->createCommand()->insert('ueb_ebay_online_listing_log',
                array('msg'=>'更新描述性开始,page:'.$this->pagenum,'account'=>$this->accountid,'type'=>'start','add_time'=>date('Y-m-d H:i:s')));
/* */                 
            //更新描述
            $descrtionInfo = $this->updatedescrtion($itemid, $itemvalue->Description);
            //日志
/* */           Ebayonlinelistinglog::model()->dbConnection->createCommand()->insert('ueb_ebay_online_listing_log',
                array('msg'=>'更新描述结束,page:'.$this->pagenum,'account'=>$this->accountid,'type'=>'end','add_time'=>date('Y-m-d H:i:s')));
            //日志
            Ebayonlinelistinglog::model()->dbConnection->createCommand()->insert('ueb_ebay_online_listing_log',
                array('msg'=>'更新运输方式开始,page:'.$this->pagenum,'account'=>$this->accountid,'type'=>'start','add_time'=>date('Y-m-d H:i:s')));
/* */                
            //更新运输方式
            $shippingInfo = $this->updateshipping($itemid, $itemvalue->ShippingDetails);
            //日志
/* */           Ebayonlinelistinglog::model()->dbConnection->createCommand()->insert('ueb_ebay_online_listing_log',
                array('msg'=>'更新运输方式结束,page:'.$this->pagenum,'account'=>$this->accountid,'type'=>'end','add_time'=>date('Y-m-d H:i:s')));
            //日志
            Ebayonlinelistinglog::model()->dbConnection->createCommand()->insert('ueb_ebay_online_listing_log',
                array('msg'=>'更新图片列表开始,page:'.$this->pagenum,'account'=>$this->accountid,'type'=>'start','add_time'=>date('Y-m-d H:i:s')));
/* */                
            //更新图片列表
            $imageInfo = $this->updateimage($itemid, $itemvalue->PictureDetails,$listId);
            //日志
/* */            Ebayonlinelistinglog::model()->dbConnection->createCommand()->insert('ueb_ebay_online_listing_log',
                array('msg'=>'更新图片结束,page:'.$this->pagenum,'account'=>$this->accountid,'type'=>'end','add_time'=>date('Y-m-d H:i:s')));
/* */                
        }
        //echo "ok";
        //exit();
        return array('status'=>200, 'hasmore'=>$HasMoreItems,'totalpagenum'=>$Totalpagenum);
    }
    
    //更新listing列表
    public function updatelist($itemvalue, $storeName) {
        $listingObj = UebModel::model('Ebayonlinelisting')->find('itemid=:itemid',array(':itemid'=>$itemvalue->ItemID));
        if(empty($listingObj)) {
            $listingObj = new Ebayonlinelisting();
        } else {
            $id = $listingObj->id;
        }
        
        $listingObj->account = $storeName;
        $listingObj->itemid = $itemvalue->ItemID;
        $listingObj->sku = $itemvalue->SKU;
        $listingObj->start_price = $itemvalue->StartPrice;
        $listingObj->buy_it_now_price = $itemvalue->BuyItNowPrice;
        $listingObj->reserve_price = $itemvalue->ReservePrice;
        
        $listingObj->currency = $itemvalue->Currency;
        $listingObj->auto_pay = $itemvalue->AutoPay;
        $listingObj->buyer_protection = $itemvalue->BuyerProtection;
        $listingObj->start_time = strtotime($itemvalue->ListingDetails->StartTime);
        $listingObj->end_time = strtotime($itemvalue->ListingDetails->EndTime);
        $listingObj->view_item_url = $itemvalue->ListingDetails->ViewItemURL;
        
        $listingObj->ending_reason = $itemvalue->ListingDetails->EndingReason;
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
        
        $listingObj->quantity = $itemvalue->Quantity;
        $listingObj->site = $itemvalue->Site;
        $listingObj->store_category_id = $itemvalue->Storefront->StoreCategoryID;
        $listingObj->store_second_category_id = $itemvalue->Storefront->StoreCategory2ID;
        $listingObj->store_url = $itemvalue->Storefront->StoreURL;
        $listingObj->title = $itemvalue->Title;
        
        $listingObj->uuid = $itemvalue->UUID;
        $listingObj->bestoffer_enabled = $itemvalue->BestOfferDetails->BestOfferEnabled;
        $listingObj->bestoffer_count = $itemvalue->BestOfferDetails->BestOfferCount;
        $listingObj->new_bestoffer = $itemvalue->BestOfferDetails->NewBestOffer;
        $listingObj->dispatch_time_max = $itemvalue->DispatchTimeMax;
        $listingObj->quantity_sold = $itemvalue->SellingStatus->QuantitySold;
        
        $listingObj->hit_count = $itemvalue->HitCounter;
        $listingObj->refund_option = $itemvalue->ReturnPolicy->RefundOption;
        $listingObj->refund = $itemvalue->ReturnPolicy->Refund;
        $listingObj->returns_within_option = $itemvalue->ReturnPolicy->ReturnsWithinOption;
        $listingObj->returns_accepted_option = $itemvalue->ReturnPolicy->ReturnsAcceptedOption;
        $listingObj->returns_description = $itemvalue->ReturnPolicy->Description;
        
        $listingObj->shipping_cost_paid_by_option = $itemvalue->ReturnPolicy->ShippingCostPaidByOption;
        $listingObj->restocking_fee_value_option = $itemvalue->ReturnPolicy->RestockingFeeValueOption;
        $listingObj->siteid = $this->siteid;
        $listingObj->country = $itemvalue->Country;
        $listingObj->variation_multi = '0';
        $listingObj->status = 0;
        $listingObj->update_time = date('Y-m-d H:i:s');
        
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
        foreach($variation as $variationvalue) {
            $sku = '';
            $sku = $variationvalue->SKU;
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
                $variationSpecifics .= '##'.$namevalue->Name.'|'.$namevalue->Value;
                $variationSpecificsName .= $namevalue->Name.'##';
                $variationSpecificsValue .= $namevalue->Value.'##';
            }
            $variationSpecifics = ltrim($variationSpecifics, '##');
            $variationSpecificsName = rtrim($variationSpecificsName, '##');
            $variationSpecificsValue = rtrim($variationSpecificsValue, '##');
            
            $listingObj->item_id = $itemid;
            $listingObj->sku = $sku;
            $listingObj->start_price = $variationvalue->StartPrice;
            $listingObj->quantity = $variationvalue->Quantity;
            $listingObj->specifics_name = $variationSpecificsName;
            $listingObj->specifics_value = $variationSpecificsValue;
            $listingObj->specifics_name_value = $variationSpecifics;
            $listingObj->variation_listing_detail_upc = $variationvalue->VariationProductListingDetails->UPC;
            $listingObj->variation_listing_detail_mpn = $variationvalue->VariationProductListingDetails->MPN;
            $listingObj->variation_listing_detail_ean = $variationvalue->VariationProductListingDetails->EAN;
            $listingObj->variation_listing_detail_isbn = $variationvalue->VariationProductListingDetails->ISBN;
            $listingObj->quantity_sold = $variationvalue->SellingStatus->QuantitySold;
            
            $listingObj->save();
        }    
        
    }
    
    //更新描述
    public function updatedescrtion($itemid, $descrtion) {
        $listingObj = UebModel::model('Ebayonlinelistingdescrtion')->find('item_id=:itemid',array(':itemid'=>$itemid));
        if(empty($listingObj)) {
            $listingObj = new Ebayonlinelistingdescrtion();
        }
        
        $listingObj->descrtion = $descrtion;
        $listingObj->item_id = $itemid;
        $result = $listingObj->save();
        
    }
    
    //更新运输方式
    public function updateshipping($itemid, $shipping) {
       
        $ShippingServiceOptions = $shipping->ShippingServiceOptions;
        $InternationalShippingServiceOption = $shipping->InternationalShippingServiceOption;
        $ShippingType = $shipping->ShippingType;
        $ExcludeShipToLocation = (array)$shipping->ExcludeShipToLocation;
        $excludevalue = implode('##', $ExcludeShipToLocation);
        
//        $ShipToLocations = $shipping->ShipToLocations;
        //判断是国内还是国际  运输方式是否存在
        foreach($ShippingServiceOptions as $shippingservicevalue) {
            $listingObj = UebModel::model('Ebayonlinelistingshipping')
                ->find('item_id=:itemid AND shipping_service=:shippingservice',array(':itemid'=>$itemid,':shippingservice'=>$shippingservicevalue->ShippingService));
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
//          $listingObj->ship_to_location = '';
//          $listingObj->ship_to_locations = '';
            $result = $listingObj->save();
        }
            
        foreach($InternationalShippingServiceOption as $internashippingvalue) {
            $intershippinglocation = '';
            $intershippinglocation = implode('##',(array)$internashippingvalue->ShipToLocation);
            $listingObj = UebModel::model('Ebayonlinelistingshipping')
                ->find('item_id=:itemid AND shipping_service=:shippingservice AND ship_to_location=:intershippinglocation',array(':itemid'=>$itemid, ':shippingservice'=>$internashippingvalue->ShippingService, ':intershippinglocation'=>$intershippinglocation));
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
//            $listingObj->ship_to_locations = '';
//            $listingObj->expedited_service = $shippingservicevalue->ExpeditedService;
//            $listingObj->shipping_time_min = $shippingservicevalue->ShippingTimeMin;
//            $listingObj->shipping_time_max = $shippingservicevalue->ShippingTimeMax;
            $result = $listingObj->save();
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
        foreach($PictureURL as $picvalue) {
            $listingObj = UebModel::model('Ebayonlinelistingimage')
            ->find('item_id=:itemid AND img_url=:imgurl AND img_status=:imgstatus',
                array(':itemid'=>$itemid,':imgurl'=>$picvalue, ':imgstatus'=>0));
            if(empty($listingObj)) {
                $listingObj = new Ebayonlinelistingimage();
            }
            
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
        
        
    }
    
    
}