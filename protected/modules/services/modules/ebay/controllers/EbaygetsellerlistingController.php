<?php
header("Content-type:text/html;charset=utf-8");

class EbaygetsellerlistingController extends UebController {
    
    public function actionsynchebaylisting() {
        set_time_limit(1800);
        
        $account = Yii::app()->request->getParam('acc');
        $pagenum = Yii::app()->request->getParam('pagenum');
        $startTime = Yii::app()->request->getParam('sttime');
        $endTime = Yii::app()->request->getParam('edtime');
        
        $ebaylistingobj = new Ebaygetsellerlists();      
        $ebaylistingobj ->setaccount($account);
        $ebaylistingobj->setpagenum($pagenum);
       
        $response = $ebaylistingobj->getsellerlist($startTime, $endTime);
        if($response['status'] == 200) {
            $hasmore = $response['hasmore'];
            $pagenums = $pagenum + 1;
            $flag_status = 0;
            if($hasmore == 'false') {
                //$pagenums = 1;
                $pagenums = $pagenum;
                $flag_status = 1;
            }
            UebModel::model('Ebayonlinelistingtask')
                ->updateAll(array('end_time'=>date('Y-m-d H:i:s'),'status'=>0,'pagenum'=>$pagenums,'flag_status'=>$flag_status,'total_num'=>$response['totalpagenum']), 'store_name="'.$account.'"');
                     
        } else {
            if($response['refresh_status'] == '200') {
                $pagenum = 1;
            }
            UebModel::model('Ebayonlinelistingtask')->updateAll(array('end_time'=>date('Y-m-d H:i:s'),'status'=>2,'pagenum'=>$pagenum,'remark'=>$response['msg']), 'store_name="'.$account.'"');
        }
        return $response;
    }
    
    //帐号控制
    public function actionAccounttask() {
        set_time_limit(600);
        $taskObj = UebModel::model('Ebayonlinelistingtask')
                    ->findAll(array('condition'=>'status != :status AND flag_status = :flag_status',
                                    'params'=>array(':status'=>1, ':flag_status'=>0),
                                    'order'=>'id asc',
                                    'limit'=>10,
                                )
                   );

        $count = count($taskObj);
        if($count < 10) {
            UebModel::model('Ebayonlinelistingtask')->updateAll(array('status'=>0), 'start_time < "'.date('Y-m-d H:i:s', time()-1800).'" AND status = 1');
        }
        if(empty($taskObj)) {
           print '没有需要同步的帐号Listing';
           return array('status'=>500, 'msg'=>'没有需要同步的帐号Listing');
        }
        
        foreach($taskObj as $taskval) {
            //执行前对帐号标识
            UebModel::model('Ebayonlinelistingtask')->updateAll(array('start_time'=>date('Y-m-d H:i:s'),'status'=>1), 'id='.$taskval->id);
            
            $account = $taskval->store_name;
            $pagenum = $taskval->pagenum;
            $startTime = date('Y-m-d\T00:00:01', strtotime('-1 day')).'.001Z';
            $endTime = date('Y-m-d\T23:59:59', strtotime('+32 day')).'.002Z';
            
            $response = $this->exec_urls($account,$pagenum,$startTime,$endTime);
            sleep(2);
        }
        
        echo 'ok';
    }
    
    //
    public function exec_urls($account,$pagenum,$startTime,$endTime) {
        $url = '';
        $hostname = $_SERVER['HTTP_HOST'];
        $url = '/services/ebay/ebaygetsellerlisting/synchebaylisting/acc/'.$account.'/pagenum/'.$pagenum.'/sttime/'.$startTime.'/edtime/'.$endTime;
        MHelper::runThreadSOCKET($url);
    }
    
    //更新所有标识
   public function actionEbaysynchtaskflag() {
       $accountInfo = UebModel::model('EbayAccount')
                        ->findAll(array(
                                    'condition'=>'status=:status AND length(user_token) > :length ',
                                    'params'=>array(':status'=>1,':length'=>300),
                        ));
       
       if(!empty($accountInfo)) {
           foreach($accountInfo as $accountval) {
               $checkObj = UebModel::model('Ebayonlinelistingtask')->find('ebay_account=:ebayaccount', array(':ebayaccount'=>$accountval->user_name));
               if(empty($checkObj)) {
                   $checkObj = new Ebayonlinelistingtask();
               }
               
               if($checkObj->pagenum != $checkObj->total_num) {
                   continue;
               }
               $checkObj->ebay_account = $accountval->user_name;
               $checkObj->store_name = $accountval->short_name;
               $checkObj->site_id = 0;
               $checkObj->pagenum = 1;
               $checkObj->start_time = date('Y-m-d H:i:s');
               $checkObj->end_time = date('Y-m-d H:i:s');
               $checkObj->status = 0;
               $checkObj->total_num = 1;
               $checkObj->flag_status = 0;
               
               $response = $checkObj->save();
           }
       }

       if($response) {
           echo "ok";
       } else {
           echo "update faluire, ".$accountval->store_name;
       }
   }
   
   //同步在线item
   public function actionSynchitemtask() {
       set_time_limit(600);
       $accountInfo = UebModel::model('Ebayonlinelistingtask')
            ->findAll(array(
                'condition'=>'status=:status',
                'params'=>array(':status'=>'0'),
                'order'=>'start_time asc',
                'limit'=>'1'
            ));
       
      if(empty($accountInfo)) {
          echo '没有需要同步的帐号';exit();
      }
      //先打标识
      $accountData = array();
      foreach($accountInfo as $accountValue) {
          $accountValue->start_time = date('Y-m-d H:i:s');
          $accountValue->status = 1;
          $accountValue->save();
          
          $url = '';
          $url = '/services/ebay/ebaygetsellerlisting/synchitem/acc/'.$accountValue->ebay_account;
          MHelper::runThreadSOCKET($url);
          sleep(3);
      }
      
      echo "ok";
   }
   
   //根据帐号同步item id
   public function actionsynchitem() {
       set_time_limit(600);  
       require_once $_SERVER['DOCUMENT_ROOT'].'/protected/vendors/ebay/EbaySession.php';
       $startTime = substr_replace(date('Y-m-d 00:00:00', strtotime('-1 day')),'T',10,1).'.211Z';
       $endTime = substr_replace(date('Y-m-d H:i:s', strtotime('+31 days')), 'T',10,1).'.210Z';
       $acc = Yii::app()->request->getParam('acc');
       $page = Yii::app()->request->getParam('p');
       $flag = Yii::app()->request->getParam('f');
       
       if(empty($page)){ 
           $page = 1;
       }

       $ebayAcc = UebModel::model('EbayAccount')->find('user_name=:name', array(':name'=>$acc));
       $token = $ebayAcc->user_token;

       $xml = '';
       $xml .= '<?xml version="1.0" encoding="utf-8"?>';
       $xml .= '<GetSellerListRequest xmlns="urn:ebay:apis:eBLBaseComponents">';
       $xml .= '<RequesterCredentials>';
       $xml .= '<eBayAuthToken>'.$token.'</eBayAuthToken>';
       $xml .= '</RequesterCredentials>';
       $xml .= '<ErrorLanguage>en_US</ErrorLanguage>';
       $xml .= '<WarningLevel>High</WarningLevel>';
       $xml .= '<DetailLevel>ReturnAll</DetailLevel>';
       $xml .= '<EndTimeFrom>'.$startTime.'</EndTimeFrom>';
       $xml .= '<EndTimeTo>'.$endTime.'</EndTimeTo>';
       $xml .= '<Pagination>';
       $xml .= '<EntriesPerPage>200</EntriesPerPage>';
       $xml .= '<PageNumber>'.$page.'</PageNumber>';
       $xml .= '</Pagination>';
       $xml .= '<OutputSelector>ItemID,PayPalEmailAddress,ListingStatus,HasMoreItems,TotalNumberOfEntries,TotalNumberOfPages</OutputSelector>';
       $xml .= '</GetSellerListRequest>';
       //记录同步日志
       $logModel = UebModel::model('Ebayonlinelistinglog')->find('account=:acc and page=:page', array(':acc'=>$acc, ':page'=>$page));
       if(empty($logModel)) {
           $logModel = new Ebayonlinelistinglog;
       }
       $logModel->account = $acc;
       $logModel->type = 0;
       $logModel->page = $page;
       $logModel->add_time = date('Y-m-d H:i:s');
       $logModel->update_time = date('Y-m-d H:i:s');
       $logModel->save();
       
       try { 
           $ebayKeys = ConfigFactory::getConfig('ebayKeys');
           $devID = $ebayKeys['devID'];
           $appID = $ebayKeys['appID'];
           $certID = $ebayKeys['certID'];
           $compatabilityLevel = $ebayKeys['compatabilityLevel'];
           $siteID = 0;
           $serverUrl = $ebayKeys['serverUrl'];
           $verb = 'GetSellerList';
           
           $session = new eBaySession($token, $devID, $appID, $certID, $serverUrl, $compatabilityLevel, $siteID, $verb);
           $response = $session->sendHttpRequest($xml);
           //echo "<pre>";var_dump($response);exit();  

           $data = $this->handleresult($response, $page, $acc, $flag);
           //记录同步日志
           $logModel = UebModel::model('Ebayonlinelistinglog')->find('account=:acc and page=:page', array(':acc'=>$acc, ':page'=>$page));
           if(empty($logModel)) {
               $logModel = new Ebayonlinelistinglog;
           }
           $logModel->account = $acc;
           $logModel->type = 1;
           $logModel->page = $page;
           $logModel->update_time = date('Y-m-d H:i:s');
           $logModel->save();
           
           if($data['status'] == '500') {
               $logModel->type = 2;
               $logModel->msg = $data['msg'];
               $logModel->save();
               echo $data['msg'];exit();
           }
           if($data['status'] == '400') {
               //$flag = false;
               //break;
               echo 'page ok';
               exit();
           }
           
           $page++;
       } catch (Exception $e ) {
           //$flag = false;
           Yii::apiDbLog($e->getMessage(), $e->getCode(), get_class($this));
       }
       echo "ok";
   }
   
 
    protected function handleresult($data, $page, $acc,$flag) {
        $ack = isset($data->Ack)?$data->Ack:'Failure';
        
        if($ack == 'Failure') {
           $errMsg = isset($data->Errors->LongMessage)?$data->Errors->LongMessage:$data->Errors['0']->LongMessage;
           return array('status'=>'500', 'msg'=>$errMsg);
        }
                
        $itemArr = $data->ItemArray->Item;
        $totalNum = $data->PaginationResult->TotalNumberOfPages;
        
        foreach($itemArr as $value) {
            $itemId = $value->ItemID;
            $email = $value->PayPalEmailAddress;
            $ListingStatus = $value->SellingStatus->ListingStatus;
            if($ListingStatus != 'Active') {
                UebModel::model('Ebayonlinelistingitemid')->updateAll(array('status'=>'2'), 'item_id=:id', array(':id'=>$itemId));
                UebModel::model('Ebayonlinelisting')->updateAll(array('status'=>'3', 'listing_status'=>$ListingStatus), 'itemid=:id', array(':id'=>$itemId));
                continue;
            }
            
            $model = UebModel::model('Ebayonlinelistingitemid')->find('item_id=:id', array(':id'=>$itemId));
            if(empty($model)) {
                $model = new Ebayonlinelistingitemid();
                $model->status = 0;
            }
            
            $model->ebay_account = $acc;
            $model->item_id = $itemId;
            $model->email = $email;
            
            $model->update_time = date('Y-m-d H:i:s');
            
            $model->save();
        }
        
        if($data->HasMoreItems == 'false' || $data->PaginationResult->TotalNumberOfPages <= $page) {
            return array('status'=>'400'); //最后一页
        }
        //
        if($page == '1' && !$flag) {
            for($i=2; $i <= $totalNum; $i++) {
                $url = '';
                $url = '/services/ebay/ebaygetsellerlisting/synchitem/acc/'.$acc.'/p/'.$i;
                MHelper::runThreadSOCKET($url);
                sleep(3);
            }
        }
        
       return array('status'=>'200');
   }
   
   //将帐号的状态调整为初始状态
   public function actionAccountinit() {
       $info = UebModel::model('Ebayonlinelistingtask')->findAll('status=:status', array(':status'=>'1'));
       
       foreach($info as $value) {
           $value->status = 0;
           $value->save();
       }
   }
   
   //检查未完成的任务
   public function actionCheckebaytask() {
       $info = UebModel::model('Ebayonlinelistinglog')
            ->find(array(
                'condition'=>'type!=:type and add_time < :time',
                'params'=>[':type'=>'1', ':time'=>date('Y-m-d H:i:s', (time()-1200))],
                'order'=>'update_time asc',
            ));
       if(empty($info)) {
           echo "没有未完成的任务";exit();
       }
       $acc = $info->account;
       $page = $info->page;
       
       $info->add_time = date('Y-m-d H:i:s');
       $info->update_time = date('Y-m-d H:i:s');
       $info->save();
       
       $url = '/services/ebay/ebaygetsellerlisting/synchitem/acc/'.$acc.'/p/'.$page.'/f/1';
       MHelper::runThreadSOCKET($url);
       
       echo "ok";
   }
     
   public function actionTest() {
	echo 11111111;exit();
  }
    
}
