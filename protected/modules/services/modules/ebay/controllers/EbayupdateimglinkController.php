<?php

class EbayupdateimglinkController extends UebController {
    
    public $number=10;
    
//     //更新图片链接
//     public function actionNewimglink() {
//         set_time_limit(540);
//         $info = UebModel::model('Ebayonlinelisting')
//                     ->findAll(array(
//                         'select'=>'id,itemid',
//                         'condition'=>'status=:status and listing_status=:listing_status and listing_type=:type',
//                         'params'=>array(':status'=>'0', ':listing_status'=>'Active',':type'=>'FixedPriceItem'),
//                         'order'=>'update_time asc',
//                         'limit'=>'200',
//                     ));
      
//        $ids = array();
//        if(!empty($info)) {
//            foreach($info as $value) {
//                $ids[] = $value->id;
//            }
//            $idstr = implode(',', $ids);
//            UebModel::model('Ebayonlinelisting')->updateAll(array('status'=>'5'),'id in ('.$idstr.')');
           
//            //查询图片地址
//            $imgInfo = UebModel::model('Ebayonlinelistingimage')->findAll('list_id in ('.$idstr.')');
//            if(!empty($imgInfo)) {
//                foreach($imgInfo as $imgValue) {
//                    $url = $imgValue->img_url;
//                    if($imgValue->picture_source == 'EPS') {
//                        if(substr($url,0,5) != 'https') {
//                            //$url = str_replace('http','https', $url); //先不替换
//                            //$imgValue->img_url = $url;
//                            //$imgValue->save();
//                            //UebModel::model('Ebayonlinelisting')->updateAll(array('status'=>'0'), 'id=:id', array(':id'=>$imgValue->list_id));
//                        }
//                    } else {
//                        $str = " src='".$url."'";
//                        $newurl = VHelper::resourceLinkTransformHttps($str);
//                        $newurl = rtrim(str_replace("src='",'',trim($newurl)),"'");
//                        $imgValue->img_url = $newurl;
//                        $imgValue->save();
//                    }
//                }
//            }
           
//        } else {
//            echo '没有需要更新的数据';exit();
//        }
        
//     }
    
    
    //描述多线程跑
    public function actionRundesclink() {
        set_time_limit(120);
        for($i=0; $i < $this->number; $i++) {
            $url = '/services/ebay/ebayupdateimglink/updesclink/id/'.$i;
            MHelper::runThreadSOCKET($url);
            sleep(3);
        }
        echo "ok";
    }
    
    //更新描述链接
    public function actionNewdesclink() {
        set_time_limit(600);
        $start_time = time();
        $id = Yii::app()->request->getParam('id');
       
        $info = UebModel::model('Ebayonlinelisting')
            ->findAll(array(
                'select'=>'id,itemid',
               'condition'=>'status=:status and listing_status=:listing_status and listing_type=:type and id % '.$this->number.'='.$id,
               'params'=>array(':status'=>'0', ':listing_status'=>'Active',':type'=>'FixedPriceItem'),
                'order'=>'update_time asc',
                'limit'=>'40',
            ));
        
       //先把 时间更新下  
       $ids = array();
       foreach($info as $v) {
           $ids[] = $v->id;
           $v->status = 5;
           $v->save();
       }
       if(!empty($ids)) {
           $idstr = implode(',', $ids);
           
           //查询图片地址
           $imgInfo = UebModel::model('Ebayonlinelistingimage')->findAll('list_id in ('.$idstr.')');
           //查询多属性图片
           $variationImg = UebModel::model('Ebayonlinelistingvariationimg')->findAll('item_id in ('.$idstr.')');
           if(!empty($imgInfo)) {
               foreach($imgInfo as $imgValue) {
                   $url = $imgValue->img_url;
                   if($imgValue->picture_source == 'EPS') {
                       if(substr($url,0,5) != 'https') {
                           //$url = str_replace('http','https', $url); //先不替换
                           //$imgValue->img_url = $url;
                           //$imgValue->save();
                           //UebModel::model('Ebayonlinelisting')->updateAll(array('status'=>'0'), 'id=:id', array(':id'=>$imgValue->list_id));
                       }
                   } else {
                       $str = " src='".$url."'";
                       $newurl = VHelper::resourceLinkTransformHttps($str);
                       if(isset($newurl['status']) && $newurl['status'] == false) {
                           continue;
                       }
                       $newurl = rtrim(str_replace("src='",'',trim($newurl)),"'");
                       $imgValue->img_url = $newurl;
                       $imgValue->save();
                   }
                   
                  
               }
           }
           // 多属性图片
           foreach($variationImg as $imgurl) {
               $url = $imgurl->img_url;
               if(strpos($url, 'ebayimg.com') === false) {
                   $imgstr = " src='".$url."'";
                   $newimg = VHelper::resourceLinkTransformHttps($imgstr);
                   if(isset($newimg['status']) && $newimg['status'] == false) {
                       continue;
                   }
                   $newurl = rtrim(str_replace("src='",'',trim($newimg)),"'");
                   $imgurl->img_url = $newurl;
                   $imgValue->save();
               } 
           }
       }
       
        if(!empty($info)) {
            foreach($info as $value) {
                if(time() - $start_time > 570) {
                    exit();
                }
                
                $imgInfo = UebModel::model('Ebayonlinelistingimage')->findAll('list_id = '.$value->id);
                if(!empty($imgInfo)) {
                    foreach($imgInfo as $imgValue) {
                        $url = $imgValue->img_url;
                        if($imgValue->picture_source == 'EPS') {
                            if(substr($url,0,5) != 'https') {
                                //$url = str_replace('http','https', $url); //先不替换
                                //$imgValue->img_url = $url;
                                //$imgValue->save();
                                //UebModel::model('Ebayonlinelisting')->updateAll(array('status'=>'0'), 'id=:id', array(':id'=>$imgValue->list_id));
                            }
                        } else {
                            $str = " src='".$url."'";
                            $newurl = VHelper::resourceLinkTransformHttps($str);
                            if(isset($newurl['status']) && $newurl['status'] == false) {
                                $value->status= 6;
                                $value->save();
                                
                                continue;
                            }
                            $newurl = rtrim(str_replace("src='",'',trim($newurl)),"'");
                            $imgValue->img_url = $newurl;
                            $imgValue->save();
                        }
                    }
                }
                
                $item_id = $value->itemid;
                $path = implode('/',str_split($item_id,3));
                $url_path = dirname(Yii::app()->BasePath).'/upload/ebay_descrtion/'.$path.'/'.$item_id.'.txt';
                
                $content = file_get_contents($url_path);
                $newcontent = VHelper::resourceLinkTransformHttps($content);
                
                //记录到日志表中
                $flagObj = UebModel::model('Ebayonlinelistingflag')->find('item_id=:item_id', array(':item_id'=>$item_id));
                if(empty($flagObj)) {
                    $flagObj = new Ebayonlinelistingflag;
                }
                
                if(isset($newcontent['status']) && $newcontent['status'] == false) { //图片下载失败
                    $value->status = 6;
                    $value->remark = $newcontent['info'];
                    $value->save();
                    
                    $flagObj->item_id = $item_id;
                    $flagObj->status = 3;
                    $flagObj->remark = $newcontent['info'];
                    $flagObj->update_time = date('Y-m-d H:i:s');
                    $flagObj->save();
                    
                    continue;
                }
                
                $tmp_url = dirname(Yii::app()->BasePath).'/upload/ebay_descrtion/'.$path;
                mkdir( $tmp_url, 0777,true);
                
                file_put_contents($url_path, $newcontent);
                
                $value->status = 1;
                $value->update_time = date('Y-m-d H:i:s');
                $value->save();
                
                $flagObj->item_id = $item_id;
                $flagObj->status = 0;
                $flagObj->update_time = date('Y-m-d H:i:s');
                $flagObj->save();
            }
            
        }
        
        echo time()-$start_time;
    }
    
    
    //更新描述链接
    public function actionUpdesclink() {
        set_time_limit(600);
        for($i=0;$i<15;$i++) {
            $url = '/services/ebay/ebayupdateimglink/updatetask/id/'.$i;
            MHelper::runThreadSOCKET($url);
            sleep(5);
        }
    } 
    
    public function actionUpdatetask() {
        set_time_limit(600);
        $start_time = time();
        
        $s = Yii::app()->request->getParam('id');
        if(!empty($s)) {
            $info = UebModel::model('Ebayonlinelistingflag')->findAll(array(
                'condition'=>'item_id='.$s,
            ));
        } else {
            $info = UebModel::model('Ebayonlinelistingflag')
                        ->findAll(array(
                            'condition'=>'status=1',
                            'order'=>'update_time asc',
                            'limit'=>'100',
                        ));
        }
        if(empty($info)) {
            echo '没有需要更新的数据';exit();
        }
        
        //先更新中间状态
        foreach($info as $value) {
            $value->status = 4;
            $value->update_time = date('Y-m-d H:i:s');
            $value->save();
        }
        
        foreach($info as $v) {
            //查询listing 信息
            $list = UebModel::model('Ebayonlinelisting')->find('itemid='.$v->item_id);
            if($list->listing_status != 'Active') {
                $v->status = 2;
                $v->remark = 'listing已下架';
                $v->save();
                continue;
            }
            //替换橱窗图片
            $imgInfo = UebModel::model('Ebayonlinelistingimage')->findAll('item_id = "'.$v->item_id.'" ');
            foreach($imgInfo as $imgValue) {
                $url = $imgValue->img_url;
                if($imgValue->picture_source == 'EPS') {
                    if(substr($url,0,5) != 'https') {
                        $url = str_replace('http','https', $url); //先不替换
                        $imgValue->img_url = $url;
                        $imgValue->save();
                        
                        $v->flag = 1;
                        $v->save();
                    }
                } else {
                    $str = " src='".$url."'";
                    $newurl = VHelper::resourceLinkTransformHttps($str);
                    if(isset($newurl['status']) && $newurl['status'] == false) {
                        $v->status=2;
                        $v->remark='替换图片链接失败';
                        $v->save();
                        
                        continue;
                    }
                    $newurl = rtrim(str_replace("src='",'',trim($newurl)),"'");
                    if($imgValue->img_url != $newurl) {
                        $v->flag = 1;
                        $v->save();
                    }
                    $imgValue->img_url = $newurl;
                    $imgValue->save();
                }
            }
            
            //判断多属性
            $multi_attr = $list->variation_multi;
            if($multi_attr == '1') {
                // 多属性图片
                $variationImg = UebModel::model('Ebayonlinelistingvariationimg')->findAll('item_id = '.$v->item_id);
                
                foreach($variationImg as $imgurl) {
                    $url = $imgurl->img_url;
                    if(strpos($url, 'ebayimg.com') === false) {
                        $imgstr = " src='".$url."'";
                        $newimg = VHelper::resourceLinkTransformHttps($imgstr);
                        
                        if(isset($newimg['status']) && $newimg['status'] == false) {
                            $v->status=2;
                            $v->remark='替换多属性图片链接失败';
                            $v->save();
                            continue;
                        }
                        $newurl = rtrim(str_replace("src='",'',trim($newimg)),"'");
                        $imgurl->img_url = $newurl;
                        $imgurl->save();
                    }
                }
            }
            
            //更新描述
            $path = implode('/',str_split($v->item_id,3));
            $url_path = dirname(Yii::app()->BasePath).'/upload/ebay_descrtion/'.$path.'/'.$v->item_id.'.txt';
            
            $content = file_get_contents($url_path);
            $newcontent = VHelper::resourceLinkTransformHttps($content);
            
            if(isset($newcontent['status']) && $newcontent['status'] == false) { //图片下载失败
                $v->status = 2;
                $v->remark = $newcontent['info'];
                $v->save();
            
                continue;
            }
            
            mkdir($url_path, 0777,true);
            $res = file_put_contents($url_path, $newcontent);
            if(!$res) {
                $v->status = 2;
                $v->remark = '替换描述链接失败';
                $v->save();
                continue;
            }
            
            if($content != $newcontent) {
                $v->flag = 1;
                $v->save();
            }
            
            $v->status = 1;
            $v->save();
        }
                    
      
    }
    
   
    public function actionCalcprofitrate() {
        set_time_limit(600);
        $num = Yii::app()->request->getParam('num',null);
        if(empty($num)) {
          $num = 10;
        }
        $line = Yii::app()->request->getParam('line',null);
        $startTime = time();
        
        if(isset($line)) {
            $info = UebModel::model('Ebayonlinelisting')->findAll(array(
                'condition'=>'profit_rate is null and listing_type="FixedPriceItem" and listing_status="Active" and id%"'.$num.'"="'.$line.'" ',
                'limit'=>'200'
            ));
            
            if(empty($info)) {
                echo '没有需要更新的数据';exit();
            }
            
            foreach($info as $v) {
                if(time() - $startTime > 590) {
                    continue;
                }
                
                UebModel::model('Ebayonlinelisting')->calcProfitRate($v->itemid);
            }
            
        } else {
            for($i=0;$i<$num;$i++) {
                $url = '/services/ebay/ebayupdateimglink/calcprofitrate/line/'.$i;
                MHelper::runThreadSOCKET($url);
                sleep(5);
            }
        }
    }
    
    
    public function actionSumcategoryitem() {
        set_time_limit(600);
        $line = Yii::app()->request->getParam('line',null);

        $startTime = Yii::app()->request->getParam('st');
        $endTime = Yii::app()->request->getParam('et');
        
        if(empty($startTime)) {
            $startTime = date('Y-m-d 00:00:00');
        }
        
        if(empty($endTime)) {
            $endTime = date('Y-m-d 23:59:59');
        }
        
        $orderFind = UebModel::model('OrderEbay')->findAll(array(
            'select'=>'order_id, paytime,ship_country_name',
            'condition'=>'paytime >= "'.$startTime.'" and paytime <= "'.$endTime.'" '
        ));
        
        if(empty($orderFind)) {
            $newObj = new Ebaycategoryanalysis();
            $newObj->date_time = date('Y-m-d 10:00:00',strtotime($startTime));
            $newObj->save();
            echo '没有需要统计的订单';exit();
        }
        
        $orderInfo = array();
        $orderId = '';
        foreach($orderFind as $value) {
            $orderInfo[$value->order_id]['pay_time'] = $value->paytime;
            $orderInfo[$value->order_id]['ship_country'] = $value->ship_country_name;
            $orderId .= $value->order_id.'","';
        } 
        
        $orderId = '"'.substr($orderId,0,-2);
        $orderDetail = UebModel::model('OrderEbayDetail')->findAll('order_id IN ('.$orderId.')');
        if(empty($orderDetail)) {
            echo '没有需要统计的订单详情';exit();
        }
        
        //location 映射仓库
        $total_flag = 'total_warehouse_flag_ebay_x';
        $warehouseInfo = UebModel::model('Logisticsruleconfig')->memcacheSetCache($total_flag,'get');
        if(empty($warehouseInfo)) {
            $warehouseInfo = UebModel::model('EbayLocationMapWarehouse')->findAll('is_delete=0');
            UebModel::model('Logisticsruleconfig')->memcacheSetCache($total_flag, 'set', $warehouseInfo);
        }
        //大仓
        $bigWarehouseInfo = UebModel::model('EbayWarehouseWarehouseCategory')->findAll('is_delete=0');
        $bigWarehouse = [];
        foreach($bigWarehouseInfo as $big_v) {
            $bigWarehouse[$big_v->id] = $big_v->name;
        }
        
        $warehouse = [];
        foreach($warehouseInfo as $warehouse_v) {
            $warehouse_v->location = strtoupper($warehouse_v->location);
            $warehouse[$warehouse_v->location]['warehouse_id'] = $warehouse_v->warehouse_category_id;
            $warehouse[$warehouse_v->location]['warehouse_name'] = $bigWarehouse[$warehouse_v->warehouse_category_id];
        }
        
        $info = [];
        foreach($orderDetail as $vs) {
            $itemId = $vs->item_id;
            if(empty($itemId)) {
                echo "订单号：".$vs->order_id.",ItemID为空，SKU为：".$vs->sku."，无法关联<br/>";
                continue;
            }
            $skuItem = 'x1skux_'.$itemId;
            $listing = UebModel::model('Logisticsruleconfig')->memcacheSetCache($skuItem, 'get');
            if(empty($listing)) {
                $listing = UebModel::model('Ebayonlinelisting')->find('itemid='.$itemId);
                UebModel::model('Logisticsruleconfig')->memcacheSetCache($skuItem, 'set', $listing);
            }
            
            $account = $listing->account;
            $site = $listing->site;
            $categoryComplete = $listing->primary_category_name;
            $categoryArr = explode(':', $categoryComplete);
            $listing->location = strtoupper($listing->location);
            $country = $orderInfo[$vs->order_id]['ship_country']; //订单到达国家
            
            $info["{$itemId}"][$country]['item_id'] = $itemId;
            $info["{$itemId}"][$country]['qty'] += $vs->quantity;
            $info["{$itemId}"][$country]['amount'] += $vs->total_price;
            $info["{$itemId}"][$country]['num'] += 1;
            $info["{$itemId}"][$country]['account'] = $account;
            $info["{$itemId}"][$country]['site_id'] = $listing->siteid;
            $info["{$itemId}"][$country]['site'] = $site;
            $info["{$itemId}"][$country]['warehouse_id'] = $warehouse[$listing->location]['warehouse_id'];
            $info["{$itemId}"][$country]['warehouse_name'] = $warehouse[$listing->location]['warehouse_name'];
            $info["{$itemId}"][$country]['category_complete'] = $categoryComplete;
            $info["{$itemId}"][$country]['category_one_name'] = $categoryArr['0'];
            $info["{$itemId}"][$country]['category_second_name'] = $categoryArr['1'];
            $info["{$itemId}"][$country]['category_last_name'] = array_pop($categoryArr);
            $info["{$itemId}"][$country]['category_id'] = $listing->primary_category_id;
            $info["{$itemId}"][$country]['country'] = $country;
            $info["{$itemId}"][$country]['listing_type'] = $listing->listing_type;
            $info["{$itemId}"][$country]['date_time'] = date('Y-m-d 00:00:01',strtotime($orderInfo[$vs->order_id]['pay_time']));
            
        }
        
        $msg = '';
        foreach($info as $key=>$vals) {
            foreach($vals as $k=>$v) {
        
                $model = UebModel::model('Ebaycategoryanalysis')
                        ->find('item_id="'.$key.'" and country="'.$k.'" and date_time = "'.$v['date_time'].'" ');
                if(empty($model)) {
                    $model = new Ebaycategoryanalysis();
                }
                
                $model->item_id = $v['item_id'];
                $model->account = $v['account'];
                $model->site_id = $v['site_id'];
                $model->site = $v['site'];
                $model->warehouse_id = $v['warehouse_id'];
                $model->warehouse_name = $v['warehouse_name'];
                $model->category_complete = $v['category_complete'];
                $model->category_one_name = $v['category_one_name'];
                $model->category_second_name = $v['category_second_name'];
                $model->category_last_name = $v['category_last_name'];
                $model->category_id = $v['category_id'];
                $model->country = $v['country'];
                $model->listing_type = $v['listing_type'];
                $model->sold_qty = $v['qty'];
                $model->sold_quota = $v['amount'];
                $model->order_qty = $v['num'];
                $model->date_time = $v['date_time'];
                $model->hit_qty = '0';   //点击率
                $model->pre_price = '0';  //客单价
                $model->turnover_rate = '0'; //成交率
                $model->bad_review_rate = '0'; //差评率
                
                $save = $model->save();
                if($save) {
                    $msg .= $v['date_time'].", 统计成功<br/>";
                } else {
                    $msg .= $v['date_time'].", 统计失败<br/>";
                }
            }
        }
        if(!empty($line)) {
            echo $msg."<br/>";
        }
        echo "ok";
    }
    
    
    
    public function actionTest() {
        $info = Yii::app()->db->createCommand()
                ->from('ueb_product.ueb_ebay_online_itemid_temp')
                ->queryAll(true);
        
        foreach($info as $v) {
            UebModel::model('Ebayonlinelisting')->updateAll(['product_line'=>$v['msg']],'itemid='.$v['item_id']);
        }
        echo "ok";
    }
    
    
    
    
}