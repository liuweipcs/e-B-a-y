<?php

class EbaymysellingController extends UebController {
    
    //获取unsold数据
    public function actionGetlist() {
        set_time_limit(600);
        $acc = Yii::app()->request->getParam('acc');
        $response = UebModel::model('Ebayonlinemyselling')->synchList($acc);
        
        echo "ok";
    }
    
    //跑unsold task
    public function actionTask() {
        set_time_limit(600);
        $line = Yii::app()->request->getParam('line');
        $num = 10;
        
        if(is_numeric($line)) {
            $model = UebModel::model('Ebayonlinemysellingtask')->find([
                        'condition'=>'status=0',
                        'order'=>'update_time asc',
                    ]);
          
              $model->status = 1;
              $model->save();
              $response =  UebModel::model('Ebayonlinemyselling')->synchList($model->account);
              if($response['status'] == 'success') {
                  $model->status = 0;
                  $model->update_time = date('Y-m-d H:i:s');
                  $model->remark = '';
                  $model->save();
              } else {
                  $model->status = 0;
                  $model->update_time = date('Y-m-d H:i:s'); 
                  $model->remark = $response['msg'];
                  $model->save();
              }
           echo "ok"; 
        } else {
            for($i=0;$i<$num;$i++) {
                MHelper::runThreadSOCKET('/services/ebay/ebaymyselling/task/line/'.$i);
                sleep(3);
            }
            
            echo "ok";
        }  
    }
    
    //初始化task帐号
    public function actionInitaccount() {
        set_time_limit(600);
        $info = UebModel::model('EbayAccount')->findAll([
            'select'=>'id,user_name',
            'condition'=>'status=1',
        ]);
        
        UebModel::model('Ebayonlinemysellingtask')->deleteAll();
        foreach($info as $v) {
            $model = UebModel::model('Ebayonlinemysellingtask')->find('account="'.$v->user_name.'"');
            if(empty($model)) {
                $model = new Ebayonlinemysellingtask();
            }
            $model->account = $v->user_name;
            $model->status = 0;
            $model->remark = '';
            $model->update_time = date('Y-m-d H:i:s');
            
            $model->save();
        }
        
    }
    
    
    //根据权限确定销售
    public function actionTest() {
        $info = UebModel::model('Ebayonlinelistingpermissions')->CheckPermissions('le_ji84',0,'1','宠物用品');
        echo "<pre>";
        var_dump($info);exit();
    }
    
}