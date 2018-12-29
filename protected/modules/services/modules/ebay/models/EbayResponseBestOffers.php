<?php

class EbayResponseBestOffers {
    
    public function replyBestOffers($shortname,$action,$itemid,$bestofferid,$price='',$qty='', $currency='',$fujia='') {
        
        $bestOfferObj = new ResponseBestOffers();
        $bestOfferObj->setAction($action);
        $bestOfferObj->setItemid($itemid);
        $bestOfferObj->setBestOfferId($bestofferid);
        
        if($price) {
            $bestOfferObj->setprice($price);
        }
        if($qty) {
            $bestOfferObj->setquantity($qty);
        }
        if($currency) {
            $bestOfferObj->setCurrency($currency);
        }
        if(!empty($fujia)) {
            $bestOfferObj->setfujia($fujia);
        }
        //获取分类
        $response = $bestOfferObj->setShortName($shortname)
        ->setVerb('RespondToBestOffer')        
        ->setRequest()
        ->sendHttpRequest()
        ->getResponse()
        ;
        
//        echo "<Pre>";var_dump($response);exit();
        
        if( $bestOfferObj->getIfSuccess() ) {
            return array('status'=>200);
        } else {
            return array('status'=>500, 'msg'=>'错误提示：'.$bestOfferObj->getErrorMsg());
        }
    }
}
