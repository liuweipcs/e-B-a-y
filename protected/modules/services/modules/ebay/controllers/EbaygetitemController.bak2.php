<?php

class EbaygetitemController extends UebController {
    
    public $number=15;
    
    public function actiongetitems() {
        set_time_limit(300);
        for($i=0; $i < 6; $i++) {
            $url = '/services/ebay/ebaygetitem/handledata';
            MHelper::runThreadSOCKET($url);
            sleep(3);
        }
        
        echo "ok";
    }
    
    public function actionHandledata() {
        set_time_limit(900);
        //查询帐号
        $accountInfo = UebModel::model('EbayAccount')->findAll(array(
            'select'=>'user_name,short_name',
            'condition'=>'status=:status',
            'params'=>array(':status'=>1),
        ));
        $accountData = array();
        foreach($accountInfo as $accountVal) {
            $accountData[$accountVal->user_name] = $accountVal->short_name;
        }
        //listing 信息
        $info = UebModel::model('Ebayonlinelisting')
                ->findAll(array(
                    'select'=>'id,account,itemid',
                    'condition'=>'listing_duration=:duration AND status=:status',
                    'params'=>array(':duration'=>'GTC', ':status'=>0),
                    'order'=>'update_time ASC',
                    'limit'=>100
                )
        );
        
        if(empty($info))  {
            echo "没有需要更新的数据";exit();
        }
        
        $idsArr = array();
        $idstr = '';
        foreach($info as $infoValue) {
            $idsArr[] = $infoValue->id;
            $idstr = implode(',', $idsArr);
            UebModel::model('Ebayonlinelisting')->updateAll(array('status'=>4),'id in ('.$idstr.')');
        }
        
        foreach($info as $value) {
            $apiobj = new Ebaygetitems();
            if(empty($accountData[$value->account])) {
                echo $value->itemid.','.$value->account."<br/>";
                continue;
            }
            $apiobj->setAccount($accountData[$value->account]);
            $apiobj->setItemid($value->itemid);
            $response = $apiobj->getebayitem();
            
            $this->checkcode($response, $value->itemid);
        }
        echo 'ok';
    }
    
    protected function checkcode($data,$itme_id) {
        if($data['status'] == '500' && $data['errcode'] == '17') { //item被移除了
            UebModel::model('Ebayonlinelisting')
                ->updateAll(array('status'=>3,'listing_status'=>'Completed'), 'itemid=:itemid', array(':itemid'=>$itme_id));
        }
    }
    
    //
    public function actiongetitemone() {
        $acc = Yii::app()->request->getParam('acc');
        $itemid = Yii::app()->request->getParam('itemid');
       
        $apiobj = new Ebaygetitems();
//        $apiobj->setAccount($acc);
        $apiobj->setItemid($itemid);
        $response = $apiobj->getnewebayitem($acc);
        echo "<pre>";
        var_dump($response);exit();
    }
    
    //new synch listing
    public function actionNewsynchtask() {
        set_time_limit(600);
        
        for($i=0; $i<20; $i++) {
            $url = '/services/ebay/ebaygetitem/synchitemdetail/id/'.$i;
            MHelper::runThreadSOCKET($url);
            sleep(5);
        }
        
        echo "ok";
    }
    
    public function actionSynchitemdetail() {
        set_time_limit(600);
        $startTime = time();
        $id = Yii::app()->request->getParam('id');
        
        $info = UebModel::model('Ebayonlinelistingitemid')->findAll(array(
                    'condition'=>'status=:status',
                    'params'=>array(':status'=>'0'),
                    'order'=>'update_time asc,ebay_account asc',
                    'limit'=>150,
                ));
       
        if(empty($info)) {
            echo "没有需要同步的listing";exit();
        }
        
       $itemArr = array();
       foreach($info as $value) {
           $value->status = 1;
           $value->update_time = date('Y-m-d H:i:s');
           $value->save();
       } 
       
       foreach($info as $v) {
           if(time() - $startTime > 580) {
               $v->status = 0;
               $v->save();
               continue;
           }
           $apiobj = new Ebaygetitems();
           $apiobj->setItemid($v->item_id);
           $response = $apiobj->getnewebayitem($v->ebay_account);
           
           $response = json_decode($response,true);
           if($response['status'] == '200') {
               echo $v->item_id." -- ".date('Y-m-d H:i:s')." --- 成功<br/>";
               UebModel::model('Ebayonlinelistingitemid')->updateAll(array('status'=>'3','update_time'=>date('Y-m-d H:i:s')), 'item_id=:id', array(':id'=>$v->item_id));
           } else {
               echo $v->item_id." -- ".date('Y-m-d H:i:s')." --- 失败<br/>";
               UebModel::model('Ebayonlinelistingitemid')->updateAll(array('status'=>'4','update_time'=>date('Y-m-d H:i:s')), 'item_id=:id', array(':id'=>$v->item_id));
           }
       }    
       
       echo time()-$startTime;exit();
    }
    
    //接收数据
    public function actionAcceptxml() {
        set_time_limit(300);
        $response = json_decode(Yii::app()->request->getParam('xml'));
        
        $ack = isset($response->Ack)?$response->Ack:'Failure';
        $itemid = $response->Item->ItemID;
        $listing_status = isset($response->Item->SellingStatus->ListingStatus)?$response->Item->SellingStatus->ListingStatus:'';

        if(!empty($listing_status) && $listing_status != 'Active') {
            UebModel::model('Ebayonlinelisting')->updateAll(array('status'=>3,'listing_status'=>'Completed'), 'itemid=:itemid', array(':itemid'=>$itemid));
            echo json_encode(array('status'=>'500', 'msg'=>'产品已下架，不需要更新'));exit();
        }

        if($ack != 'Failure') {
            date_default_timezone_set('Asia/Shanghai');
            $xmlDataTime = date('Y-m-d H:i:s',strtotime($response->Timestamp));
            $info = UebModel::model('Ebayonlinelisting')->find('itemid=:itemid', array(':itemid'=>$itemid));
            if(empty($info)) {
                $info = new Ebayonlinelisting();
            }

            if(!empty($info->xml_data_time) && $info->xml_data_time > $xmlDataTime)
            {
                echo json_encode(array('status'=>'200', 'msg'=>'数据库数据比当前xml数据更新。'));exit();
            }
            else
            {
                if($info->status == 1 || $info->status == 6)   //listing有手动修改
                {
                    $listingStatus = $response->Item->SellingStatus->ListingStatus;
                    if(!empty($listingStatus) && $listingStatus != $info->listing_status)
                    {
                        $info->listing_status = $listingStatus;
                        $info->save();
                    }
                    echo json_encode(array('status'=>'200', 'msg'=>'数据库数据有手动修改，不更新。'));exit();
                }
            }
        
            $listingObj = new Ebaygetsellerlists();
            $storeName = $response->Item->Seller->UserID;
             
            $listResponse = $listingObj->updatelist($response->Item, $storeName,$xmlDataTime);
            //echo "<pre>";var_dump($listResponse);exit();
            if($listResponse['status'] == '300') { //listing有修改 直接返回
                echo json_encode(array('status'=>'200'));exit();
            }
            
            $listingObj->updatevariations($itemid, $response->Item->Variations);
            $listingObj->updatedescrtion($itemid, $response->Item->Description);
            $listingObj->updateshipping($itemid, $response->Item->ShippingDetails);
            $listingObj->updateimage($itemid, $response->Item->PictureDetails,$listResponse['id']);
            $listingObj->updateattributes($itemid, $response->Item->ItemSpecifics);
            $listingObj->updatevariationimg($itemid,$response->Item->Variations->Pictures);
            $listingObj->updatebuyerrequire($itemid, $response->Item->BuyerRequirementDetails);
            
            if($listResponse['status'] == '200') {
                echo json_encode(array('status'=>'200'));exit();
            } else {
                echo json_encode(array('status'=>'500', 'msg'=>'更新失败'));exit();
            }
        
        } else {
            $err_msg = '';
            $err_code = $response->Errors->ErrorCode;
            $err_msg = isset($response->Errors->LongMessage)?$response->Errors->LongMessage:$response->Errors['0']->LongMessage;
            echo json_encode(array('status'=>'500', 'msg'=>'API Error,'.$err_msg, 'errcode'=>$err_code));exit();
        }
        
    }
    
    public function actionNewsynchtaskday() {
        set_time_limit(600);
    
        for($i=0; $i<4; $i++) {
            $url = '/services/ebay/ebaygetitem/synchitemdetail/id/'.$i;
            MHelper::runThreadSOCKET($url);
            sleep(5);
        }
    
        echo "ok";
    }
    
    //temp
    public function actionNewtemptask() {
        set_time_limit(300);
        for($i=0;$i<5;$i++) {
            $url = '/services/ebay/ebaygetitem/synchtemp/id/'.$i;
            MHelper::runThreadSOCKET($url);
            sleep(5);
            
        }
    }
    
    public function actionSynchtemp() {
        set_time_limit(600);
        $startTime = time();
        $limit = 100;
        $id = Yii::app()->request->getParam('id');
        
//         $info = UebModel::model('Ebayonlinelistingflag')->findAll(
//                 array(
//                     'condition'=>'status=0',
//                     'order'=>'update_time asc',
//                     'limit'=>$limit
//                 )
//             );
        
        $x = 0;
//         $info = UebModel::model('Ebayonlinelistingtemp')->findAll(array(
//                 'condition'=>'status=0',
//                 'order'=>'update_time asc',
//                 'limit'=>$limit
//         ));
        
        $info = UebModel::model('Ebayonlinelistingitemid')->findAll(array(
            'condition'=>'item_id in (
              222725461065


              
                
            )',
        ));
        
        
        if(empty($info)) {
            echo "没有需要同步的listing";exit();
        }
    
        $itemArr = array();
        
        foreach($info as $value) {
            $value->status = 3;
            $value->update_time = date('Y-m-d H:i:s');
            $value->save();
        }
        
        
        foreach($info as $v) {
             
            if(time() - $startTime > 580) {
                $v->status = 0;
                $v->save();
                echo $v->item_id .",未完成<br/>";
                $x++;
                continue;
            }
            $apiobj = new Ebaygetitems();
            $apiobj->setItemid($v->item_id);
            $response = $apiobj->getnewebayitem($v->ebay_account);
            
            $response = json_decode($response,true);
            
            if($response['status'] == '200') {
                echo $v->item_id." -- ".date('Y-m-d H:i:s')." --- 成功<br/>";
                UebModel::model('Ebayonlinelistingflag')->updateAll(array('status'=>'1'), 'item_id=:id', array(':id'=>$v->item_id));
            } else {
                echo $v->item_id." -- ".date('Y-m-d H:i:s')." --- 失败<br/>";
                UebModel::model('Ebayonlinelistingflag')->updateAll(array('status'=>'2','msg'=>$response['msg']), 'item_id=:id', array(':id'=>$v->item_id));
            }
        }
        
        echo $limit - $x;
        echo "<br/>";
        echo time()-$startTime;
        echo "<br/>";exit();
    }
    
    public function actionInitstatus() {
        set_time_limit(600);
        $now_time = date('Y-m-d H:i:s', time()-1800);
        
        UebModel::model('Ebayonlinelistingtask')->updateAll(array('status'=>0), 'status=1');
        
        UebModel::model('Ebayonlinelistingitemid')
            ->updateAll(array('status'=>0), 'update_time < "'.$now_time.'" and status in (3)');
        
        UebModel::model('Ebayonlinelistingitemid')->updateAll(array('status'=>0), 'status=1 and update_time < "'.$now_time.'" ');
    }
    

    
//     public function actionTest() {
//         $token = UebModel::model('EbayAccount')->find('status=1')->user_token;
//         $api = new TradingAPI();
//         $api->setUserToken($token);
//         $api->xmlTagArray = [
//             'GeteBayOfficialTimeRequest'=>[
//                 'ErrorLanguage'=>'en_US',
//                 'WarningLevel'=>'High'
//             ],
//         ];
// //        $api->setSiteId(0);
//         $response = $api->send()->response;
//         echo "<pre>";
//         var_dump($response);
        
//     }


}