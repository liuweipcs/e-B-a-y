<?php

class EbayonlinelistingtaskController extends UebController {
    
    //更新销售员
    public function actionUpdateselleruser() {
        set_time_limit(600);
        $response = UebModel::model('Ebayonlinelistingpermissions')->batchUpdate();
        echo "<pre>";var_dump($response);
    }
    
    //更新产品线
    public function actionUpdateproductline() {
        set_time_limit(600);
        $num = 10;
        $line = Yii::app()->request->getParam('line');
        $id = Yii::app()->request->getParam('id');
        $productLine = UebModel::model('Ebayonlinelisting')->getproductLine();
        
        if(is_numeric($line)) {
            $result = UebModel::model('Ebayonlinelisting')->findAll([
                'select'=>'id,itemid,listing_status,sku,variation_multi,product_line',
                'condition'=>'listing_status="Active" and id%'.$num.'='.$line,
            ]);
            
            if(!empty($id)) {
                $result = UebModel::model('Ebayonlinelisting')->findAll([
                    'select'=>'id,itemid,listing_status,sku,variation_multi,product_line',
                    'condition'=>'itemid='.$id
                ]);
            }
            
            foreach($result as $v) {
                $multi = $v->variation_multi;
                $pline = $v->product_line;
                                
                if($multi == '0') {
                    $sku = $v->sku;
                } else {
                    $sku = UebModel::model('Ebayonlinelistingvariation')->find('item_id='.$v->itemid)->sku;
                }
                
                $skuSplit = Product::splitBundleSku($sku);
                if(!empty($skuSplit)) {
                    $skus = $skuSplit['sku']['0'];
                } else {
                    $skus = $sku;
                }
                
                $product = Product::getFirstLevelProductLineListId(addslashes($skus));
                if(empty($product)) {
                    $v->product_line = '未识别的sku';
                    $v->save();
                    continue;
                }
                
                if($pline != $productLine[$product]['cn']) {
                    $v->product_line = $productLine[$product]['cn'];
                    $v->save();
                }
            }
            
            echo "<pre>";var_dump($sku, $skus, $product);
            echo "ok";
        } else {
            for($i=0;$i<$num;$i++) {
                MHelper::runThreadSOCKET('/services/ebay/ebayonlinelistingtask/updateproductline/line/'.$i);
                sleep(3);
            }
            echo "ok";
        }
    }
    
    //更新错误的产品线
    public function actionUpdatelineid() {
        set_time_limit(600);
        UebModel::model('Ebayonlinelistingpermissions')->checkPorductLine();
        echo "ok";
    }
    
    //更新前一天的销量统计
    public function actionUpdatesumcategory() {
        set_time_limit(600);
        $st = date('Y-m-d 00:00:00', strtotime('-1 day'));
        $et = date('Y-m-d 23:59:59', strtotime('-1 day'));
         
        $url = 'http://120.24.249.36/services/ebay/ebayupdateimglink/sumcategoryitem/st/'.$st.'/et/'.$et;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        //        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $content = curl_exec($ch);
        echo "ok";
    }
    
    public function actionUpdateitem() {
        set_time_limit(600);
        $line = Yii::app()->request->getParam('line');
        $id = Yii::app()->request->getParam('id');
        $startTime = time();
        $num = 40;

        if(is_numeric($line)) {
            $info = UebModel::model('Ebayonlinelistingitemid')->findAll(array(
                'condition'=>'status=0 and id%'.$num.'='.$line,
                'order'=>'update_time asc,ebay_account asc',
                'limit'=>150,
            ));
            if(!empty($id)) {
                $info = UebModel::model('Ebayonlinelistingitemid')->findAll([
                    'condition'=>'item_id='.$id,
                ]);
            }
            
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
                    $errCode = $response['errcode'];
                    if($errCode == '17') {
                        UebModel::model('Ebayonlinelisting')->updateAll(array('listing_status'=>'Completed'), 'itemid='.$v->item_id);
                    }
                    echo $v->item_id." -- ".date('Y-m-d H:i:s')." --- 失败<br/>";
                    UebModel::model('Ebayonlinelistingitemid')->updateAll(array('status'=>'4','update_time'=>date('Y-m-d H:i:s')), 'item_id=:id', array(':id'=>$v->item_id));
                }
            }
            
        } else {
            for($i=0; $i<$num; $i++) {
                $url = '/services/ebay/ebayonlinelistingtask/updateitem/line/'.$i;
                MHelper::runThreadSOCKET($url);
                sleep(5);
            }
        }
        
        
    }
    
    //接收数据
    public function actionAcceptxml() {
        set_time_limit(600);
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
    
            if($info->status == 1)   //listing有手动修改
            {
                $listingStatus = $response->Item->SellingStatus->ListingStatus;
                if(!empty($listingStatus) && $listingStatus != $info->listing_status)
                {
                    $info->listing_status = $listingStatus;
                    $info->save();
                }
                echo json_encode(array('status'=>'200', 'msg'=>'数据库数据有手动修改，不更新。'));exit();
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
    
    public function actionTest() {
	echo 1111111;exit();
    }
    
    
    
}
