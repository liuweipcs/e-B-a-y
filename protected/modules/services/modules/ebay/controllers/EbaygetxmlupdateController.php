<?php

class EbaygetxmlupdateController extends UebController {
    
    public function actionUpdatelisting() {
        set_time_limit(300);
//        exit();
        $socketNum = 20;
        if(isset($_GET['line']))
        {
            $line = $_GET['line'];
//            $logPath = 'log/getxmlinsert.log';
//            file_put_contents($logPath,'startTime:'.date('Y-m-d H:i:s').PHP_EOL,FILE_APPEND);
            set_time_limit(120);
            $startTime = time();
            $model = new EbayItemNotification();
            $ids = array_column(VHelper::selectAsArray($model,'id','item_id%'.$socketNum.'='.$line.' and status=1 and flag in (0,2)',true,'','update_time ASC',400),'id');
            if(empty($ids))
            {
                echo '没有需要更新的数据';exit();
            }
            $model->updateByPk($ids,['flag'=>2]);
            $info = $model->findAllByPk($ids);
            $errMsg = '';
            foreach($info as $value) {
                if(time() - $startTime > 110) {
//                    file_put_contents($logPath,'forceEndTime:'.date('Y-m-d H:i:s').PHP_EOL,FILE_APPEND);
                    exit();
                }
                $itemId = $value->item_id;
                $path = $value->path;
                $file = 'http://'.$_SERVER['HTTP_HOST'].'/'.$path;

                $xml = file_get_contents($file);
                $response = simplexml_load_string($xml);
//            echo "<pre>";var_dump($response);exit();
                $result = $this->handleresponse($response,$value);
                if($result['status'] == '200') {
                    //$errMsg .= $itemId." -- 更新成功<br/> ";
                } else {
                    $errMsg .= $itemId." -- ".$result['msg'];
                }
            }
//            file_put_contents($logPath,'endTime:'.date('Y-m-d H:i:s').PHP_EOL,FILE_APPEND);
            echo $errMsg;
        }
        else
        {
            while($socketNum > 0)
            {
                $socketNum--;
                MHelper::runThreadSOCKET('/services/ebay/ebaygetxmlupdate/updatelisting/line/'.$socketNum);
                sleep(2);
            }
            exit('Done');
        }



    }

    public function actionDeleterepetition()
    {
        set_time_limit(420);
        $startTime = time();
        $model = new EbayItemNotification();
        $itemIds = VHelper::selectAsArray($model,'item_id,max(update_time)','flag=0 and status=1',false,'item_id','','','count(id) > 5');
        if(!empty($itemIds))
        {
            foreach ($itemIds as $row)
            {
                if(time()-$startTime > 418)
                    exit('10分钟已到');
//                $ids = VHelper::selectAsArray($model,'id',"item_id='{$row['item_id']}' and flag=0 and status=1",false,'','update_time ASC');
               /* if(count($ids) > 1)
                {*/
//                    array_pop($ids);
                    $model->deleteAll("item_id='{$row['item_id']}' and update_time<{$row['max(update_time)']} and flag=0 and status=1");
//                }
            }
        }
        exit('DONE');
    }
    
    protected function handleresponse($response,$value) {
        
        $ack = isset($response->Ack)?$response->Ack:'Failure';
        $itemid = $response->Item->ItemID;
        if($ack != 'Failure') {
            $listingStatus = $response->Item->SellingStatus->ListingStatus->__toString();
            date_default_timezone_set('Asia/Shanghai');
            $xmlDataTime = date('Y-m-d H:i:s',strtotime($response->Timestamp->__toString()));
            $info = UebModel::model('Ebayonlinelisting')->find('itemid=:itemid', array(':itemid'=>$itemid));
            if(empty($info)) {
                $info = new Ebayonlinelisting();
            }

            if(!empty($info->xml_data_time) && $info->xml_data_time > $xmlDataTime)
            {
                $value->flag = 1;
                $value->save();
                return array('status'=>'500', 'msg'=>'数据库数据比当前xml数据更新。');
            }
            else
            {
                if($info->status == 1 || $info->status == 6)   //listing有手动修改
                {
                    if(!empty($listingStatus) && $listingStatus != $info->listing_status)
                    {
                        $info->listing_status = $listingStatus;
                        $info->save();
                        $value->flag = 1;
                        $value->save();
                    }
                    return array('status'=>'500', 'msg'=>'数据库数据有手动修改，不更新。');
                }
            }



            $listingObj = new Ebaygetsellerlistsnew();
            $storeName = $response->Item->Seller->UserID;
             
            $listResponse = $listingObj->updatelist($response->Item, $storeName,$xmlDataTime);
            if($listResponse['status'] == '300') {
                UebModel::model('EbayItemNotification')->updateAll(array('flag'=>1), 'item_id='.$itemid);
                return array('status'=>'200');
            }
            $listingObj->updatevariations($itemid, $response->Item->Variations);
            $listingObj->updatedescrtion($itemid, $response->Item->Description);
            $listingObj->updateshipping($itemid, $response->Item->ShippingDetails);
            $listingObj->updateimage($itemid, $response->Item->PictureDetails,$listResponse['id']);
            $listingObj->updateattributes($itemid, $response->Item->ItemSpecifics);
            $listingObj->updatevariationimg($itemid,$response->Item->Variations->Pictures);
            $listingObj->updatebuyerrequire($itemid,$response->Item->BuyerRequirementDetails);
        
            if($listResponse['status'] == '200') {
                $galleryURL = trim($response->Item->PictureDetails->GalleryURL->__toString());
//                if(strpos($galleryURL,'http://i.ebayimg.com') !== 0 && $listingStatus == 'Active')
                if(strpos($galleryURL,'http://image-us.bigbuy.win') === 0 && $listingStatus == 'Active')
                {
                    (new Ebayonlinelisting())->find('itemid=:itemid', array(':itemid'=>$itemid))->sendApi();
                }
                UebModel::model('EbayItemNotification')->updateAll(array('flag'=>1), 'item_id='.$itemid);
                return array('status'=>'200');
            } else {
                return array('status'=>'500', 'msg'=>'更新失败');
            }
        
        } else {
            $err_msg = '';
            $err_code = $response->Errors->ErrorCode;
            $err_msg = isset($response->Errors->LongMessage)?$response->Errors->LongMessage:$response->Errors['0']->LongMessage;
            return array('status'=>'500', 'msg'=>'API Error,'.$err_msg, 'errcode'=>$err_code);
        }
    }
    
    
    //更新店铺等级
    public function actionGetstorelevel() {
        set_time_limit(600);
        $id = Yii::app()->request->getParam('id');
        $account = UebModel::model('EbayAccount')->findAll('status=1');
        if(!empty($id)) {
            $account = UebModel::model('EbayAccount')->findAll('status=1 and user_name = "'.$id.'" ');
        }
        
        if(!empty($account)) {
            foreach($account as $v) {
                $api = new TradingAPI();
                $api->setUserToken($v->user_token);
                $api->xmlTagArray = [
                    'GetStoreRequest'=>[
                        'DetailLevel'=>'ReturnAll',
                        'ErrorLanguage'=>'en_US',
                        'WarningLevel'=>'High'
                    ],
                ];
                $response = $api->send()->response;
                $model = UebModel::model('Ebayaccountlevel')->find('account_id='.$v->id);
                if(empty($model)) {
                    $model = new Ebayaccountlevel();
                }
                if(isset($response->Ack) && $response->Ack == 'Success') {
                    $level = $response->Store->SubscriptionLevel;
                    
                    $model->account_id = $v->id;
                    $model->store_level = $level;
                    $model->status = 0;
                    $model->update_time = date('Y-m-d H:i:s');
                    
                    $model->save();
                } else {
                    $model->status = 2;
                    $model->save();
                }
                
            }
        }
        
        echo "ok";
    }
    
    //更新开店铺站点
    public function actionGetstoresite() {
        set_time_limit(600);
        $id = Yii::app()->request->getParam('id');
        $account = UebModel::model('EbayAccount')->findAll('status=1');
        if(!empty($id)) {
            $account = UebModel::model('EbayAccount')->findAll('status=1 and user_name = "'.$id.'" ');
        }
        
        if(!empty($account)) {
            foreach($account as $v) {
                $api = new TradingAPI();
                $api->setUserToken($v->user_token);
                $api->xmlTagArray = [
                    'GetUserRequest'=>[
                        'DetailLevel'=>'ReturnAll',
                        'ErrorLanguage'=>'en_US',
                        'WarningLevel'=>'High'
                    ],
                ];
                
                $response = $api->send()->response;
                $model = UebModel::model('Ebayaccountlevel')->find('account_id='.$v->id);
                if(empty($model)) {
                    $model = new Ebayaccountlevel();
                }
                if(isset($response->Ack) && $response->Ack == 'Success') {
                    $site = $response->User->SellerInfo->StoreSite;
                    
                    $model->account_id = $v->id;
                    $model->store_site = $site;
                    $model->status = 0;
                    $model->update_time = date('Y-m-d H:i:s');
                    
                    $model->save();
                } else {
                    $model->status = 2;
                    $model->save();
                }
            }
        }
        
        echo "ok";
    }
    
    //eBay在线listing更新销售员
    public function actionUpdateseller() {
        set_time_limit(600);
        $num = 10;
        $limit = 5000;
        $line = Yii::app()->request->getParam('line');
        $limitTemp = Yii::app()->request->getParam('l');
        if(!empty($limitTemp)) {
            $limit = $limitTemp;
        }
        //location 映射大仓
        $warehouseInfo = UebModel::model('EbayLocationMapWarehouse')->findAll('is_delete=0');
        $warehouse = [];
        foreach($warehouseInfo as $v) {
            $v->location = strtoupper($v->location);
            $warehouse[$v->location] = $v->warehouse_category_id;
        }
        
        //大仓信息
        $virtualWarehouse = UebModel::model('EbayWarehouseWarehouseCategory')->findAll('is_delete=0');
        $virtual = [];
        foreach($virtualWarehouse as $_v) {
            $virtual[$_v->id] = $_v->name;
        }
        
        if(is_numeric($line)) {
    		$info = UebModel::model('Ebayonlinelisting')->findAll([
    		    'select'=>'id,itemid,account,location,siteid,product_line',
    		    'condition'=>'id%'.$num.'='.$line.'  and product_line != "未识别的sku" and product_line != ""  ',
    		    'order'=>'update_time asc',
    		    'limit'=>$limit
    		]);
    		
    		
    		foreach($info as $value) {
    		  $value->location = strtoupper($value->location);
    		  $obj = UebModel::model('Ebayonlinelistingpermissions')->findAll('account="'.$value->account.'" and site_id="'.$value->siteid.'" and products_line="'.$value->product_line.'" and warehouse_name="'.$virtual[$warehouse[$value->location]].'"   ');
    		  
    		 if(empty($obj) ) {
        		  $obj = UebModel::model('Ebayonlinelistingpermissions')->findAll('account="'.$value->account.'" and site_id="'.$value->siteid.'"  and warehouse_name="'.$virtual[$warehouse[$value->location]].'" ');

    		  }
    		  if(empty($obj)) {
    		      //$value->seller_user = '没有匹配到权限';
    		      //$value->save();
    		      continue;
    		  }
    		  
    		  if(count($obj) > 1) {
    		      //$value->seller_user = '有2条权限';
    		      //$value->save();
    		      continue;
    		  }
    		  
    		  $value->update_time = date('Y-m-d H:i:s');
    		  $value->seller_user = UebModel::model('User')->find('id='.$obj['0']->seller_user)->user_full_name;
    		  $value->save();
    		}
    		
        } else {
            for($i=0;$i<10;$i++) {
                MHelper::runThreadSOCKET('/services/ebay/ebaygetxmlupdate/updateseller/line/'.$i);
                sleep(2);
            }
            
            echo "ok";
        }
		
	}
    
	//更新没有设置销售的
	public function actionTest() {
	    $flag = UebModel::model('Ebayonlinelisting')->updateAll(array('seller_user'=>null),'seller_user="没有匹配到权限"');
	    echo "<pre>";var_dump($flag);
	}
    
	//修正sku
	public function actionSkumodify() {
	    set_time_limit(600);
	    $info = Yii::app()->db->createCommand()->from('ueb_product.ueb_ebay_online_itemid_temp')
	           ->select('item_id,msg')
	           ->queryAll(true);
	    
	    foreach($info as $v) {
	        $model = UebModel::model('Ebayonlinelisting')->find('itemid='.$v['item_id']);
	        if($model->product_line != '未识别的sku' ) {
	            continue;
	        }
	        
	        if($model->listing_status == 'Active') {
	            $model->status=1;
	            $model->sku = $v['msg'];
	            $model->product_line = null;
	            $model->seller_user=null;
	            $model->postcode = '';
	            $model->remark = '';
	            $model->save();
	        } else {
	            $model->sku = $v['msg'];
	            $model->product_line = null;
	            $model->remark = '';
	            $model->save();
	        }
	    }
	    
	    echo "ok";
	}
	
	
	
}