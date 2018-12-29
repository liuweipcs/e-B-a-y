<?php

class Ebaysetpromotionalsale {
    
    public function setpromotionalsale($param, $short_name) {
//        $siteid = 0;
        
        $objApi = new Ebaysetpromotionalsaleapi();
        $objApi->setParam($param);
        $response = $objApi->setShortName($short_name)
//                ->setSiteId($siteid)
                    ->setVerb('SetPromotionalSale')
                    ->setRequest()
                    ->sendHttpRequest()
                    ->getResponse();
        
        $result = $this->analysisresponse($response);
        return $result;
    }
    
    protected function analysisresponse($data) {
        $ack = isset($data->Ack)?$data->Ack:'Failure';
        
        if($ack == 'Failure') {
            $msg = '';
            $errors = $data->Errors;
            foreach($errors as $err) {
                $msg .= isset($err->LongMessage)?$err->LongMessage:$err['0']->LongMessage."<br/>";
            }
            
            return array('status'=>'500', 'msg'=>$msg);
        } else {
            $id = $data->PromotionalSaleID->__toString();
            return array('status'=>'200','id'=>$id);
        }
        
    }
    
}