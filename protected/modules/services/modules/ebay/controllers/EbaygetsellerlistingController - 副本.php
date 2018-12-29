<?php
header("Content-type:text/html;charset=utf-8");

class EbaygetsellerlistingController extends UebController {
    
    public function actionsynchebaylisting() {
        set_time_limit(0);
        
        $account = Yii::app()->request->getParam('acc');
        $pagenum = Yii::app()->request->getParam('pagenum');
        $startTime = Yii::app()->request->getParam('sttime');
        $endTime = Yii::app()->request->getParam('edtime');
        
        $ebaylistingobj = new Ebaygetsellerlists();      
        $ebaylistingobj ->setaccount($account);
        $ebaylistingobj->setpagenum($pagenum);
        //测试日志
        Ebayonlinelistinglog::model()->dbConnection->createCommand()->insert('ueb_ebay_online_listing_log',
                array('msg'=>'start,acc:'.$account.' ,page:'.$pagenum,'account'=>$account,'type'=>'start','add_time'=>date('Y-m-d H:i:s')));
       
        $response = $ebaylistingobj->getsellerlist($startTime, $endTime);
//        echo "<pre>";var_dump($response);exit();
        //测试日志
        Ebayonlinelistinglog::model()->dbConnection->createCommand()->insert('ueb_ebay_online_listing_log',
                array('msg'=>'end,acc:'.$account.' ,page:'.$pagenum,'account'=>$account,'type'=>'end','add_time'=>date('Y-m-d H:i:s')));
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
            //
            $taskdata = UebModel::model('Ebayonlinelistingtask')->find('store_name=:strorename', array(':strorename'=>$account));
            $task_startTime = $taskdata->start_time;
            $flag_status = $taskdata->flag_status;
            $diff_second = time() - strtotime($task_startTime);
            if($diff_second < 600 && $flag_status == '0') {
                $pagenum = $pagenum + 1;
                UebModel::model('Ebayonlinelistingtask')->updateAll(array('status'=>1), 'store_name = "'.$account.'"');
                $this->exec_urls($account,$pagenum,$startTime,$endTime);
                sleep(1);
            }
            
        } else {
            if($response['refresh_status'] == '200') {
                $pagenum = 1;
            }
            UebModel::model('Ebayonlinelistingtask')->updateAll(array('end_time'=>date('Y-m-d H:i:s'),'status'=>2,'pagenum'=>$pagenum,'remark'=>$response['msg']), 'store_name="'.$account.'"');
        }
        return $response;
    }
    
    //帐号控制
    public function actionaccounttask() {
        set_time_limit(600);
        $taskObj = UebModel::model('Ebayonlinelistingtask')
                    ->findAll(array('condition'=>'status != :status AND flag_status = :flag_status',
                                    'params'=>array(':status'=>1, ':flag_status'=>0),
                                    'order'=>'start_time asc',
                                    'limit'=>15,
                                )
                   );

        $count = count($taskObj);
        if($count < 15) {
            UebModel::model('Ebayonlinelistingtask')->updateAll(array('status'=>2), 'start_time < "'.date('Y-m-d H:i:s', time()-1800).'" AND status = 1');
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
        //$url = $hostname.'/services/ebay/ebaygetsellerlisting/synchebaylisting/acc/'.$account.'/pagenum/'.$pagenum.'/sttime/'.$startTime.'/edtime/'.$endTime;
        $url = '/services/ebay/ebaygetsellerlisting/synchebaylisting/acc/'.$account.'/pagenum/'.$pagenum.'/sttime/'.$startTime.'/edtime/'.$endTime;
        
        MHelper::runThreadSOCKET($url);
        
        //$response = $this->curl_urls($url);
        //return $response;
    }
    
    //
    protected function curl_urls($url) {
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch,CURLOPT_TIMEOUT,5);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }
    
    //更新所有标识
   public function actionebaysynchtaskflag() {
       $accountInfo = UebModel::model('Ebayaccount')
                        ->findAll(array(
                                    'condition'=>'status=:status',
                                    'params'=>array(':status'=>1),
                        ));
                        
       if(!empty($accountInfo)) {
           foreach($accountInfo as $accountval) {
               $checkObj = UebModel::model('Ebayonlinelistingtask')->find('ebay_account=:ebayaccount', array(':ebayaccount'=>$accountval->store_name));
               if(empty($checkObj)) {
                   $checkObj = new Ebayonlinelistingtask();
               }
               
               if($checkObj->pagenum != $checkObj->total_num) {
                   continue;
               }
               $checkObj->ebay_account = $accountval->store_name;
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
    
}