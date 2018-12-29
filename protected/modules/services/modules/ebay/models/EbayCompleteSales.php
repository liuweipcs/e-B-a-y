<?php

class EbayCompleteSales {
    
    public function remarkship($data) {
        $account_id = trim($data['account_id']);    
        $order_id = trim($data['order_id']);
        $carrier = trim($data['carrier']);
        $tracking_number = trim($data['tracking_number']);
        
        if(!$account_id) {
            return 'eBay订单帐号不能为空';
        }

        $accountInfo = Yii::app()->db->createCommand()
            ->select('short_name')->from('ueb_system.ueb_ebay_account')
            ->where('id=:id')->queryAll(true,array(':id'=>$account_id));
        
        $orderInfo = Yii::app()->db->createCommand()
                    ->select('b.item_id,b.transaction_id')->from('ueb_order.ueb_order_ebay as a')
                    ->leftJoin('ueb_order.ueb_order_ebay_detail as b', 'a.order_id = b.order_id')
                    ->where('a.order_id=:order_id')->queryAll(true, array(':order_id'=>$order_id));
        
        if(empty($orderInfo)) {
           return '没有查到订单记录';
        }

        $flag = true;
        $remarkShipObj = new Ebayremarkship();
        foreach($orderInfo as $val) {
            if(!$val['item_id']) {
                continue;
            }
            $remarkShipObj->setItemId($val['item_id']);
            $remarkShipObj->setTransaction($val['transaction_id']);
            
            if($tracking_number && $carrier) {
                $remarkShipObj->setTrackNumber($tracking_number);
                $remarkShipObj->setCarrier($carrier);
            }

            $siteID = 0;
            $response = $remarkShipObj->setShortName($accountInfo['0']['short_name'])
            ->setSiteId($siteID)->setVerb('CompleteSale')
            ->setRequest()
            ->sendHttpRequest()->getResponse();

            if(!$remarkShipObj->getIfSuccess()) {
                $flag = false;
                $msg = $remarkShipObj->getErrorMsg();
                break;
            }
        }

        if($flag) {
            return true;
        } else {
            return '标记发货失败，'.$msg;
        }
    }


}