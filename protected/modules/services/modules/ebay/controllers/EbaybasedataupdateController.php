<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/10 0010
 * Time: 下午 2:38
 */
class EbaybasedataupdateController extends UebController
{
    public function actionShippingservice()
    {
        set_time_limit(600);
        $startTime = time();
        $siteModels = (new EbaySites())->findAll('is_delete=0');
        foreach($siteModels as $siteModel)
        {
            $siteid = $siteModel->siteid;
            echo '<hr/>',$siteModel->value;
            ob_flush();
            flush();
            $api = new TradingAPI();
            $api->setSiteId($siteid);
            $account = (new Ebay())->find('status=1');
            $api->setUserToken($account->user_token);
            $api->xmlTagArray = [
                'GeteBayDetailsRequest'=>[
                    'DetailName'=>'ShippingServiceDetails'
                ]
            ];
            $response = $api->send()->response;
            switch($response->Ack->__toString())
            {
                case 'Failure':
                    break;
                case 'Warning':
                case 'Success':
                    foreach($response->ShippingServiceDetails as $ShippingServiceDetail)
                    {
                        $shippingService = $ShippingServiceDetail->ShippingService->__toString();
                        $shippingServiceId = $ShippingServiceDetail->ShippingServiceID->__toString();
                        $model = (new EbayShippingService())->find('siteid=:siteid and shipping_service=:shipping_service and shipping_service_id=:shipping_service_id',array(':siteid'=>$siteid,':shipping_service'=>$shippingService,':shipping_service_id'=>$shippingServiceId));
                        if(empty($model))
                            $model = new EbayShippingService();
                        $model->siteid = $siteid;
                        $model->shipping_service = $shippingService;
                        $model->shipping_service_id = $shippingServiceId;
                        if(isset($ShippingServiceDetail->Description))
                            $model->description = $ShippingServiceDetail->Description->__toString();
                        else
                            $model->description = '';
                        if(isset($ShippingServiceDetail->ShippingTimeMax))
                            $model->shipping_time_max = $ShippingServiceDetail->ShippingTimeMax->__toString();
                        else
                            $model->shipping_time_max = -1;
                        if(isset($ShippingServiceDetail->ShippingTimeMin))
                            $model->shipping_time_min = $ShippingServiceDetail->ShippingTimeMin->__toString();
                        else
                            $model->shipping_time_min = -1;
                        $serviceType = (array)$ShippingServiceDetail->ServiceType;
                        sort($serviceType);
                        $model->service_type = implode('|',$serviceType);
                        if(isset($ShippingServiceDetail->ValidForSellingFlow))
                        {
                            $validForSellingFlow = $ShippingServiceDetail->ValidForSellingFlow->__toString();
                            $model->valid_for_selling_flow = $validForSellingFlow == 'true'? 1:0;
                        }
                        else
                        {
                            $model->valid_for_selling_flow = 0;
                        }
                        if(isset($model->shipping_category))
                        {
                            $model->shipping_category = $ShippingServiceDetail->ShippingCategory->__toString();
                        }
                        else
                        {
                            $model->shipping_category = '';
                        }
                        if(isset($model->international_service))
                            $model->international_service = $ShippingServiceDetail->InternationalService->__toString() == 'true'?1:0;
                        else
                            $model->international_service = 0;
                        if(isset($ShippingServiceDetail->ShippingPackage))
                        {
                            $shippingPackage = (array)$ShippingServiceDetail->ShippingPackage;
                            sort($shippingPackage);
                            $model->shipping_package = implode('|',$shippingPackage);
                        }
                        else
                        {
                            $model->shipping_package = '';
                        }
                        if(isset($ShippingServiceDetail->DimensionsRequired))
                            $model->dimensions_required = $ShippingServiceDetail->DimensionsRequired->__toString() == 'true'?1:0;
                        else
                            $model->dimensions_required = 0;
                        if(isset($ShippingServiceDetail->ShippingCarrier))
                            $model->shipping_carrier = $ShippingServiceDetail->ShippingCarrier->__toString();
                        else
                            $model->shipping_carrier = '';
                        $model->shipping_service_package_details_dimensions_required = 0;
                        $model->shipping_service_package_details_name = '';
                        if(isset($ShippingServiceDetail->WeightRequired))
                            $model->weight_required = $ShippingServiceDetail->WeightRequired->__toString() == 'true'?1:0;
                        else
                            $model->weight_required = 0;
                        if(isset($ShippingServiceDetail->SurchargeApplicable))
                            $model->surcharge_applicable = $ShippingServiceDetail->SurchargeApplicable->__toString() == 'true'?1:0;
                        else
                            $model->surcharge_applicable = 0;
                        if(isset($ShippingServiceDetail->ExpeditedService))
                            $model->expedited_service = $ShippingServiceDetail->ExpeditedService->__toString() == 'true'?1:0;
                        else
                            $model->expedited_service = 0;
                        $model->opration_id = $model->opration_id === null ? 1:$model->opration_id;
                        $model->opration_date = date('Y-m-d H:i:s');
                        findClass($model->save(),1,0);

                    }
            }
        }
        echo '消耗时间：',time()-$startTime;
    }
}