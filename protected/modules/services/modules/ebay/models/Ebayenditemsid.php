<?php

class Ebayenditemsid {
    
    public function enditems($itemid) {
        $info = Yii::app()->db->createCommand()
                ->select('id, item_id, siteid, ebay_account_id')
                ->from('ueb_product.ueb_ebay_listing')
                ->where('item_id=:itemid')->queryAll(true, array(':itemid'=>$itemid));
        if(empty($info)) {
           return array('status'=>500, 'msg'=>'没有查询到itemid信息'); 
        }
        
        $siteid = $info['0']['siteid'];
        $accountid = $info['0']['ebay_account_id'];

        //帐号信息
        $accInfo = Yii::app()->db->createCommand()
                    ->select('id,short_name')
                    ->from('ueb_system.ueb_ebay_account')
                    ->where('id=:id')->queryAll(true, array(':id'=>$accountid));
        
        if(empty($accInfo)) {
            return array('status'=>500, 'msg'=>'没有查询到帐号信息');
        }
        $shortname = $accInfo['0']['short_name'];
        
        $ApiObj = new Ebayenditems();
        $ApiObj->setitemid($itemid);
        //获取分类
        $response = $ApiObj->setShortName($shortname)
                    ->setSiteId($siteid)
                    ->setVerb('EndFixedPriceItem')
                    ->setRequest()
                    ->sendHttpRequest()
                    ->getResponse()
        ;
//        echo "<pre>";var_dump($response);exit();
        $ack = isset($response->Ack)?$response->Ack:'';
        if($ack != 'Failure' && $ack != '') {
            $param['status'] = 3;
            $flag = Yii::app()->db->createCommand()->update('ueb_product.ueb_ebay_listing', $param, 'item_id='.$itemid);
            return array('status'=>200);
        } else {
            return array('status'=>500, 'msg'=>'api错误：'.$ApiObj->getErrorMsg());
        }
        
    }
    
    
    public function endonlineitems($itemid) {
        $info = Yii::app()->db->createCommand()
        ->select('id, itemid, siteid, account')
        ->from('ueb_product.ueb_ebay_online_listing')
        ->where('itemid=:itemid')->queryAll(true, array(':itemid'=>$itemid));
        if(empty($info)) {
            return array('status'=>500, 'msg'=>'没有查询到itemid信息');
        }
    
        $siteid = $info['0']['siteid'];
        $accountid = $info['0']['account'];
    
        //帐号信息
        $accInfo = Yii::app()->db->createCommand()
        ->select('id,short_name')
        ->from('ueb_system.ueb_ebay_account')
        ->where('user_name=:user_name')->queryAll(true, array(':user_name'=>$accountid));
    
        if(empty($accInfo)) {
            return array('status'=>500, 'msg'=>'没有查询到帐号信息');
        }
        $shortname = $accInfo['0']['short_name'];
    
        $ApiObj = new Ebayenditems();
        $ApiObj->setitemid($itemid);
        //获取分类
        $response = $ApiObj->setShortName($shortname)
        ->setSiteId($siteid)
        ->setVerb('EndFixedPriceItem')
        ->setRequest()
        ->sendHttpRequest()
        ->getResponse()
        ;
        //        echo "<pre>";var_dump($response);exit();
        $ack = isset($response->Ack)?$response->Ack:'';
        if($ack != 'Failure' && $ack != '') {
            $param['status'] = 3;
            $param['listing_status'] = 'Completed';
            $flag = Yii::app()->db->createCommand()->update('ueb_product.ueb_ebay_listing', $param, 'item_id='.$itemid);
            return array('status'=>200);
        } else {
            return array('status'=>500, 'msg'=>'api错误：'.$ApiObj->getErrorMsg());
        }
    
    }
    
    
}