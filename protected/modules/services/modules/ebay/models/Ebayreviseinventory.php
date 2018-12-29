<?php

class Ebayreviseinventory {
    
    //修改库存和数量
    public function modifyinventory($param) {
        $shortname = $param['shortname'];
        $data = $param['data'];
        
        $apiObj = new Ebayreviseinventoryapi();
        $apiObj->setParam($data);
        
        $response = $apiObj->setShortName($shortname)
                    ->setVerb('ReviseInventoryStatus')
                    ->setRequest()
                    ->sendHttpRequest()
                    ->getResponse();
        
        $ack = isset($response->Ack)?$response->Ack:'Failure';
        if($ack != 'Failure') {
            return array('status'=>'200', 'msg'=>'修改成功');
        } else {
            $err_msg = '';
            $error = $response->Errors;
            foreach($error as $errval) {
                $err_msg .= '【 '.$errval->LongMessage.' 】';
            }
            return array('status'=>'500', 'msg'=>'修改失败,'.$err_msg);
        }
        
    }
}