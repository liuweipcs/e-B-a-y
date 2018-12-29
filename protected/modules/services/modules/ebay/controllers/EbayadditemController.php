<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/12/19 0019
 * Time: 下午 5:59
 */
class EbayadditemController extends UebController
{

    protected $startTime;
    protected $cycleAccount = array();

    /*public function actionAdditem(){
        if(isset($_REQUEST['account'])){
            $account = (int)trim($_REQUEST['account']);
            $nowDate = date('Y-m-d H:i:s');
            $listingIds = array_column(VHelper::selectAsArray('EbayListing','id','ebay_account_id='.$account.' and status in (2,11) and is_delete=0 and (listing_status=0 or (listing_status=1 and TIMESTAMPDIFF(MINUTE,listing_date,"'.$nowDate.'")>115))'),'id');
            UebModel::model('EbayListing')->updateByPk($listingIds,['listing_status'=>1,'listing_date'=>$nowDate]);
//            $ebayListings = UebModel::model('EbayListing')->findAll('ebay_account_id=:ebay_account_id and status in (2,11) and is_delete=0',array(':ebay_account_id'=>$account));
            $ebayListings = UebModel::model('EbayListing')->findAllByPk($listingIds);
            if(!empty($ebayListings))
            {
                set_time_limit(6600);
                foreach($ebayListings as $ebayListing)
                {
                    try{
                        $this->actionOne($ebayListing);
                    }catch(Exception $e){
                        continue;
                    }
                }
            }
        }else{
            $ebayAccounts = UebModel::model('EbayAccount')->getEbayAccountList();
            if(!empty($ebayAccounts)){
                foreach ($ebayAccounts as $id=>$val){
                    MHelper::runThreadSOCKET('/services/ebay/ebayadditem/additem/account/'.$id);
                    sleep(2);
                }
            }else{
                die('there are no any account!');
            }
        }
    }*/


/*    public function actionIndex()
    {
        if(isset($_GET['handle']))
        {
            $logFile = "log/additem_{$_GET['handle']}.log";
            file_put_contents($logFile,'========================================='.PHP_EOL,FILE_APPEND);
            file_put_contents($logFile,'时间'.date('Y-m-d H:i:s').'  start'.PHP_EOL,FILE_APPEND);
            set_time_limit(590);
            $this->startTime = time();
            $this->cycleListing($logFile);
        }
        else
        {
            $ebayAccounts = array_column(VHelper::selectAsArray('Ebay','id','status=1 and platform like "ebay%"'),'id');
            $handleModel = UebModel::model('EbayRunHandle');
            $runInfo = VHelper::selectAsArray($handleModel,'ebay_account_id,listing_count');
            if(empty($runInfo))
            {
                $insertAccounts = $ebayAccounts;
                $runAccounts = array();
            }
            else
            {
                $runAccounts = array_column($runInfo,'ebay_account_id');
                $insertAccounts = array_diff($ebayAccounts,$runAccounts);
                $intersection = array_intersect($ebayAccounts,$runAccounts);
            }
            if(!empty($intersection))
            {
                $maxListingCount = max(array_column($runInfo,'listing_count'));
                UebModel::model('EbayRunHandle')->updateAll(['status'=>1,'listing_count'=>$maxListingCount],'status=2 and ebay_account_id in ('.implode(',',$intersection).')');
            }
            if(!empty($insertAccounts))
            {
                $insertSql = 'insert into '.$handleModel->tableName().' values ';
                $listingCount = isset($maxListingCount) ? $maxListingCount:(empty($runAccounts) ? 'default': '"'.max(array_column($runInfo,'listing_count')).'"');
                $insertFlag = false;
                $updateInvalid = array();
                foreach($insertAccounts as $insertAccount)
                {
                    if(!in_array($insertAccount,$runAccounts))
                    {
                        $insertSql .='(default,'.$insertAccount.','.$listingCount.',default),';
                        $insertFlag = true;
                    }
                    else
                    {
                        $updateInvalid[] = $insertAccount;
                    }
                }
                if(!empty($updateInvalid))
                {
                    UebModel::model('EbayRunHandle')->updateAll(['status'=>2],'status=1 and ebay_account_id in ('.implode(',',$updateInvalid).')');
                }
                if($insertFlag)
                {
                    $insertSql = trim($insertSql,',');
                    $handleModel->getDbConnection()->createCommand($insertSql)->execute();
                }
            }
            $socketNum = 3;
            while($socketNum > 0)
            {
                MHelper::runThreadSOCKET('/services/ebay/ebayadditem/index/handle/'.$socketNum);
                $socketNum--;
                sleep(2);
            }
        }
    }

    protected function cycleListing($logFile)
    {
        $handleModel = UebModel::model('EbayRunHandle')->find(array(
            'order' => 'listing_count ASC',
            'condition' => 'status=1',
        ));
        $handleModel->listing_count += 1;
        $handleModel->save();
        if(array_key_exists($handleModel->ebay_account_id,$this->cycleAccount))
        {
            //账号循环一圈，如果没有要刊登的数据，脚本结局
            if(!in_array(true,$this->cycleAccount))
                Yii::app()->end();
        }
        file_put_contents($logFile,'时间'.date('Y-m-d H:i:s').'   handleId:'.$handleModel->id.PHP_EOL,FILE_APPEND);
        $nowDate = date('Y-m-d H:i:s');
        $listingInfo = VHelper::selectAsArray('EbayListing','id','ebay_account_id='.$handleModel->ebay_account_id.' and status in (2,11) and is_delete=0 and (listing_status=0 or (listing_status=1 and TIMESTAMPDIFF(SECOND,listing_date,"'.$nowDate.'")>200))',false,'','',10);
        if(!empty($listingInfo))
        {
            $listingIds = array_column($listingInfo,'id');
            //锁数据
            UebModel::model('EbayListing')->updateByPk($listingIds,['listing_status'=>1,'listing_date'=>$nowDate]);
            $idMaps = array_column($listingInfo,'id','id');
            //记录账号是否还有要刊登的数据
            if(count($listingIds) == 10)
            {
                $this->cycleAccount[$handleModel->ebay_account_id] = UebModel::model('EbayListing')->exists('ebay_account_id='.$handleModel->ebay_account_id.' and status in (2,11) and is_delete=0 and (listing_status=0 or (listing_status=1 and TIMESTAMPDIFF(SECOND,listing_date,"'.$nowDate.'")>200))');
            }
            else
            {
                $this->cycleAccount[$handleModel->ebay_account_id] = false;
            }
            $listingModels = UebModel::model('EbayListing')->findAllByPk($listingIds);
            foreach ($listingModels as $listingModel)
            {
                if(time() - $this->startTime > 540)
                {
                    //没刊登完的解锁
                    UebModel::model('EbayListing')->updateByPk(array_values($idMaps),['listing_status'=>0]);
                    Yii::app()->end();
                }
                else
                {
                    try{
                        $this->actionOne($listingModel);
                        file_put_contents($logFile,'时间'.date('Y-m-d H:i:s').'   listingId:'.$listingModel->id.PHP_EOL.'成功。',FILE_APPEND);
                    }catch(Exception $e){
                        echo $e->getMessage();
                        file_put_contents($logFile,'时间'.date('Y-m-d H:i:s').'   listingId:'.$listingModel->id.PHP_EOL.'失败。失败原因：'.$e->getMessage(),FILE_APPEND);
                    }
                    unset($idMaps[$listingModel->id]);
                }
            }
            unset($idMaps,$listingIds,$listingModels,$listingModel);
        }
        else
        {
            $this->cycleAccount[$handleModel->ebay_account_id] = false;
        }
        unset($handleModel,$nowDate,$listingInfo);
        if(time() - $this->startTime < 540)
            $this->cycleListing($logFile);
    }*/

    public function actionRun()
    {
        $taskName = EbayRunHandleByRemainder::TASK_ADD_ITEM;
        $socketNum = 10;
        if(isset($_GET['line']))
        {
            set_time_limit(600);
            $startRunTime = time();
            $remainder = (int)$_GET['line'];
            if(UebModel::model('EbayRunHandleByRemainder')->checkDie($taskName,$remainder,$socketNum))
            {
                exit('checkDie');
            }
            else
            {
                $ebayRunHandleByRemainderModel = UebModel::model('EbayRunHandleByRemainder')->find('task_name=:task_name and remainder=:remainder',array(':task_name'=>$taskName,':remainder'=>$remainder));
                if(empty($ebayRunHandleByRemainderModel))
                {
                    $ebayRunHandleByRemainderModel = new EbayRunHandleByRemainder();
                }
                else
                {
                    if($ebayRunHandleByRemainderModel->force_die == 1)
                    {
                        exit('进程开始时发现强制停止进程指令，强制结束进程。');
                    }
                }
                $ebayRunHandleByRemainderModel->task_name = $taskName;
                $ebayRunHandleByRemainderModel->remainder = $remainder;
                $ebayRunHandleByRemainderModel->alive_time = date('Y-m-d H:i:s');
                $ebayRunHandleByRemainderModel->socket_num = $socketNum;
                $ebayRunHandleByRemainderModel->save();
                $scheduleTime = time()-600;
                $ebayListingModels = UebModel::model('EbayListing')->findAll(array(
                    'condition' => 'id%'.$socketNum.'='.$remainder.' and status in (2,11) and is_delete=0 and (schedule_time=0 or schedule_time<'.$scheduleTime.')',
                    'order'     => 'schedule_time desc, opration_date ASC',
                    'limit'     => 500
                ));
                if(!empty($ebayListingModels))
                {
                    foreach($ebayListingModels as $ebayListingModel)
                    {
                        //超过9分钟停止，nginx限制
                        if(time() - $startRunTime > 360)
                           exit('进程超过6分钟。');
                        echo $ebayListingModel->id,'<br/>';
                        try{
                            $this->actionOne($ebayListingModel);
                        }catch(Exception $e){
                            echo $e->getMessage().PHP_EOL;
                        }
                        $ebayRunHandleByRemainderModel = UebModel::model('EbayRunHandleByRemainder')->find('task_name=:task_name and remainder=:remainder',array(':task_name'=>$taskName,':remainder'=>$remainder));
                        if($ebayRunHandleByRemainderModel->force_die == 1 || $ebayRunHandleByRemainderModel->socket_num != $socketNum)
                        {
                            exit('接受指令或进程数修改，强制结束进程。');
                        }
                        $ebayRunHandleByRemainderModel->updateByPk($ebayRunHandleByRemainderModel->id,['alive_time'=>date('Y-m-d H:i:s')]);
                    }
                }
                exit('Done');
            }
        }
        else
        {
            $ebayRunHandleByRemainderModels = UebModel::model('EbayRunHandleByRemainder')->findAll('task_name=:task_name',array(':task_name'=>$taskName));
            //判断线程数和数据库记录的线程数是否一致，如果不一致，说明修改了线程数，做相应处理
            if(!empty($ebayRunHandleByRemainderModels) && $ebayRunHandleByRemainderModels[0]->socket_num != $socketNum)
            {
                echo '修改线程数。'.PHP_EOL;
                $hasAlive = false; //是否有活线程的标志
                foreach($ebayRunHandleByRemainderModels as $ebayRunHandleByRemainderModel)
                {
                    if(time() - strtotime($ebayRunHandleByRemainderModel->alive_time) < 240)
                    {
                        $hasAlive = true;
                        $ebayRunHandleByRemainderModel->updateByPk($ebayRunHandleByRemainderModel->id,['force_die'=>1]);
                        break;
                    }
                }
                if($hasAlive)
                {
                    echo '还有活线程'.PHP_EOL;
                    exit;
                }
                else
                {
                    EbayRunHandleByRemainder::model()->deleteAll('task_name=:task_name',array(':task_name'=>$taskName));
                }
            }
            while($socketNum > 0)
            {
                $socketNum--;
                MHelper::runThreadSOCKET('/services/ebay/ebayadditem/run/line/'.$socketNum);
                sleep(2);
            }
            exit('Done');
        }
    }

    public function actionOne($id)
    {
        $addItemModel = new EbayAddItem($id);
        $addItemModel->listing();
        echo '刊登一条','<br/>';
    }

    public function actionText()
    {
        $id = Yii::app()->request->getParam('id');
        $addItemModel = new EbayAddItem();
        $addItemModel->test = true;
        $addItemModel->init($id);
        echo htmlspecialchars($addItemModel->requestXmlBody());
//        echo $addItemModel->requestXmlBody();
    }

    public function actionTest($sku)
    {
        $imageModel = new Productimage();
        $imageList = $imageModel->getFtLists($sku);
        echo '<pre>';
        var_dump($imageList);
        echo '<hr/>';
        foreach($imageList as $k=>$v)
        {
            $v = VHelper::decreaseImageMemorySize(ltrim($v,'/'));
            $v = '/'.$v;
            echo $k,'----',$v,'----',ImageUploadTools::uploadImageUrl($sku,$v,null,Platform::CODE_EBAY),'<br/>';
        }
    }
}