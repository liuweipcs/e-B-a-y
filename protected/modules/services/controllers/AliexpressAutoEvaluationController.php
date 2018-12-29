<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/10/23
 * Time: 19:33
 */

class AliexpressAutoEvaluationController
{
    /**
     * @desc 速卖通自动评价
     */
    public function AliexpressAutoEvaluation($cateId = 0)
    {
        //实例化这个类
        $orderObj = new GetChildrenAutoEvaluationPostCategory();
        //构造参数
        $response_s = $orderObj
            ->setCateId($cateId)
            ->setAccessToken($this->access)
            ->putOtherTextParam('app_key', $this->app_key)
            ->putOtherTextParam('secret_key', $this->secret_key);
        //引入发送类
        $client = new Client();
        //压入参数
        $client->setRequest($response_s);
        //发送请求
        $response = $client->exec();

        $this->render('aliexpressautoevaluation', array(
            'model' => $model,
            'modelName'=>$model_name,
            'key'=>$key,
        ));
    }
}
