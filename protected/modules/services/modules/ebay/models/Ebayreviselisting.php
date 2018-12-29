<?php

class Ebayreviselisting extends EbayApiAbstract {
    
    public $token ;
    public $sendxml;
    public $siteid;
    public $verb;
    
    public function setToken($token) {
        $this->token = $token;
    }
    
    public function setsendxml($sendxml) {
        $this->sendxml = $sendxml;
    }
    
    public function setsite($siteid) {
        $this->siteid = $siteid;
    }
    
    public function requestXmlBody()
    {
        return $this->sendxml;
    }
    
    public function setRequest()
    {
        $this->setUserToken($this->token);
        $ebayKeys = ConfigFactory::getConfig('ebayKeys');
        
        $this->appID = $ebayKeys['appID'];
        $this->devID = $ebayKeys['devID'];
        $this->certID = $ebayKeys['certID'];
        $this->serverUrl = $ebayKeys['serverUrl'];
        $this->siteID = $this->siteid;
        $this->compatabilityLevel = 983;
        
        return $this;
    }
    
    public function reviselisting() {
        $response = $this->setRequest()
                            ->setVerb('ReviseFixedPriceItem')
                            ->setSiteId($this->siteid)
                            ->sendHttpRequest()
                            ->handleResponse()
        ;
        
        return $response;
    }
    
    public function handleResponse() {
        
        $return = array();
        switch($this->response->Ack)
        {
            case 'Success':
                $return['status'] = '200';
                $return['msg'] = '更新完成';
                date_default_timezone_set('Asia/Shanghai');
                $return['Timestamp'] = date('Y-m-d H:i:s',strtotime($this->response->Timestamp->__toString()));
                break;
            case 'Warning':
                $return['status'] = '200';
                $return['msg'] = '更新完成,'.isset($this->response->Errors['0']->LongMessage)?$this->response->Errors['0']->LongMessage:$this->response->Errors->LongMessage;
                date_default_timezone_set('Asia/Shanghai');
                $return['Timestamp'] = date('Y-m-d H:i:s',strtotime($this->response->Timestamp->__toString()));
                break;
            case 'Failure':
                $return['status'] = '500';
                $return['msg'] = '修改失败,'.isset($this->response->Errors['0']->LongMessage)?$this->response->Errors['0']->LongMessage:$this->response->Errors->LongMessage;;
                $return['error_code'] = $this->response->Errors->ErrorCode; // 20004
                break;
        }
        
        return $return;
    }
    
    public function listingxml($param) {
        $list = $param['list'];
        $descrtion = $param['descrtion'];
        $shipping = $param['shipping'];
        $variations = $param['variations'];
        $imglist = $param['imglist'];
//        echo "<pre>";var_dump($variations);exit();
        
        $listType = $list->listing_type;
        if($listType != 'FixedPriceItem') {
            UebModel::model('Ebayonlinelisting')->updateAll(array('status'=>0), 'itemid=:id', array(':id'=>$list->itemid));
            echo "不是固价，暂时无法修改";exit();
        }
        
        $xml = '';

        $xml .= '<ErrorLanguage>en_US</ErrorLanguage>';
        $xml .= '<WarningLevel>High</WarningLevel>';
        $xml .= '<Item>';
        $xml .= '<AutoPay>'.$list->auto_pay.'</AutoPay>';
        
        if($list->bestoffer_enabled == 'true') {
            $xml .= '<BestOfferDetails>';
            $xml .= '<BestOfferEnabled>'.$list->bestoffer_enabled.'</BestOfferEnabled>';
            $xml .= '</BestOfferDetails>';
            
            if($list->bestoffer_accept_price > 0 || $list->bestoffer_decline_price > 0) {
                $xml .= '<ListingDetails>';
                if($list->bestoffer_accept_price > 0) {
                    $xml .= '<BestOfferAutoAcceptPrice>'.$list->bestoffer_accept_price.'</BestOfferAutoAcceptPrice>';
                }
                
                if($list->bestoffer_decline_price > 0) {
                    $xml .= '<MinimumBestOfferPrice>'.$list->bestoffer_decline_price.'</MinimumBestOfferPrice>';
                }
                $xml .= '</ListingDetails>';
            }
        }
        
        
        
        $xml .= '<Country>'.$list->country.'</Country>';
        $xml .= '<Description><![CDATA['.$descrtion.']]></Description>';
        $xml .= '<DispatchTimeMax>'.$list->dispatch_time_max.'</DispatchTimeMax>';
        $xml .= '<ItemID>'.$list->itemid.'</ItemID>';
        $xml .= '<ListingDuration>'.$list->listing_duration.'</ListingDuration>';
        $xml .= '<Location>'.$list->location.'</Location>';
        if(empty($list->paypal_email_address)) {
            echo json_encode(array('status'=>500, 'msg'=>'收款PayPal不能为空'));exit();
        }
        $xml .= '<PayPalEmailAddress>'.$list->paypal_email_address.'</PayPalEmailAddress>';
        //图片
        if(!empty($imglist)) {
            $xml .= '<PictureDetails>';
            foreach($imglist as $imgkey=>$imglistval) {
                if($imgkey == '0') {
                    $xml .= '<GalleryType>Gallery</GalleryType>';
                    $xml .= '<PhotoDisplay>PicturePack</PhotoDisplay>';
                    $xml .= '<PictureSource>'.$imglistval->picture_source.'</PictureSource>';
                }
                
                $xml .= '<PictureURL>'.$imglistval->img_url.'</PictureURL>';
            }
            $xml .= '</PictureDetails>';
        }
        //类目
        $xml .= '<PrimaryCategory><CategoryID>'.$list->primary_category_id.'</CategoryID></PrimaryCategory>';
        if($list->second_category_id > 0) {
            $xml .= '<SecondaryCategory><CategoryID>'.$list->second_category_id.'</CategoryID></SecondaryCategory>';
        }
        if($list->variation_multi == '0') {
            $xml .= '<Quantity>'.$list->quantity.'</Quantity>';
        }
        //退款
        $xml .= '<ReturnPolicy>';
        $xml .= '<Description>'.$list->returns_description.'</Description>';
        if($list->refund_option) {
            $xml .= '<RefundOption>'.$list->refund_option.'</RefundOption>';
        } else {
            if($list->refund == 'Money Back') {
                $xml .= '<RefundOption>MoneyBack</RefundOption>';
            } elseif($list->refund == 'Money back or replacement (buyer\'s choice)') {
                $xml .= '<RefundOption>MoneyBackOrReplacement</RefundOption>';
            } elseif($list->refund == 'Money back or exchange (buyer\'s choice)') {
                $xml .= '<RefundOption>MoneyBackOrExchange</RefundOption>';
            } else {
//                $xml .= '<RefundOption>MoneyBack</RefundOption>';
            }
        }
        
        $xml .= '<RestockingFeeValueOption>'.$list->restocking_fee_value_option.'</RestockingFeeValueOption>';
        $xml .= '<ReturnsAcceptedOption>'.$list->returns_accepted_option.'</ReturnsAcceptedOption>';
        $xml .= '<ReturnsWithinOption>'.$list->returns_within_option.'</ReturnsWithinOption>';
        $xml .= '<ShippingCostPaidByOption>'.$list->shipping_cost_paid_by_option.'</ShippingCostPaidByOption>';
        $xml .= '</ReturnPolicy>';
        //运输方式
        if(!empty($shipping)) {
            $xml .= '<ShippingDetails>';
            foreach($shipping as $shippingval) {
                if($shippingval->shipping_status == 2) {
                    $xml .= '<InternationalShippingServiceOption>';
                    $xml .= '<ShippingService>'.$shippingval->shipping_service.'</ShippingService>';
                    $xml .= '<ShippingServiceAdditionalCost>'.$shippingval->shipping_service_additional_cost.'</ShippingServiceAdditionalCost>';
                    $xml .= '<ShippingServiceCost>'.$shippingval->shipping_service_cost.'</ShippingServiceCost>';
                    $xml .= '<ShippingServicePriority>'.$shippingval->shipping_service_priority.'</ShippingServicePriority>';
                    if(!empty($shippingval->ship_to_location)) {
                        $shipLocation = explode('##', $shippingval->ship_to_location);
                        foreach($shipLocation as $shipLocationValue) {
                            $xml .= '<ShipToLocation>'.$shipLocationValue.'</ShipToLocation>';
                        }
                    }
                    $xml .= '</InternationalShippingServiceOption>';
                } else {
                    $xml .= '<ShippingServiceOptions>';
                    if($shippingval->shipping_service_cost <= 0) {
                        $xml .= '<FreeShipping>true</FreeShipping>';
                    }
                    $xml .= '<ShippingServiceCost>'.$shippingval->shipping_service_cost.'</ShippingServiceCost>';
                    
                    $xml .= '<ShippingService>'.$shippingval->shipping_service.'</ShippingService>';
                    $xml .= '<ShippingServiceAdditionalCost>'.$shippingval->shipping_service_additional_cost.'</ShippingServiceAdditionalCost>';
                    $xml .= '<ShippingServicePriority>'.$shippingval->shipping_service_priority.'</ShippingServicePriority>';
                    $xml .= '</ShippingServiceOptions>';
                }     
                $shiptype = $shippingval->shipping_type;
            }
            //不送达地区
            $excludeship = explode('##',$shippingval->exclude_ship_to_location);
            if(!empty($excludeship)) {
                foreach($excludeship as $excludevalue) {
                    $xml .= '<ExcludeShipToLocation>'.$excludevalue.'</ExcludeShipToLocation>';
                }
            }
            $xml .= '<ShippingType>'.$shiptype.'</ShippingType>';
            $xml .= '</ShippingDetails>';
        }    
        //sku price
        $xml .= '<SKU>'.$list->sku.'</SKU>';
        if($list->variation_multi == '0') {
            $xml .= '<StartPrice>'.$list->start_price.'</StartPrice>';
        }
        $xml .= '<Title>'.$list->title.'</Title>';
        if(!empty($list->sub_title)) {
            $xml .= '<SubTitle>'.$list->sub_title.'</SubTitle>';
        }
        if($list->uuid) {
            $xml .= '<UUID>'.$list->uuid.'</UUID>';
        }
        //variations
        
        if(!empty($variations)) {
            $xml .= '<Variations>';
    //         $xml .= '<Pictures>
    //                     <VariationSpecificName> string </VariationSpecificName>
    //                     <VariationSpecificPictureSet> VariationSpecificPictureSetType
    //                       <PictureURL> anyURI </PictureURL>
    //                       <!-- ... more PictureURL values allowed here ... -->
    //                       <VariationSpecificValue> string </VariationSpecificValue>
    //                     </VariationSpecificPictureSet>
    //                     <!-- ... more VariationSpecificPictureSet nodes allowed here ... -->
    //                   </Pictures>';
            foreach($variations as $variation) {
                $specificsName = explode('##',$variation->specifics_name);
                $specificsValue = explode('##',$variation->specifics_value);
                if($variation->status == '1') {
                    $xml .= '<Variation>';
                    $xml .= '<Delete>true</Delete>';
                    $xml .= '<SKU>'.$variation->sku.'</SKU>';
                    $xml .= '</Variation>';
                } else {
                
                    $xml .= '<Variation>';
                    $xml .= '<Quantity>'.$variation->quantity.'</Quantity>';
                    $xml .= '<SKU>'.$variation->sku.'</SKU>';
                    $xml .= '<StartPrice>'.$variation->start_price.'</StartPrice>';
                    $xml .= '<VariationProductListingDetails>';
                    if($variation->variation_listing_detail_ean) {
                        $xml .= '<EAN>'.$variation->variation_listing_detail_ean.'</EAN>';
                    }
                    if($variation->variation_listing_detail_isbn) {
                        $xml .= '<ISBN>'.$variation->variation_listing_detail_isbn.'</ISBN>';
                    }
                    if($variation->variation_listing_detail_upc) {
                        $xml .= '<UPC>'.$variation->variation_listing_detail_upc.'</UPC>';
                    }
                    $xml .= '</VariationProductListingDetails>';
                    $xml .= '<VariationSpecifics>';
                    foreach($specificsName as $specificsNameKey=>$specificsNameVal) {              
                        $xml .= '<NameValueList>';
                        $xml .= '<Name>'.$specificsNameVal.'</Name>';
                        $xml .= '<Value>'.$specificsValue[$specificsNameKey].'</Value>';
                        $xml .= '</NameValueList>';
                    }         
                    $xml .='</VariationSpecifics></Variation>';
                }
            }
                        
            $xml .= '</Variations>';
        }
        
        $xml .= '</Item>';
        
        return $xml;
    }
    
    
}