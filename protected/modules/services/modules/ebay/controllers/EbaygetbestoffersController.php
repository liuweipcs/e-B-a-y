<?php

class EbaygetbestoffersController extends UebController {
    
    //active list
    public function actiongetbestoffer() {
        set_time_limit(600);
        $accInfo = array_column(VHelper::selectAsArray('EbayAccount','short_name', 'status=1'), 'short_name');
        foreach($accInfo as $acc) {
            MHelper::runThreadSOCKET('/services/ebay/ebaygetbestoffers/executetask/acc/'.$acc);
            sleep(3);
        }
    }
    
    public function actionExecutetask() {
        $acc = Yii::app()->request->getParam('acc');
        $obj = new EbayGetBestOffer();
        $response = $obj->synchBestOffer($acc);
        echo "ok";
    }
    
    //list detail
    public function actiongetbestofferdetail() {
        set_time_limit(600);
        $itemid = Yii::app()->request->getParam('itemid');
        
        $obj = new EbayGetBestOffer();
        if($itemid) {
            $response = $obj->synchBestOfferByItem($itemid);
        } else {
            $flag = Yii::app()->request->getParam('flag');
            //$response = $obj->synchBestOfferByItem();
            if($flag == 1) {
                $response = $obj->synchBestOfferByItem();
            } else {
                for($i=0; $i<10; $i++) {
                    MHelper::runThreadSOCKET('/services/ebay/ebaygetbestoffers/getbestofferdetail/flag/1');
                    sleep(2);
                }
            }
        }
        echo "ok";
    }
}