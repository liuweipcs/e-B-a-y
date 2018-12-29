<?php
/**
 * @Author: anchen
 * @Date:   2017-02-16 14:59:46
 * @Last Modified by:   anchen
 * @Last Modified time: 2017-03-02 11:23:51
 */
/*
 订单异常原因代码
 */
class OrderHelper{
    public  static  function getabnormalcauses($type = null)
    {
        $result = [
            '1' => Yii::t('system', '订单重复'),
            '2' => Yii::t('system', '金额异常'),
            '3' => Yii::t('system', '买家电话号码异常'),
            '4' => Yii::t('system', '国家代码无效'),
            '5' => Yii::t('system', '发货地址异常'),
            '6' => Yii::t('system', 'sku无效'),
            '7' => Yii::t('system', '用户有留言'),
        ];
        if (isset($type))
        {
            return $result[$type];
        }
        return $result;
    }

.
}