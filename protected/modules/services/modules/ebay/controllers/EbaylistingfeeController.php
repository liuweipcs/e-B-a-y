<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/18 0018
 * Time: 下午 4:32
 */
class EbaylistingfeeController extends UebController
{
    protected $account = array();
    protected $getAccountLog = 'log/ebaylistingfee_getaccount.log';
    protected $pageNumber = 1;

    //根据在线listing拉取佣金
    public function actionGetaccount()
    {
        $socketNum = 20;
        if(isset($_GET['line']))
        {
            set_time_limit(600);
            $startTime = time();
            $line = $_GET['line'];
            $monthDate = EbayOrderItemidGetFee::getDateDue('both','timestamp');
            $onlineListings = (new Ebayonlinelisting())->findAll([
                'select'=>'id,account,itemid,siteid',
                'condition'=>'end_time>="'.$monthDate[0].'" and start_time<"'.$monthDate[1].'" and id%'.$socketNum.'='.$line,
                'limit'=>200,
                'order'=>'get_fee_time ASC'
            ]);
//            $onlineListings = (new Ebayonlinelisting())->findAll('itemid="282668851366"');
            $yearMonth = date('Y-m',$monthDate[0]);
            foreach ($onlineListings as $onlineListing)
            {
                if(time()-$startTime > 580)
                {
                    exit('已到5分钟。');
                }
                $this->pageNumber = 1;
                $this->actionGetaccountone($onlineListing);
                EbayFeeStatistics::saveUnique($yearMonth,$onlineListing->itemid,['siteid'=>$onlineListing->siteid,'account_id'=>$this->account[$onlineListing->account]->id]);
                echo $onlineListing->itemid,'<br/>';
            }
            exit('DONE');
        }
        else
        {
            while($socketNum > 0)
            {
                $socketNum--;
                MHelper::runThreadSOCKET('/services/ebay/ebaylistingfee/getaccount/line/'.$socketNum);
                sleep(2);
            }
            exit('Done');
        }
    }

    public function actionGetaccountone($id)
    {
        if(is_numeric($id))
        {
            $onlineListing = (new Ebayonlinelisting())->findByPk((int)$id);
        }
        else if(is_object($id))
        {
            $onlineListing = $id;
        }
        else
        {
            throw new Exception('参数传入错误。');
        }
        if(!isset($this->account[$onlineListing->account]))
        {
            $this->account[$onlineListing->account] = (new Ebay())->find('user_name=:user_name',array(':user_name'=>$onlineListing->account));
        }
        try{
            $api = new TradingAPI();
            $api->setSiteId($onlineListing->siteid);
            $api->setUserToken($this->account[$onlineListing->account]->user_token);
            $api->xmlTagArray = [
                'GetAccountRequest'=>[
                    'ExcludeBalance'=>'true',
                    'ExcludeSummary'=>'true',
                    'IncludeConversionRate'=>'true',
                    'ItemID'=>$onlineListing->itemid,
                    'OutputSelector'=>[
                        'AccountEntries',
                        'PaginationResult',
                        'EntriesPerPage',
                        'HasMoreEntries',
                        'PageNumber',
                    ],
                    'Pagination'=>[
                        'EntriesPerPage'=>50,
                        'PageNumber'=>$this->pageNumber,
                    ]
                ]
            ];
            $response = $api->send()->response;
        }catch(Exception $e){
            file_put_contents($this->getAccountLog,'Time:'.date('Y-m-d H:i:s').' ItemID:'.$onlineListing->itemid.' 接口PHP错误:'.$e->getMessage().PHP_EOL,FILE_APPEND);
        }
        if(!empty($response))
        {
            switch($response->Ack->__toString())
            {
                case 'Warning':
                    file_put_contents($this->getAccountLog,'Time:'.date('Y-m-d H:i:s').' ItemID:'.$onlineListing->itemid.' 接口返回ACK:Warning。'.PHP_EOL,FILE_APPEND);
                case 'Success':
                    foreach($response->AccountEntries->AccountEntry as $accountEntry)
                    {
                        $saveInfo = EbayListingFee::saveUnique($accountEntry);
                        if(!$saveInfo['flag']){
                            file_put_contents($this->getAccountLog,'Time:'.date('Y-m-d H:i:s').' ItemID:'.$onlineListing->itemid.' RefNumber:'.$accountEntry->RefNumber->__toString().' 保存数据库失败：'.$saveInfo['error'].PHP_EOL,FILE_APPEND);
                        }
                    }
                    if(isset($response->HasMoreEntries) && $response->HasMoreEntries->__toString() == 'true')
                    {
                        $this->pageNumber++;
                        $this->actionGetaccountone($onlineListing);
                    }
                    else if(isset($onlineListing->id))
                    {
                        echo  'id:',$onlineListing->id,'<br/>';
                        try{
                            $updateNum = $onlineListing->updateByPk($onlineListing->id,['get_fee_time'=>time()]);
                            if(!$updateNum)
                            {
                                file_put_contents($this->getAccountLog,'Time:'.date('Y-m-d H:i:s').'onlineListingID:'.$onlineListing->id.' 修改数据库字段get_fee_time失败：'.PHP_EOL,FILE_APPEND);
                                echo 'update 失败';
                            }
                        }
                        catch(Exception $e)
                        {
                            file_put_contents($this->getAccountLog,'Time:'.date('Y-m-d H:i:s').'onlineListingID:'.$onlineListing->id.' 修改数据库字段get_fee_time保存：'.$e->getMessage().PHP_EOL,FILE_APPEND);
                            echo $e->getMessage();
                        }
                    }
                    break;
                case 'Failure':
                    file_put_contents($this->getAccountLog,'Time:'.date('Y-m-d H:i:s').' ItemID:'.$onlineListing->itemid.' 接口返回ACK:Failure。'.PHP_EOL,FILE_APPEND);
                    break;
            }
        }
        else
        {
            file_put_contents($this->getAccountLog,'Time:'.date('Y-m-d H:i:s').' ItemID:'.$onlineListing->itemid.' 接口返回数据为空。'.PHP_EOL,FILE_APPEND);
        }
    }

    //根据订单拉取佣金
    public function actionGetfeebyorder()
    {
        set_time_limit(600);
        $startTime = time();
        $monthDate = EbayOrderItemidGetFee::getDateDue('start');
        $yearMonth = substr($monthDate,0,7);
        $itemIdFeeModels = (new EbayOrderItemidGetFee())->findAll([
            'limit'=>300,
            'condition'=>"year_mouth='{$yearMonth}'"
        ]);
        if(empty($itemIdFeeModels))
        {
            EbayOrderItemidGetFee::getDataInsert();
            $itemIdFeeModels = (new EbayOrderItemidGetFee())->findAll([
                'limit'=>100,
                'condition'=>"year_mouth='{$yearMonth}'"
            ]);
            if(empty($itemIdFeeModels))
            {
                exit('无数据。');
            }
        }
        $accounts = [];
        $sites = [];
        $i = 1;
        foreach($itemIdFeeModels as $itemIdFeeModel)
        {
            if(time()-$startTime > 580)
            {
                exit('已到5分钟。');
            }
            echo $i,'<br/>';
            $i++;
            $onlineListing = (new Ebayonlinelisting())->find('itemid=:itemid',array(':itemid'=>$itemIdFeeModel->item_id));
            if(empty($onlineListing))
            {
                $orderModel = (new OrderEbay())->findByPk($itemIdFeeModel->order_id);
                $onlineListing = new stdClass();
                if(!isset($accounts[$orderModel->account_id]))
                {
                    $accounts[$orderModel->account_id] = (new Ebay())->findByPk((int)$orderModel->account_id);
                }
                $this->account[$accounts[$orderModel->account_id]->user_name] = $accounts[$orderModel->account_id];
                $onlineListing->account = $accounts[$orderModel->account_id]->user_name;
                $onlineListing->itemid = $itemIdFeeModel->item_id;
                if(!isset($sites[$itemIdFeeModel->site]))
                {
                    $sites[$itemIdFeeModel->site] = (new EbaySites())->find(['select'=>'siteid','condition'=>"value='{$itemIdFeeModel->site}'"])->siteid;
                }
                $onlineListing->siteid = $sites[$itemIdFeeModel->site];
            }
            $this->pageNumber = 1;
            $this->actionGetaccountone($onlineListing);
            EbayFeeStatistics::saveUnique($itemIdFeeModel->year_mouth,$itemIdFeeModel->item_id,['siteid'=>$onlineListing->siteid,'account_id'=>$this->account[$onlineListing->account]->id]);
            $itemIdFeeModel->delete();
        }
        exit('DONE');
    }

    //计算跑数据(itemID)
    public function actionFeestatistics()
    {
        set_time_limit(600);
        $socketNumArray = ['night'=>60,'daytime'=>30];
        $day = $_GET['day'];
        if(!array_key_exists($day,$socketNumArray))
        {
            $hour = date('H');
            if($hour > 21 || $hour < 9)
            {
                $day = 'night';//晚上
            }
            else
            {
                $day = 'daytime'; //白天
            }
        }
        $socketNum = $socketNumArray[$day];
        if(isset($_GET['line']))
        {
            $startTime = time();
            $line = $_GET['line'];
            $yearMonth = EbayOrderItemidGetFee::runMonth();
            $yearMonth = empty($yearMonth) ? date('Y-m',strtotime('-2 day') - 8*3600):$yearMonth;
            $models = (new EbayFeeStatistics())->findAll([
                'condition'=>'id%'.$socketNum.'='.$line.' and year_mouth="'.$yearMonth.'"',
                'order'=>'statistics_time ASC',
                'limit'=>1000
            ]);
            foreach($models as $model)
            {
                if(time() - $startTime > 585)
                {
                    exit('已到10分钟。');
                }
                var_dump(is_callable([$model,'statistics']));
                var_dump($model->statistics());
                /*try{
                    $model->statistics();
                }catch(Exception $e)
                {
                    var_dump($e);
                }*/
                echo $model->id,'<br/>';
            }
            exit('DONE');
        }
        else
        {
            while($socketNum > 0)
            {
                $socketNum--;
                MHelper::runThreadSOCKET('/services/ebay/ebaylistingfee/feestatistics/line/'.$socketNum.'/day/'.$day);
                sleep(2);
            }
            exit('Done');
        }
    }
    public function actionFeestatisticsone($id)
    {
        var_dump((new EbayFeeStatistics())->findByPk((int)$id)->statistics());
    }

    //按账号统计获取数据和统计费用
    public function actionStatisticsaccountdata()
    {
        set_time_limit(300);
        try{
            EbayFeeStatisticsByAccount::statisticsGetData();
        }
        catch(Exception $e)
        {
            echo $e->getMessage();
        }
        exit('DONE');
    }

    //按账号统计订单及金额
    public function actionStatisticsaccount()
    {
        set_time_limit(600);
        $startTime = time();
        $models = (new EbayFeeStatisticsByAccount())->findAll(array(
            'condition'=> 'year_mouth="'.EbayFeeStatisticsByAccount::getStatisticsDate().'"',
            'order'=>'statistics_time',
            'limit'=>200,
        ));
        foreach ($models as $model)
        {
            if(time() - $startTime > 580)
            {
                exit('已到10分钟。');
            }
            try{
               $model->statisticsOrder();
            }
            catch(Exception $e)
            {
                echo $e->getMessage(),'<br/>';
            }
        }
        exit('DONE');
    }

    //按站点统计获取数据和统计费用
    public function actionStatisticssitedata()
    {
        set_time_limit(300);
        try{
            EbayFeeStatisticsBySite::statisticsGetData();
        }
        catch(Exception $e)
        {
            echo $e->getMessage();
        }
        exit('DONE');
    }

    //按站点统计订单及金额
    public function actionStatisticssite()
    {
        $socketNum = 3;
        if(isset($_GET['line']))
        {
            set_time_limit(600);
            $line = $_GET['line'];
            $startTime = time();
            $models = (new EbayFeeStatisticsBySite())->findAll(array(
                'condition'=> 'year_mouth="'.EbayFeeStatisticsBySite::getStatisticsDate().'" and id%'.$socketNum.'='.$line,
                'order'=>'statistics_time',
                'limit'=>500,
            ));
            foreach ($models as $model)
            {
                if(time() - $startTime > 580)
                {
                    exit('已到10分钟。');
                }
                try{
                    $model->statisticsOrder();
                }
                catch(Exception $e)
                {
                    echo $e->getMessage(),'<br/>';
                }
            }
            exit('DONE');
        }
        else
        {
            while($socketNum > 0)
            {
                $socketNum--;
                MHelper::runThreadSOCKET('/services/ebay/ebaylistingfee/statisticssite/line/'.$socketNum);
                sleep(2);
            }
            exit('Done');
        }
    }

    //按站点统计获取数据和统计费用
    public function actionStatisticsproductlinedata()
    {
        set_time_limit(300);
        try{
            EbayFeeStatisticsByProductLine::statisticsGetData();
        }
        catch(Exception $e)
        {
            echo $e->getMessage();
        }
        exit('DONE');
    }

    //按产品线统计订单及金额
    public function actionStatisticsproductline()
    {
        $socketNum = 5;
        if(isset($_GET['line']))
        {
            set_time_limit(600);
            $line = $_GET['line'];
            $startTime = time();
            $models = (new EbayFeeStatisticsByProductLine())->findAll(array(
                'condition'=> 'year_mouth="'.EbayFeeStatisticsByProductLine::getStatisticsDate().'" and id%'.$socketNum.'='.$line,
                'order'=>'statistics_time',
                'limit'=>500,
            ));
            foreach ($models as $model)
            {
                if(time() - $startTime > 580)
                {
                    exit('已到10分钟。');
                }
                try{
                    $model->statisticsOrder();
                }
                catch(Exception $e)
                {
                    echo $e->getMessage(),'<br/>';
                }
            }
            exit('DONE');
        }
        else
        {
            while($socketNum > 0)
            {
                $socketNum--;
                MHelper::runThreadSOCKET('/services/ebay/ebaylistingfee/statisticsproductline/line/'.$socketNum);
                sleep(2);
            }
            exit('Done');
        }
    }

    //按大仓统计获取数据和统计费用
    public function actionStatisticswarehousecategorydata()
    {
        set_time_limit(300);
        try{
            EbayFeeStatisticsByWarehouseCategory::statisticsGetData();
        }
        catch(Exception $e)
        {
            echo $e->getMessage();
        }
        exit('DONE');
    }

    //按大仓统计订单及金额
    public function actionStatisticswarehousecategory()
    {
        $socketNum = 5;
        if(isset($_GET['line']))
        {
            set_time_limit(600);
            $line = $_GET['line'];
            $startTime = time();
            $models = (new EbayFeeStatisticsByWarehouseCategory())->findAll(array(
                'condition'=> 'year_mouth="'.EbayFeeStatisticsByWarehouseCategory::getStatisticsDate().'" and id%'.$socketNum.'='.$line,
                'order'=>'statistics_time',
                'limit'=>500,
            ));
            foreach ($models as $model)
            {
                if(time() - $startTime > 580)
                {
                    exit('已到10分钟。');
                }
                try{
                    $model->statisticsOrder();
                }
                catch(Exception $e)
                {
                    echo $e->getMessage(),'<br/>';
                }
            }
            exit('DONE');
        }
        else
        {
            while($socketNum > 0)
            {
                $socketNum--;
                MHelper::runThreadSOCKET('/services/ebay/ebaylistingfee/statisticswarehousecategory/line/'.$socketNum);
                sleep(2);
            }
            exit('Done');
        }
    }

    //按销售员统计获取数据和统计费用
    public function actionStatisticssalespersondata()
    {
        set_time_limit(300);
        try{
            EbayFeeStatisticsBySalesPerson::statisticsGetData();
        }
        catch(Exception $e)
        {
            echo $e->getMessage();
        }
        exit('DONE');
    }

    //按销售员统计订单及金额
    public function actionStatisticssalesperson()
    {
        $socketNum = 5;
        if(isset($_GET['line']))
        {
            set_time_limit(600);
            $line = $_GET['line'];
            $startTime = time();
            $models = (new EbayFeeStatisticsBySalesPerson())->findAll(array(
                'condition'=> 'year_mouth="'.EbayFeeStatisticsBySalesPerson::getStatisticsDate().'" and id%'.$socketNum.'='.$line,
                'order'=>'statistics_time',
                'limit'=>500,
            ));
            foreach ($models as $model)
            {
                if(time() - $startTime > 580)
                {
                    exit('已到10分钟。');
                }
                try{
                    $model->statisticsOrder();
                }
                catch(Exception $e)
                {
                    echo $e->getMessage(),'<br/>';
                }
            }
            exit('DONE');
        }
        else
        {
            while($socketNum > 0)
            {
                $socketNum--;
                MHelper::runThreadSOCKET('/services/ebay/ebaylistingfee/statisticssalesperson/line/'.$socketNum);
                sleep(2);
            }
            exit('Done');
        }
    }

}