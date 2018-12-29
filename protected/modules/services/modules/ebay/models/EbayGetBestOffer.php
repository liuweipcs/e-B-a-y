<?php
header("Content-type:text/html;charset=utf-8");

class EbayGetBestOffer {
    
    //帐号查 active
    public function synchBestOffer($requestAcc) {
        set_time_limit(600);
        
        if(!empty($requestAcc)) {
            $accInfo = array();
            $accInfo[] = $requestAcc;
        }
        foreach($accInfo as $acc) {
            $data = $this->bestOfferByAcc($acc);
//            echo "<pre>".$acc.'--<br/>';var_dump($data);
        }
    }
    
    public function bestOfferByAcc($shortname) {
        set_time_limit(600);
        $pagenum = 1;
        $flag = true;
        //
        $countryInfo = VHelper::selectAsArray('Country','en_name,en_abbr');
        $countrys = array();
        foreach($countryInfo as $countryval) {
            if(!$countryval['en_abbr']) {
                continue;
            }
            $countrys[$countryval['en_abbr']] = $countryval['en_name'];
        }
        
        $bestOfferApiObj = new GetBestOffer();
        while($flag) {
            
            $bestOfferApiObj->setPageNum($pagenum);
            //获取分类
            $response = $bestOfferApiObj->setShortName($shortname)
            ->setVerb('GetBestOffers')
            ->setRequest()
            ->sendHttpRequest()
            ->getResponse();
//            echo "<pre>";var_dump($response);exit();
            $totalPageNum = $response->PaginationResult->TotalNumberOfPages; //总页数
            $totalNum = $response->PaginationResult->TotalNumberOfEntries; //总条数            
            $backInfo = $this->checkBestOffers($response, $countrys, $shortname);
            if($backInfo['status'] != 200) {
                $flag = false;
                break;
            }
            
            if($pagenum > $totalPageNum) {
                $flag = false;
                break;
            }
            
            $pagenum ++;
        }
        
        return $backInfo;
    }
    
    // 更新 eBay 议价
    public function checkBestOffers($data, $countrys, $acc) {
        set_time_limit(600);
        $ack = isset($data->Ack)?$data->Ack:'Failure';
        if($ack != 'Failure') {
            $bestOffersObj = $data->ItemBestOffersArray->ItemBestOffers;
            if(!empty($bestOffersObj)) {
                foreach($bestOffersObj as $bestOffersVal) {
                    //check list
                    $itemid = $bestOffersVal->Item->ItemID->__toString();
                    $checkListInfo = VHelper::selectAsArray('EbayBestOffersList','id','item_id='.$itemid);
                    $listInfoArr = array_column($checkListInfo,'id');
                    if(!empty($listInfoArr)) {
                        $paramid = $listInfoArr['0'];
                        $modelList = EbayBestOffersList::model();
                        $modelList = $modelList->findByPk($paramid);
                    } else {
                        $newModelList = new EbayBestOffersList();
                        $modelList = $newModelList;
                    }
                    
                    $modelList->account =  UebModel::model('EbayAccount')->getByShortName($acc)['user_name'];
                    $modelList->item_id = $itemid;
                    $modelList->buy_it_now_price = $bestOffersVal->Item->BuyItNowPrice->__toString();
                    $modelList->listing_end_time = date('Y-m-d H:i:s', strtotime($bestOffersVal->Item->ListingDetails->EndTime));
                    $modelList->location = $bestOffersVal->Item->Location;
                    $modelList->title = $bestOffersVal->Item->Title; 
                    $modelList->condition_id = $bestOffersVal->Item->ConditionID;
                    $modelList->condition_display_name = $bestOffersVal->Item->ConditionDisplayName;
                    $modelList->currency = $bestOffersVal->Item->BuyItNowPrice->attributes()->currencyID->__toString();
                    $modelList->sku = UebModel::model('Ebayonlinelisting')->find('itemid=:itemid', array(':itemid'=>$itemid))->sku;
                    $modelList->site = UebModel::model('Ebayonlinelisting')->find('itemid=:itemid', array(':itemid'=>$itemid))->site;
                    
                    $result = $modelList->save();                    
                    $bestid = $modelList->attributes['id'];
                    
                    $this->checkBestOffer($bestid,$bestOffersVal,$countrys);
                } 
                return array('status'=>200, 'msg'=>'success');
            } else {
                return array('status'=>200, 'msg'=>'success');
            }
        
        } else {
            $errMsgObj = $data->Errors;
            $errmsg = isset($errMsgObj->LongMessage)?$errMsgObj->LongMessage:$errMsgObj->LongMessage['0']->LongMessage;
            
            return array('status'=>500, 'msg'=>$errmsg);
        }
    }
    
    //更新/新增议价详情
    public function checkBestOffer($bestid,$param,$countrys) {
        set_time_limit(600);
        foreach ($param->BestOfferArray->BestOffer as $bestVal)
        {
            $bestofferid = $bestVal->BestOfferID->__toString();
            $checkDetailInfo = VHelper::selectAsArray('EbayBestOffersDetails','id','best_offer_id='.$bestofferid);
        
            $detailInfoArr = array_column($checkDetailInfo,'id');
            if(!empty($detailInfoArr)) {
                $detailid = $detailInfoArr['0'];
                $modelDetail = EbayBestOffersDetails::model();
                $detailObj = $modelDetail->findByPk($detailid);
            } else {
                $newModelDetail = new EbayBestOffersDetails();
                $detailObj = $newModelDetail;
            }
        
            $detailObj->best_id = $bestid;
//            $detailObj->role = $param->Role;
            $detailObj->best_offer_id = $bestofferid;
            $detailObj->expiration_time = date('Y-m-d H:i:s',strtotime($bestVal->ExpirationTime));
            $detailObj->buyer_email = $bestVal->Buyer->Email;
            $detailObj->buyer_msg = $bestVal->BuyerMessage;
            $detailObj->buyer_feedback_score = $bestVal->Buyer->FeedbackScore;
            $detailObj->register_date = date('Y-m-d H:i:s',strtotime($bestVal->Buyer->RegistrationDate));
            $detailObj->buyer_user_id = $bestVal->Buyer->UserID;
            $detailObj->shipping_province = $bestVal->Buyer->ShippingAddress->StateOrProvince;
            $detailObj->shipping_country_2 = $bestVal->Buyer->ShippingAddress->CountryName;
            $detailObj->shipping_country = $countrys[$bestVal->Buyer->ShippingAddress->CountryName->__toString()];
            $detailObj->shipping_post_code = $bestVal->Buyer->ShippingAddress->PostalCode;
            $detailObj->buy_price = $bestVal->Price;
            $detailObj->bese_offer_status = $bestVal->Status;
            $detailObj->quantity = $bestVal->Quantity;
            $detailObj->best_offer_code = $bestVal->BestOfferCodeType;
            $detailObj->currency = $bestVal->Price->attributes()->currencyID->__toString();
        
            $detailObj->save();
        }
    }
    
    
    //按item查询
    public function synchBestOfferByItem($itemid = '') {
        set_time_limit(600);
        $start_time = time();
        $countryInfo = VHelper::selectAsArray('Country','en_name,en_abbr');
        $countrys = array();
        foreach($countryInfo as $countryval) {
            if(!$countryval['en_abbr']) {
                continue;
            }
            $countrys[$countryval['en_abbr']] = $countryval['en_name'];
        }
        
        $nowDAte = date('Y-m-d H:i:s');
        if($itemid) {
            $listInfo = VHelper::selectAsArray('EbayBestOffersList','id,account,item_id', 'listing_end_time > "'.$nowDAte.'" AND item_id = "'.$itemid.'" ');
        } else {
            $listInfo = VHelper::selectAsArray('EbayBestOffersList','id,account,item_id', 'listing_end_time > "'.$nowDAte.'" and TIMESTAMPDIFF(SECOND,run_time,"'.$nowDAte.'") > 601 ',false,'','run_time asc',60);
            
            if(empty($listInfo)) {
                echo '无数据需要同步';
                return false;
            }
            $idsArr = array_column($listInfo, 'id');
            $idsstr = implode(',', $idsArr);
            
            $modifysql = "UPDATE ueb_product.ueb_ebay_best_offers SET run_time = '".date('Y-m-d H:i:s')."' WHERE id IN (".$idsstr.") ";
            Yii::app()->db->createCommand($modifysql)->execute();
        }
        
        if(!empty($listInfo)) {
            foreach($listInfo as $accVal) {
                if(time - $start_time > 580) {
                    exit();
                }
                $bestOfferApiObj = new GetBestOffer();
                $bestOfferApiObj->setStatus('All');
                $bestOfferApiObj->setItemid($accVal['item_id']);
                
                $short_name = UebModel::model('EbayAccount')->find('user_name=:name', array(':name'=>$accVal['account']))->short_name;
                $response = $bestOfferApiObj->setShortName($short_name)
                    ->setVerb('GetBestOffers')
                    ->setRequest()
                    ->sendHttpRequest()
                    ->getResponse();
                
                $ack = $response->Ack;
                if($ack != 'Failure') {
                    $param = new stdClass;
                    $param->BestOfferArray = $response->BestOfferArray;
                    UebModel::model('EbayBestOffersList')->updateByPk($accVal['id'], array('run_time'=>date('Y-m-d H:i:s')));
                    $this->checkBestOffer($accVal['id'],$param,$countrys);
                }        
            }
        }
    }
    

    
    
}