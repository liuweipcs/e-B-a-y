<?php

class EbaycategoryanalysisController extends UebController {
    
    public function actionAnalysis() {
        set_time_limit(600);
        $num = 5;
        $st = date('Y-m-d 00:00:00', strtotime('-10 day'));
        $et = date('Y-m-d 23:59:59');
        
        $result = $this->calcdata($st, $et);
        var_dump($result);
//        if(is_numeric($line)) {

            
//         } 
//         else {
//             for($i=0;$i<$num;$i++) {
//                 $url = '';
                
//                 $obj = UebModel::model('Ebaycategorydate')->find([
//                     'condition'=>'stauts=1',
//                 ]);
                
//                 $st = date('Y-m-d 00:00:00', strtotime($obj->date_time));
//                 $et = date('Y-m-d 23:59:59', strtotime($obj->date_time));
                
//                 $url = sprintf('%s/services/ebay/ebaycategoryanalysis/analysis/line/1/st/'.$st.'/et/'.$et, Yii::app()->request->hostInfo);
//                 MHelper::runThreadSOCKET($url);
//                 echo $url;
//                 sleep(3);
//             }
//         }
    }
    
    public function calcdata($startTime,$endTime) {
        $info = UebModel::model('Ebaycategoryanalysis')->findAll([
            'condition'=>'date_time >= "'.$startTime.'" and date_time <= "'.$endTime.'"  ',
        ]);
        
        if(empty($info)) {
            return array('status'=>'error', 'msg'=>'没有需要统计的数据');
        }
        
        $param = [];
        foreach($info as $v) {
            if(time() - strtotime($v->date_time) <= 3*86400) {
                $param['trend_3'][$v->category_complete]['num'] += $v->sold_qty;
            }
            
            if(time() - strtotime($v->date_time) <= 7*86400) {
                $param['trend_7'][$v->category_complete]['num'] += $v->sold_qty;
            }
            
            if(time() - strtotime($v->date_time) <= 15*86400) {
                $param['trend_15'][$v->category_complete]['num'] += $v->sold_qty;
            }
            
            if(time() - strtotime($v->date_time) <= 30*86400) {
                $param['trend_30'][$v->category_complete]['num'] += $v->sold_qty;
            }
            
            if(time() - strtotime($v->date_time) <= 60*86400) {
                $param['trend_60'][$v->category_complete]['num'] += $v->sold_qty;
            }
            
            if(time() - strtotime($v->date_time) <= 91*86400) {
                $param['trend_90'][$v->category_complete]['num'] += $v->sold_qty;
            }
        }
        
        //
        $data = UebModel::model('Ebaycategoryanalysis')->findAll([
            'condition'=>'date_time >= "'.$startTime.'" and date_time <= "'.$endTime.'"  ',
            'group'=>'site,category_complete',
        ]);
        
        
        foreach($data as $value) {
            $model = new Ebaycategorytrendanalysis();
            $trend_3 = isset($param['trend_3'][$value->category_complete]['num'])?$param['trend_3'][$value->category_complete]['num']:0;
            $trend_7 = isset($param['trend_7'][$value->category_complete]['num'])?$param['trend_7'][$value->category_complete]['num']:0;
            $trend_15 = isset($param['trend_15'][$value->category_complete]['num'])?$param['trend_15'][$value->category_complete]['num']:0;
            $trend_30 = isset($param['trend_30'][$value->category_complete]['num'])?$param['trend_30'][$value->category_complete]['num']:0;
            $trend_60 = isset($param['trend_60'][$value->category_complete]['num'])?$param['trend_60'][$value->category_complete]['num']:0;
            $trend_90 = isset($param['trend_90'][$value->category_complete]['num'])?$param['trend_90'][$value->category_complete]['num']:0;
            
            $model->account = $value->account;    
            $model->site_id = $value->site_id;
            $model->site = $value->site;
            $model->warehouse_id = $value->warehouse_id;
            $model->warehouse_name = $value->warehouse_name;
            
            $model->category_complete = $value->category_complete;
            $model->category_one_name = $value->category_one_name;
            $model->category_second_name = $value->category_second_name;
            $model->category_last_name = $value->category_last_name;
            $model->country = $value->country;
            $model->listing_type = $value->listing_type;
            
            $model->category_id = $value->category_id;
            $model->date_time = $value->date_time;
            $model->trend_7_sold = $trend_7;
            $model->trend_15_sold = $trend_15;
            $model->trend_30_sold = $trend_30;
            $model->trend_30_ratio = '1';
            
            $model->trend_15_diff = round( ((($trend_7/7)*15) - $trend_15), 4);
            $model->trend_30_diff = round( ((($trend_7/7)*30) - $trend_30), 4);
            $model->trend_7 = sprintf('%.4f',(((($trend_3/3)*7) -$trend_7)/$trend_7)*100);
            $model->trend_15 = sprintf('%.4f',(((($trend_7/7)*15) -$trend_15)/$trend_15)*100);
            $model->trend_30 = sprintf('%.4f',(((($trend_7/7)*30) -$trend_30)/$trend_30)*100);
            $model->trend_60 = sprintf('%.4f',(((($trend_7/7)*60) -$trend_60)/$trend_60)*100);
            $model->trend_90 = sprintf('%.4f',(((($trend_7/7)*90) -$trend_90)/$trend_90)*100);
            
            $model->save();
        }
        
        
        
        
    }
    
    
    
    
    
    
}