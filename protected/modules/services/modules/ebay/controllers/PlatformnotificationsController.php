<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/8/11 0011
 * Time: 下午 6:29
 */
class PlatformnotificationsController extends UebController
{
    public function actionRunsalesschemetask()
    {
        $taskName = EbayRunHandleByRemainder::TASK_SALES_SCHEME;
        $socketNum = 3;
        if(isset($_GET['handle']))
        {
            $remainder = (int)$_GET['handle'];
            if(UebModel::model('EbayRunHandleByRemainder')->checkAlive($taskName,$remainder))
            {
                exit('上一次线程未结束');
            }
            else
            {
                $ebayRunHandleByRemainderModel = UebModel::model('EbayRunHandleByRemainder')->find('task_name=:task_name and remainder=:remainder',array(':task_name'=>$taskName,':remainder'=>$remainder));
                if(empty($ebayRunHandleByRemainderModel))
                {
                    $ebayRunHandleByRemainderModel = new EbayRunHandleByRemainder();
                    $ebayRunHandleByRemainderModel->task_name = $taskName;
                    $ebayRunHandleByRemainderModel->remainder = $remainder;
                    $ebayRunHandleByRemainderModel->alive_time = date('Y-m-d H:i:s');
                }
                else
                    $ebayRunHandleByRemainderModel->alive_time = date('Y-m-d H:i:s');
                $ebayRunHandleByRemainderModel->save();
                $ebaySalesSchemeTaskModels = UebModel::model('EbaySalesSchemeTask')->findAll(array(
                    'condition' => 'id%'.$socketNum.'='.$remainder.' and status in (0,1) and enable=1 and condition_establishment=1',
                    'order'     => 'create_time ASC',
                ));
                if(!empty($ebaySalesSchemeTaskModels))
                {
                    foreach($ebaySalesSchemeTaskModels as $ebaySalesSchemeTaskModel)
                    {
                        $ebayRunHandleByRemainderModel->alive_time = date('Y-m-d H:i:s');
                        $ebayRunHandleByRemainderModel->save();
                        try{
                            $ebaySalesSchemeTaskModel->execTask();
                        }catch(Exception $e){
                            echo $e->getMessage().PHP_EOL;
                        }
                    }
                }
                exit('Done');
            }
        }
        else
        {
            $ebayRunHandleByRemainderModels = EbayRunHandleByRemainder::model()->findAll('task_name=:task_name',array(':task_name'=>$taskName));
            //判断线程数和数据库记录的线程数是否一致，如果不一致，说明修改了线程数，做相应处理
            if(!empty($ebayRunHandleByRemainderModels) && count($ebayRunHandleByRemainderModels) != $socketNum)
            {
                echo '修改线程数。'.PHP_EOL;
                $hasAlive = false; //是否有活线程的标志
                foreach($ebayRunHandleByRemainderModels as $ebayRunHandleByRemainderModel)
                {
                    if(time() - strtotime($ebayRunHandleByRemainderModel->alive_time) < 180)
                    {
                        $hasAlive = true;
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
                MHelper::runThreadSOCKET('/services/ebay/platformnotifications/runsalesschemetask/handle/'.$socketNum);
                sleep(2);
            }
            exit('Done');
        }
    }

    /*public function actionItemsold()
    {
        $get = file_get_contents('php://input');
        if(!is_string($get))
            $get = var_export($get);
        $get .= PHP_EOL.'---------------'.PHP_EOL;
        file_put_contents('F:\testContent\itemsold.xml',$get,FILE_APPEND);
        header('HTTP/1.0 200 OK');
    }*/

    public function actionFixedpricetransaction()
    {
        $get = file_get_contents('php://input');
//        file_put_contents('log/transactionNotes.xml',$get,FILE_APPEND);
//        header('HTTP/1.0 200 OK');
//        exit;
        $simXml = simplexml_load_string($get);
        try{
            $contentXml = $simXml->children('soapenv',true)->Body->children('urn:ebay:apis:eBLBaseComponents');
//            $itemTransactionsXml = $simXml->children('soapenv',true)->Body->children('urn:ebay:apis:eBLBaseComponents')->GetItemTransactionsResponse;
            $contentName = $contentXml->getName();
        }catch(Exception $e) {
            header('HTTP/1.0 400 Bad Request');
            exit;
        }
        switch($contentName)
        {
            case 'GetItemTransactionsResponse':
                $itemTransactionsXml = $contentXml->GetItemTransactionsResponse;
                $itemXml = $itemTransactionsXml->Item;
                if(isset($itemXml->Seller->UserID))
                    $accountName = trim($itemXml->Seller->UserID->__toString());
                else
                {
                    header('HTTP/1.0 400 Bad Request');
                    exit;
                }
                if(isset($itemTransactionsXml->ExternalUserData))
                    $accountId = trim($itemTransactionsXml->ExternalUserData->__toString());
                else
                {
                    header('HTTP/1.0 400 Bad Request');
                    exit;
                }
                $itemId = trim($itemXml->ItemID->__toString());
                try{
                    if(!UebModel::model('Ebay')->exists('id=:id and user_name=:user_name',array(':id'=>$accountId,':user_name'=>$accountName)))
                    {
                        header('HTTP/1.0 403 Forbidden');
                        file_put_contents('log/FixedPriceTransactionNotification.log',date('Y-m-d H:i:s').' Account:'.$accountName.' ItemID:'.$itemId.' error:'.'账号ID和账号名称不对应。'.PHP_EOL,FILE_APPEND);
                        exit;
                    }
                    $ebaySalesSchemeInfo = VHelper::selectAsArray('EbayListingMapSalesScheme','id,sales_scheme_id',"item_id='{$itemId}'",true);
                    if(!empty($ebaySalesSchemeInfo))
                    {
                        $quantityPurchased = [];
                        foreach($itemTransactionsXml->TransactionArray->Transaction as $transaction)
                        {
                            if(isset($transaction->Variation))
                            {
                                $sku = $transaction->Variation->SKU->__toString();
                                if(isset($quantityPurchased[$sku]))
                                    $quantityPurchased['Variation'][$sku]['QuantityPurchased'] += (int)$transaction->QuantityPurchased->__toString();
                                else
                                    $quantityPurchased['Variation'][$sku]['QuantityPurchased'] = (int)$transaction->QuantityPurchased->__toString();
                            }
                            else
                            {
                                if(isset($quantityPurchased['QuantityPurchased']))
                                    $quantityPurchased['QuantityPurchased'] += (int)$transaction->QuantityPurchased->__toString();
                                else
                                    $quantityPurchased['QuantityPurchased'] = (int)$transaction->QuantityPurchased->__toString();
                            }
                        }
                        foreach($ebaySalesSchemeInfo as $ebaySalesSchemeItem)
                        {
                            $ebaySalesSchemeTaskModel = new EbaySalesSchemeTask();
                            $ebaySalesSchemeTaskModel->listing_map_sales_scheme_id = $ebaySalesSchemeItem['id'];
                            $ebaySalesSchemeTaskModel->sales_scheme_id = $ebaySalesSchemeItem['sales_scheme_id'];
                            $ebaySalesSchemeTaskModel->item_id = $itemId;
                            $ebaySalesSchemeTaskModel->account_id = $accountId;
                            $ebaySalesSchemeTaskModel->info = serialize($quantityPurchased);
                            $ebaySalesSchemeTaskModel->create_time = time();
                            $ebaySalesSchemeTaskModel->status = 0;
                            $ebaySalesSchemeTaskModel->save();
                        }
                    }
                }catch(Exception $e){
                    file_put_contents('log/FixedPriceTransactionNotification.log',date('Y-m-d H:i:s').' Account:'.$accountName.' ItemID:'.$itemId.' error:'.$e->getMessage().PHP_EOL,FILE_APPEND);
                }
                break;
            case 'GetItemResponse':
                $itemXmlObj = $contentXml->GetItemResponse;
                $notificationEventName = $itemXmlObj->NotificationEventName->__toString();
                $itemId = $itemXmlObj->Item->ItemID->__toString();
                $path = 'upload/ebay_notification/'.$notificationEventName.'/useful/'.date('Y/m/d');
                if(!is_dir($path))
                {
                    mkdir($path,0777,true);
                }
                $path = $path.'/'.uniqid().'.xml';
                $model = new EbayItemNotification();
                date_default_timezone_set('Asia/Shanghai');
                $model->update_time = strtotime($itemXmlObj->Timestamp->__toString());
                $model->item_id = $itemId;
                $model->event = $notificationEventName;
                $model->account_id = $itemXmlObj->ExternalUserData->__toString();
                if($contentXml->asXML($path))
                {
                    $model->path = $path;
                    chmod($path,0777);
                    $model->status = EbayItemNotification::STATUS_NOTIFICATION_SUCCESS;
                }
                else
                {
                    $model->upload_error = '保存XML失败';
                    $model->status = EbayItemNotification::STATUS_NOTIFICATION_FAIL;
                }
                $model->save();
                break;
            default:
                file_put_contents('log/kefu.xml',$get,FILE_APPEND);
        }
        header('HTTP/1.0 200 OK');
    }

    public function actionFixedpricetransaction1()
    {
        $get = file_get_contents('php://input');
        file_put_contents('log/kefu.log',$get,FILEAPPEND);
exit;
        $simXml = simplexml_load_string($get);

        try{
            $contentXml = $simXml->children('soapenv',true)->Body->children('urn:ebay:apis:eBLBaseComponents');
//            $itemTransactionsXml = $simXml->children('soapenv',true)->Body->children('urn:ebay:apis:eBLBaseComponents')->GetItemTransactionsResponse;
            $contentName = $contentXml->getName();
        }catch(Exception $e) {
            if(filesize('log/kefu1.xml') < 1024*1024*10)
            {
                file_put_contents('log/kefu1.xml',$get,FILE_APPEND);
            }
            header('HTTP/1.0 400 Bad Request');
            exit;
        }
        if(filesize('log/kefu1.xml') < 1024*1024*10)
        {
            file_put_contents('log/kefu1.xml',$contentName,FILE_APPEND);
        }
        file_put_contents('log/kefu1.xml','<br>',FILE_APPEND);
        if($contentName != 'GetItemTransactionsResponse' && $contentName != 'GetItemResponse')
        {
            if(filesize('log/kefu.xml') < 1024*1024*10)
                file_put_contents('log/kefu.xml',$get,FILEAPPEND);
        }

       /* switch($contentName)
        {
            case 'GetItemTransactionsResponse':
                $itemTransactionsXml = $contentXml->GetItemTransactionsResponse;
                $itemXml = $itemTransactionsXml->Item;
                if(isset($itemXml->Seller->UserID))
                    $accountName = trim($itemXml->Seller->UserID->__toString());
                else
                {
                    header('HTTP/1.0 400 Bad Request');
                    exit;
                }
                if(isset($itemTransactionsXml->ExternalUserData))
                    $accountId = trim($itemTransactionsXml->ExternalUserData->__toString());
                else
                {
                    header('HTTP/1.0 400 Bad Request');
                    exit;
                }
                $itemId = trim($itemXml->ItemID->__toString());
                try{
                    if(!UebModel::model('Ebay')->exists('id=:id and user_name=:user_name',array(':id'=>$accountId,':user_name'=>$accountName)))
                    {
                        header('HTTP/1.0 403 Forbidden');
                        file_put_contents('log/FixedPriceTransactionNotification.log',date('Y-m-d H:i:s').' Account:'.$accountName.' ItemID:'.$itemId.' error:'.'账号ID和账号名称不对应。'.PHP_EOL,FILE_APPEND);
                        exit;
                    }
                    $ebaySalesSchemeInfo = VHelper::selectAsArray('EbayListingMapSalesScheme','id,sales_scheme_id',"item_id='{$itemId}'",true);
                    if(!empty($ebaySalesSchemeInfo))
                    {
                        $quantityPurchased = [];
                        foreach($itemTransactionsXml->TransactionArray->Transaction as $transaction)
                        {
                            if(isset($transaction->Variation))
                            {
                                $sku = $transaction->Variation->SKU->__toString();
                                if(isset($quantityPurchased[$sku]))
                                    $quantityPurchased['Variation'][$sku]['QuantityPurchased'] += (int)$transaction->QuantityPurchased->__toString();
                                else
                                    $quantityPurchased['Variation'][$sku]['QuantityPurchased'] = (int)$transaction->QuantityPurchased->__toString();
                            }
                            else
                            {
                                if(isset($quantityPurchased['QuantityPurchased']))
                                    $quantityPurchased['QuantityPurchased'] += (int)$transaction->QuantityPurchased->__toString();
                                else
                                    $quantityPurchased['QuantityPurchased'] = (int)$transaction->QuantityPurchased->__toString();
                            }
                        }
                        foreach($ebaySalesSchemeInfo as $ebaySalesSchemeItem)
                        {
                            $ebaySalesSchemeTaskModel = new EbaySalesSchemeTask();
                            $ebaySalesSchemeTaskModel->listing_map_sales_scheme_id = $ebaySalesSchemeItem['id'];
                            $ebaySalesSchemeTaskModel->sales_scheme_id = $ebaySalesSchemeItem['sales_scheme_id'];
                            $ebaySalesSchemeTaskModel->item_id = $itemId;
                            $ebaySalesSchemeTaskModel->account_id = $accountId;
                            $ebaySalesSchemeTaskModel->info = serialize($quantityPurchased);
                            $ebaySalesSchemeTaskModel->create_time = time();
                            $ebaySalesSchemeTaskModel->status = 0;
                            $ebaySalesSchemeTaskModel->save();
                        }
                    }
                }catch(Exception $e){
                    file_put_contents('log/FixedPriceTransactionNotification.log',date('Y-m-d H:i:s').' Account:'.$accountName.' ItemID:'.$itemId.' error:'.$e->getMessage().PHP_EOL,FILE_APPEND);
                }
                break;
            case 'GetItemResponse':
                $itemXmlObj = $contentXml->GetItemResponse;
                $notificationEventName = $itemXmlObj->NotificationEventName->__toString();
                $itemId = $itemXmlObj->Item->ItemID->__toString();
                $path = 'upload/ebay_notification/'.$notificationEventName.'/useful/'.date('Y/m/d');
                if(!is_dir($path))
                {
                    mkdir($path,0777,true);
                }
                $path = $path.'/'.uniqid().'.xml';
                $model = new EbayItemNotification();
                date_default_timezone_set('Asia/Shanghai');
                $model->update_time = strtotime($itemXmlObj->Timestamp->__toString());
                $model->item_id = $itemId;
                $model->event = $notificationEventName;
                $model->account_id = $itemXmlObj->ExternalUserData->__toString();
                if($contentXml->asXML($path))
                {
                    $model->path = $path;
                    chmod($path,0777);
                    $model->status = EbayItemNotification::STATUS_NOTIFICATION_SUCCESS;
                }
                else
                {
                    $model->upload_error = '保存XML失败';
                    $model->status = EbayItemNotification::STATUS_NOTIFICATION_FAIL;
                }
                $model->save();
                break;
            default:
                file_put_contents('log/kefu.xml',$get,FILE_APPEND);
        }*/
//        header('HTTP/1.0 200 OK');
    }

    public function actionGetnotificationpreferences()
    {
        set_time_limit(7200);
        $accountModels = UebModel::model('Ebay')->findAll();
        foreach ($accountModels as $accountModel)
        {
            $api = new TradingAPI();
            $api->setUserToken($accountModel->user_token);
            $api->xmlTagArray = [
                'GetNotificationPreferencesRequest'=>[
                    'PreferenceLevel'=>'Application'
                ]
            ];
            $response = $api->send()->response;
            $ebayNotificationPreferences = UebModel::model('EbayNotificationPreferences')->find('account_id='.$accountModel->id);
            if(empty($ebayNotificationPreferences))
                $ebayNotificationPreferences = new EbayNotificationPreferences();
            if(isset($response->Ack) && in_array($response->Ack,array('Success','Warning')))
            {
                $ebayNotificationPreferences->account_id = $accountModel->id;
                $applicationDeliveryPreferences = $response->ApplicationDeliveryPreferences;
                $ebayNotificationPreferences->application_url = isset($applicationDeliveryPreferences->ApplicationURL) ? $applicationDeliveryPreferences->ApplicationURL:'';
                $ebayNotificationPreferences->application_enable = $applicationDeliveryPreferences->ApplicationEnable;
                $ebayNotificationPreferences->alert_email = isset($applicationDeliveryPreferences->AlertEmail) ? $applicationDeliveryPreferences->AlertEmail:'';
                $ebayNotificationPreferences->alert_enable = $applicationDeliveryPreferences->AlertEnable;
                $ebayNotificationPreferences->device_type = $applicationDeliveryPreferences->DeviceType;
                $ebayNotificationPreferences->payload_encoding_type = $applicationDeliveryPreferences->PayloadEncodingType;
                $ebayNotificationPreferences->payload_version = $applicationDeliveryPreferences->PayloadVersion;
                $ebayNotificationPreferences->update_time = date('Y-m-d H:i:s');
                $ebayNotificationPreferences->save();
            }
            $api->xmlTagArray = [
                'GetNotificationPreferencesRequest'=>[
                    'PreferenceLevel'=>'UserData'
                ]
            ];
            $response = $api->send()->response;
            if(isset($response->Ack) && in_array($response->Ack,array('Success','Warning')))
            {
                $ebayNotificationPreferences->external_user_data = isset($response->UserData->ExternalUserData) ? $response->UserData->ExternalUserData : '';
                $ebayNotificationPreferences->save();
            }
            $api->xmlTagArray = [
                'GetNotificationPreferencesRequest'=>[
                    'PreferenceLevel'=>'User'
                ]
            ];
            $response = $api->send()->response;
            if(isset($response->Ack) && in_array($response->Ack,array('Success','Warning')))
            {
                $notificationEnables = $response->UserDeliveryPreferenceArray->NotificationEnable;
                UebModel::model('EbayNotificationPreferencesEvent')->deleteAll('notification_preferences_id='.$ebayNotificationPreferences->id);
                if(!empty($notificationEnables))
                {
                    foreach ($notificationEnables as $notificationEnable)
                    {
                        $ebayNotificationPreferencesEvent = UebModel::model('EbayNotificationPreferencesEvent')->find('notification_preferences_id='.$ebayNotificationPreferences->id.' and event_type=:event_type',array(':event_type'=>$notificationEnable->EventType));
                        if(empty($ebayNotificationPreferencesEvent))
                            $ebayNotificationPreferencesEvent = new EbayNotificationPreferencesEvent();
                        $ebayNotificationPreferencesEvent->notification_preferences_id = $ebayNotificationPreferences->id;
                        $ebayNotificationPreferencesEvent->event_type = $notificationEnable->EventType;
                        $ebayNotificationPreferencesEvent->event_enable = $notificationEnable->EventEnable;
                        $ebayNotificationPreferencesEvent->save();
                    }
                }
            }
            echo $accountModel->user_name,'<br/>';
            ob_flush();
            flush();
        }
        exit('DONE');
    }

    public function actionSetnotification()
    {
        set_time_limit(5400);
        $accountModels = UebModel::model('Ebay')->findAll('status=1');
        $ebayNotificationPreferences = array_column(VHelper::selectAsArray('EbayNotificationPreferences','*'),null,'account_id');
        $applicationURL = EbayNotificationPreferences::APPLICATION_URL;
        $notificationEnable = [
            [
                'EventType'=>'FixedPriceTransaction',
                'EventEnable'=>'Enable'
            ],
            [
                'EventType'=>'ItemExtended',
                'EventEnable'=>'Enable'
            ],
            [
                'EventType'=>'ItemListed',
                'EventEnable'=>'Enable'
            ],
            [
                'EventType'=>'ItemLost',
                'EventEnable'=>'Enable'
            ],
            [
                'EventType'=>'ItemMarkedPaid',
                'EventEnable'=>'Enable'
            ],
            [
                'EventType'=>'ItemOutOfStock',
                'EventEnable'=>'Enable'
            ],
            [
                'EventType'=>'ItemRevised',
                'EventEnable'=>'Enable'
            ],
            [
                'EventType'=>'ItemClosed',
                'EventEnable'=>'Enable'
            ],
            [
                'EventType'=>'ItemSold',
                'EventEnable'=>'Enable'
            ],
            [
                'EventType'=>'ItemUnsold',
                'EventEnable'=>'Enable'
            ],
            [
                'EventType'=>'ItemWon',
                'EventEnable'=>'Enable'
            ],
        ];
        $allowEvent = array_column($notificationEnable,'EventEnable','EventType');
        foreach($accountModels as $accountModel)
        {
            if($ebayNotificationPreferences[$accountModel->id]['application_enable'] == 'Enable' && $ebayNotificationPreferences[$accountModel->id]['application_url'] == $applicationURL)
            {
                $ebayNotificationPreferencesEvents = array_column(VHelper::selectAsArray('EbayNotificationPreferencesEvent','event_type,event_enable','notification_preferences_id='.$ebayNotificationPreferences[$accountModel->id]['id']),'event_enable','event_type');
                if(empty(array_diff_assoc($allowEvent,$ebayNotificationPreferencesEvents)))
                {
                    echo $accountModel->user_name,'----','已经设置好。';
                    ob_flush();
                    flush();
                    continue;
                }
            }
            $api = new TradingAPI();
            $api->setUserToken($accountModel->user_token);
            $api->xmlTagArray = [
                'SetNotificationPreferencesRequest'=>[
                    'ApplicationDeliveryPreferences'=>[
                        'ApplicationEnable'=>'Enable',
                        'ApplicationURL'=> $applicationURL,
                        'DeviceType'=> 'Platform',
                    ],
                    'UserData'=>[
                        'ExternalUserData'=>$accountModel->id,
                    ],
                    'UserDeliveryPreferenceArray'=>[
                        'NotificationEnable'=>$notificationEnable
                    ]
                ]
            ];
            $response = $api->send()->response;
            echo $accountModel->user_name,'-----';
            if(isset($response->Ack) && in_array($response->Ack,array('Success','Warning')))
            {
                echo 'Success';
            }
            else
            {
                echo 'Failure';
            }
            echo '<br/>';
            ob_flush();
            flush();
        }
        exit('DONE');
    }

   public function actionTest()
   {
       $userToken = 'AgAAAA**AQAAAA**aAAAAA**DQUTWA**nY+sHZ2PrBmdj6wVnY+sEZ2PrA2dj6wNlIWnD5SKoQSdj6x9nY+seQ**sHQDAA**AAMAAA**sHq6OnnChkaUzhje5FMVNrGtB3fr/WtJQrZ6rODBSPtwJm8AOERfGOhogH5fftI0V8mBJXYPq6s7ZbtkySsYKKOHzfrxQhZgsRU3Zmv9gDgRCQUeP/PyBwCkGmDOPnmn3sUoPOnvFSw11yguKGrE53u2wed6ECqLRE1NvMwDgunABK+0BLZRU4LY7VtsnpHy3vgo4HwbZGYClfnns9AHAh+cOIEh1aeG56MX+e8ax099JQgb3PkL1c69S+ce1jDZW7eiDHyRgrb0RVIsVkI7E+d60wwHzyMZ8CXyrDVS0+L8MU3K7BT24rhgKYfuSJ6F6oqkT5X3rqi1e1xoRDD6H/ootnskP+4BeelUwaoPONtit6ftQiL28+PAqK5VRLpz7vKALkxCH/3cyY6r1co4GIrRrx4KaDV2UPC2uuIrZ0uHziyNf956I1+eYOcQPZcsClYy43NpvbI5Oo5oJ2FGLlNpyhMKfL2g/QMoPQ5QWE1CqTGxHG5tNrQIDt3s5tq6g/V6vV8WXe1tFFJ3cLAQhOlIvw5R1YYDqgMcHQnc8TosCaB/y8rVYPhwm55D+swPEnC7piNgAu08b2BdQ9g40NURJMxdH8uonKhRY/YVc/4qiIiA2cmkpIW8HAkws+waHXgYIWaIVLBn5Ht/F8K4SAYLqVelsIjHZwsQI2AyssVto3HNESlL2L1xsV/Ii1URtOPKh4DhpxyPxKcwH57uR2eOfRX1U6DLVIQHWMWkZx939QVmB/oMTFIdCPyYcmEz';
       $api = new TradingAPI();
       $api->setUserToken($userToken);
       $notificationEnable = [
           [
               'EventType'=>'FixedPriceTransaction',
               'EventEnable'=>'Enable'
           ],
           [
               'EventType'=>'ItemExtended',
               'EventEnable'=>'Enable'
           ],
           [
               'EventType'=>'ItemListed',
               'EventEnable'=>'Enable'
           ],
           [
               'EventType'=>'ItemLost',
               'EventEnable'=>'Enable'
           ],
           [
               'EventType'=>'ItemMarkedPaid',
               'EventEnable'=>'Enable'
           ],
           [
               'EventType'=>'ItemOutOfStock',
               'EventEnable'=>'Enable'
           ],
           [
               'EventType'=>'ItemRevised',
               'EventEnable'=>'Enable'
           ],
           [
               'EventType'=>'ItemSold',
               'EventEnable'=>'Enable'
           ],
           [
               'EventType'=>'ItemUnsold',
               'EventEnable'=>'Enable'
           ],
           [
               'EventType'=>'ItemWon',
               'EventEnable'=>'Enable'
           ],
       ];
       $api->xmlTagArray = [
           'SetNotificationPreferencesRequest'=>[
               'ApplicationDeliveryPreferences'=>[
                   'ApplicationEnable'=>'Enable',
                   'ApplicationURL'=> 'http://47.90.106.87/ebayNotice.php',
                   'DeviceType'=> 'Platform',
               ],
               'UserData'=>[
                   'ExternalUserData'=>6,
               ],
               'UserDeliveryPreferenceArray'=>[
                   'NotificationEnable'=>$notificationEnable
               ]
           ]
       ];
       $response = $api->send()->response;
       findClass($response,1);
   }
}