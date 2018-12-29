<?php

header("Content-type:text/html;charset=utf-8");

class EbaygetpromotionalsaleController extends UebController {
    
    //调用方法
    public function actionGetebaypromotional() {
        set_time_limit(600);
        
        $info = Yii::app()->db->createCommand()
                ->select('id,short_name')
                ->from('ueb_product.ueb_ebay_promotional_task')
                ->where('status=:status')->order('update_time asc')->limit(10)->queryAll(true, array(':status'=>0));
        
        if(!empty($info)) {
            foreach($info as $value) {
               UebModel::model('Ebaypromotionaltask')->updateAll(array('status'=>1),'id=:id', array(':id'=>$value['id'])); 
               $url = '/services/ebay/ebaygetpromotionalsale/getebaypromotionalsale/acc/'.$value['short_name'];
               MHelper::runThreadSOCKET($url);
               sleep(3);
            }
        }    
        echo "ok";
    }
    
    
    //同步打折
    public function actiongetebaypromotionalsale() {
        set_time_limit(300);
        $account = Yii::app()->request->getParam('acc');    
        
        $Api = new Ebaygetpromotionalsale();
        $response = $Api->getpromotionalsale($account);
            
        UebModel::model('Ebaypromotionaltask')
            ->updateAll(array('status'=>0,'update_time'=>date('Y-m-d H:i:s')),'short_name=:shortname', array(':shortname'=>$account));
       
    }
    
    public function actionAccountupdate() {
        $accountInfo = UebModel::model('EbayAccount')->findAll('status=:status AND length(user_token) > :len', array(':status'=>1, ':len'=>200));
        
        Yii::app()->db->createCommand('TRUNCATE TABLE ueb_product.ueb_ebay_promotional_task')->execute();
        $msg = '';
        foreach($accountInfo as $value) {
            $objModel = new Ebaypromotionaltask();
            
            $objModel->account_id = $value->id;
            $objModel->short_name = $value->short_name;
            $objModel->status = 0;
            $objModel->update_time = date('Y-m-d H:i:s');
            
            $flag = $objModel->save();
            if(!$flag) {
                $msg .= $value->user_name.' ,插入失败<br/>';
            }
        }
        
        if(empty($msg)) {
            echo 'ok';
        } else {
            echo $msg;
        }
        
        exit();
    }
    
    
    
    
}