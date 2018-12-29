<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/25 0025
 * Time: 下午 4:11
 */
class EbaygetaccountController extends UebController
{
    public function actionCurrentbalance()
    {
        set_time_limit(600);
        $socketNum = 2;
        if(isset($_GET['line']))
        {
            $startTime = time();
            $line = $_GET['line'];
            $models = (new EbayAccountRemaining())->findAll('id%'.$socketNum.'='.$line);
            foreach ($models as $model) {
                if(time()-$startTime > 580)
                {
                    exit('已到10分钟。');
                }
                var_dump($model->refreshBalance());
                echo '<br/>';
            }
        }
        else
        {
            while($socketNum > 0)
            {
                $socketNum--;
                MHelper::runThreadSOCKET('/services/ebay/ebaygetaccount/currentbalance/line/'.$socketNum);
                sleep(2);
            }
            exit('Done');
        }
    }

    public function actionCurrentbalanceone($id)
    {
        $result = (new EbayAccountRemaining())->findByPk($id)->refreshBalance();
        findClass($result,1);
    }

}