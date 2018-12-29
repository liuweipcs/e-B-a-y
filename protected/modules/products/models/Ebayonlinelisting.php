<?php

class Ebayonlinelisting extends ProductsModel {
    
    /**
     * Returns the static model of the specified AR class.
     * @return CActiveRecord the static model class
     */
    public static function model($className = __CLASS__){
        return parent::model($className);
    }
    
    /**
     * @return string the associated database table name
     */
    public function tableName(){
        return 'ueb_ebay_online_listing';
    }
    
    /**
     * @return array validation rules for model attributes.
     */
    public function rules(){
        return array();
    }
    
    public function checkpermission($loginid) {
        $permissionInfo = Yii::app()->db->createCommand()
            ->select('c.user_name')->from('ueb_system.ueb_user_map_ebay_account as a')
            ->leftJoin('ueb_system.ueb_user as b', 'a.user_id = b.id')
            ->leftJoin('ueb_system.ueb_ebay_account as c', 'a.ebay_account_id = c.id')
            ->where('b.id=:id')->queryAll(true,array(':id'=>$loginid));
        return $permissionInfo;
    }
    
    
    //站点
    public function getsite() {
        return array('0'=>'US',
            '3'=>'UK',
            '2'=>'Canada',
            '15'=>'Australia',
            '100'=>'eBayMotors',
            '71'=>'France',
            '77'=>'Germany',
            '186'=>'Spain',
            '101'=>'Italy',
            '201'=>'HongKong',
        );
    }
    
    public function getCurrency($id) {
        $arr = array(
                '0'=>'USD',
                '2'=>'CAD',
                '3'=>'GBP',
                '15'=>'AUD',
                '100'=>'USD',
                '71'=>'EUR',
                '77'=>'EUR',
                '186'=>'EUR',
                '101'=>'EUR',
                '201'=>'HKD',
            );
        return $arr[$id];    
    }
    
    
    
    public function getAccount()
    {
        if(empty($this->account))
            return null;
        else
            return (new Ebay())->find('user_name=:user_name',array(':user_name'=>$this->account));
    }

    public function getDescription()
    {
        return self::getDescriptionByItemId($this->itemid);
    }

    public static function getDescriptionByItemId($itemId)
    {
        if($itemId == '' || $itemId == null)
        {
            return false;
        }
        else
        {
            $path = dirname(Yii::app()->BasePath).'/upload/ebay_descrtion/'.implode('/',str_split($itemId,3)).'/'.$itemId.'.txt';
            return file_get_contents($path);
        }
    }

    public function getShipping()
    {
        if(!empty($this->itemid))
        {
            return (new Ebayonlinelistingshipping())->findAll(array(
                'condition'=>"item_id='{$this->itemid}'",
                'order'=>'shipping_status ASC,shipping_service_priority ASC'
            ));
        }
        else
        {
            return null;
        }
    }

    public function getShippingXml()
    {
        $shippingModels = $this->getShipping();
        if(!empty($shippingModels))
        {
            //$xml = '<ShippingDetails>';
            $xml = '';
            $xml .= '<ShippingType>Flat</ShippingType>';
            if(!empty($shippingModels[0]->exclude_ship_to_location))
            {
                $excludeShipToLocations = explode('##',$shippingModels[0]->exclude_ship_to_location);
                foreach ($excludeShipToLocations as $excludeShipToLocation)
                {
                    $xml .="<ExcludeShipToLocation>$excludeShipToLocation</ExcludeShipToLocation>";
                }
            }
            foreach ($shippingModels as $shippingModel)
            {
                if($shippingModel->shipping_status == 1)
                {
                    $xml .= '<ShippingServiceOptions>';
                    $xml .= '<ShippingService>'.$shippingModel->shipping_service.'</ShippingService>';
                    $xml .= '<ShippingServiceAdditionalCost>'.$shippingModel->shipping_service_additional_cost.'</ShippingServiceAdditionalCost>';
                    $xml .= '<ShippingServiceCost>'.$shippingModel->shipping_service_cost.'</ShippingServiceCost>';
                    $xml .= '<ShippingServicePriority>'.$shippingModel->shipping_service_priority.'</ShippingServicePriority>';
                    $xml .= '</ShippingServiceOptions>';
                }
                elseif ($shippingModel->shipping_status == 2)
                {
                    $xml .= '<InternationalShippingServiceOption>';
                    $xml .= '<ShippingService>'.$shippingModel->shipping_service.'</ShippingService>';
                    $xml .= '<ShippingServiceAdditionalCost>'.$shippingModel->shipping_service_additional_cost.'</ShippingServiceAdditionalCost>';
                    $xml .= '<ShippingServiceCost>'.$shippingModel->shipping_service_cost.'</ShippingServiceCost>';
                    $xml .= '<ShippingServicePriority>'.$shippingModel->shipping_service_priority.'</ShippingServicePriority>';
                    if(!empty($shippingModel->ship_to_location))
                    {
                        $InternationalShippingToLocations = explode('##',$shippingModel->ship_to_location);
                        foreach ($InternationalShippingToLocations as $InternationalShippingToLocation)
                        {
                            $xml .= "<ShipToLocation>$InternationalShippingToLocation</ShipToLocation>";
                        }
                    }
                    $xml .= '</InternationalShippingServiceOption>';
                }

            }
            //$xml .= '</ShippingDetails>';
            return $xml;
        }
        else
        {
            return null;
        }
    }

    //发送item到ebay
    public function sendApi($userToken = null)
    {
        if(empty($this->listing_status))
            return array('status'=>'error','info'=>'listing_status不能为空。');
        if(!in_array($this->listing_type,array('FixedPriceItem','Chinese')))
            return array('status'=>'error','info'=>'listing_type不为FixedPriceItem或Chinese。');
        if($this->itemid === '' || $this->itemid === null)
            return array('status'=>'error','info'=>'itemid不能为空。');
        if($this->primary_category_id === '' || $this->primary_category_id === null)
            return array('status'=>'error','info'=>'primary_category_id不能为空。');
        if($this->sku === '' || $this->sku === null)
            return array('status'=>'error','info'=>'sku不能为空。');
        if(empty($userToken))
        {
            $account = $this->getAccount();
            if(empty($account) || empty($account->user_token))
                return array('status'=>'error','info'=>'找不到token。');
            else
                $userToken = $account->user_token;
        }
        switch($this->listing_status)
        {
            case 'Active': //在线状态
                switch($this->listing_type)
                {
                    case 'FixedPriceItem':
                        return $this->ReviseFixedPriceItem($userToken);
                    case 'Chinese':
                        return $this->ReviseItem($userToken);
                }
                break;
            case 'Completed':
            case 'Ended':     //下线状态
                switch($this->listing_type)
                {
                    case 'FixedPriceItem':
                        return $this->RelistFixedPriceItem($userToken);
                    case 'Chinese':
                        return $this->RelistItem($userToken);
                }
                break;
        }
    }

    public function egdTest($userToken)
    {
        $api = new TradingAPI();
        if(empty($userToken))
        {
            $account = $this->getAccount();
            if(empty($account) || empty($account->user_token))
                return array('status'=>'error','info'=>'找不到token。');
            else
                $userToken = $account->user_token;
        }
        $api->setUserToken($userToken);
        $api->setSiteId($this->siteid);
        if($this->sell_sku === null || $this->sell_sku === '')
            $this->sell_sku = $this->sku;
        $api->xmlTagArray = [
            'ReviseFixedPriceItemRequest'=>[
                'Item'=>[
                    'AutoPay'=>'true',
                    'ItemID'=> $this->itemid,                   //判断itemID 是存在
                    'PrimaryCategory'=>[
                        'CategoryID'=>$this->primary_category_id,
                    ],
                    'SKU'=> htmlspecialchars($this->sell_sku),
                    'ShippingDetails'=>[
                        'ShippingType'=>'Flat',
                        'ShippingServiceOptions'=>[
                            'FreeShipping'=>'true',
                            'ShippingServiceCost'=>0,
                            'ShippingServiceAdditionalCost'=>0,
                            'ShippingService'=>'ShippingMethodStandard',
                            'ShippingServicePriority'=>1
                        ],
                        'RateTableDetails'=>[
                            'DomesticRateTableId'=>'5004612013'
                        ]
                    ],
                ]
            ]
        ];
        $api->xmlTagArray['ReviseFixedPriceItemRequest']['Item']['StartPrice'] = $this->start_price;
        $api->xmlTagArray['ReviseFixedPriceItemRequest']['Item']['Quantity'] = $this->quantity;
        $response = $api->send()->response;
        echo '<pre>';
        var_dump($response);
    }

    protected function ReviseFixedPriceItem($userToken = null)
    {
        if(empty($userToken))
        {
            $account = $this->getAccount();
            if(empty($account) || empty($account->user_token))
                return array('status'=>'error','info'=>'找不到token。');
            else
                $userToken = $account->user_token;
        }
        $api = new TradingAPI();
        $api->setUserToken($userToken);
        $api->setSiteId($this->siteid);
        if($this->sell_sku === null || $this->sell_sku === '')
            $this->sell_sku = $this->sku;
        $api->xmlTagArray = [
            'ReviseFixedPriceItemRequest'=>[
                'Item'=>[
                    'ItemID'=> $this->itemid,                   //判断itemID 是存在
                    'PrimaryCategory'=>[
                        'CategoryID'=>$this->primary_category_id,
                    ],
                    'SKU'=> htmlspecialchars($this->sell_sku),
                ]
            ]
        ];
        if($this->sub_title != '') {
            $api->xmlTagArray['ReviseFixedPriceItemRequest']['DeletedField'] = ['Item.SubTitle'];
        }
        $pictureDetails = $this->getImage($this->itemid);
        if(!empty($pictureDetails))
        {
            $api->xmlTagArray['ReviseFixedPriceItemRequest']['Item']['PictureDetails'] = $pictureDetails;
        }
        if($this->auto_pay !== '' && $this->auto_pay !== null)
        {
            $api->xmlTagArray['ReviseFixedPriceItemRequest']['Item']['AutoPay'] = $this->auto_pay;
        }
        if($this->conditon_id !== '' && $this->conditon_id !== null)
        {
            $api->xmlTagArray['ReviseFixedPriceItemRequest']['Item']['ConditionID'] = $this->conditon_id;
        }
        if($this->country !== '' && $this->country !== null)
        {
            $api->xmlTagArray['ReviseFixedPriceItemRequest']['Item']['Country'] = $this->country;
        }
        if($this->bestoffer_enabled !== '' && $this->bestoffer_enabled !== null)
        {
            $api->xmlTagArray['ReviseFixedPriceItemRequest']['Item']['BestOfferDetails']['BestOfferEnabled'] = $this->bestoffer_enabled;
        }
        if($this->dispatch_time_max !== '' && $this->dispatch_time_max !== null)
        {
            $api->xmlTagArray['ReviseFixedPriceItemRequest']['Item']['DispatchTimeMax'] = $this->dispatch_time_max;
        }
        $description = $this->getDescription();
        if($description !== false)
        {
            $api->xmlTagArray['ReviseFixedPriceItemRequest']['Item']['Description'] = '<![CDATA['.$description.']]>';
            $api->xmlTagArray['ReviseFixedPriceItemRequest']['Item']['DescriptionReviseMode'] = 'Replace';
        }
        if($this->layoutid !== '' && $this->layoutid !== null)
        {
            $api->xmlTagArray['ReviseFixedPriceItemRequest']['Item']['ListingDesigner']['LayoutID'] = $this->layoutid;
        }
        if($this->bestoffer_accept_price !== '' && $this->bestoffer_accept_price !== null && $this->bestoffer_accept_price > 0)
        {
            $api->xmlTagArray['ReviseFixedPriceItemRequest']['Item']['ListingDetails']['BestOfferAutoAcceptPrice'] = $this->bestoffer_accept_price;
        }
        if($this->bestoffer_decline_price !== '' && $this->bestoffer_decline_price !== null && $this->bestoffer_decline_price > 0)
        {
            $api->xmlTagArray['ReviseFixedPriceItemRequest']['Item']['ListingDetails']['MinimumBestOfferPrice'] = $this->bestoffer_decline_price;
        }
        if($this->refund_option !== '' && $this->refund_option !== null)
        {
            $api->xmlTagArray['ReviseFixedPriceItemRequest']['Item']['ReturnPolicy']['RefundOption'] = $this->refund_option;
        }
        if($this->restocking_fee_value_option !== '' && $this->restocking_fee_value_option !== null)
        {
            $api->xmlTagArray['ReviseFixedPriceItemRequest']['Item']['ReturnPolicy']['RestockingFeeValueOption'] = $this->restocking_fee_value_option;
        }
        if($this->returns_accepted_option !== '' && $this->returns_accepted_option !== null)
        {
            $api->xmlTagArray['ReviseFixedPriceItemRequest']['Item']['ReturnPolicy']['ReturnsAcceptedOption'] = $this->returns_accepted_option;
        }
        if($this->returns_within_option !== '' && $this->returns_within_option !== null)
        {
            $api->xmlTagArray['ReviseFixedPriceItemRequest']['Item']['ReturnPolicy']['ReturnsWithinOption'] = $this->returns_within_option;
        }
        if($this->shipping_cost_paid_by_option !== '' && $this->shipping_cost_paid_by_option !== null)
        {
            $api->xmlTagArray['ReviseFixedPriceItemRequest']['Item']['ReturnPolicy']['ShippingCostPaidByOption'] = $this->shipping_cost_paid_by_option;
        }
        if(isset($api->xmlTagArray['ReviseFixedPriceItemRequest']['Item']['ReturnPolicy']) && $this->returns_description !== null)
        {
            $api->xmlTagArray['ReviseFixedPriceItemRequest']['Item']['ReturnPolicy']['Description'] = $this->returns_description;
        }
        if($this->location !== '' && $this->location !== null)
        {
            $api->xmlTagArray['ReviseFixedPriceItemRequest']['Item']['Location'] = $this->location;
        }
        if($this->postcode !== '' && $this->postcode !== null)
        {
            $api->xmlTagArray['ReviseFixedPriceItemRequest']['Item']['PostalCode'] = $this->postcode;
        }
        if($this->payment_methods !== '' && $this->payment_methods !== null)
        {
            $api->xmlTagArray['ReviseFixedPriceItemRequest']['Item']['PaymentMethods'] = $this->payment_methods;
        }
        if($this->paypal_email_address !== '' && $this->paypal_email_address !== null)
        {
            $api->xmlTagArray['ReviseFixedPriceItemRequest']['Item']['PayPalEmailAddress'] = $this->paypal_email_address;
        }
        if($this->private_listing !== '' && $this->private_listing !== null)
        {
            $api->xmlTagArray['ReviseFixedPriceItemRequest']['Item']['PrivateListing'] = $this->private_listing;
        }
        if($this->second_category_id !== '' && $this->second_category_id !== null)
        {
            $api->xmlTagArray['ReviseFixedPriceItemRequest']['Item']['SecondaryCategory']['CategoryID'] = $this->second_category_id;
        }
        $itemSpecifics = $this->getSpecifics($this->itemid);
        if(!empty($itemSpecifics))
        {
            $api->xmlTagArray['ReviseFixedPriceItemRequest']['Item']['ItemSpecifics'] = $itemSpecifics;
        }
        if($this->listing_duration !== '' && $this->listing_duration !== null)
        {
            $api->xmlTagArray['ReviseFixedPriceItemRequest']['Item']['ListingDuration'] = $this->listing_duration;
        }
        $shippingDetails = $this->getShippingXml();
        if(!empty($shippingDetails))
        {
            $api->xmlTagArray['ReviseFixedPriceItemRequest']['Item']['ShippingDetails'] = $shippingDetails;
        }
        if($this->store_second_category_id !== '' && $this->store_second_category_id !== null)
        {
            $api->xmlTagArray['ReviseFixedPriceItemRequest']['Item']['Storefront']['StoreCategory2ID'] = $this->store_second_category_id;
        }
        if($this->store_category_id !== '' && $this->store_category_id !== null)
        {
            $api->xmlTagArray['ReviseFixedPriceItemRequest']['Item']['Storefront']['StoreCategoryID'] = $this->store_category_id;
        }
        if($this->sub_title !== '' && $this->sub_title !== null)
        {
            $api->xmlTagArray['ReviseFixedPriceItemRequest']['Item']['SubTitle'] = htmlspecialchars($this->sub_title);
        }
        if($this->title !== '' && $this->title !== null)
        {
            $api->xmlTagArray['ReviseFixedPriceItemRequest']['Item']['Title'] = htmlspecialchars($this->title);
        }
        //多属性
        if($this->variation_multi == '1') {
            $api->xmlTagArray['ReviseFixedPriceItemRequest']['Item']['Variations'] = $this->getVariation($this->itemid);
        } else { //单属性
            $api->xmlTagArray['ReviseFixedPriceItemRequest']['Item']['StartPrice'] = $this->start_price;
            $api->xmlTagArray['ReviseFixedPriceItemRequest']['Item']['Quantity'] = $this->quantity;
        }

        $response = $api->send()->response;
        return $this->handleSendResponse($response);
    }
    protected function ReviseItem($userToken = null)
    {
        if(empty($userToken))
        {
            $account = $this->getAccount();
            if(empty($account) || empty($account->user_token))
                return array('status'=>'error','info'=>'找不到token。');
            else
                $userToken = $account->user_token;
        }
        $api = new TradingAPI();
        $api->setUserToken($userToken);
        $api->setSiteId($this->siteid);
        $api->xmlTagArray = [
            'ReviseItemRequest'=>[
                'Item'=>[
                    'ItemID'=> $this->itemid,                   //判断itemID 是存在
                    'PrimaryCategory'=>[
                        'CategoryID'=>$this->primary_category_id,
                    ],
                    'SKU'=> htmlspecialchars($this->sku),
                ]
            ]
        ];
        $pictureDetails = $this->getImage($this->itemid);
        if(!empty($pictureDetails))
        {
            $api->xmlTagArray['ReviseItemRequest']['Item']['PictureDetails'] = $pictureDetails;
        }
        if($this->auto_pay !== '' && $this->auto_pay !== null)
        {
            $api->xmlTagArray['ReviseItemRequest']['Item']['AutoPay'] = $this->auto_pay;
        }
        if($this->conditon_id !== '' && $this->conditon_id !== null)
        {
            $api->xmlTagArray['ReviseItemRequest']['Item']['ConditionID'] = $this->conditon_id;
        }
        if($this->country !== '' && $this->country !== null)
        {
            $api->xmlTagArray['ReviseItemRequest']['Item']['Country'] = $this->country;
        }
//         if($this->bestoffer_enabled !== '' && $this->bestoffer_enabled !== null)
//         {
//             $api->xmlTagArray['ReviseItemRequest']['Item']['BestOfferDetails']['BestOfferEnabled'] = $this->bestoffer_enabled;
//         }
        if($this->dispatch_time_max !== '' && $this->dispatch_time_max !== null)
        {
            $api->xmlTagArray['ReviseItemRequest']['Item']['DispatchTimeMax'] = $this->dispatch_time_max;
        }
        $description = $this->getDescription();
        if($description !== false)
        {
            $api->xmlTagArray['ReviseItemRequest']['Item']['Description'] = '<![CDATA['.$description.']]>';
            $api->xmlTagArray['ReviseItemRequest']['Item']['DescriptionReviseMode'] = 'Replace';
        }
        if($this->layoutid !== '' && $this->layoutid !== null)
        {
            $api->xmlTagArray['ReviseItemRequest']['Item']['ListingDesigner']['LayoutID'] = $this->layoutid;
        }
//         if($this->bestoffer_accept_price !== '' && $this->bestoffer_accept_price !== null && $this->bestoffer_accept_price > 0)
//         {
//             $api->xmlTagArray['ReviseItemRequest']['Item']['ListingDetails']['BestOfferAutoAcceptPrice'] = $this->bestoffer_accept_price;
//         }
//         if($this->bestoffer_decline_price !== '' && $this->bestoffer_decline_price !== null && $this->bestoffer_decline_price > 0)
//         {
//             $api->xmlTagArray['ReviseItemRequest']['Item']['ListingDetails']['MinimumBestOfferPrice'] = $this->bestoffer_decline_price;
//         }
        if($this->refund_option !== '' && $this->refund_option !== null)
        {
            $api->xmlTagArray['ReviseItemRequest']['Item']['ReturnPolicy']['RefundOption'] = $this->refund_option;
        }
        if($this->restocking_fee_value_option !== '' && $this->restocking_fee_value_option !== null)
        {
            $api->xmlTagArray['ReviseItemRequest']['Item']['ReturnPolicy']['RestockingFeeValueOption'] = $this->restocking_fee_value_option;
        }
        if($this->returns_accepted_option !== '' && $this->returns_accepted_option !== null)
        {
            $api->xmlTagArray['ReviseItemRequest']['Item']['ReturnPolicy']['ReturnsAcceptedOption'] = $this->returns_accepted_option;
        }
        if($this->returns_within_option !== '' && $this->returns_within_option !== null)
        {
            $api->xmlTagArray['ReviseItemRequest']['Item']['ReturnPolicy']['ReturnsWithinOption'] = $this->returns_within_option;
        }
        if($this->shipping_cost_paid_by_option !== '' && $this->shipping_cost_paid_by_option !== null)
        {
            $api->xmlTagArray['ReviseItemRequest']['Item']['ReturnPolicy']['ShippingCostPaidByOption'] = $this->shipping_cost_paid_by_option;
        }
        if(isset($api->xmlTagArray['ReviseItemRequest']['Item']['ReturnPolicy']) && $this->returns_description !== null)
        {
            $api->xmlTagArray['ReviseItemRequest']['Item']['ReturnPolicy']['Description'] = $this->returns_description;
        }
        if($this->location !== '' && $this->location !== null)
        {
            $api->xmlTagArray['ReviseItemRequest']['Item']['Location'] = $this->location;
        }
        var_dump($this->postcode);
        var_dump(($this->postcode !== '' && $this->postcode !== null));
        if($this->postcode !== '' && $this->postcode !== null)
        {
            $api->xmlTagArray['ReviseItemRequest']['Item']['PostalCode'] = $this->postcode;
        }
        if($this->payment_methods !== '' && $this->payment_methods !== null)
        {
            $api->xmlTagArray['ReviseItemRequest']['Item']['PaymentMethods'] = $this->payment_methods;
        }
        if($this->paypal_email_address !== '' && $this->paypal_email_address !== null)
        {
            $api->xmlTagArray['ReviseItemRequest']['Item']['PayPalEmailAddress'] = $this->paypal_email_address;
        }
        if($this->private_listing !== '' && $this->private_listing !== null)
        {
            $api->xmlTagArray['ReviseItemRequest']['Item']['PrivateListing'] = $this->private_listing;
        }
        if($this->second_category_id !== '' && $this->second_category_id !== null)
        {
            $api->xmlTagArray['ReviseItemRequest']['Item']['SecondaryCategory']['CategoryID'] = $this->second_category_id;
        }
        $itemSpecifics = $this->getSpecifics($this->itemid);
        if(!empty($itemSpecifics))
        {
            $api->xmlTagArray['ReviseItemRequest']['Item']['ItemSpecifics'] = $itemSpecifics;
        }
        if($this->listing_duration !== '' && $this->listing_duration !== null)
        {
            $api->xmlTagArray['ReviseItemRequest']['Item']['ListingDuration'] = $this->listing_duration;
        }
        $shippingDetails = $this->getShippingXml();
        if(!empty($shippingDetails))
        {
            $api->xmlTagArray['ReviseItemRequest']['Item']['ShippingDetails'] = $shippingDetails;
        }
        if($this->store_second_category_id !== '' && $this->store_second_category_id !== null)
        {
            $api->xmlTagArray['ReviseItemRequest']['Item']['Storefront']['StoreCategory2ID'] = $this->store_second_category_id;
        }
        if($this->store_category_id !== '' && $this->store_category_id !== null)
        {
            $api->xmlTagArray['ReviseItemRequest']['Item']['Storefront']['StoreCategoryID'] = $this->store_category_id;
        }
        if($this->sub_title !== '' && $this->sub_title !== null)
        {
            $api->xmlTagArray['ReviseItemRequest']['Item']['SubTitle'] = $this->sub_title;
        }
        if($this->title !== '' && $this->title !== null)
        {
            $api->xmlTagArray['ReviseItemRequest']['Item']['Title'] = $this->title;
        }
        if($this->reserve_price !== '' && $this->reserve_price !== null)
        {
            $api->xmlTagArray['ReviseItemRequest']['Item']['ReservePrice'] = $this->reserve_price;
        }
        if($this->start_price !== '' && $this->start_price !== null)
        {
            $api->xmlTagArray['ReviseItemRequest']['Item']['StartPrice'] = $this->start_price;
        }
        if($this->quantity !== '' && $this->quantity !== null)
        {
            $api->xmlTagArray['ReviseItemRequest']['Item']['Quantity'] = $this->quantity;
        }
        if($this->buy_it_now_price !== '' && $this->buy_it_now_price !== null)
        {
            $api->xmlTagArray['ReviseItemRequest']['Item']['BuyItNowPrice'] = $this->buy_it_now_price;
        }
//        return htmlspecialchars($api->requestXmlBody());
        $response = $api->send()->response;
        return $this->handleSendResponse($response);
    }

    public function RelistItem($userToken = null)
    {
        if(empty($userToken)) {
            $account = $this->getAccount();
            if(empty($account) || empty($account->user_token))
                return array('status'=>'error','info'=>'找不到token。');
            else
                $userToken = $account->user_token;
        }
        
        $api = new TradingAPI();
        $api->setSiteId($this->siteid);
        $api->setUserToken($userToken);
        $api->xmlTagArray = [
            'RelistItemRequest'=>[
                'ErrorLanguage'=>'en_US',
                'WarningLevel'=>'High',
                'Item'=>[
                    'ItemID'=>$this->itemid,
                    'Description'=>'<![CDATA['.$this->getDescription().']]>',
                    'DispatchTimeMax'=>$this->dispatch_time_max,
                    'ListingDuration'=>$this->listing_duration,
                    'ListingType'=>$this->listing_type,
                    'Location'=>$this->location,
                    'PaymentMethods'=>$this->payment_methods,
                    'PayPalEmailAddress'=>$this->paypal_email_address,
                    'PrimaryCategory'=>[
                        'CategoryID'=>$this->primary_category_id,
                    ],
                    
                    'PrivateListing'=>$this->private_listing,
                    'ReturnPolicy'=>[
                        'Description'=>$this->returns_description,
                        'RestockingFeeValueOption'=>$this->restocking_fee_value_option,
                        'ReturnsAcceptedOption'=>$this->returns_accepted_option,
                        'ReturnsWithinOption'=>$this->returns_within_option,
                        'ShippingCostPaidByOption'=>empty($this->shipping_cost_paid_by_option)?'Buyer':$this->shipping_cost_paid_by_option,
                    ],
                    'Site'=>$this->site,
                    'SKU'=>$this->sku,
                    'Title'=>$this->title,
                    'PictureDetails'=>$this->getImage($this->itemid),
                    'ShippingDetails'=>$this->getShippingXml(),
                ],
            ]
        ];
        
        if(!empty($this->refund_option)) {
            $api->xmlTagArray['RelistItemRequest']['Item']['ReturnPolicy']['RefundOption'] = $this->refund_option;
        }
        //ConditionID
        if(!empty($this->conditon_id)) {
            $api->xmlTagArray['RelistItemRequest']['Item']['ConditionID'] = $this->conditon_id;
        }
        //country
        if(!empty($this->country)) {
            $api->xmlTagArray['RelistItemRequest']['Item']['Country'] = $this->country;
        }
        //PostalCode
        if(!empty($this->postcode)) {
            $api->xmlTagArray['RelistItemRequest']['Item']['PostalCode'] = $this->postcode;
        }
        //SecondaryCategory
        if(!empty($this->second_category_id)) {
            $api->xmlTagArray['RelistItemRequest']['Item']['SecondaryCategory']['CategoryID'] = $this->second_category_id;
        }
        //sub_title
        if(!empty($this->sub_title)) {
            $api->xmlTagArray['RelistItemRequest']['Item']['SubTitle'] = $this->sub_title;
        }
        // uuid
//        if(!empty($this->uuid)) {
            //$api->xmlTagArray['RelistFixedPriceItemRequest']['Item']['UUID'] = $this->uuid;
//        }
        // store_category 1
        if(!empty($this->store_category_id)) {
            $api->xmlTagArray['RelistItemRequest']['Item']['Storefront']['StoreCategoryID'] = $this->store_category_id;
        }
        // store_category2
        if(!empty($this->store_second_category_id)) {
            $api->xmlTagArray['RelistItemRequest']['Item']['Storefront']['StoreCategory2ID'] = $this->store_second_category_id;
        }
        //specifics
        if(count($this->getSpecifics($this->itemid)) > 0) {
            $api->xmlTagArray['RelistItemRequest']['Item']['ItemSpecifics'] = $this->getSpecifics($this->itemid);
        }
         
        //多属性
        if($this->variation_multi == '1') {
            $api->xmlTagArray['RelistItemRequest']['Item']['Variations'] = $this->getVariation($this->itemid);
        } else { //单属性
            $api->xmlTagArray['RelistItemRequest']['Item']['StartPrice'] = $this->start_price;
            $api->xmlTagArray['RelistItemRequest']['Item']['Quantity'] = $this->quantity;
        }
        
        $response = $api->send()->response;
        return $this->handleRelistResponse($response);
    }

    public function RelistFixedPriceItem($userToken = null)
    {
        if(empty($userToken)) {
            $account = $this->getAccount();
            if(empty($account) || empty($account->user_token))
                return array('status'=>'error','info'=>'找不到token。');
            else
                $userToken = $account->user_token;
        }
        
        $api = new TradingAPI();
        $api->setUserToken($userToken);
        $api->setSiteId($this->siteid);
        $api->xmlTagArray = [
            'RelistFixedPriceItemRequest'=>[
                'ErrorLanguage'=>'en_US',
                'WarningLevel'=>'High',
                'Item'=>[
                    'ItemID'=>$this->itemid,
                    'Description'=>'<![CDATA['.$this->getDescription().']]>',
                    'DispatchTimeMax'=>$this->dispatch_time_max,
                    'ListingDuration'=>$this->listing_duration,
                    'ListingType'=>$this->listing_type,
                    'Location'=>$this->location,
                   'PaymentMethods'=>$this->payment_methods,
                   'PayPalEmailAddress'=>$this->paypal_email_address,
                   'PrimaryCategory'=>[
                       'CategoryID'=>$this->primary_category_id,
                   ],
                    
                   'PrivateListing'=>$this->private_listing,
                    'ReturnPolicy'=>[
                        'Description'=>$this->returns_description,
                        'RestockingFeeValueOption'=>$this->restocking_fee_value_option,
                        'ReturnsAcceptedOption'=>$this->returns_accepted_option,
                        'ReturnsWithinOption'=>$this->returns_within_option,
                        'ShippingCostPaidByOption'=>empty($this->shipping_cost_paid_by_option)?'Buyer':$this->shipping_cost_paid_by_option,
                    ],
                    'Site'=>$this->site,
                    'SKU'=>$this->sku,
                    'Title'=>$this->title,
                    'PictureDetails'=>$this->getImage($this->itemid),
                    'ShippingDetails'=>$this->getShippingXml(),
                ],
            ],
        ];
                
        if(!empty($this->refund_option)) {
            $api->xmlTagArray['RelistFixedPriceItemRequest']['Item']['ReturnPolicy']['RefundOption'] = $this->refund_option;
        }
        //ConditionID
        if(!empty($this->conditon_id)) {
            $api->xmlTagArray['RelistFixedPriceItemRequest']['Item']['ConditionID'] = $this->conditon_id;
        }
        //country
        if(!empty($this->country)) {
            $api->xmlTagArray['RelistFixedPriceItemRequest']['Item']['Country'] = $this->country;
        }
        //PostalCode
        if(!empty($this->postcode)) {
            $api->xmlTagArray['RelistFixedPriceItemRequest']['Item']['PostalCode'] = $this->postcode;
        }
        //SecondaryCategory
       if(!empty($this->second_category_id)) {
           $api->xmlTagArray['RelistFixedPriceItemRequest']['Item']['SecondaryCategory']['CategoryID'] = $this->second_category_id;
       }    
        //sub_title
        if(!empty($this->sub_title)) {
            $api->xmlTagArray['RelistFixedPriceItemRequest']['Item']['SubTitle'] = $this->sub_title;
        }
       // uuid
//       if(!empty($this->uuid)) {
           //$api->xmlTagArray['RelistFixedPriceItemRequest']['Item']['UUID'] = $this->uuid;
//       }
       // store_category 1
      if(!empty($this->store_category_id)) {
          $api->xmlTagArray['RelistFixedPriceItemRequest']['Item']['Storefront']['StoreCategoryID'] = $this->store_category_id;
      }
       // store_category2
      if(!empty($this->store_second_category_id)) {
          $api->xmlTagArray['RelistFixedPriceItemRequest']['Item']['Storefront']['StoreCategory2ID'] = $this->store_second_category_id;
      }
       //specifics
      if(count($this->getSpecifics($this->itemid)) > 0) {
          $api->xmlTagArray['RelistFixedPriceItemRequest']['Item']['ItemSpecifics'] = $this->getSpecifics($this->itemid);
      }
       
       //多属性
       if($this->variation_multi == '1') {
           $api->xmlTagArray['RelistFixedPriceItemRequest']['Item']['Variations'] = $this->getVariation($this->itemid);
       } else { //单属性
           $api->xmlTagArray['RelistFixedPriceItemRequest']['Item']['StartPrice'] = $this->start_price;
           $api->xmlTagArray['RelistFixedPriceItemRequest']['Item']['Quantity'] = $this->quantity;
       }
//       echo "<pre>";var_dump($api->requestXmlBody());exit();
       $response = $api->send()->response;
       //echo "<pre>";var_dump($response);exit();
       return $this->handleRelistResponse($response);
    }

    //specifics
    public function getSpecifics($itemId) {
        $info = UebModel::model('Ebayonlinelistingspecifics')->findAll('item_id='.$itemId);
        $arr = [];
        if(!empty($info)) {
            foreach($info as $value) {
                $arr['NameValueList'][] = [
                        'Name'=>htmlspecialchars($value->name),
                        'Value'=>htmlspecialchars($value->value)
                ];
            }  
        }
        
        return $arr;
    }
    // image
    public function getImage($itemId) {
        $info = UebModel::model('Ebayonlinelistingimage')->findAll('item_id='.$itemId.' and img_status=0');
        $arr = [];
        if(!empty($info)) {
            foreach($info as $key=>$value) {
                if($key == '0') {
                    $arr['PhotoDisplay'] = 'PicturePack';
                    $arr['PictureSource'] = 'EPS';//$value->picture_source;
                    $arr['PictureURL'][$key] = str_replace('http://image-us.bigbuy.win','https://image-us.bigbuy.win',$value->img_url);
                }
                else
                    $arr['PictureURL'][$key] = $value->img_url;
            }
        }
        return $arr;
    }
    //variation
    public function getVariation($itemId) {
        $variationInfo = UebModel::model('Ebayonlinelistingvariation')->findAll('item_id='.$itemId.' and status=0 ');
        $variationImg = UebModel::model('Ebayonlinelistingvariationimg')->findAll(array(
            'condition'=>'item_id='.$itemId.' and status=0',
            'group'=>'variation_name,variation_value'
        ));
        
        $model = UebModel::model('Ebayonlinelisting')->find('itemid='.$itemId);
        $variationSpecificsImg = [];
        if(!empty($variationImg)) {
            foreach($variationImg as $imgKey=>$imgValue) {
                $tempImg = UebModel::model('Ebayonlinelistingvariationimg')->findAll(array(
                    'condition'=>'item_id='.$itemId.' and variation_name="'.addslashes($imgValue->variation_name).'" and variation_value="'.addslashes($imgValue->variation_value).'" order by id asc ',
                ));
                $tempImgArr = array();
                foreach($tempImg as $tempImgValue) {
                    $tempImgArr[] = VHelper::imageLinkTransformHttps($tempImgValue->img_url);
                }
                
                if($imgKey == '0') {
                    $variationSpecificsImg['VariationSpecificName'] = htmlspecialchars($imgValue->variation_name);
                }
                $variationSpecificsImg['VariationSpecificPictureSet'][$imgKey]['VariationSpecificPictureSet'] = htmlspecialchars($imgValue->variation_value);
                $variationSpecificsImg['VariationSpecificPictureSet'][$imgKey]['PictureURL'] = $tempImgArr;
            }
        }
        
        $arr = [];
        if(!empty($variationInfo)) {
            foreach($variationInfo as $value) {
                $specArr = [];
                $specName = explode('#@#',$value->specifics_name);
                $specValue = explode('#@#',$value->specifics_value);
                
                if(!empty($specName)) {
                    foreach($specName as $specK=>$specNameValue) {
                        $specArr['NameValueList'][] = [
                            'Name'=>htmlspecialchars($specNameValue), 
                            'Value'=>htmlspecialchars($specValue[$specK]),
                        ];
                    }
                }
                
                $variationSpecifics = [];
                if($value->variation_listing_detail_ean != '') {
                    $variationSpecifics['EAN'] = $value->variation_listing_detail_ean;
                }
                
                if($value->variation_listing_detail_isbn != '') {
                    $variationSpecifics['ISBN'] = $value->variation_listing_detail_isbn;
                }
                
                if($value->variation_listing_detail_upc) {
                    $variationSpecifics['UPC'] = $value->variation_listing_detail_upc;
                }
                
                $arr['Variation'][] = [
                    'Quantity'=>$value->quantity,
                    'SKU'=>$value->sku,
                    'StartPrice'=>$value->start_price,
                    'VariationSpecifics'=>$specArr,
                    'VariationProductListingDetails'=>$variationSpecifics,
                ];
            }
            //if($model->quantity_sold =='0') {
                $arr['Pictures'] = $variationSpecificsImg;
            //}
        }
        
        return $arr;
    }
    
    //处理重新上架 结果
    public function handleRelistResponse($response) {
        $return  = array();
        switch ($response->Ack)
        {
            case 'Failure':
                $this->status = 6;
                $this->remark = $response->Errors->asXML();
                $return = array('status'=>'error', 'info'=>'API调用失败，Failure');
                break;
            case 'Success':
                $this->status = 0;
                date_default_timezone_set('Asia/Shanghai');
                $this->end_time = strtotime($response->EndTime);
                $this->listing_status = 'Completed';
                $this->xml_data_time = date('Y-m-d H:i:s', strtotime($response->Timestamp->__toString()) -2);
                $this->remark = $response->ItemID;
                $return = array('status'=>'success');
                break;
            case 'Warning':
                $this->status = 0;
                date_default_timezone_set('Asia/Shanghai');
                $this->xml_data_time = date('Y-m-d H:i:s', strtotime($response->Timestamp->__toString()) -2);
                $this->remark = $response->Errors->asXML();
                $this->end_time = strtotime($response->EndTime);
                $this->listing_status = 'Completed';
                $return = array('status'=>'warning', 'info'=>'API调用结果：warning');
                break;
        }
        if($this->save())
        {
            $return['save'] = true;
        }
        else
        {
            $return['save'] = false;
        }
        
        return $return;
    }
    
    //发送item到ebay的返回信息处理，对应sendApi方法
    public function handleSendResponse($response)
    {
        $return = array();
        switch($response->Ack)
        {
            case 'Failure':
                $this->status = 6;
                $this->remark = $response->Errors->asXML();
                $return = array('status'=>'error','info'=>'接口ACK:Failure。');
                break;
            case 'Success':
                $this->status = 0;
                date_default_timezone_set('Asia/Shanghai');
                $this->xml_data_time = date('Y-m-d H:i:s',strtotime($response->Timestamp->__toString()) - 2);
                $return = array('status'=>'success');
                break;
            case 'Warning':
                $this->status = 0;
                date_default_timezone_set('Asia/Shanghai');
                $this->xml_data_time = date('Y-m-d H:i:s',strtotime($response->Timestamp->__toString()) - 2);
                $this->remark = $response->Errors->asXML();
                $return = array('status'=>'warning','info'=>'接口ACK:Warning。');
                break;
        }
        if($this->save())
        {
            $return['save'] = true;
        }
        else
        {
            $return['save'] = false;
        }
        return $return;
    }
    
    //从ebay同步信息到数据库
    public function getItemApi()
    {
        if(empty($userToken)) {
            $account = $this->getAccount();
            if(empty($account) || empty($account->user_token))
                return array('status'=>'error','info'=>'找不到token。');
            else
                $userToken = $account->user_token;
        }
        $api = new TradingAPI();
        $api->setUserToken($userToken);
        
        $api->xmlTagArray = [
            'GetItemRequest'=>[
                'DetailLevel'=>'ReturnAll',
                'ErrorLanguage'=>'en_US',
                'WarningLevel'=>'High',
                'IncludeItemSpecifics'=>'true',
                'IncludeWatchCount'=>'true',
                'ItemID'=>$this->itemid,
            ],
        ];
        
//        $api->requestXmlBody();
        $response = $api->send();    
        //echo "<pre>";var_dump($response);exit();
        return self::getItemByApi($response);
    }
    
    public static function getItemByApi($response) 
    {
        date_default_timezone_set('Asia/Shanghai');
        $itemid = $response->response->Item->ItemID;
        $xmlDataTime = date('Y-m-d H:i:s',strtotime($response->response->Timestamp) - 2);
        $ack = isset($response->response->Ack)?$response->response->Ack:'Failure';
        if($ack == 'Failure') {
            $errCode = $response->response->Errors->ErrorCode;
            if($errCode == '17') {
                $itemids = $response->response->Errors->ErrorParameters->Value;
                UebModel::model('Ebayonlinelisting')->updateAll(array('listing_status'=>'Completed'),'itemid='.$itemids);
            }
            return array('status'=>'error', 'info'=>$response->response->Errors->LongMessage);
        }
        
        $listingObj = new Ebaygetsellerlistsnew();
        $listingObj->setforced(true);
        $storeName = $response->response->Item->Seller->UserID;
         
        $listResponse = $listingObj->updatelist($response->response->Item, $storeName,$xmlDataTime);
        
        if($listResponse['status'] == '300') {  //listing有修改 直接返回
            return array('status'=>'error','info'=>'Listing有修改未同步到eBay');
        }
        
        $listingObj->updatevariations($itemid, $response->response->Item->Variations);
        $listingObj->updatedescrtion($itemid, $response->response->Item->Description);
        $listingObj->updateshipping($itemid, $response->response->Item->ShippingDetails);
        $listingObj->updateimage($itemid, $response->response->Item->PictureDetails,$listResponse['id']);
        $listingObj->updateattributes($itemid, $response->response->Item->ItemSpecifics);
        $listingObj->updatevariationimg($itemid,$response->response->Item->Variations->Pictures);
        
        if($listResponse['status'] == '200') {
            return array('status'=>'success');
        } else {
            return array('status'=>'error', 'info'=>'同步失败');
        }
    }
    
    //从ebay同步信息到数据库
    public static function getItemApi2($itemid,$userToken)
    {
        $api = new TradingAPI();
        $api->setUserToken($userToken);
    
        $api->xmlTagArray = [
            'GetItemRequest'=>[
                'DetailLevel'=>'ReturnAll',
                'ErrorLanguage'=>'en_US',
                'WarningLevel'=>'High',
                'IncludeItemSpecifics'=>'true',
                'IncludeWatchCount'=>'true',
                'ItemID'=>$itemid,
            ],
        ];
        
        $response = $api->send()->response;
        return $response;
    }
    
    //检查ebay费用
    public static function verifyFeeForItem() {
        
    }
    
    //检查ebay费用 Fixed
    public static function verifyFeeForFixed($data, $token, $siteId='0') {
        Yii::import('application.modules.services.modules.ebay.components.EbayApiAbstract',true);
        Yii::import('application.modules.services.modules.ebay.models.TradingAPI',true);
        $api = new TradingAPI();
        $api->setUserToken($token);
        $api->setSiteId($siteId);
        
        $api->xmlTagArray = [
            'VerifyAddFixedPriceItemRequest'=>[
                'DetailLevel'=>'ReturnAll',
                'ErrorLanguage'=>'en_US',
                'WarningLevel'=>'High',
                'Item'=>$data,
            ],
        ];
        
        //$response = $api->requestXmlBody();
        $response = $api->send()->response;
        //echo "<pre>";var_dump($response);exit();
        $return = self::handVerifyResponse($response);
        return $return;
    }
    
    //检查ebay费用 chinese
    public static function verifyFeeForAuction($data, $token, $siteId = '0') {
        $api = new TradingAPI();
        $api->setUserToken($token);
        $api->setSiteId($siteId);
        
        $api->xmlTagArray = [
            'VerifyAddItemRequest'=>[
                'DetailLevel'=>'ReturnAll',
                'ErrorLanguage'=>'en_US',
                'WarningLevel'=>'High',
                'Item'=>$data
            ],
        ];
        $response = $api->send()->response;
        $return = self::handVerifyResponse($response);
        return $return;
    }
    
    public static function handVerifyResponse($response) {
        $ack = isset($response->Ack)?$response->Ack:'Failure';
        $data['status'] = array();
        if($ack != 'Failure') {
            $data['status'] = 'success';
            foreach($response->Fees->Fee as $value) {
                if($value->Name == 'ListingFee') {
                    $data['fee'] = $value->Fee->__toString().' '.$value->Fee['currencyID']->__toString(); //currencyID
                }
            }
        } else {
            $data['status'] = 'error';
            $data['msg'] = isset($response->Errors->LongMessage)?$response->Errors->LongMessage->__toString():'api 错误';
        }
        
        return $data;
    }
    
    //修改时检查eBay费用 处理数据结构
    public static function handleparam($data) {
        $xml = '';
        if(!empty($data['ebay_site'])) {
            $xml .= '<Site>'.$data['ebay_site'].'</Site>';
        }
        
        if(!empty($data['listing_type'])) {
            $xml .= '<ListingType>'.$data['listing_type'].'</ListingType>';
        }
        
        if(!empty($data['ebay_sku'])) {
            $xml .= '<SKU>'.$data['ebay_sku'].'s</SKU>';
        }
        
        if(!empty($data['title'])) {
            $xml .= '<Title>test sku test test</Title>';
        }
        
        if(!empty($data['sub_title'])) {
            $xml .= '<SubTitle>'.$data['sub_title'].'</SubTitle>';
        }
        
        if(!empty($data['ebay_img'])) {
            $xml .= '<PictureDetails>';
            $xml .= '<PhotoDisplay>PicturePack</PhotoDisplay>';
            foreach($data['ebay_img'] as $img_key=>$img_value) {
                if($img_key == '0') {
                    $imgFlag = strpos($img_value, 'ebayimg.com');
                    if($imgFlag === false) {
                        $source = 'Vendor';
                    } else {
                        $source = 'EPS';
                    }
                    $xml .= '<PictureSource>'.$source.'</PictureSource>';
                }
                
                $xml .= '<PictureURL>'.$img_value.'</PictureURL>';
            }
            
            $xml .= '</PictureDetails>';
        }
        
        if(!empty($data['primary_category'])) {
            $xml .= '<PrimaryCategory><CategoryID>'.$data['primary_category'].'</CategoryID></PrimaryCategory>';
        }
        
        if(!empty($data['second_category'])) {
            $xml .= '<SecondaryCategory><CategoryID>'.$data['second_category'].'</CategoryID></SecondaryCategory>';
        }
        
        if(!empty($data['store_category']) || !empty($data['store_category2'])) {
            $xml .= '<Storefront>';
            if(!empty($data['store_category'])) {
                $xml .= '<StoreCategoryID>'.$data['store_category'].'</StoreCategoryID>';
            }
            
            if($data['store_category2']) {
                $xml .= '<StoreCategory2ID>'.$data['store_category2'].'</StoreCategory2ID>';
            }
            $xml .= '</Storefront>';
        }
        $xml .= '<ProductListingDetails>';
        if(!empty($data['specifics']['UPC']['name'])) {
            $xml .= '<UPC>'.$data['specifics']['UPC']['value'].'</UPC>';
        }
        if(!empty($data['specifics']['ISBN']['name'])) {
            $xml .= '<ISBN>'.$data['specifics']['ISBN']['value'].'</ISBN>';
        }
        if(!empty($data['specifics']['EAN']['name'])) {
            $xml .= '<EAN>'.$data['specifics']['EAN']['value'].'</EAN>';
        }
        $xml .= '</ProductListingDetails>';
        
        if(!empty($data['specifics'])) {
            $xml .= '<ItemSpecifics>';
            foreach($data['specifics'] as $specificsKey=>$specificsValue) {
                $xml .= '<NameValueList>';
                $xml .= '<Name>'.htmlspecialchars($specificsKey).'</Name>';
                $xml .= '<Value>'.htmlspecialchars($specificsValue['value']).'</Value>';
                $xml .= '</NameValueList>';
            }
            
            $xml .= '</ItemSpecifics>';
        }
        
        if(!empty($data['listing_duration'])) {
            $xml .= '<ListingDuration>'.$data['listing_duration'].'</ListingDuration>';
        }
        
        if(empty($data['variations'])) {
            if(!empty($data['start_price'])) {
                $xml .= '<StartPrice>'.$data['start_price'].'</StartPrice>';
            }
            
            if(!empty($data['quantity'])) {
                $xml .= '<Quantity>'.$data['quantity'].'</Quantity>';
            }
        }
        
        if(!empty($data['bestoffer_enabled'])) {
            $xml .= '<BestOfferDetails><BestOfferEnabled>'.$data['bestoffer_enabled'].'</BestOfferEnabled></BestOfferDetails>';
        }
        
        if(!empty($data['paypal_email'])) {
            $xml .= '<PaymentMethods>PayPal</PaymentMethods>';
            $xml .= '<PayPalEmailAddress>'.$data['paypal_email'].'</PayPalEmailAddress>';
        }
        $xml .= '<ReturnPolicy>';
        if(!empty($data['return_policy'])) {
            $xml .= '<ReturnsAcceptedOption>'.$data['return_policy'].'</ReturnsAcceptedOption>';
        }
        
        if(!empty($data['return_days'])) {
            $xml .= '<ReturnsWithinOption>'.$data['return_days'].'</ReturnsWithinOption>';
        }
        
        if(!empty($data['refund_type'])) {
            $xml .= '<RefundOption>'.$data['refund_type'].'</RefundOption>';
        }
        
        if(!empty($data['shipping_cost_paid_by_option'])) {
            $xml .= '<ShippingCostPaidByOption>'.$data['shipping_cost_paid_by_option'].'</ShippingCostPaidByOption>';
        }
        
        if(!empty($data['return_restocking_fee'])) {
            $xml .= '<RestockingFeeValueOption>'.$data['return_restocking_fee'].'</RestockingFeeValueOption>';
        }
        
        if(!empty($data['returns_description'])) {
            $xml .= '<Description>'.$data['returns_description'].'</Description>';
        }
        $xml .= '</ReturnPolicy>';
        
        if(!empty($data['location'])) {
            $xml .= '<Location>'.$data['location'].'</Location>';
        }
        
        if(!empty($data['country'])) {
            $xml .= '<Country>'.$data['country'].'</Country>';
        } elseif(empty($data['country']) && empty($data['postcode'])) {
           $xml .= '<Country>HK</Country>'; 
        }
        
        if(!empty($data['postcode'])) {
            $xml .= '<PostalCode>'.$data['postcode'].'</PostalCode>';
        }
        
        if(!empty($data['dispatch_time'])) {
            $xml .= '<DispatchTimeMax>'.$data['dispatch_time'].'</DispatchTimeMax>';
        }
        
        $xml .= '<ShippingDetails>';
        if(!empty($data['shipping_type'])) {
            $xml .= '<ShippingType>'.$data['shipping_type'].'</ShippingType>';
        }
        
        if(!empty($data['shipping_service'])) {
            foreach($data['shipping_service'] as $shippingKey=>$shippingValue) {
                $xml .= '<ShippingServiceOptions>';                
                if(isset($data['shipping_service_cost'][$shippingKey]['0'])) {
                    $xml .= '<ShippingServiceCost>'.$data['shipping_service_cost'][$shippingKey]['0'].'</ShippingServiceCost>';
                }
                
                $xml .= '<ShippingService>'.$shippingValue['0'].'</ShippingService>';
                if(isset($data['shipping_service_addcost'][$shippingKey]['0'])) {
                    $xml .= '<ShippingServiceAdditionalCost>'.$data['shipping_service_addcost'][$shippingKey]['0'].'</ShippingServiceAdditionalCost>';
                }
                
                $xml .= '<ShippingServicePriority>'.$shippingKey.'</ShippingServicePriority>';
                $xml .= '</ShippingServiceOptions>';
            }
        }
        
        if(!empty($data['inter_shipping_service'])) {
            foreach($data['inter_shipping_service'] as $interShippingKey=>$interShippingValue) {
                $xml .= '<InternationalShippingServiceOption>';
                $xml .= '<ShippingService>'.$interShippingValue['0'].'</ShippingService>';
                
                if(isset($data['inter_shipping_service_addcost'])) {
                    $xml .= '<ShippingServiceAdditionalCost>'.$data['inter_shipping_service_addcost'][$interShippingKey]['0'].'</ShippingServiceAdditionalCost>';
                }
                
                if(isset($data['inter_shipping_service'])) {
                    $xml .= '<ShippingServiceCost>'.$data['inter_shipping_service_cost'][$interShippingKey]['0'].'</ShippingServiceCost>';
                }
                $xml .= '<ShippingServicePriority>'.$interShippingKey.'</ShippingServicePriority>';
                
                if(isset($data['ship_to_location'][$interShippingKey])) {
                    foreach($data['ship_to_location'][$interShippingKey] as $tolocation) {
                        $xml .= '<ShipToLocation>'.$tolocation.'</ShipToLocation>';
                    }
                }
                
                if(isset($data['inter_shipping_all'][$interShippingKey])) {
                    $xml .= '<ShipToLocation>'.$data['inter_shipping_all'][$interShippingKey]['0'].'</ShipToLocation>';
                }
                
                $xml .= '</InternationalShippingServiceOption>';
            }
        }
        
        if(!empty($data['exclude_ship_to_location'])) {
            $excludeLocationArr = explode('##', $data['exclude_ship_to_location']);
            foreach($excludeLocationArr as $excludeLocation) {
                $xml .= '<ExcludeShipToLocation>'.$excludeLocation.'</ExcludeShipToLocation>';
            }
        }
        $xml .= '</ShippingDetails>';
        $xml .= '<ConditionID>1000</ConditionID>';
        $xml .= '<Description>test description</Description>';
        if(!empty($data['currency'])) {
            $xml .= '<Currency>'.$data['currency'].'</Currency>';
        }
        
        if(!empty($data['variations'])) {
            $xml .= '<Variations>';
            if(!empty($data['variations_img'])) {
                $variationsAttr = [];
                $variationsNameAttr = '';
                foreach($data['variations_img'] as $attrValue) {
                    if(!in_array($attrValue->variation_value,$variationsAttr)) {
                        $variationsNameAttr = $attrValue->variation_name;
                        $variationsAttr[] = $attrValue->variation_value;
                    }
                }
                
                $xml .= '<Pictures>';
                $xml .= '<VariationSpecificName>'.$variationsNameAttr.'</VariationSpecificName>';
                
                
                foreach($variationsAttr as $variationValue) {
                    $xml .= '<VariationSpecificPictureSet>';
                    $variationImg = UebModel::model('Ebayonlinelistingvariationimg')
                        ->findAll('item_id='.$data['item_id'].' and variation_value= "'.$variationValue.'" ');
                    $xml .= '<VariationSpecificValue>'.$variationValue.'</VariationSpecificValue>';
                    foreach($variationImg as $variationKey=>$variationImgValue) {
                        $xml .= '<PictureURL>'.$variationImgValue->img_url.'</PictureURL>';
                    }
                    $xml .= '</VariationSpecificPictureSet>';
                    
                }
                $xml .= '</Pictures>';
            }
            
            $sku = $data['variations']['sku'];
            $price = $data['variations']['price'];
            $qty = $data['variations']['qty'];
            $attr = $data['variations']['attr'];
            
            $variationSet = '';
            foreach($attr as $attrSetValue) { 
                $variationSet .= '<NameValueList>';
                $variationSet .= '<Name>'.$attrSetValue.'</Name>';
                foreach($data['variations'][$attrSetValue] as $sets) {
                    $variationSet .= '<Value>'.$sets.'</Value>';
                }
                $variationSet .= '</NameValueList>';
            }    
            
            if(!empty($sku) && !empty($price) && !empty($qty)) {
                
                foreach($sku as $skuKey=>$skuValue) {
                    $xml .= '<Variation>';
                    $xml .= '<Quantity>'.$qty[$skuKey].'</Quantity>';
                    $xml .= '<SKU>'.$skuValue.'s</SKU>';
                    $xml .= '<StartPrice>'.$price[$skuKey].'</StartPrice>';
                    
                    $xml .= '<VariationProductListingDetails>';
                    if(isset($data['variations']['upc'][$skuKey])) {
                        $xml .= '<UPC>'.$data['variations']['upc'][$skuKey].'</UPC>';
                    }
                    
                    if(isset($data['variations']['ean'][$skuKey])) {
                        $xml .= '<EAN>'.$data['variations']['ean'][$skuKey].'</EAN>';
                    }
                    
                    if(isset($data['variations']['isbn'][$skuKey])) {
                        $xml .= '<ISBN>'.$data['variations']['isbn'][$skuKey].'</ISBN>';
                    }
                    
                    $xml .= '</VariationProductListingDetails>';
                    
                    if(!empty($attr)) {
                        
                        $xml .= '<VariationSpecifics>';
                        foreach($attr as $attrsKey=>$attrValue) {
                            $xml .= '<NameValueList>';
                            $xml .= '<Name>'.$attrValue.'</Name>';
                            $xml .= '<Value>'.$data['variations'][$attrValue][$skuKey].'</Value>';
                            $xml .= '</NameValueList>';
                        }
                        $xml .= '</VariationSpecifics>';
                    }
                    
                    $xml .= '</Variation>';
                }
                
            }
            $xml .= '<VariationSpecificsSet>';
            $xml .= $variationSet;
            $xml .= '</VariationSpecificsSet>';
            
            $xml .= '</Variations>';
        }
        
       return $xml; 
    }
    

    /*
     * 根据paypal_email_address获取itemid
     * */
    public function getItemIdByPaypalEmailAddress($paypal_email_address = null){
        $where = "paypal_email_address = '{$paypal_email_address}'";
        $data =  $this->getDbConnection()->createCommand()
            ->select('itemid')
            ->from(self::model()->tableName())
            ->where($where)
            ->queryAll();

        //$result = $data[0]['order_id'];
        return $data;
    }
    
    public function modifyDesction($token) {
        $itemId = $this->itemid;
        $path = implode('/',str_split($itemId, 3));
        $desction = file_get_contents(dirname(Yii::app()->BasePath).'/upload/ebay_descrtion/'.$path.'/'.$itemId.'.txt');
        
        $sendApi = new TradingAPI();
        $sendApi->setUserToken($token);
        $sendApi->setSiteId($this->siteid);
        
        if($this->listing_type == 'FixedPriceItem') {
            $sendApi->xmlTagArray = [
                'ReviseFixedPriceItemRequest'=>[
                    'Item'=>[
                        'ItemID'=>$itemId,
                        'Description'=>'<![CDATA['.$desction.']]>',
                        'DescriptionReviseMode'=>'Replace'
                    ]
                ],
            ];
            
        } else {
            $sendApi->xmlTagArray = [
                'ReviseItemRequest'=>[
                    'Item'=>[
                        'ItemID'=>$itemId,
                        'Description'=>'<![CDATA['.$desction.']]>',
                        'DescriptionReviseMode'=>'Replace'
                    ],
                ],
            ];
            
        }
        
        $sendResponse = $sendApi->send()->response;
         
        if(!empty($sendResponse)) {
            $ackFlag =  $sendResponse->Ack;
            if($ackFlag != 'Failure') {
                $this->status = 0;
                $this->update_time = date('Y-m-d H:i:s');
                $this->save();
            } else {
                $this->status = 6;
                $this->remark = $sendResponse->Errors;
                $this->save();
            }
        
        } else {
            $this->status = 6;
            $this->remark = isset($sendResponse->Errors)?$sendResponse->Errors:'API错误，修改失败';
            $this->save();
        }
        
        
    }
    
    /**
     * @desc 根据Item ID 获取listing数据
     * @param unknown $itemId
     * @return CActiveRecord
     */
    public function getListingByItemId($itemId)
    {
        return $this->find('itemid = :item_id', [':item_id' => $itemId]);
    }
    
    //查询最合适的物流方式
    public function caleLogisticFee($sellprice,$sku,$country,$warehouseId) {
        $calc_sku = 'calc_sku_'.$sku;
        $productModel = UebModel::model('Logisticsruleconfig')->memcacheSetCache($calc_sku,'get'); //获取memcache缓存

        if(empty($productModel)) {
            $productModel = UebModel::model('Product')->find('sku="'.$sku.'"');
            UebModel::model('Logisticsruleconfig')->memcacheSetCache($calc_sku, 'set', $productModel);
        }
        
        if(empty($productModel)) {
            return array('status'=>'error', 'msg'=>'产品信息为空');
        }
        $calc_sku_weight = 'calc_sku_weight_'.$sku;
        $weightInfo = UebModel::model('Logisticsruleconfig')->memcacheSetCache($calc_sku_weight,'get');
        if(empty($weightInfo)) {
            $weightInfo = UebModel::model('Product')->getWeightPriceBySku($sku);
            UebModel::model('Logisticsruleconfig')->memcacheSetCache($calc_sku_weight, 'set', $weightInfo);
        }
        
        if(empty($weightInfo)) {
            return array('status'=>'error', 'msg'=>'产品信息为空');
        }
        
        $productId = $productModel->id;
        $calc_sku_product = 'calc_sku_'.$productId;
        $productAttr = UebModel::model('Logisticsruleconfig')->memcacheSetCache($calc_sku_product,'get');
        if(empty($productAttr)) {
            $productAttr = UebModel::model('ProductSelectAttribute')->getAttrList($productId);
            UebModel::model('Logisticsruleconfig')->memcacheSetCache($calc_sku_product,'set', $productAttr);
        }
        
        $weight 		= $weightInfo['0']['gross_product_weight'];
        $c_leng 		= $weightInfo['0']['pack_product_length'];
        $c_width		= $weightInfo['0']['pack_product_width'];
        $c_height 		= $weightInfo['0']['pack_product_height'];
        $product_cost 	= $weightInfo['0']['product_cost']; // 成本
        
        $tariff 		= ''; // 税率
        if(isset($weightInfo['0']['tariff'])) {
            $tariff = $weightInfo['0']['tariff']; // 税率
        }
        $zipcode 		= '';
        $logistics_code = ''; //邮编
        $attributeIds 	= $productAttr['3'];
        
        $calc_sku_warehouse = 'calc_sku_warehouse_'.$warehouseId;
        $logisticsArrList = UebModel::model('Logisticsruleconfig')->memcacheSetCache($calc_sku_warehouse,'get');
        if(empty($logisticsArrList)) {
            $logisticsArrList 	= UebModel::model('Logistics')->getLogisticsArrByPlatform('EB', $warehouseId); //获取可用的公司物流方式列表
            UebModel::model('Logisticsruleconfig')->memcacheSetCache($calc_sku_warehouse,'set', $logisticsArrList);
        }
        
        $logisticsArr = array();
        $ship_type_where = ''; //如果筛选
       
//         if($logisticsArrList)
//         {
//             foreach($logisticsArrList as $logisticsId_1 => $logistics_1)
//             {
//                 $_intersectionattr=0;
//                 if(!empty($attributeIds['0']) && count($attributeIds) > 0)
//                 {
//                     $calc_sku_logistics = 'calc_sku_logistics_'.$logisticsId_1;
//                     $Logisticsattr = UebModel::model('Logisticsruleconfig')->memcacheSetCache($calc_sku_logistics,'get');
//                     if(empty($Logisticsattr)) {
//                         $Logisticsattr = UebModel::model('LogisticsAttribute')->find('logistics_id=:logistics_id', array(':logistics_id' => $logisticsId_1))->include_attribute_id;
//                          UebModel::model('Logisticsruleconfig')->memcacheSetCache($calc_sku_logistics,'set', $Logisticsattr);
//                     }
//                     $Logisticsattr=explode(',',$Logisticsattr);
//                     foreach ($attributeIds as $key_attr => $value_attr)
//                     {
//                         if(in_array($value_attr,$Logisticsattr))
//                         {
//                             $_intersectionattr=1;break;
//                         }else {
//                             $_intersectionattr=2;break;
//                         }
//                     }
//                 }
    
//                 if ( $_intersectionattr == 2 ){continue;}
    
//                 //有筛选类型
//                 if(empty($ship_type_where) || ($logistics_1['ship_type']==$ship_type_where && $ship_type_where)) //筛选类型
//                 {
//                     $total_c_restrict = $c_leng + $c_width + $c_height;
//                     if($total_c_restrict > 1)
//                     {
//                         $total_restrict   = $logistics_1['restrict_length'] + $logistics_1['restrict_width'] + $logistics_1['restrict_height'];
//                         if( $total_restrict  >= $total_c_restrict) //帅选尺寸
//                         {
//                             //边长超过
//                             if( $logistics_1['restrict_length'] > 0 && $c_leng > 0 &&   $c_leng > $logistics_1['restrict_length']){
//                                 continue;
//                             }
//                             if(empty($_intersectionattr) || $_intersectionattr===1)
//                             {
//                                 $ship_warehouse = $logistics_1['ship_warehouse'];
//                                 if($ship_warehouse)
//                                 {
//                                     $ship_warehouse_arr = explode(',', $ship_warehouse);
//                                     if(in_array($warehouseId, $ship_warehouse_arr))
//                                     {
//                                         $logisticsArr[$logisticsId_1] = $logistics_1;
//                                     }
//                                 }
//                             }
//                         }
//                     }else {
//                         if(empty($_intersectionattr) || $_intersectionattr===1)
//                         {
//                             $ship_warehouse = $logistics_1['ship_warehouse'];
//                             if($ship_warehouse)
//                             {
//                                 $ship_warehouse_arr = explode(',', $ship_warehouse);
//                                 if(in_array($warehouseId, $ship_warehouse_arr))
//                                 {
//                                     $logisticsArr[$logisticsId_1] = $logistics_1;
//                                 }
//                             }
//                         }
//                     }
//                 }
//             }
//         }
        
        $calc_warehouse_list = 'calc_warehouse_list';
        $warehouses =  UebModel::model('Logisticsruleconfig')->memcacheSetCache($calc_warehouse_list,'get');
        if(empty($warehouses)) {
            $warehouses 	= UebModel::model('Warehouse')->getParaList();   // 可用仓库列表
            UebModel::model('Logisticsruleconfig')->memcacheSetCache($calc_warehouse_list,'set', $warehouses);
        }
        
        $calResult 		= array();
        if($c_leng !='' && $c_width !='' && $c_height !=''){
            $volume = $c_leng*$c_width*$c_height;
        }
        $logisticsArr = $logisticsArrList;
        if($logisticsArr) {
            foreach($logisticsArr as $logisticsId=>$logistics){
                $tempWeight = '';
                $shipFeeArr = UebModel::model('Logistics')->getShipFeeById($logisticsId,$zipcode, $weight,$tempWeight, array('ship_country' => $country,'volume'=>$volume), 1);
                
                $shipFee = $shipFeeArr['shipCost'];
                $shipFee_not_discount=$shipFeeArr['shipCost_not_discount'];
                $ship_fee = $shipFeeArr['shipFee']; // 物流商处理费
                $ship_fee1 = $shipFeeArr['shipFee1']; // 销售处理费
                $shipFee_not_discount=$shipFeeArr['shipFee_not_discount']; // 没有折扣的处理费
                $seller_ship_discount_shipCost=$shipFeeArr['seller_ship_discount_shipCost']; //销售折扣后费用
    
                if($shipFee > 0){
                    $calc_sku_ry_1 = 'calc_sku_ry_'.$logisticsId;
                    $ShipFuelCost =  UebModel::model('Logisticsruleconfig')->memcacheSetCache($calc_sku_ry_1, 'get');
                    if(empty($ShipFuelCost)) {
                        $ShipFuelCost = UebModel::model('Logistics')->getShipFuelCost($logisticsId); // 燃油附加费
                        UebModel::model('Logisticsruleconfig')->memcacheSetCache($calc_sku_ry_1, 'set', $ShipFuelCost);
                    }
                    
                    $calc_sku_logistics_id = 'calc_sku_logistics_id_'.$logisticsId;
                    $logisticsInfo = UebModel::model('Logisticsruleconfig')->memcacheSetCache($calc_sku_logistics_id, 'get');
                    if(empty($logisticsInfo)) {
                        $logisticsInfo = UebModel::model('Logistics')->getLogisticsInfoById($logisticsId); //  物流方式信息
                        UebModel::model('Logisticsruleconfig')->memcacheSetCache($calc_sku_logistics_id, 'set', $logisticsInfo);
                    }
    
                    if(!empty($ShipFuelCost)){
                        $shipFee = $shipFee*(1+$ShipFuelCost);
                        $shipFee_not_discount=$shipFee_not_discount*(1+$ShipFuelCost);
                        $seller_ship_discount_shipCost=$seller_ship_discount_shipCost*(1+$ShipFuelCost);
                    }
    
                    $shipFee = $shipFee + $ship_fee; //物流商折扣后运费
                    $shipFee_not_discount=$shipFee_not_discount + $shipFee_not_discount;//没有折扣后运费
                    $seller_ship_discount_shipCost=$seller_ship_discount_shipCost + $ship_fee1; //销售折扣后运费
                    $warehouseStr = '';
                    $supportWarehouseArr = explode(',',$logisticsInfo['ship_warehouse']);
                    if( isset($warehouseId) ){
    
                        if( $warehouseId > 0 ){
                            if( !in_array($warehouseId,$supportWarehouseArr) ){
                                continue;
                            }
                        }
                    }
    
                    foreach( $supportWarehouseArr as $wh ){
                        $warehouseStr .= isset($warehouses[$wh]) ? $warehouses[$wh].'&nbsp;&nbsp;' : '';
                    }
                    //var_dump($logistics['ship_code']);exit();
                    $extra_price_sku = $logistics_code.'_'.$logistics['ship_code'].'_'.$warehouseId;
                    $extra_price_info = UebModel::model('Logisticsruleconfig')->memcacheSetCache($extra_price_sku, 'get');
                    if(empty($extra_price_info)) {
                        $extra_price_info = UebModel::model('CalculateShipCost')->_getextracharge($logistics_code,$logistics['ship_code'],$warehouseId);
                        UebModel::model('Logisticsruleconfig')->memcacheSetCache($extra_price_sku, 'set', $extra_price_info);
                    }
                    
                    $calResult[$logisticsId] = array(
                        'logistics_name'	=> $logisticsInfo['ship_name'],
                        'shipping_cost'		=> $shipFee,
                        'shipFee_not_discount'=>$shipFee_not_discount, //添加无折扣
                        'ship_type'=>UebModel::model('LogisticsType')->find('id = :id',array(':id'=>$logisticsInfo['ship_type']))['type_name'],
                        'warehouse'			=> $warehouseStr,
                        //添加显示查询网址
                        'site'=>$logisticsInfo['site'],
                        'time_out_data'=>$logisticsInfo['time_out_data'],
                        'seller_ship_discount_shipCost'=>$seller_ship_discount_shipCost,
                        'platform_code'=>$logisticsInfo['platform_code'],
                        'extra_price'=>$extra_price_info
                    );
                    $priceArr[$logisticsId] = $shipFee;
                }
            }
        }
       
        // 头程运输列表
        $shippingResult = array();
        $countryCode='';
        if(strlen($country) > 4){
            $calc_sku_c = 'calc_sku_c_'.$country;
            $countryinfo = UebModel::model('Logisticsruleconfig')->memcacheSetCache($calc_sku_c,'get');
            if(empty($countryinfo)) {
                $countryinfo = UebModel::model('CountryNew')->find('en_name=:en_name',array(':en_name' => $country));
                UebModel::model('Logisticsruleconfig')->memcacheSetCache($calc_sku_c, 'set', $countryinfo);
            }
            if( $countryinfo )
                $countryCode= $countryinfo->en_abbr;
        }
        
        $shippingParam = array('country'=>$country, 'destination_stock'=>$warehouseId,'tariff'=>$tariff,'product_cost'=>$product_cost,'weight'=>$weight,'c_leng'=>$c_leng,'c_width'=>$c_width,'c_height'=>$c_height,'volume'=>$volume,'countryCode'=>$countryCode);
        
        $shippingResult = UebModel::model('firstshippingmethod')->getShippingByStockCountry($shippingParam);
       
        asort($priceArr,SORT_NUMERIC);
        foreach($priceArr as $id=>$val){
            $data = array();
            if($shippingResult)
            {
                foreach($shippingResult as $key => $valShipping)
                {
                    if($valShipping['shippingId'] == $id)
                    {
                        $data = $valShipping;
                        unset($shippingResult[$key]);
                        break;
                    }
                }
            }
            $calResult[$id]['firstShipping'] = $data;
            $newCalResult[]	= $calResult[$id];
        }
        
        if(empty($newCalResult)) {
            return array('status'=>'error', 'msg'=>'没有符合条件的物流方式');
        }
        
        $shipCost = 0;
        $param = array();
        foreach($newCalResult as $k=>$v) {
            if($k == '0') {
                $shipCost = $v['seller_ship_discount_shipCost'];
                $param['logistics'] = $v['logistics_name'];
                $param['shipcost'] = $v['seller_ship_discount_shipCost'];
            } else {
                if($v['seller_ship_discount_shipCost'] < $shipCost) {
                    $shipCost = $v['seller_ship_discount_shipCost'];
                    $param['logistics'] = $v['logistics_name'];
                    $param['shipcost'] = $v['seller_ship_discount_shipCost'];
                }
            }
            
            if($sellprice > 5 && $countryCode == 'US' && $warehouseId==1 && $v['logistics_name'] == '美国E邮宝' && $weight < 2000) {
                $param['logistics'] = $v['logistics_name'];
                $param['shipcost'] = $v['seller_ship_discount_shipCost'];
                break;
            }
        }
        
        
//         if($warehouseId != '1') {
//             $result = UebModel::model('LogisticsPrice')->getshippingResult($country,array($warehouseId),$tariff,$product_cost,$weight,$c_leng,$c_width,$c_height);
//             echo "<pre>";var_dump($result);exit();
//         }
        
        return $param;
    }
    
    public function calcProfitRate($itemId) {
        //(总价-运费-成本-ebay费用-paypal费用)/成本
        $model = UebModel::model('Ebayonlinelisting');
        $info = $this->find('itemid="'.$itemId.'"');
        $countryArr = $this->getSiteCountry();
        $shippingCost = UebModel::model('Ebayonlinelistingshipping')
                    ->find('item_id='.$itemId.' and shipping_status=1 and shipping_service_priority=1')->shipping_service_cost; //ebay运费
        
        //$shippingCost = 0;
        $paypalAccount = $info->paypal_email_address;
        $location = $info->location;
        $paypalModel = UebModel::model('PaypalAccount')->find('email="'.$paypalAccount.'"');
        
        $ebayrate = 0.09; //ebay成交费
        if($paypalModel->amount_end > 12) {
            $paypalrate = 0.027; //大额2.7%+0.3，小额6%+0.05;
            $fixedrate = 0.3;
        } else {
            $paypalrate = 0.06; //大额2.7%+0.3，小额6%+0.05;
            $fixedrate = 0.05;
        }
        
        $fixedFee = CurrencyConvertor::currencyConvert($fixedrate, $info->currency, CurrencyConvertor::CURRENCY_CNY); //固额费用
        
        $variation_multi = $info->variation_multi;
        if($variation_multi == '0') {
            $sku = $info->sku;
            
            $warehouseId = UebModel::model('EbayLocationMapWarehouse')->find('location="'.$location.'"')->warehouse_id;
            $country = $countryArr[$info->siteid];
            
            $totalSellprice = $info->start_price + $shippingCost;
            $sellprice = CurrencyConvertor::currencyConvert($totalSellprice, $info->currency, CurrencyConvertor::CURRENCY_CNY);
            
            $mem_key = $sku.'_'.$country.'_'.$totalSellprice.'_'.$warehouseId;
            $logistics = UebModel::model('Logisticsruleconfig')->memcacheSetCache($mem_key, 'get');
            if(empty($logistics)) {
                $logistics = $this->caleLogisticFee($totalSellprice, $sku, $country, $warehouseId);
                UebModel::model('Logisticsruleconfig')->memcacheSetCache($mem_key, 'set', $logistics);
            }
            
            $prifix = 'calc_sku1_'.$sku;
            $skuData = UebModel::model('Logisticsruleconfig')->memcacheSetCache($prifix, 'get');
            if(empty($skuData)) {
                $skuData = (new EbayListingCalculate($sku))->getProductInfo();
                UebModel::model('Logisticsruleconfig')->memcacheSetCache($prifix, 'set', $skuData);
            }
            
            $shipPrice = $logistics['shipcost'];
            $costPrice = $skuData['product_cost'];
            
            $ebayFee = CurrencyConvertor::currencyConvert($totalSellprice*$ebayrate,$info->currency,CurrencyConvertor::CURRENCY_CNY);
            $paypalFee = CurrencyConvertor::currencyConvert($totalSellprice*$paypalrate, $info->currency, CurrencyConvertor::CURRENCY_CNY);
            
            $profit = ($sellprice-$shipPrice-$costPrice-$ebayFee-$paypalFee-$fixedFee);
            if($logistics['status'] == 'error') {
                $info->profit_rate = $sku.",".$logistics['msg'];
                $info->save();
                return false;
            }

            $profitRate = round($profit/$sellprice,4)*100;
            $info->profit_rate = $profitRate.' %';
            $info->save();
        } else { //多属性
            $variationInfo = UebModel::model('Ebayonlinelistingvariation')->findAll('item_id='.$itemId);
            $result = '';
            foreach($variationInfo as $v) {
                $sku = $v->sku;    
                //$productId = UebModel::model('Product')->find('sku="'.$sku.'"')->id;
                //$warehouseAttrId = UebModel::model('ProductSelectAttribute')->find('product_id='.$productId.' and attribute_id=27')->attribute_value_id;
                //$warehouseId = $this->warehouseAttribute()[$warehouseAttrId];
                $warehouseId = UebModel::model('EbayLocationMapWarehouse')->find('location="'.$location.'"')->warehouse_id;
                
                $country = $countryArr[$info->siteid];
                $totalSellprice = $v->start_price + $shippingCost;
                $sellprice = CurrencyConvertor::currencyConvert($totalSellprice, $info->currency, CurrencyConvertor::CURRENCY_CNY);
                
                $mem_key = $sku.'_'.$country.'_'.$totalSellprice.'_'.$warehouseId;
                $logistics = UebModel::model('Logisticsruleconfig')->memcacheSetCache($mem_key, 'get');
                if(empty($logistics)) {
                    $logistics = $this->caleLogisticFee($totalSellprice, $sku, $country, $warehouseId);
                    UebModel::model('Logisticsruleconfig')->memcacheSetCache($mem_key, 'set', $logistics);
                }
//                $logistics = $this->caleLogisticFee($sellprice, $sku, $country, $warehouseId);
                //echo "<pre>";var_dump($logistics);
                if($logistics['status'] == 'error') {
                    $result .= $sku.",".$logistics['msg']."<br/>";
                    continue;
                }
                
                $prifix = 'calc_sku1_'.$sku;
                $skuData = UebModel::model('Logisticsruleconfig')->memcacheSetCache($prifix, 'get');
                if(empty($skuData)) {
                    $skuData = (new EbayListingCalculate($sku))->getProductInfo();
                    UebModel::model('Logisticsruleconfig')->memcacheSetCache($prifix, 'set', $skuData);
                }
//                $skuData = (new EbayListingCalculate($sku))->getProductInfo();
                $shipPrice = $logistics['shipcost'];
                $costPrice = $skuData['product_cost'];
                
                $ebayFee = CurrencyConvertor::currencyConvert($totalSellprice*$ebayrate,$info->currency,CurrencyConvertor::CURRENCY_CNY);
                $paypalFee = CurrencyConvertor::currencyConvert($totalSellprice*$paypalrate, $info->currency, CurrencyConvertor::CURRENCY_CNY);
                
                $profit = ($sellprice-$shipPrice-$costPrice-$ebayFee-$paypalFee-$fixedFee);
                $profitRate = round($profit/$sellprice,4)*100;
                $result .= $sku.' ：'.$profitRate."%<br/>";
            }
            //echo "<pre>";var_dump(rtrim($result, '/ '));exit();
            $info->profit_rate = rtrim($result, '<br/>');
            $info->save();
        }
        
    }
    
    public function getSiteCountry() {
        return [
            '0'=>'United States of America',
            '3'=>'United Kingdom',
            '2'=>'Canada',
            '15'=>'Australia',
            '100'=>'United States of America',
            '71'=>'France',
            '77'=>'Germany',
            '186'=>'Spain',
            '101'=>'Italy',
            '201'=>'Hongkong',
        ];
    }
    
    public function warehouseAttribute() {
        return  $warehouseArr = [
                    '33'=>'36', // 万邑通澳大利亚仓
                    '34'=>'38', //万邑通英国仓
                    '35'=>'35', // 万邑通德国仓
                    '37'=>'59', //万邑通美国东岸仓
                    '38'=>'1', //深圳仓
                    '39'=>'40', //出口易英国仓
                    '40'=>'41', //出口易德国仓
                    '41'=>'42', //出口易澳洲仓
                    '42'=>'55', //出口易美国仓
                    '10442'=>'53', //新易贸德仓
                    '10443'=>'50', //谷仓美东仓
                    '10444'=>'49', //谷仓美西仓
                    '10445'=>'48', //易佰美国仓
                    '10446'=>'47', //万邑通美国仓
                    '13784'=>'62', //递四方西班牙仓
                    '15153'=>'63', //旺集俄罗斯仓库
                ];
    }
    
    public function currencyExchange() {
        $info = UebModel::model('CurrencyRate')->findAll();
        $data = [];
        foreach($info as $v) {
            if($v->rate > 0) {
                $data[$v->from_currency_code][$v->to_currency_code] = $v->rate;
            }
        }
        return $data;
    }
    
    //查找产品线
    public function getproductLine($id = '') {
        
        $param = [];
        $info = UebModel::model('Productlinelist')->findAll();
        foreach($info as $v) {
            $v->id = intval($v->id);
            $param[$v->id]['en'] = $v->linelist_en_name;
            $param[$v->id]['cn'] = $v->linelist_cn_name;
        }
        
        if(!empty($id)) {
            return $param[$id];
        }
        
        return $param;
    }

    //由于end_time字段更新不及时，计算出合适的end_time值，身不由己
    public function getCalculateEndTime()
    {
        if($this->listing_duration == 'GTC')
        {
            return self::calculateGTCEndTime($this->end_time);
        }
        else
        {
            return $this->end_time;
        }
    }
    public static function calculateGTCEndTime($endTime,$type = 'timestamp')
    {
        $endTimestamp = $type == 'date' ? strtotime($endTime) : $endTime;
        $currentTimestamp = time();
        if($currentTimestamp > $endTimestamp)
        {
            $deepCalculate =  self::calculateGTCEndTime($endTimestamp + 30*24*3600,$type = 'timestamp');
            return $type == 'date' ? date('Y-m-d H:i:s',$deepCalculate):$deepCalculate;
        }
        else
        {
            return $endTime;
        }
    }
    
    
}
