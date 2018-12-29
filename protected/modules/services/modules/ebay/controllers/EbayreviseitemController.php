<?php
header("Content-type:text/html;charset=utf-8");

class EbayreviseitemController extends UebController {
    
    public $number = 3;
    
    //需要修改的列表
/* **   
     public function actionModifyitem() {
 //        exit();
         set_time_limit(600);
         Yii::import('application.modules.services.modules.ebay.components.EbayApiAbstract',true);
         Yii::import('application.modules.services.modules.ebay.models.Ebayreviselisting',true);
         $list = UebModel::model('Ebayonlinelisting')
                         ->findAll(array('condition'=>'status=:status and listing_type=:type and listing_status=:s ',
                                         'params'=>array(':status'=>1, ':type'=>'FixedPriceItem',':s'=>'Active'),
                                         'limit'=>1
                         ));
       
        if(empty($list)) {
            echo '没有需要更新的数据';exit();
        }
       
        $accountInfo = array();
        $accountInfoArr = VHelper::selectAsArray('EbayAccount','user_name,store_name,user_token');
        foreach($accountInfoArr as $accountvals) {
            $accountInfo[$accountvals['user_name']] =  $accountvals['user_token'];
        }
       
        foreach($list as $value) {
            $param = array();
            $id = $value->id;
            $itemid = $value->itemid;
            $account = $value->account;
            $siteid = $value->siteid;
           
            $param['list'] = $value;
            $path = dirname(Yii::app()->BasePath).'/upload/ebay_descrtion/'.implode('/',str_split($itemid,3)).'/'.$itemid.'.txt';
            $param['descrtion'] = file_get_contents($path);
            $param['shipping'] = UebModel::model('Ebayonlinelistingshipping')->findAll('item_id=:itemid', array(':itemid'=>$itemid));
            $param['variations'] = UebModel::model('Ebayonlinelistingvariation')->findAll('item_id=:itemid and status=0', array(':itemid'=>$itemid));
            $param['imglist'] = UebModel::model('Ebayonlinelistingimage')->findAll('list_id=:listid AND img_status=:status', array(':listid'=>$id, ':status'=>'0'));
           
            $listingObj = new Ebayreviselisting();
            $xml = $listingObj->listingxml($param);
           
            $xmlstr = '<?xml version="1.0" encoding="utf-8" ?>';
            $xmlstr .= '<ReviseFixedPriceItemRequest xmlns="urn:ebay:apis:eBLBaseComponents">';
            $xmlstr .= '<RequesterCredentials>
                     <eBayAuthToken>'.$accountInfo[$account].'</eBayAuthToken>
                   </RequesterCredentials>';
            $xml = $xmlstr.$xml.'</ReviseFixedPriceItemRequest>';
           
            $listingObj->setToken($accountInfo[$account]);
            $listingObj->setsendxml($xml);
            $listingObj->setsite($siteid);
            $response = $listingObj->reviselisting();
           
            if($response['status'] == '200') {
               UebModel::model('Ebayonlinelisting')->updateByPk($id, array('status'=>0,'xml_data_time'=>$response['Timestamp'],'update_time'=>date('Y-m-d H:i:s')));
            } else {
                if($response['error_code'] == '2004') {
                    $remark = $response['error_code'];
                } else {
                    $remark = $response['msg'];
                }
               
                UebModel::model('Ebayonlinelisting')->updateByPk($id, array('status'=>'6','remark'=>$remark));
            }
        }  
     }
 **  */   
    //需要修改的列表
    public function actionModifyitem() {
        set_time_limit(600);
        $num = 10;
        $line = Yii::app()->request->getParam('line');
        
        if(isset($line)) {
            $startTime = time();
            Yii::import('application.modules.services.modules.ebay.components.EbayApiAbstract',true);
            Yii::import('application.modules.services.modules.ebay.models.Ebayreviselisting',true);
            $list = UebModel::model('Ebayonlinelisting')
                    ->findAll(array('condition'=>'status=:status and id%'.$num.'='.$line,
                        'params'=>array(':status'=>1),
                        'limit'=>50
                    ));
             
            if(empty($list)) {
                echo '没有需要更新的数据';exit();
            }
             
            $accountInfo = array();
            $accountInfoArr = VHelper::selectAsArray('EbayAccount','user_name,store_name,user_token');
            foreach($accountInfoArr as $accountvals) {
                $accountInfo[$accountvals['user_name']] =  $accountvals['user_token'];
            }
             
            foreach($list as $value) {
                if(time() - $startTime > 166)
                    exit('时间到3分钟，定制运行。');
                $response = $value->sendApi($accountInfo[$value->account]);
                
                if($response['status'] == 'success' || $response['status'] == 'warning') {
                    UebModel::model('Ebayonlinelisting')->updateByPk($value->id, array('status'=>'0','update_time'=>date('Y-m-d H:i:s')));
                } else {                 
                    UebModel::model('Ebayonlinelisting')->updateByPk($value->id, array('status'=>'6'));
                }
            }
        } else {
            for($i=0; $i<$num; $i++) {
                $url = '/services/ebay/ebayreviseitem/modifyitem/line/'.$i;
                MHelper::runThreadSOCKET($url);
                sleep(3);
            }
        }    
    }
        
    public function actionNewrun() {
        exit();
        set_time_limit(120);
        for($i=0; $i < $this->number; $i++) {
            $url = '/services/ebay/ebayreviseitem/modifylisting/id/'.$i;
            MHelper::runThreadSOCKET($url);
            sleep(3);
        }
        echo "ok";
        
    }
    
    public function actionModifylisting() {
        exit();
        set_time_limit(600);
        $startTime = time();
        $id = Yii::app()->request->getParam('id');
        $logPath = 'log/reviseitem_'.$id.'.log';
        file_put_contents($logPath,'---------------------'.PHP_EOL.'startime'.date('Y-m-d H:i:s').PHP_EOL,FILE_APPEND);
        $info = UebModel::model('Ebayonlinelisting')
                ->findAll(array(
                    'select'=>'*',
                    'condition'=>'status=:status and listing_status=:listing_status and listing_type=:type and id % '.$this->number.'='.$id,
                    'params'=>array(':status'=>'1', ':listing_status'=>'Active',':type'=>'FixedPriceItem'),
                    'order'=>'update_time asc',
                    'limit'=>'50',
                ));
        $count = 0;   
        foreach($info as $value) {
            file_put_contents($logPath,$value->itemid.PHP_EOL,FILE_APPEND);
            if(time() - $startTime > 580)
            {
                file_put_contents($logPath,'endtime:'.date('Y-m-d H:i:s').PHP_EOL,FILE_APPEND);
                exit;
            }

            $imgInfo = UebModel::model('Ebayonlinelistingimage')->findAll('list_id=:id', array(':id'=>$value->id));
            $path = implode('/',str_split($value->itemid, 3));
            $url_path = dirname(Yii::app()->BasePath).'/upload/ebay_descrtion/'.$path.'/'.$value->itemid.'.txt';
            $desc = file_get_contents($url_path);
            
            $token = UebModel::model('EbayAccount')->find('user_name="'.$value->account.'"')->user_token;
            
            $xml = '';
            $xml .= '<?xml version="1.0" encoding="utf-8"?>';
            $xml .= '<ReviseFixedPriceItemRequest xmlns="urn:ebay:apis:eBLBaseComponents">';
            $xml .= '<RequesterCredentials>';
            $xml .= '<eBayAuthToken>'.$token.'</eBayAuthToken>';
            $xml .= '</RequesterCredentials>';
            
            $xml .= '<Item>';
            $xml .= '<ItemID>'.$value->itemid.'</ItemID>';
            $xml .= '<Description><![CDATA['.$desc.']]></Description>';
            $xml .= '<PrimaryCategory><CategoryID>'.$value->primary_category_id.'</CategoryID></PrimaryCategory>';
            
            $picxml = '';
            if(!empty($imgInfo)) {
                $picxml .= '<PictureDetails>';
                foreach($imgInfo as $key=>$imgvalue) {
                    if($key == '0') {
                        $picxml .= '<PhotoDisplay>PicturePack</PhotoDisplay>';
                        $picxml .= '<PictureSource>'.$imgvalue->picture_source.'</PictureSource>';
                    }
                    $picxml .= '<PictureURL>'.$imgvalue->img_url.'</PictureURL>';
                }
                $picxml .= '</PictureDetails>';
            }
            $xml .= $picxml;
             
            $varxml = '';
            //多属性
            if($value->variation_multi == 1) {
                $variationInfo = UebModel::model('Ebayonlinelistingvariation')->findAll('item_id=:id and status=0 ',array(':id'=>$value->itemid));
                $tempVariation = array();
                $varxml .= '<Variations>';
                
                foreach($variationInfo as $k=>$variaValue) {
                    $varxml .= '<Variation>';
                    $varxml .= '<Quantity>'.$variaValue->quantity.'</Quantity>';
                    $varxml .= '<StartPrice>'.$variaValue->start_price.'</StartPrice>';
                    $varxml .= '<SKU>'.$variaValue->sku.'</SKU>';
                    $varxml .= '</Variation>';
                }
                 //多属性图片
                $variationImg = UebModel::model('Ebayonlinelistingvariationimg')->findAll(array(
                    'condition'=>'item_id=:id',
                    'params'=>array(':id'=>$value->itemid),
                    'group'=>'variation_name,variation_value',
                ));
                
                if(!empty($variationImg)) {
                    $varxml .= '<Pictures>';
                    foreach($variationImg as $imgKey=>$img) {
                        $imgUrlInfo = UebModel::model('Ebayonlinelistingvariationimg')
                            ->findAll('item_id=:id and variation_name=:name and variation_value=:value', array(':id'=>$value->itemid, 'name'=>$img->variation_name,'value'=>$img->variation_value));
                        if($imgKey == '0') {
                            $varxml .= '<VariationSpecificName>'.$img->variation_name.'</VariationSpecificName>';
                        }
                        $varxml .= '<VariationSpecificPictureSet>';
                        if(!empty($imgUrlInfo)) {
                            foreach($imgUrlInfo as $url) {
                                $varxml .= '<PictureURL>'.$url->img_url.'</PictureURL>';
                            }
                        }
                
                        $varxml .= '<VariationSpecificValue>'.$img->variation_value.'</VariationSpecificValue>';
                        $varxml .= '</VariationSpecificPictureSet>';
                    }
                
                    $varxml .= '</Pictures>';
                }
                
                
                $varxml .= '</Variations>';
                $xml .= $varxml;
            } else {
                
                $xml .= '<StartPrice>'.$value->start_price.'</StartPrice>';
                $xml .= '<Quantity>'.$value->quantity.'</Quantity>';
              
            }
            
            $xml .= '</Item>';
            $xml .= '</ReviseFixedPriceItemRequest>';
           
           $listingObj = new Ebayreviselisting();
           $listingObj->setToken($token);
           $listingObj->setsendxml($xml);
           $listingObj->setsite($value->siteid);
           $response = $listingObj->reviselisting();

           if($response['status'] == '200') {
               echo 'ch'.$value->itemid."<br/>";
               UebModel::model('Ebayonlinelisting')->updateByPk($id, array('status'=>0,'update_time'=>date('Y-m-d H:i:s')));
               UebModel::model('Ebayonlinelistingflag')->updateAll(array('status'=>1), 'item_id=:itemid', array(':itemid'=>$value->itemid));
           } else {
               echo 'shibai：'.$value->itemid."<br/>";
               if($response['error_code'] == '2004') {
                   $remark = $response['error_code'];
               } else {
                   $remark = $response['msg'];
               }
                
               UebModel::model('Ebayonlinelisting')->updateByPk($id, array('status'=>'6','remark'=>$remark));
               UebModel::model('Ebayonlinelistingflag')->updateAll(array('status'=>2,'remark'=>$remark), 'item_id=:itemid', array(':itemid'=>$value->itemid));
           }
           $count++;
        }
        file_put_contents($logPath,'endtime:'.date('Y-m-d H:i:s').PHP_EOL,FILE_APPEND);
    }
    
    //
    public function actionNewmodifyitem() {
        exit();
        for($i=0;$i<5;$i++) {
            $url = '/services/ebay/ebayreviseitem/newmodifytask/id/'.$i;
            MHelper::runThreadSOCKET($url);
            sleep(3);
        }
    }
    
    public function actionNewmodifytask() {
        exit();
        set_time_limit(600);
        $st = time();
        $s = Yii::app()->request->getParam('id');
        
        if(!empty($s)) {
            $info = UebModel::model('Ebayonlinelistingflag')->findAll(array(
                'condition'=>'item_id IN ('.$s.')',
            ));
        } else {
            $info = UebModel::model('Ebayonlinelistingflag')->findAll(array(
                'condition'=>'status=1',
                'order'=>'update_time asc',
                'limit'=>'50',
            ));
        }
        
        if(!$info) {
            echo '没有需要修改的数据';exit();
        }
        
        foreach($info as $infov) {
            $infov->status = 3;
            $infov->update_time = date('Y-m-d H:i:s');
            $infov->save();
        }
        
        
        $msg = '';
        foreach($info as $v) {
            if(time() - $st > 550) {
                $v->status=1;
                $v->save();
                $msg .= $v->item_id.", 未完成<br/>";
                continue;
            }
        
            $obj = UebModel::model('Ebayonlinelisting')->find('itemid='.$v->item_id);
            if($obj->listing_type != 'FixedPriceItem') {
                echo $obj->itemid.", 刊登类型不能修改<br/>";
                continue;
            }
            $account = $obj->account;
            $itemId = $obj->itemid;
            $token = UebModel::model('EbayAccount')->find('user_name="'.$account.'"')->user_token;
            $path = implode('/',str_split($itemId, 3));
            $desc = file_get_contents('http://120.24.249.36/upload/ebay_descrtion/'.$path.'/'.$itemId.'.txt');
        
            $xml = '';
            $xml .= '<?xml version="1.0" encoding="utf-8"?>';
            $xml .= '<ReviseFixedPriceItemRequest xmlns="urn:ebay:apis:eBLBaseComponents">';
            $xml .= '<RequesterCredentials>';
            $xml .= '<eBayAuthToken>'.$token.'</eBayAuthToken>';
            $xml .= '</RequesterCredentials>';
        
            $xml .= '<Item>';
            $xml .= '<ItemID>'.$itemId.'</ItemID>';
            $xml .= '<Description><![CDATA['.$desc.']]></Description>';
            $xml .= '<PrimaryCategory><CategoryID>'.$obj->primary_category_id.'</CategoryID></PrimaryCategory>';
            //图片
            $imgInfo = UebModel::model('Ebayonlinelistingimage')->findAll('item_id=:id', array(':id'=>$v->item_id));
            if(count($imgInfo) > 12) {
                if(count($imgInfo) == 13) {
                    UebModel::model('Ebayonlinelistingimage')->deleteAll('id='.$imgInfo['0']->id);
                    array_shift($imgInfo);
                } else {
                    echo $itemId.'--图片张数为'.count($imgInfo).'张<br/>';
                    continue;
                }
            }
            $picxml = '';
            if(!empty($imgInfo)) {
                $picxml .= '<PictureDetails>';
                $picxml .= '<PhotoDisplay>PicturePack</PhotoDisplay>';
                $picxml .= '<PictureSource>Vendor</PictureSource>';
                foreach($imgInfo as $key=>$imgvalue) {

                    $picxml .= '<PictureURL>'.$imgvalue->img_url.'</PictureURL>';
                }
                $picxml .= '</PictureDetails>';
            }
            $xml .= $picxml;
        
            //多属性
            if($obj->variation_multi == '1') {
                $variationInfo = UebModel::model('Ebayonlinelistingvariation')->findAll('item_id="'.$itemId.'" and status=0');
                $xml .= '<Variations>';
                foreach($variationInfo as $variation) {
                    $xml .= '<Variation>';
                    $xml .= '<StartPrice>'.$variation->start_price.'</StartPrice>';
                    $xml .= '<SKU>'.$variation->sku.'</SKU>';
                    $xml .= '<Quantity>'.$variation->quantity.'</Quantity>';
                    $xml .= '</Variation>';
                }
                //多属性图片
                $variationImg = UebModel::model('Ebayonlinelistingvariationimg')->findAll(array(
                    'condition'=>'item_id=:id',
                    'params'=>array(':id'=>$v->item_id),
                    'group'=>'variation_name,variation_value',
                ));
        
                if(!empty($variationImg)) {
                    $xml .= '<Pictures>';
                    foreach($variationImg as $imgKey=>$img) {
                        $imgUrlInfo = UebModel::model('Ebayonlinelistingvariationimg')
                        ->findAll('item_id=:id and variation_name=:name and variation_value=:value', array(':id'=>$v->item_id, 'name'=>$img->variation_name,'value'=>$img->variation_value));
                        if($imgKey == '0') {
                            $xml .= '<VariationSpecificName>'.$img->variation_name.'</VariationSpecificName>';
                        }
                        $xml .= '<VariationSpecificPictureSet>';
                        if(!empty($imgUrlInfo)) {
                            foreach($imgUrlInfo as $url) {
                                $xml .= '<PictureURL>'.$url->img_url.'</PictureURL>';
                            }
                        }
        
                        $xml .= '<VariationSpecificValue>'.$img->variation_value.'</VariationSpecificValue>';
                        $xml .= '</VariationSpecificPictureSet>';
                    }
        
                    $xml .= '</Pictures>';
                }
        
                $xml .= '</Variations>';
            } else {
                $xml .= '<StartPrice>'.$obj->start_price.'</StartPrice>';
                $xml .= '<Quantity>'.$obj->quantity.'</Quantity>';
            }
        
            $xml .= '</Item>';
            $xml .= '</ReviseFixedPriceItemRequest>';
        
            $listingObj = new Ebayreviselisting();
            $listingObj->setToken($token);
            $listingObj->setsendxml($xml);
            $listingObj->setsite($obj->siteid);
            $response = $listingObj->reviselisting();
        
            if($response['status'] == '200') {
                UebModel::model('Ebayonlinelistingflag')->updateAll(array('flag'=>2,'status'=>3), 'item_id="'.$v->item_id.'"');
        
                $msg .= $itemId. ", 修改成功<br/>";
            } else {
                UebModel::model('Ebayonlinelistingflag')->updateAll(array('flag'=>3, 'remark'=>$xml), 'item_id="'.$v->item_id.'"');
                $msg .= $itemId. ", 修改失败<br/>";
            }
        
        }
        
        echo $msg;
    }
    
    
    public function actionModifydesction() {
        set_time_limit(600);
        $num = 10;
        $line = Yii::app()->request->getParam('line');
        
        if(isset($line)) {
            $startTime = time();
//            Yii::import('application.modules.services.modules.ebay.components.EbayApiAbstract',true);
//            Yii::import('application.modules.services.modules.ebay.models.Ebayreviselisting',true);
            $list = UebModel::model('Ebayonlinelisting')
            ->findAll(array('condition'=>'status=:status and listing_status = "Active" and id%'.$num.'='.$line,
                'params'=>array(':status'=>4),
                'limit'=>50
            ));
             
            if(empty($list)) {
                echo '没有需要更新的数据';exit();
            }
             
            $accountInfo = array();
            $accountInfoArr = VHelper::selectAsArray('EbayAccount','user_name,store_name,user_token');
            foreach($accountInfoArr as $accountvals) {
                $accountInfo[$accountvals['user_name']] =  $accountvals['user_token'];
            }
             
            foreach($list as $value) {
                if(time() - $startTime > 280)
                    exit('时间到5分钟，定制运行。');
                $response = $value->modifyDesction($accountInfo[$value->account]);
        
//                 if($response['status'] == 'success' || $response['status'] == 'warning') {
//                     UebModel::model('Ebayonlinelisting')->updateByPk($value->id, array('status'=>'0','update_time'=>date('Y-m-d H:i:s')));
//                 } else {
//                     UebModel::model('Ebayonlinelisting')->updateByPk($value->id, array('status'=>'6'));
//                 }
            }
        } else {
            for($i=0; $i<$num; $i++) {
                $url = '/services/ebay/ebayreviseitem/modifydesction/line/'.$i;
                MHelper::runThreadSOCKET($url);
                sleep(3);
            }
        }
        
        echo "ok";
    }
    
    
}
