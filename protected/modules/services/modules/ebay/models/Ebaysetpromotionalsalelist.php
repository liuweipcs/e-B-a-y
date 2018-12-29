<?php

class Ebaysetpromotionalsalelist {
    
    public function setpromotionalsalelist($param, $short_name) {
        $objApi = new Ebaysetpromotionalsalelistapi();
        $objApi->setParam($param);
        $response = $objApi->setShortName($short_name)
                    ->setVerb('SetPromotionalSaleListings')
//                    ->setSiteId(0)
                    ->setRequest()
                    ->sendHttpRequest()
                    ->getResponse();
        
//        echo "<pre>";var_dump($response);exit();
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
            return array('status'=>'200');
        }
    
    }
    
}