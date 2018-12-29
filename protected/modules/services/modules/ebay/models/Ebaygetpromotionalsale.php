<?php

class Ebaygetpromotionalsale {
    
    protected $param;
    
    public function setParam($param) {
        $this->param = $param;
    }
    
    //查询打折列表
    public function getpromotionalsale($short_name) {
        
        $objApi = new Ebaygetpromotionalsaleapi();
        if(!empty($this->param)) {
            $objApi->setParam($this->param);
        }
        
        $response = $objApi->setShortName($short_name)
                ->setVerb('GetPromotionalSaleDetails')
                ->setRequest()
                ->sendHttpRequest()
                ->getResponse()
        ;  
       
        $data = $this->handledata($response,$short_name);
        return $data;
    }
    
    //更新列表
    protected function handledata($data, $short_name) {
        $ack = isset($data->Ack)?$data->Ack:'Failure';
        if($ack == 'Failure') {
            $err_msg = empty($data->Errors->LongMessage)?$data->Errors->LongMessage->__toString():$data->Errors['0']->LongMessage->__toString();
            return array('status'=>'500', 'msg'=>$err_msg);
        }
        //eBay帐号信息
        $accountInfo = UebModel::model('EbayAccount')->getByShortName($short_name);
        if(empty($accountInfo)) {
            return array('status'=>'500', 'msg'=>'eBay帐号简称没有查询到帐号信息');
        }
        
        $list = $data->PromotionalSaleDetails->PromotionalSale;
        $errmsg = '';
        if(!empty($list)) {
            foreach($list as $value) {
                $promotModel = UebModel::model('Ebaypromotionallist')->find('promotional_sale_id=:saleid', array(':saleid'=>$value->PromotionalSaleID));
                if(empty($promotModel)) {
                    $promotModel = new Ebaypromotionallist();
                    
                    $promotModel->user_id = empty(Yii::app()->user->id)?0:Yii::app()->user->id;
                    $promotModel->create_date = date('Y-m-d H:i:s');
                }
                
                $promotModel->account_id = $accountInfo['id'];
                $promotModel->promotional_sale_id = $value->PromotionalSaleID;
                $promotModel->promotion_sale_name = $value->PromotionalSaleName;
                $promotModel->status = $value->Status;
                $promotModel->discount_type = $value->DiscountType;
                $promotModel->discount_value = $value->DiscountValue;
                
                $promotModel->promotional_sale_start_time = date('Y-m-d H:i:s', strtotime($value->PromotionalSaleStartTime));
                $promotModel->promotional_sale_end_time = date('Y-m-d H:i:s', strtotime($value->PromotionalSaleEndTime));
                $promotModel->promotional_sale_type = $value->PromotionalSaleType;
                $promotModel->update_user_id = empty(Yii::app()->user->id)?0:Yii::app()->user->id;
                $promotModel->update_date = date('Y-m-d H:i:s');
                
                $result = $promotModel->save();
                if(!$result) {
                    $errmsg .= '帐号 ：'.$accountInfo['user_name'].', '.$value->PromotionalSaleName.', 更新失败'; //
                }
                
                $backid = $promotModel->attributes['id'];
                $this->updateitme($value->PromotionalSaleItemIDArray, $backid);
            }
        }
        
        if(empty($errmsg)) {
            return array('status'=>'200', 'msg'=>'更新成功');
        } else {
            return array('status'=>'500', 'msg'=>$errmsg);    
        }  
    }
    
    //更新Item ID
    protected function updateitme($data , $id) {
        
        UebModel::model('Ebaypromotionaldetail')->deleteAll('list_id='.$id);
        if(!empty($data->ItemID)) {
            foreach($data->ItemID as $value) {
                $itemModel = new Ebaypromotionaldetail();
                $itemModel->list_id = $id;
                $itemModel->item_id = $value;
                
                $itemModel->save();
            }
        }
    }
    
    
    
}