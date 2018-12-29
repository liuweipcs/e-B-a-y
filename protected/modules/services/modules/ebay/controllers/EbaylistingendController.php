<?php

class EbaylistingendController extends UebController {
    
    //ebay 下架
    public function actionEbaylistingendplan() {
        set_time_limit(600);
        $list = UebModel::model('Ebayonlinelistingoption')
            ->findAll(array(
                'condition'=>'status=:status AND options = :options AND num < :num',
                'params'=>array(':status'=>0, ':options'=>1,':num'=>3),
                'limit'=>100,  
            ));
        
        if(!empty($list)) {
            foreach($list as $value) {
                $api = new Ebayenditemsid;
                $response = $api->endonlineitems($value->item_id);
                if($response['status'] == '200') {
                    UebModel::model('Ebayonlinelistingoption')
                        ->updateAll(array('status'=>1, 'update_time'=>date('Y-m-d H:i:s')), 'id=:id', array(':id'=>$value->id));
                    
                    UebModel::model('Ebayonlinelisting')
                        ->updateAll(array('status'=>'Completed'),'itemid=:itemid', array(':itemid'=>$value->item_id));
                } else {
                    UebModel::model('Ebayonlinelistingoption')
                        ->updateAll(array('remark'=>$response['msg'],'num'=>($value->num+1),'status'=>2), 'id=:id', array(':id'=>$value->id));
                }
            }
        }
        echo 'ok';
        
    }
}