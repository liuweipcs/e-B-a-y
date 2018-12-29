<?php

class EbayCompleteSaleController extends UebController {
    
    public function accessRules() {
        return array();
    }
    
    public function actionremarkship() {
        $account = Yii::app()->request->getParam('account');
        $itemid = trim(Yii::app()->request->getParam('itemid'));
        $transactionid = trim(Yii::app()->request->getParam('transactionid'));
        $tracknumber = trim(Yii::app()->request->getParam('tracknumber'));
        $carrier = trim(Yii::app()->request->getParam('carrier'));
        
        if(!$itemid || !$transactionid) {
            return array('status'=>500, 'msg'=>'订单ItemID或交易ID不能为空');
        }
        
        $remarkShipObj = new Ebayremarkship();
        $remarkShipObj->setItemId($itemid);
        $remarkShipObj->setTransaction($transactionid);
        
        if($tracknumber && $carrier) {
            $remarkShipObj->setTrackNumber($tracknumber);
            $remarkShipObj->setCarrier($carrier);
        }
        
        //
        $siteID = 0;
        $response = $remarkShipObj->setShortName($account)
                    ->setSiteId($siteID)->setVerb('CompleteSale')
                    ->setRequest()
                    ->sendHttpRequest()->getResponse();
       
        if($remarkShipObj->getIfSuccess()) {
            return array('status'=>200, 'msg'=>'订单标记发货成功');
        } else {
            return array('status'=>500, 'msg'=>'标记发货失败，'.$remarkShipObj->getErrorMsg());
        }
    }
}