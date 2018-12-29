<?php

class Ebaygetitems {
    
    protected $account;
    
    protected $itemid;
    
    protected $sku;
    
    public function setAccount($account) {
        $this->account = $account;
    }
    
    public function setItemid($itemid) {
        $this->itemid = $itemid;
    }
    
    public function setsku($sku) {
        $this->sku = $sku;
    }
    
    public function getebayitem() {
        set_time_limit(300);
        $apiObj = new Ebaygetitemapi();
        
        if(!empty($this->sku)) {
            $apiObj->setsku($this->sku);
        }
        
        if(!empty($this->itemid)) {
            $apiObj->setItemid($this->itemid);
        }
        
        $response = $apiObj->setShortName($this->account)
                    ->setVerb('GetItem')
                    ->setRequest()
                    ->sendHttpRequest()
                    ->getResponse();

        $data = $this->handleresponse($response);
        return $data;
    }
    
    public function getnewebayitem($ebayAccount) {
        set_time_limit(600);
        require_once $_SERVER['DOCUMENT_ROOT'].'/protected/vendors/ebay/EbaySession.php';
        
        $token = UebModel::model('EbayAccount')->find('user_name=:name', array(':name'=>$ebayAccount))->user_token;
        
        $xml = '<?xml version="1.0" encoding="utf-8"?>';
        $xml .= '<GetItemRequest xmlns="urn:ebay:apis:eBLBaseComponents">';
        $xml .= '<RequesterCredentials>';
        $xml .= '<eBayAuthToken>'.$token.'</eBayAuthToken>';
	$xml .= '<OutputSelector>ApplicationData,ApplyBuyerProtection,AutoPay,AvailableForPickupDropOff</OutputSelector>';
        $xml .= '<OutputSelector>BestOfferDetails,BusinessSellerDetails,BuyerGuaranteePrice,BuyerProtection</OutputSelector>';
        $xml .= '<OutputSelector>BuyerRequirementDetails,BuyerResponsibleForShipping,BuyItNowPrice,ConditionDefinition</OutputSelector>';
        $xml .= '<OutputSelector>ConditionDisplayName,ConditionID,Country,Currency,DigitalGoodInfo</OutputSelector>';
        $xml .= '<OutputSelector>DisableBuyerRequirements,DiscountPriceInfo,DispatchTimeMax,Currency,DigitalGoodInfo,eBayNowAvailable</OutputSelector>';
        $xml .= '<OutputSelector>eBayPlus,eBayPlusEligible,HitCount,InventoryTrackingMethod,ItemID,ItemSpecifics,ListingDetails</OutputSelector>';
        $xml .= '<OutputSelector>ListingDuration,Location,PayPalEmailAddress,PictureDetails,PostalCode,PrimaryCategory,ProductListingDetails</OutputSelector>';
        $xml .= '<OutputSelector>Quantity,ReturnPolicy,SecondaryCategory,Seller,SellingStatus,ShippingDetails,Site,SKU,StartPrice,Title</OutputSelector>';
        $xml .= '<OutputSelector>Variations,WatchCount</OutputSelector>';

        $xml .= '</RequesterCredentials>';
        
        if(!empty($this->itemid)) {
            $xml .= '<ItemID>'.$this->itemid.'</ItemID>';
        }
        
        if(!empty($this->sku)) {
            $xml .= '<SKU>'.$this->sku.'</SKU>';
        }
        $xml .= '<IncludeItemSpecifics>true</IncludeItemSpecifics>';
        $xml .= '<IncludeWatchCount>true</IncludeWatchCount>';
        $xml .= '<DetailLevel>ReturnAll</DetailLevel>';
        $xml .= '<ErrorLanguage>en_US</ErrorLanguage>';
        $xml .= '</GetItemRequest>';
        
        $ebayKeys = ConfigFactory::getConfig('ebayKeys');
        $devID = $ebayKeys['devID'];
        $appID = $ebayKeys['appID'];
        $certID = $ebayKeys['certID'];
        $compatabilityLevel = $ebayKeys['compatabilityLevel'];
        $siteID = 0;
        $serverUrl = $ebayKeys['serverUrl'];
        $verb = 'GetItem';
         
        $session = new eBaySession($token, $devID, $appID, $certID, $serverUrl, $compatabilityLevel, $siteID, $verb);
        $response = $session->sendHttpRequest($xml);
        
        $data = $this->pushxml($response);
        return $data;
    }
      

    public function pushxml($param) {
        set_time_limit(600);
        $url = 'http://120.24.249.36/services/ebay/ebayonlinelistingtask/acceptxml'; 
        $data = array('xml'=>json_encode($param));
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        
        $res = curl_exec($ch);
        
        // 失败重试一次
        if (curl_getinfo($ch, CURLINFO_HTTP_CODE) !== 200) {
           $res = curl_exec($ch);
        }
       
        curl_close($ch);
        return $res;
    }
    
    
}
