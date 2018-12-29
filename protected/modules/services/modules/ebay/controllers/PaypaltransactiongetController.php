<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/7 0007
 * Time: 上午 9:19
 */
class PaypaltransactiongetController extends UebController
{
    public function actionIndex()
    {
        if(isset($_GET['id']))
        {
            $logPath = 'log/PayPalTransactionSearch'.$_GET['id'].'.log';
            file_put_contents($logPath,'====================='.PHP_EOL.date('Y-m-d H:i:s').PHP_EOL,FILE_APPEND);
            set_time_limit(600);
            $paypalModel = (new PaypalAccount())->findByPk((int)$_GET['id']);
            if(!empty($paypalModel) && !empty($paypalModel->api_user_name) && !empty($paypalModel->api_password) && !empty($paypalModel->api_signature))
            {
                date_default_timezone_set('Asia/Shanghai');
                file_put_contents($logPath,'查出payPalModel:'.date('Y-m-d H:i:s').PHP_EOL,FILE_APPEND);
                if((new EbayApiTask())->taskExists('PayPalTransactionSearch',$paypalModel->id))
                {
                    file_put_contents($logPath,'上一个线程还在运行:'.date('Y-m-d H:i:s').PHP_EOL,FILE_APPEND);
                    exit('上一个线程还在运行。');
                }
                file_put_contents($logPath,'开始运行API:'.date('Y-m-d H:i:s').PHP_EOL,FILE_APPEND);
                $taskModel = new EbayApiTask();
                $taskModel->account_id = $paypalModel->id;
                $taskModel->task_name = 'PayPalTransactionSearch';
                $taskModel->start_time = date('Y-m-d H:i:s');
                $taskModel->task_status = 1;
                $taskModel->execute_time = date('Y-m-d H:i:s');
                $taskModel->save();
//                $startTimeInfo = VHelper::selectAsArray('EbayPaypalTransaction','max(l_timestamp)',"paypal='{$paypalModel->email}'")[0]['max(l_timestamp)'];
                $startTimeInfo = (new EbayPaypalTransactionStarttime())->find("paypal_id={$paypalModel->id}");
                if(empty($startTimeInfo))
                {
//                    $startTime = '2017-10-01T00:00:00Z';
                    $startTime = '2017-01-01T00:00:00Z';
                    $startTimeInfo = new EbayPaypalTransactionStarttime();
                    $startTimeInfo->paypal_id = $paypalModel->id;
                }
                else
                {
                    $startTime = $startTimeInfo->start_time;
                }
                if($startTimeInfo->force_die == 1)
                {
                    $startTimeInfo->save();
                    $taskModel->complete_time = date('Y-m-d H:i:s');
                    $taskModel->end_time = date('Y-m-d H:i:s');
                    $taskModel->task_status = 2;
                    $taskModel->error = '强制结束进程。';
                    $taskModel->save();
                    exit('强制结束进程。');
                }
                file_put_contents($logPath,'获取STARTDATE:'.$startTime.PHP_EOL,FILE_APPEND);
                $api = new PaypalApi();
                $api->setTokenByPayPal($paypalModel);
                $api->data = ['METHOD'=>'TransactionSearch','STARTDATE'=>$startTime,'TRANSACTIONCLASS'=>'All'];
                $taskModel->sendContent = serialize($api->data);
                date_default_timezone_set('Asia/Shanghai');
                if($api->sendHttpRequestMany(3600*12))
                {
                    file_put_contents($logPath,'STARTDATE:'.$api->getSendData()['STARTDATE'].'-----ENDDATE:'.$api->getSendData()['ENDDATE'].PHP_EOL,FILE_APPEND);
                    $handleResult = EbayPaypalTransaction::handleApiResponse(array(
                        'api'=>$api,
                        'paypal'=>$paypalModel->id,
                        'taskModel'=>$taskModel,
                        'startTimeModel'=> $startTimeInfo,
                        'logPath' => $logPath
                    ));
                    file_put_contents($logPath,'handle结果:'.$handleResult['status'].PHP_EOL,FILE_APPEND);
                    switch($handleResult['status'])
                    {
                        case 'error':
                            echo 'error:',$handleResult['errorInfo'],'<br/>';
                            break;
                        case 'warning':
                            echo 'warning:',$handleResult['errorInfo'],'<br/>';
                        case 'success':
                            echo 'success','<br/>';
                            date_default_timezone_set('UTC');
                            if(time() - strtotime($api->data['STARTDATE']) > 600 || count($handleResult['models']) == 100)
                            {
                                MHelper::runThreadSOCKET('/services/ebay/paypaltransactionget/index/id/'.$paypalModel->id);
                                exit('发起下一个线程.');
                            }
                    }
                }
                else
                {
                    file_put_contents($logPath,'STARTDATE:'.$api->getSendData()['STARTDATE'].'-----ENDDATE:'.$api->getSendData()['ENDDATE'].PHP_EOL.'sendHttpRequestMany失败'.PHP_EOL,FILE_APPEND);
                    $taskModel->task_status = -1;
                    $taskModel->end_time = date('Y-m-d H:i:s');
                    $taskModel->complete_time = date('Y-m-d H:i:s');
                    $taskModel->error = 'curl执行失败。';
                    $taskModel->save();
                    file_put_contents($logPath,$taskModel->error.PHP_EOL,FILE_APPEND);
                }
                exit('DONE');
            }
            else
            {
                file_put_contents($logPath,'账号查不到或者签名等信息为空:'.date('Y-m-d H:i:s').PHP_EOL,FILE_APPEND);
                exit('找不到paypal.');
            }
        }
        else
        {
            set_time_limit(600);
            $paypals = (new PaypalAccount())->findAll();
            foreach($paypals as $paypal)
            {
                MHelper::runThreadSOCKET('/services/ebay/paypaltransactionget/index/id/'.$paypal->id);
                sleep(2);
            }
            exit('Done');
        }
    }

    public function actionUpdate()
    {
        $socketNum = 5;
        if(isset($_GET['line']))
        {
            $logPath = 'log/PayPalTransactionUpdate'.$_GET['line'].'.log';
            set_time_limit(300);
            date_default_timezone_set('Asia/Shanghai');
            $startRunTime = time();
            $line = $_GET['line'];
            $models = (new EbayPaypalTransaction())->findAll(array(
                'condition'=>'id%'.$socketNum.'='.$line.' and l_status in ("Pending","Processing","Uncleared")',
                'order'=> 'repeat_num ASC',
                'limit'=> '200',
            ));
            $paypalModels = array();
            file_put_contents($logPath,'待更新个数:'.count($models),FILE_APPEND);
            foreach($models as $model)
            {
                date_default_timezone_set('Asia/Shanghai');
                if(time() - $startRunTime > 290)
                {
                    exit('已经运行5分钟。');
                }
                $model->repeat_num += 1;
                $model->save();
                file_put_contents($logPath,'更新ID:'.$model->id,FILE_APPEND);
                if(!isset($paypalModels[$model->paypal]))
                {
                    $paypalModels[$model->paypal] = (new PaypalAccount())->findByPk((int)$model->paypal);
                }
                $api = new PaypalApi();
                $api->setTokenByPayPal($paypalModels[$model->paypal]);
                $api->data = ['METHOD'=>'TransactionSearch','STARTDATE'=>$model->l_timestamp,'ENDDATE'=>$model->l_timestamp,'TRANSACTIONCLASS'=>'All'];
//                $taskModel->sendContent = serialize($api->data);
//                date_default_timezone_set('Asia/Shanghai');
                if($api->sendHttpRequest())
                {
                    file_put_contents($logPath,'sendHttpRequest成功:'.date('Y-m-d H:i:s'),FILE_APPEND);
                    $handleResult = EbayPaypalTransaction::handleApiResponse(array(
                        'api'=>$api,
                        'paypal'=>$model->paypal,
//                        'taskModel'=>$taskModel,
//                        'startTimeModel'=> $startTimeInfo,
//                        'logPath' => $logPath
                    ));
                    switch($handleResult['status'])
                    {
                        case 'error':
                            (new EbayPaypalTransaction())->updateByPk($model->id,['repeat_update_status'=>'error','repeat_update_error'=>$handleResult['errorInfo']]);
                            echo 'error:',$handleResult['errorInfo'],'<br/>';
                            break;
                        case 'warning':
                            (new EbayPaypalTransaction())->updateByPk($model->id,['repeat_update_status'=>'warning','repeat_update_error'=>$handleResult['errorInfo']]);
                            echo 'warning:',$handleResult['errorInfo'],'<br/>';
                        case 'success':
                            (new EbayPaypalTransaction())->updateByPk($model->id,['repeat_update_status'=>'success','repeat_update_error'=>$handleResult['errorInfo']]);
                            echo 'success','<br/>';
                    }
                }
            }
            exit('DONE');
        }
        else
        {
            while ($socketNum > 0)
            {
                $socketNum--;
                MHelper::runThreadSOCKET('/services/ebay/paypaltransactionget/update/line/'.$socketNum);
                sleep(2);
            }
            exit('DONE');
        }
    }

    public function actionUpdatebyid($id)
    {
        $model = (new EbayPaypalTransaction())->findByPk((int)$id);
        if(!isset($paypalModels[$model->paypal]))
        {
            $paypalModels[$model->paypal] = (new PaypalAccount())->findByPk((int)$model->paypal);
        }
        $api = new PaypalApi();
        $api->setTokenByPayPal($paypalModels[$model->paypal]);
        $api->data = ['METHOD'=>'TransactionSearch','STARTDATE'=>$model->l_timestamp,'ENDDATE'=>$model->l_timestamp,'TRANSACTIONCLASS'=>'All'];
//                $taskModel->sendContent = serialize($api->data);
//                date_default_timezone_set('Asia/Shanghai');
        if($api->sendHttpRequest())
        {
            echo '<pre>';
            var_dump($api->getResponse());
            $handleResult = EbayPaypalTransaction::handleApiResponse(array(
                'api'=>$api,
                'paypal'=>$model->paypal,
//                        'taskModel'=>$taskModel,
//                        'startTimeModel'=> $startTimeInfo,
//                        'logPath' => $logPath
            ));
            switch($handleResult['status'])
            {
                case 'error':
                    (new EbayPaypalTransaction())->updateByPk($model->id,['repeat_update_status'=>'error','repeat_update_error'=>$handleResult['errorInfo']]);
                    echo 'error:',$handleResult['errorInfo'],'<br/>';
                    break;
                case 'warning':
                    (new EbayPaypalTransaction())->updateByPk($model->id,['repeat_update_status'=>'warning','repeat_update_error'=>$handleResult['errorInfo']]);
                    echo 'warning:',$handleResult['errorInfo'],'<br/>';
                case 'success':
                    (new EbayPaypalTransaction())->updateByPk($model->id,['repeat_update_status'=>'success','repeat_update_error'=>$handleResult['errorInfo']]);
                    echo 'success','<br/>';
            }
        }
    }

    public function actionIndex1()
    {
        if(isset($_GET['id']))
        {
            set_time_limit(600);
            $paypalModel = (new PaypalAccount())->findByPk((int)$_GET['id']);
            if(!empty($paypalModel))
            {
                date_default_timezone_set('Asia/Shanghai');
                if((new EbayApiTask())->taskExists('PayPalTransactionSearch',$paypalModel->id))
                {
                    exit('上一个线程还在运行。');
                }
                $taskModel = new EbayApiTask();
                $taskModel->account_id = $paypalModel->id;
                $taskModel->task_name = 'PayPalTransactionSearch';
                $taskModel->start_time = date('Y-m-d H:i:s');
                $taskModel->task_status = 1;
                $taskModel->execute_time = date('Y-m-d H:i:s');
                $taskModel->save();
//                $startTimeInfo = VHelper::selectAsArray('EbayPaypalTransaction','max(l_timestamp)',"paypal='{$paypalModel->email}'")[0]['max(l_timestamp)'];
                $startTimeInfo = (new EbayPaypalTransactionStarttime())->find("paypal_id={$paypalModel->id}");
                if(empty($startTimeInfo))
                {
//                    $startTime = '2017-11-07T07:00:00Z';
                    $startTime = '2017-06-01T00:00:00Z';
                    $startTimeInfo = new EbayPaypalTransactionStarttime();
                    $startTimeInfo->paypal_id = $paypalModel->id;
                }
                else
                {
                    $startTime = $startTimeInfo->start_time;
                }
                if($startTimeInfo->force_die == 1)
                {
                    $startTimeInfo->save();
                    $taskModel->complete_time = date('Y-m-d H:i:s');
                    $taskModel->end_time = date('Y-m-d H:i:s');
                    $taskModel->task_status = 2;
                    $taskModel->error = '强制结束进程。';
                    $taskModel->save();
                    exit('强制结束进程。');
                }
                $api = new PaypalApi();
                $api->setTokenByPayPal($paypalModel);
                $api->data = ['METHOD'=>'TransactionSearch','STARTDATE'=>$startTime,'TRANSACTIONCLASS'=>'All'];
                $taskModel->sendContent = serialize($api->data);
                date_default_timezone_set('Asia/Shanghai');
                if($api->sendHttpRequestMany(3600*12))
                {
                    $response = $api->getResponse();
                    if($response['ACK'] == 'Success' || $response['ACK'] == 'SuccessWithWarning')
                    {
                        $models = array();
                        $L_TIMESTAMP = array();
                        foreach($response as $k=>$v)
                        {
                            if(preg_match('/\d{1,2}$/',$k,$matchs,PREG_OFFSET_CAPTURE))
                            {
                                $filedIndex = $matchs[0][0];
                                $filedName = substr($k,0,$matchs[0][1]);
                                if(!isset($models[$filedIndex]))
                                {
                                    $models[$filedIndex] = new EbayPaypalTransaction();
                                }
                                if($filedName == 'L_TIMESTAMP')
                                {
                                    $L_TIMESTAMP[] = $v;
                                }
                                $models[$filedIndex]->setValueByApiFiled($filedName,$v);
//                                if(array_key_exists($filedName,EbayPaypalTransaction::$apiFiledMapAttribute))
//                                    $datas[$filedIndex][$filedName] = $v;
                            }
                        }
                        if(!empty($L_TIMESTAMP))
                        {
                            foreach ($models as $model)
                            {
                                $model->paypal = $paypalModel->email;
                                if(! $model->saveUnique())
                                {
                                    if(empty($taskModel->error))
                                    {
                                        $taskModel->error = $model->l_transaction_id.'保存失败。';
                                    }
                                    else
                                    {
                                        $taskModel->error .= $model->l_transaction_id.'保存失败。';
                                    }
                                }
                            }
                            sort($L_TIMESTAMP);
                            echo '$L_TIMESTAMP','<br/>';
                            $startTimeInfo->start_time = array_pop($L_TIMESTAMP);
                        }
                        else
                        {
                            $startTimeInfo->start_time = $api->getSendData()['ENDDATE'];
                            echo 'ENDDATE','<br/>';
                        }
                        $startTimeInfo->save();
                        $taskModel->complete_time = date('Y-m-d H:i:s');
                        $taskModel->end_time = date('Y-m-d H:i:s');
                        $taskModel->task_status = 2;
                        $taskModel->save();
                        date_default_timezone_set('Asia/Shanghai');
                        if(time() - strtotime($api->data['STARTDATE']) > 600 || count($models) == 100)
                        {
                            MHelper::runThreadSOCKET('/services/ebay/paypaltransactionget/index/id/'.$paypalModel->id);
                            exit('发起下一个线程.');
                        }
                    }
                    else
                    {
                        $taskModel->task_status = -1;
                        $taskModel->end_time = date('Y-m-d H:i:s');
                        $taskModel->complete_time = date('Y-m-d H:i:s');
                        $taskModel->error = serialize($response);
                        $taskModel->save();
                    }
                }
                else
                {
                    $taskModel->task_status = -1;
                    $taskModel->end_time = date('Y-m-d H:i:s');
                    $taskModel->complete_time = date('Y-m-d H:i:s');
                    $taskModel->error = 'curl执行失败。';
                    $taskModel->save();
                }
                exit('DONE');
            }
            else
            {
                exit('找不到paypal.');
            }
        }
        else
        {
            $paypals = (new PaypalAccount())->findAll('status=1');
            foreach($paypals as $paypal)
            {
                MHelper::runThreadSOCKET('/services/ebay/paypaltransactionget/index/id/'.$paypal->id);
                sleep(2);
            }
        }
    }
    public function actionTest()
    {
        echo date('Y-m-d\TH:i:s\Z',strtotime('2017-11-01 7:00:00') - 8*60*60);
    }
}