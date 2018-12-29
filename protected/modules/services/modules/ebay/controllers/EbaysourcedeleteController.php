<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/10/18 0018
 * Time: 上午 10:16
 */
class EbaysourcedeleteController extends UebController
{
    public function actionReplaceresource()
    {
        set_time_limit(0);
        $socketNum = 200;
        if(isset($_GET['line']))
        {
            $idRange = ['start'=>129601,'end'=>137400];
//            $idRange = ['start'=>129601,'end'=>137400];
            $startTime = time();
            $line = $_GET['line'];
//            $line = 222746;
            $logPath = 'log/replaceresource_'.$line.'.log';
            file_put_contents($logPath,'====================================='.PHP_EOL.'startTime:'.date('Y-m-d H:i:s').PHP_EOL,FILE_APPEND);
            $data = (new Ebayonlinelisting())->getDbConnection()->createCommand("select * from ueb_ebay_variation_temp where api_status=0 and id%{$socketNum}={$line} and (id BETWEEN {$idRange['start']} and {$idRange['end']}) limit 300")->queryAll();
//            file_put_contents($logPath,$line.PHP_EOL,FILE_APPEND);
//            $data = (new Ebayonlinelisting())->getDbConnection()->createCommand("select * from ueb_ebay_variation_temp where id={$line}")->queryAll();
            file_put_contents($logPath,'查询数据个数：'.count($data).PHP_EOL,FILE_APPEND);
            $tokens = [];
//            $count = 1;
            foreach($data as $row)
            {
                if(time() - $startTime > 580)
                {
                    exit('已到10分钟。');
                }
                file_put_contents($logPath,'----------------------------------'.PHP_EOL.'ID:'.$row['id'].' ItemID:'.$row['itemid'].PHP_EOL,FILE_APPEND);
                $api = new TradingAPI();
                if(!isset($tokens[$row['account']]))
                {
                    $tokens[$row['account']] = (new Ebay())->find('user_name=:user_name',array(':user_name'=>$row['account']))->user_token;
                }
                $api->setUserToken($tokens[$row['account']]);
                $api->xmlTagArray = [
                    'GetItemRequest'=>[
                        'DetailLevel'=>'ReturnAll',
                        'ErrorLanguage'=>'en_US',
                        'WarningLevel'=>'High',
//                'IncludeItemSpecifics'=>'true',
//                'IncludeWatchCount'=>'true',
                        'ItemID'=>$row['itemid'],
                    ],
                ];
//                echo htmlspecialchars($api->requestXmlBody());exit;
                $HKResponse = $api->sendViaHK();
                if($HKResponse['ack'] != 'Success')
                {
                    (new Ebayonlinelisting())->getDbConnection()->createCommand("update ueb_ebay_variation_temp set api_status=1,error_info='拉取item时香港服务器报错:{$HKResponse['error']}' where id={$row['id']}")->execute();
                    break;
                }
                file_put_contents($logPath,'getItem HK ACK:Success'.PHP_EOL,FILE_APPEND);
                $response = simplexml_load_string($HKResponse['response']);
//                findClass($response,1);
//                echo '<hr/>';
//                echo $row['itemid'],'------',$count,'<br/>';
//                echo $count++;
                if(time() - $startTime > 580)
                {
                    exit('已到10分钟。');
                }
                switch($response->Ack->__toString()) {
                    case 'Warning':
                    case 'Success':
                        file_put_contents($logPath,'getItem api ACK:Success'.PHP_EOL,FILE_APPEND);
                        $listingStatus = $response->Item->SellingStatus->ListingStatus->__toString();
                        echo $listingStatus,'<br/>';
                        file_put_contents($logPath,'getItem api listingStatus:'.$listingStatus.PHP_EOL,FILE_APPEND);
                        if($listingStatus != 'Active')
                        {
                            (new Ebayonlinelisting())->getDbConnection()->createCommand("update ueb_ebay_variation_temp set api_status=1,error_info='状态:{$listingStatus}' where id={$row['id']}")->execute();
                            break;
                        }
                        $egdApi = new TradingAPI();
                        file_put_contents($logPath,'new TradingAPI():Revise'.PHP_EOL,FILE_APPEND);
                        $egdApi->setUserToken($tokens[$row['account']]);
                        $pictureDetails = [
                            'PictureSource' => 'EPS',
                        ];
                        if (isset($response->Item->PictureDetails->GalleryDuration))
                        {
                            $pictureDetails['GalleryDuration'] = $response->Item->PictureDetails->GalleryDuration->__toString();
                        }
                        if(isset($response->Item->PictureDetails->GalleryType))
                        {
                            $pictureDetails['GalleryType'] = $response->Item->PictureDetails->GalleryType->__toString();
                        }
                        if(isset($response->Item->PictureDetails->GalleryType->PhotoDisplay))
                        {
                            $pictureDetails['PhotoDisplay'] = $response->Item->PictureDetails->GalleryType->PhotoDisplay->__toString();
                        }
                        foreach ($response->Item->PictureDetails->PictureURL as $pictureURL)
                        {
                            $pictureURL = $pictureURL->__toString();
                            if(strpos($pictureURL,'https://i.ebayimg.com') === 0)
                            {
                                $pictureDetails['PictureURL'][] = $pictureURL;
                            }
                            elseif(strpos($pictureURL,'http://i.ebayimg.com') === 0)
                            {
                                $pictureDetails['PictureURL'][] = str_replace('http://','https://',$pictureURL);
                            }
                            else
                            {
                                (new Ebayonlinelisting())->getDbConnection()->createCommand("update ueb_ebay_variation_temp set need_replace=1 where id={$row['id']}")->execute();
                                $replaceObj = new ReplaceThirdResource();
                                $replaceObj->savePlace = 'USA';
                                $replaceObj->subject = $pictureURL;
                                $pictureDetails['PictureURL'][] = $replaceObj->replaceLink();
                            }
                        }
                        file_put_contents($logPath,'组装主SKU图片'.PHP_EOL,FILE_APPEND);
                        $replaceObj = new ReplaceThirdResource();
                        $replaceObj->savePlace = 'USA';
                        $replaceObj->errorLogPath = $logPath;
                        $replaceObj->subject = $response->Item->Description->__toString();
                        $description = $replaceObj->replace();
                        if($replaceObj->getHasReplace())
                        {
                            (new Ebayonlinelisting())->getDbConnection()->createCommand("update ueb_ebay_variation_temp set need_replace=1 where id={$row['id']}")->execute();
                        }
                        file_put_contents($logPath,'下载描述资源'.PHP_EOL,FILE_APPEND);
                        if (isset($response->Item->Variations)) {
                            file_put_contents($logPath,'多属性'.PHP_EOL,FILE_APPEND);
                            $egdApi->xmlTagArray = [
                                'ReviseFixedPriceItemRequest' => [
                                    'Item' => [
                                        'ItemID' => $response->Item->ItemID->__toString(),
                                        'Description'=>"<![CDATA[{$description}]]>",
                                        'PrimaryCategory' => [
                                            'CategoryID' => $response->Item->PrimaryCategory->CategoryID->__toString(),
                                        ],
                                        'PictureDetails' => $pictureDetails,
                                        /*'Variations'=>[
                                            'Pictures'=>[
                                                'VariationSpecificName'=>$response->Item->Variations->Pictures->VariationSpecificName->__toString(),
                                            ]
                                        ],*/
                                    ]
                                ],
                            ];
                            if(!empty($response->Item->Variations->Pictures->VariationSpecificName))
                            {
                                $egdApi->xmlTagArray['ReviseFixedPriceItemRequest']['Item']['Variations']['Pictures']['VariationSpecificName'] = '<![CDATA['.$response->Item->Variations->Pictures->VariationSpecificName->__toString().']]>';
                            }
                            foreach ($response->Item->Variations->Variation as $variation) {
                                $egdApi->xmlTagArray['ReviseFixedPriceItemRequest']['Item']['Variations']['Variation'][] = ['Quantity' => $variation->Quantity->__toString() - $variation->SellingStatus->QuantitySold->__toString(),
                                    'SKU' => $variation->SKU->__toString(),
                                    'StartPrice' => $variation->StartPrice->__toString()
                                ];
                            }
                            foreach($response->Item->Variations->Pictures->VariationSpecificPictureSet as $variationSpecificPictureSet)
                            {
                                $pictureSet = [
                                    'VariationSpecificValue'=>'<![CDATA['.$variationSpecificPictureSet->VariationSpecificValue->__toString().']]>'
                                ];
                                foreach($variationSpecificPictureSet->PictureURL as $childPictureURL)
                                {
                                    $childPictureURL = $childPictureURL->__toString();
                                    if(strpos($childPictureURL,'https://i.ebayimg.com') === 0)
                                    {
                                        $pictureSet['PictureURL'][] = $childPictureURL;
                                    }
                                    elseif(strpos($childPictureURL,'http://i.ebayimg.com') === 0)
                                    {
                                        $pictureSet['PictureURL'][] = str_replace('http://','https://',$childPictureURL);
                                    }
                                    else
                                    {
                                        (new Ebayonlinelisting())->getDbConnection()->createCommand("update ueb_ebay_variation_temp set need_replace=1 where id={$row['id']}")->execute();
                                        $replaceObj = new ReplaceThirdResource();
                                        $replaceObj->subject = $childPictureURL;
                                        $pictureSet['PictureURL'][] = $replaceObj->replaceLink();
                                    }
                                }
                                $egdApi->xmlTagArray['ReviseFixedPriceItemRequest']['Item']['Variations']['Pictures']['VariationSpecificPictureSet'][] = $pictureSet;
                            }
                        } else {
                            file_put_contents($logPath,'单属性'.PHP_EOL,FILE_APPEND);
                            $listingType = $response->Item->ListingType->__toString();
                            switch($listingType)
                            {
                                case 'FixedPriceItem':
                                    $egdApi->xmlTagArray = [
                                        'ReviseFixedPriceItemRequest' => [
                                            'Item' => [
                                                'ItemID' => $row['itemid'],
                                                'Description'=>"<![CDATA[{$description}]]>",
                                                'PrimaryCategory' => [
                                                    'CategoryID' => $response->Item->PrimaryCategory->CategoryID->__toString(),
                                                ],
                                                'StartPrice' => $response->Item->StartPrice->__toString(),
                                                'Quantity' => $response->Item->Quantity->__toString() - $response->Item->SellingStatus->QuantitySold->__toString(),
                                                'PictureDetails'=>$pictureDetails,
                                            ]
                                        ],
                                    ];
                                    break;
                                case 'Chinese':
                                    $egdApi->xmlTagArray = [
                                        'ReviseItemRequest' => [
                                            'Item' => [
                                                'ItemID' => $row['itemid'],
                                                'Description'=>"<![CDATA[{$description}]]>",
                                                'PrimaryCategory' => [
                                                    'CategoryID' => $response->Item->PrimaryCategory->CategoryID->__toString(),
                                                ],
                                                'PictureDetails'=>$pictureDetails,
                                            ]
                                        ],
                                    ];
                                    break;
                                default:
                                    (new Ebayonlinelisting())->getDbConnection()->createCommand("update ueb_ebay_variation_temp set api_status=1,error_info=CONCAT_WS('----',error_info,'ListingType:{$listingType}') where id={$row['id']}")->execute();
                                    break 2;
                            }
                        }
                    if(time() - $startTime > 580)
                    {
                        exit('已到10分钟。');
                    }
                        echo htmlspecialchars($egdApi->requestXmlBody());exit;
                    file_put_contents($logPath,'发送Revise api'.PHP_EOL,FILE_APPEND);
                        $HKEgdResponse = $egdApi->sendViaHK();
//                        findClass($HKEgdResponse,1);
                        if($HKEgdResponse['ack'] != 'Success')
                        {
                            (new Ebayonlinelisting())->getDbConnection()->createCommand("update ueb_ebay_variation_temp set api_status=1,error_info=CONCAT_WS('----',error_info,'提交时香港服务器报错:{$HKEgdResponse['error']}') where id={$row['id']}")->execute();
                            break;
                        }
                    file_put_contents($logPath,'发送Revise HK ack:Success'.PHP_EOL,FILE_APPEND);
                        $edgApiResponse = simplexml_load_string($HKEgdResponse['response']);
//                        findClass($edgApiResponse,1);
                    file_put_contents($logPath,'发送Revise api ack:'.$edgApiResponse->Ack->__toString().PHP_EOL,FILE_APPEND);
                        switch ($edgApiResponse->Ack->__toString()) {
                            case 'Warning':
                                $errorInfo = '提交后有警告：'.$edgApiResponse->Errors->asXML();
                                $errorInfo = addslashes($errorInfo);
                                (new Ebayonlinelisting())->getDbConnection()->createCommand("update ueb_ebay_variation_temp set api_status=2,error_info=CONCAT_WS('----',error_info,'{$errorInfo}') where id={$row['id']}")->execute();
                                break;
                            case 'Success':
                                (new Ebayonlinelisting())->getDbConnection()->createCommand("update ueb_ebay_variation_temp set api_status=2 where id={$row['id']}")->execute();
                                break;
                            case 'Failure':
                                $errorInfo = '提交后有错误：'.$edgApiResponse->Errors->asXML();
                                $errorInfo = addslashes($errorInfo);
                                (new Ebayonlinelisting())->getDbConnection()->createCommand("update ueb_ebay_variation_temp set api_status=1,error_info=CONCAT_WS('----',error_info,'{$errorInfo}') where id={$row['id']}")->execute();
                                break;
                            default:
                                (new Ebayonlinelisting())->getDbConnection()->createCommand("update ueb_ebay_variation_temp set api_status=1,error_info=CONCAT_WS('----',error_info,'提交不返回ACK') where id={$row['id']}")->execute();
                        }
                        break;
                    case 'Failure':
                        $errorXml = $response->Errors->asXML();
                        (new Ebayonlinelisting())->getDbConnection()->createCommand("update ueb_ebay_variation_temp set api_status=1,error_info='拉取Item时ACK:Failure.{$errorXml}' where id={$row['id']}")->execute();
                        break;
                    default:
                        (new Ebayonlinelisting())->getDbConnection()->createCommand("update ueb_ebay_variation_temp set api_status=1,error_info='拉取item时无ACK' where id={$row['id']}")->execute();
                }
            }
            echo '总时间：',time()-$startTime,'<br/>';
            exit('DONE');
        }
        else
        {
            while($socketNum > 0)
            {
                $socketNum--;
//                MHelper::runThreadSOCKET('/services/ebay/ebaysourcedelete/replaceresource/line'.$socketNum.'/day/'.$day);
                MHelper::runThreadSOCKET('/services/ebay/ebaysourcedelete/replaceresource/line/'.$socketNum);
                sleep(2);
            }
            exit('Done');
        }
    }

    public function actionHandlelog()
    {
        //UTF-8  GBK
        //UTF-8  GB2312  ISO-8859-1 US-ASCII UTF-16
        set_time_limit(0);
        $files = scandir('log');
        foreach ($files as $file)
        {
            if(preg_match('/replaceresource_\d+\.log/',$file))
            {
                $contents = file_get_contents('log/'.$file);
                if(!empty($contents))
                {
                    $lineContents = explode('=====================================',$contents);
                    foreach($lineContents as $lineContent)
                    {
                        $idContents = explode('----------------------------------',$lineContent);
                        $ids = '';
                        foreach ($idContents as $idContent)
                        {
                            if(strpos($idContent,'资源文件推送美国服务器不成功') !== false)
                            {
                                if(preg_match('/ID:\d+/',$idContent,$match))
                                {
                                    $id = trim(substr($match[0],3));
                                    $ids .= ','.$id;
                                }
                            }
                        }
                        if($ids !== '')
                        {
                            $ids = trim($ids,',');
                            (new Ebayonlinelisting())->getDbConnection()->createCommand("insert into ueb_ebay_id_collect VALUES (default,'{$ids}');")->execute();
                        }
                    }
                }
            }
        }
        exit('Done');
    }

}