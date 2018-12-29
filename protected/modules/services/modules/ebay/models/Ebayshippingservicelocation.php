<?php

class Ebayshippingservicelocation {
    
    public function getshippinglocation() {
        //取一个可用账号
        $accountInfo = EbayAccount::getOneEnableAccount();
        if( !$accountInfo ){
            throw new CException('No Account!');
        }
        $countrys = $this->getconttry();
        $siteid = 0;
        $shippingObj = new Ebayshippingservicecountry();
       
        //获取分类
        $response = $shippingObj->setShortName($accountInfo['short_name'])
            ->setSiteId($siteid)
            ->setVerb('GeteBayDetails')
            ->setRequest()
            ->sendHttpRequest()
            ->getResponse();
		echo "<pre>";var_dump($response);exit();
        $response = json_decode(json_encode($response),true);
        
        $ack = isset($response['Ack'])?$response['Ack']:'';
        if($ack == 'Success') {
            $shippingDetail = $response['ShippingLocationDetails'];
            if(!empty($shippingDetail)) {
                foreach($shippingDetail as $val) {
                    $country_name_short = $val['ShippingLocation'];
                    $country_name = $val['Description'];
                    if($country_name_short == 'None') {
                        continue;
                    }
                    $checkInfo = Yii::app()->db->createCommand()
                                    ->select('id')
                                    ->from('ueb_product.ueb_ebay_shipping_server_country')
                                    ->where('site=:site and country_name_short=:country_short')
                                    ->queryAll(true, array(':site'=>$siteid, ':country_short'=>$country_name_short));
                    
                    $ids = array_column($checkInfo, 'id');
                    if(!empty($ids)) {
                        $param = array();
                        $param['site'] = $siteid;
                        $param['country_name'] = $country_name;
                        $param['country_name_short'] = $country_name_short;
                        $param['country_name_cn'] = isset($countrys[$country_name_short])?$countrys[$country_name_short]:"0";
                        $param['sort'] = 1;
                        if($country_name_short == 'Worldwide') {
                            $param['sort'] = 0;
                        }
                        $flag = Yii::app()->db->createCommand()->update('ueb_product.ueb_ebay_shipping_server_country', $param, 'id='.$ids['0']);
                        
                    } else {
                        $param = array();
                        $param['site'] = $siteid;
                        $param['country_name'] = $country_name;
                        $param['country_name_short'] = $country_name_short;
                        $param['country_name_cn'] = isset($countrys[$country_name_short])?$countrys[$country_name_short]:"0";
                        $param['sort'] = 1;
                        if($country_name_short == 'Worldwide') {
                            $param['sort'] = 0;
                        }
                        $flag = Yii::app()->db->createCommand()->insert('ueb_product.ueb_ebay_shipping_server_country', $param);
                            
                    }
                }
            }
            
        }
        
    }
    
    public function getconttry() {
        $countryInfo = VHelper::selectAsArray('Country','en_name,en_abbr,cn_name');
        $countrys = array();
        foreach($countryInfo as $countryval) {
            if(!$countryval['en_abbr']) {
                continue;
            }
            $countrys[$countryval['en_abbr']] = $countryval['cn_name'];
        }
        
        $countrys['Europe'] = '欧洲';
        $countrys['GB'] = '联合王国';
        $countrys['Americas'] = '美洲';
        $countrys['Asia'] = '亚洲';
        $countrys['Worldwide'] = '全球';
        $countrys['EuropeanUnion'] = '欧盟';
        return $countrys;
    }
    
    
    
}