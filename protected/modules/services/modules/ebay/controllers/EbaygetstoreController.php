<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/4/1 0001
 * Time: 下午 3:23
 */
class EbaygetstoreController extends UebController
{
    public function actionIndex()
    {
        if(isset($_REQUEST['siteid']))
        {
            set_time_limit(36000);
            $siteid = $_REQUEST['siteid'];
            $models = UebModel::model('EbaySiteMapAccount')->findAll('siteid=:siteid',array(':siteid'=>$siteid));
            if(!empty($models))
            {
                foreach ($models as $model)
                {
                    try{
                        $this->actionByaccount($model->ebay_account_id);
                    }catch(Exception $e){
                        continue;
                    }
                }
            }
        }
        else
        {
            $siteids = VHelper::selectAsArray('EbaySiteMapAccount','siteid','is_delete=0',true);
            if(!empty($siteids)){
                foreach ($siteids as $val){
                    MHelper::runThreadSOCKET('/services/ebay/ebaygetstore/index/siteid/'.$val['siteid']);
                    sleep(2);
                }
            }else{
                die('there are no any group!');
            }
        }
    }

    public function actionByaccount($account)
    {
        if(is_numeric($account))
        {
            $account = UebModel::model('Ebay')->findByPk((int)$account);
        }
        if($account instanceof Ebay)
        {
                $ebayGetStore = new EbayGetStore($account);
                $ebayGetStore->CategoryStructureOnly = 'true';
                $ebayGetStore->getContent();
        }
    }


}