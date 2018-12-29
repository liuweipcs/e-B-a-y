<?php
defined('UEB_STOCK_KEYID') OR define('UEB_STOCK_KEYID', 'yibai');

defined('UEB_STOCK_TIMESTAMP') OR define('UEB_STOCK_TIMESTAMP', 1800);

defined('UEB_STOCK_SECURIRY') OR define('UEB_STOCK_SECURIRY', 'yibaistock20170311');

class MiddleWaveApiAbstract implements ApiInterface
{
    protected $_baseUrl = null;

    protected $_token = null;

    protected $_url = null;

    protected $_urlPath = null;

    protected $_client = null;

    protected $_apiMethod = null;

    protected $_exception = null;

    protected $_method = true;

    protected $_params = array();

    protected $_response = null;

    function stockAuth(){
        $data = array('error'=>-1);
    
        //设置param数组的值
        $param['key'] = UEB_STOCK_KEYID;
        $param['timestamp'] = time();
        $param['ip'] = '';
    
        ksort($param,SORT_REGULAR);
        $urlStr = http_build_query($param,'yibai_','&',PHP_QUERY_RFC1738);
        $securityStr = md5(UEB_STOCK_SECURIRY.$urlStr.UEB_STOCK_SECURIRY, false);
    
        if(!empty($securityStr)){
            $data['param'] = $param;
            $data['sign'] = $securityStr;
            $data['error'] = 1;
        }
    
        return $data;
    }

    public function setApiMethod($method)
    {
        $this->_apiMethod = $method;
        return $this;
    }

    public function __construct()
    {
        $config = include Yii::getPathOfAlias('application.config') . '/api_middle_wave.php';
        if (isset($config['base_url']))
            $this->_baseUrl = $config['base_url'];
        if (isset($config['token']))
            $this->_token = $config['token'];
        $this->_client = new Curl();
        $this->_client->init();
    }

    public function setMethod($type = 'post')
    {
        $this->_method = $type;
        return $this;
    }

    public function buildUrl()
    {
        $this->_url = rtrim($this->_baseUrl, '/') . '/' . ltrim($this->_urlPath, '/') . '/' . ltrim($this->_apiMethod, '/');
        return $this->_url;
    }

    public function setParams($params)
    {
        if (is_array($params))
        {
            foreach ($params as $key => $value)
                $this->_params[$key] = $value;
        }
        else
        {
            $this->_params[] = $params;
        }
        $this->_params['token'] = CJSON::encode($this->stockAuth());
        return $this;
    }

    public function setRequest()
    {
        return $this;
    }

    public function sendHttpRequest()
    {
        try
        {
            $this->buildUrl();
            if ($this->_method == 'post')
                $response = $this->_client->post($this->_url, $this->_params);
            else
                $response = $this->_client->get($this->_url, $this->_params);

            //print_r($this->_params);exit;
            $this->_response = $response;
        }
        catch (Exception $e)
        {
            $this->_exception = $e->getMessage();
        }
        return $this;
    }

    public function getRequest()
    {

    }

    public function getResponse()
    {
        return $this->_response;
    }

    public function isSuccess()
    {
        if ($this->_exception != '')
            return false;
        if (empty($this->_response))
        {
            $this->_exception = 'Server Response Empty';
            return false;
        }
        return true;
    }

    public function getException()
    {
        return $this->_exception;
    }
}