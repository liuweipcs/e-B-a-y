<?php

class Ebayrelisting {
    
    public function relistitem($itemid, $shortname,$siteid, $qty) {
        
        $relistApi = new Ebayrelistingapi();
        $relistApi->setitemid($itemid);
        $relistApi->setQty($qty);
        
        $response = $relistApi->setShortName($shortname)
                ->setVerb('RelistFixedPriceItem')
                ->setSiteId($siteid)
                ->setRequest()
                ->sendHttpRequest()
                ->getResponse();
        
        $ack = isset($response->Ack)?$response->Ack:'Failure';
        if($ack != 'Failure') {
            return array('status'=>'200', 'msg'=>'重新上架成功,', 'item_id'=>$response->ItemID);
        } else {
            return array('status'=>'500', 'msg'=>'重新上架失败');
        }
    }
}