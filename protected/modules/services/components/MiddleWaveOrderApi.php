<?php
class MiddleWaveOrderApi extends MiddleWaveApiAbstract
{
    protected $_urlPath = '/Orders';

    protected $_method = 'post';

    public function insertOrder($params)
    {
        $this->setApiMethod('insertOrder');
        $this->setParams($params);
        return $this->sendHttpRequest();
    }

    public function assignOrder($params)
    {
        $this->setApiMethod('assignOrder');
        $this->setParams($params);
        return $this->sendHttpRequest();
    }

    public function unassignOrder($params)
    {
        $this->setApiMethod('unassignOrder');
        $this->setParams($params);
        return $this->sendHttpRequest();
    }

    public function cancelOrder($params)
    {
        $this->setApiMethod('cancelOrder');
        $this->setParams($params);
        return $this->sendHttpRequest();
    }

    public function orderCompleteStatusToMysql($params)
    {
        $this->setApiMethod('orderCompleteStatusToMysql');
        $this->setParams($params);
        return $this->sendHttpRequest();
    }
    
    public function getInventory($params)
    {
        $this->_urlPath='/stock';
        $this->setApiMethod('orderStockInfo');
        $this->setParams($params);
        return $this->sendHttpRequest();
    }
    
    /**
     * @desc 检查订单是否在中间件存在
     * @param unknown $params
     * @return MiddleWaveOrderApi
     */
    public function getOrderStatus($params)
    {
        $this->_urlPath='/orders';
        $this->setApiMethod('getOrderStatus');
        $this->setParams($params);
        return $this->sendHttpRequest();        
    }
}