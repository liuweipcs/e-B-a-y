<?php


class Ebayverifypublishfees extends EbayApiAbstract 
{
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
    
    public function setverbs($verb) {
        $this->verb = $verb;
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
         $this->siteID = $this->EbayListingModel->siteid;
         $this->compatabilityLevel = 983;
        return $this;
    }
    
    
    
    public function verifyfee() {
        return $this->setRequest()
            ->setVerb($this->verb)
            ->setSiteId($this->siteid)
            ->sendHttpRequest()
            ->handleResponse()
            ;
    }
    
    
    protected function handleResponse($longMessage = ''){ 
        $return =  array('Ack'=>'Failure');
        switch($this->response->Ack)
        {
            case 'Success':
                $return['Ack'] = 'Success';
                break;
            case 'Warning':
                $return['Ack'] = 'Warning';
                break;
            case 'Failure':
                $return['Ack'] = 'Failure';
                break;
        }
//        echo "<pre>";var_dump($this->response->Fees);exit();
        if(isset($this->response->Fees->Fee))
        {
            $return['listing_fee'] = '';
            $return['total_listing_fee'] = 0;
            foreach($this->response->Fees->Fee as $feeV) {
                if($feeV->Fee[0] > 0) {
                    if($feeV->Name == 'ListingFee') {
                        $return['total_listing_fee'] = ($feeV->Fee->__toString() - 0);
                        continue;
                    }
                    $return['listing_fee'] .= $feeV->Name.' -- '.$feeV->Fee->__toString().' '.$feeV->Fee->attributes()->currencyID->__toString().'<br/>';
                }
            }
            $return['listing_fee'] .= '-----------------------------------------------------<br/>';
            $return['listing_fee'] .= '<span style="display:inline-block;text-align:right;margin-right:10px;width:180px;">'.$return['total_listing_fee'].'</span>';
        }
        if(isset($this->response->Errors))
        {
            $errorCount = $this->response->Errors->count();
            $return['error_msg'] = '';
            for($i=0;$i<$errorCount;$i++)
            {
                if(isset($this->response->Errors->LongMessage))
                    $return['error_msg'] .= '['.$this->response->Errors[$i]->LongMessage->__toString().']';
            }
        }
        
        return $return;
    }
    
    
    
    
   
    
}
