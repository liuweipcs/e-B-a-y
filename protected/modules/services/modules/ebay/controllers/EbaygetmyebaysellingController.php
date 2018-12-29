<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/20 0020
 * Time: 下午 3:32
 */
class EbaygetmyebaysellingController extends UebController
{
    public function actionSellingsummary()
    {
        $taskName = EbayRunHandleByRemainder::TASK_SELLING_SUMMARY;
        $socketNum = 2;
        if(isset($_GET['line']))
        {
            $startRunTime = time();
            $remainder = (int)$_GET['line'];
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
                $ebayAccountModels = UebModel::model('Ebay')->findAll(array(
                    'condition' => 'id%'.$socketNum.'='.$remainder.' and status=1',
                ));
                if(!empty($ebayAccountModels))
                {
                    foreach($ebayAccountModels as $ebayAccountModel)
                    {
                        //超过9分钟停止，nginx限制
                        if(time() - $startRunTime > 580)
                        {
                            echo '超过nginx限制时间。';
                            break;
                        }
                        $taskModel = new EbayApiTask();
                        $taskModel->account_id = $ebayAccountModel->id;
                        $taskModel->task_name = $taskName;
                        $taskModel->start_time = date('Y-m-d H:i:s');
                        $taskModel->execute_time = date('Y-m-d H:i:s');
                        try{
                            $this->actionSellingsummaryone($ebayAccountModel);
                            $taskModel->task_status = 2;
                        }catch(Exception $e){
                            echo $e->getMessage().PHP_EOL;
                            $taskModel->task_status = -1;
                            $taskModel->error = $e->getMessage();
                        }
                        $taskModel->end_time = date('Y-m-d H:i:s');
                        $taskModel->complete_time = date('Y-m-d H:i:s');
                        $taskModel->save();
                        $ebayRunHandleByRemainderModel = UebModel::model('EbayRunHandleByRemainder')->find('task_name=:task_name and remainder=:remainder',array(':task_name'=>$taskName,':remainder'=>$remainder));
                        if($ebayRunHandleByRemainderModel->force_die == 1)
                        {
                            exit('接受指令，强制结束进程。');
                        }
                        $ebayRunHandleByRemainderModel->alive_time = date('Y-m-d H:i:s');
                        $ebayRunHandleByRemainderModel->save();
                    }
                }
                echo time()-$startRunTime;
                exit('Done');
            }
        }
        else
        {
            $ebayRunHandleByRemainderModels = UebModel::model('EbayRunHandleByRemainder')->findAll('task_name=:task_name',array(':task_name'=>$taskName));
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
                        $ebayRunHandleByRemainderModel->force_die = 1;
                        $ebayRunHandleByRemainderModel->save();
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
                MHelper::runThreadSOCKET('/services/ebay/ebaygetmyebayselling/sellingsummary/line/'.$socketNum);
                sleep(2);
            }
            exit('Done');
        }

    }

    public function actionSellingsummaryone($id)
    {
        if(is_numeric($id))
        {
            $model = (new Ebay())->findByPk((int)$id);
        }
        else
        {
            $model = $id;
        }
        if($model instanceof SystemsModel)
        {
            set_time_limit(120);
            $api = new TradingAPI();
            $api->setSiteId(201);
            $api->setUserToken($model->user_token);
            $api->xmlTagArray = [
                'GetMyeBaySellingRequest'=>[
                    'SellingSummary'=>[
                        'Include'=>'true'
                    ],
                ]
            ];
            $response = $api->send()->response;
            if(empty($response))
                throw new Exception('无返回值。');
            switch($response->Ack)
            {
                case 'Warning':
                case 'Success':
                    $accountRemainingModel = (new EbayAccountRemaining())->find('account_id='.$model->id);
                    if(empty($accountRemainingModel))
                        $accountRemainingModel = new EbayAccountRemaining();
                    $accountRemainingModel->account_id = $model->id;
                    $accountRemainingModel->active_auction_count = isset($response->Summary->ActiveAuctionCount)?$response->Summary->ActiveAuctionCount->__toString():0;
                    $accountRemainingModel->amount_limit_remaining = isset($response->Summary->AmountLimitRemaining)?$response->Summary->AmountLimitRemaining->__toString():0;
    //            findClass($response->Summary->AmountLimitRemaining->attributes(),1,0);
                    $accountRemainingModel->amount_limit_remaining_currency = isset($response->Summary->AmountLimitRemaining)?$response->Summary->AmountLimitRemaining->attributes()['currencyID']->__toString():'';
                    $accountRemainingModel->quantity_limit_remaining = isset($response->Summary->QuantityLimitRemaining)?$response->Summary->QuantityLimitRemaining->__toString():0;
                    $accountRemainingModel->auction_bid_count = isset($response->Summary->AuctionBidCount)?$response->Summary->AuctionBidCount->__toString():0;
                    $accountRemainingModel->auction_selling_count = isset($response->Summary->AuctionSellingCount)?$response->Summary->AuctionSellingCount->__toString():0;
                    $accountRemainingModel->classified_ad_count = isset($response->Summary->ClassifiedAdCount)?$response->Summary->ClassifiedAdCount->__toString():0;
                    $accountRemainingModel->classified_ad_offer_count = isset($response->Summary->ClassifiedAdOfferCount)?$response->Summary->ClassifiedAdOfferCount->__toString():0;
                    $accountRemainingModel->sold_duration_in_days = isset($response->Summary->SoldDurationInDays)?$response->Summary->SoldDurationInDays->__toString():0;
                    $accountRemainingModel->total_auction_selling_value = isset($response->Summary->TotalAuctionSellingValue)?$response->Summary->TotalAuctionSellingValue->__toString():0;
                    $accountRemainingModel->total_auction_selling_value_currency = isset($response->Summary->TotalAuctionSellingValue)?$response->Summary->TotalAuctionSellingValue->attributes()['currencyID']->__toString():'';
                    $accountRemainingModel->total_lead_count = isset($response->Summary->TotalLeadCount)?$response->Summary->TotalLeadCount->__toString():0;
                    $accountRemainingModel->total_listings_with_leads = isset($response->Summary->TotalListingsWithLeads)?$response->Summary->TotalListingsWithLeads->__toString():0;
                    $accountRemainingModel->total_sold_count = isset($response->Summary->TotalSoldCount)?$response->Summary->TotalSoldCount->__toString():0;
                    $accountRemainingModel->total_sold_value = isset($response->Summary->TotalSoldValue)?$response->Summary->TotalSoldValue->__toString():0;
                    $accountRemainingModel->total_sold_value_currency = isset($response->Summary->TotalSoldValue)?$response->Summary->TotalSoldValue->attributes()['currencyID']->__toString():'';
                    $accountRemainingModel->update_summary_time = time();
                    if(!$accountRemainingModel->save())
                        throw new Exception('保存数据库失败。');
                    break;
                case 'Failure':
                    throw new Exception('ACK:Failure.');
                    break;
                default:
                    throw new Exception('ACK不为Success、Warning、Failure.');
            }
        }
        else
        {
            throw new Exception('找不到Ebay账号。');
        }
    }

    public function actionTest()
    {
        findClass((new EbayAccountRemaining())->attributeNames(),1);
        set_time_limit(120);
        $user_token = 'AgAAAA**AQAAAA**aAAAAA**mLbNWQ**nY+sHZ2PrBmdj6wVnY+sEZ2PrA2dj6AClYCjC5WApA+dj6x9nY+seQ**sHQDAA**AAMAAA**qfQAd92iyEpvSJB/MxNBX0B4vNeyhGcDd+OfQ9UxTX8pykP0sl1F7qx9IIfir8uMEfoRFDDTykL3wDwD4H4w34Yku712aSRJWdzU2tLdD3X8qjGh6KcfK+jud3/oX54nXu6orqBCfLTwCZmMP7lAsbTd4sJg1wZG86aY8e6xPwiZZ7xwUMuDR+htBrWS93uC+y74khalXOSp1NooE44mSU0zwVouimIkuCkW1cYLVWa1SAOaV0cR2Fwa8Pe8KnOY4/mTTlfBhqB6GV0zyzemifPz4YIU2Zji55IPA9KE3SU9pEAxBJOly3LXFrumvoohmQiJnI+je8UAe4FmBHFBGKBS8UBw0ssPhCrALpWQNbzkXOdSST1Zil+sxzcjLa+0MUWoCGZald+uLvRV8UWosVrknMAPGDoo/QKoZ5rpmMKeM528dGTvw5rMJpwa1i/fmOePl4D9Z8gpQku0hNv2D6hXmfEUIrV3lorqJfeRC3HEqmsDl5ZPnFBg5Eq9GReQI45zXKOQmTmDO/Ru1w0ApiQvg6xMJwijlbfUDDh6cvbT/r5D/n4GRcuEr36oAv+s2wII1hkqPpyU1TMv6ElXsxLPmvPl20JgtqsoOTEdF5NIljfvjbn/K+5+HzBPT17YIBVloPI/xWAcgCpzXUiBBIsQg/irNwF+T0fgW1rWEuB5hShsNWY/QKlN2HJpPDD2ytpnUq4iYteG3GXBORAfAYcvzbRrEre97awx9MqN8eLDJ5Q5++FOZalJbLGsZ6tx';
//        $user_token = 'AgAAAA**AQAAAA**aAAAAA**WMFIWQ**nY+sHZ2PrBmdj6wVnY+sEZ2PrA2dj6ACkoGjCpSBoAudj6x9nY+seQ**sHQDAA**AAMAAA**7/6HAsLDQVzdSvnCxbTzDBz3AxCn3xeP/GYF4VRI3Xt003KxtUdDdZWkz1B/TfcadPD4Sez+iqlpctT3gVQTNUyBHfPV67yoCdeEjkuus7G4mGVJsNbHv1+o4omWnBPPv0yIJnK9pKCviRLC89rjGq3yUXlajvaLPfotKp3NDm7QjJjq0u75Cm9o2N8SAr5OPl5uj3IolYApNYzuwabi6toap3DDyjcUPh3f8UBM5hVcdIRVER3OrKEqzg5kLmLQXhb/3G0TZ5ZKGgO63tdM+IuCFLG550PSvs9JyDxnx254imjxixpVZd17vnqGZvuhtalz+2qSiXAiU8C7RSC5W3M0JLVxnqB6xq3gPx5sSaAvWHQsf3o+SGk2DJNk002bBfKfM1Vsqh7jiFj5ehKi/aA8U81NYvi10E3CshzfQ0fVkkFgkzESgLmh8Ka1XJUpi3t5hhfHd7cDPKYx5iTX/jBDnW1tbUHDtquZUIy2zNjf9l2vk0nvSLC9v/gHJQ8Y5xM3tRMNCx87mvkjsdupdVHEnUfAwcgQqcwwgjW1ZfwFf6Q2Pkz2/57QpHiPiY4UIRf236BFACuZRa4qiX3fxg26ePVsHIAJWqjIAbf+lh6PNIntnrIuCI3uWLy0LNE7IdmmPnCTbL3c+E+Vn01V962k64/SQhQ18rj5VH3SdYVDcFPsXVhw2lcR/1MtWp73ByzUnCqvWd5UZ/iiLgTn1ypluMo5Gk6dNKOFvDMuHfdxv+rw2yHVVKw05ddJGwDP';
        $api = new TradingAPI();
        $api->setUserToken($user_token);
        $api->setSiteId(3);
        $api->xmlTagArray = [
            'GetMyeBaySellingRequest'=>[
                'SellingSummary'=>[
                    'Include'=>'true'
                ],
                'DetailLevel'=>'ReturnSummary',
            ]
        ];
        $response = $api->send()->response;
        findClass($response,1);
    }

}