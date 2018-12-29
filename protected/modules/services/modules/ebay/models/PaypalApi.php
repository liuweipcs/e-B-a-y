<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/7 0007
 * Time: 上午 9:21
 */
class PaypalApi
{
    public $url = 'https://api-3t.paypal.com/nvp';
    public $data = array();
    protected $response;            //api返回的数据
    public $payPalModel;
    protected $dataToken = array();   //查找数据库自动生成的签字信息
    protected $sendData;            //最终发送给api的数据

    public function setTokenByPayPal($paypal)
    {
        if(is_numeric($paypal))
        {
            $paypal = (new PaypalAccount())->findByPk((int)$paypal);
        }
        elseif (is_string($paypal))
        {
            $paypal = (new PaypalAccount())->find('email=:email',array(':email'=>$paypal));
        }
        if($paypal instanceof PaypalAccount)
        {
            $this->payPalModel = $paypal;
            $this->dataToken = array('USER'=>$this->payPalModel->api_user_name,'PWD'=>$this->payPalModel->api_password,'SIGNATURE'=>$this->payPalModel->api_signature);
        }
    }

    public function getSendData()
    {
        return $this->sendData;
    }

    public function sendHttpRequest()
    {
        $this->sendData = array_merge(array('VERSION'=>200),$this->data,$this->dataToken);
        $data = http_build_query($this->sendData);
        $cn = curl_init();
        curl_setopt($cn,CURLOPT_URL,$this->url);
        curl_setopt($cn,CURLOPT_POST,true);
        curl_setopt($cn,CURLOPT_SSL_VERIFYHOST,false);
        curl_setopt($cn,CURLOPT_SSL_VERIFYPEER,false);
        curl_setopt($cn, CURLOPT_CONNECTTIMEOUT, 180);
        curl_setopt($cn,CURLOPT_POSTFIELDS,$data);
        curl_setopt($cn,CURLOPT_RETURNTRANSFER,true);
        $response = curl_exec($cn);
        curl_close($cn);
        if($response !== false)
        {
            $this->response = array();
            parse_str($response,$this->response);
            return true;
        }
        else
        {
            return false;
        }
    }

    public function sendHttpRequestMany($offsetTime,$count = 2)
    {
        date_default_timezone_set('UTC');
        $startTimestamp = strtotime($this->data['STARTDATE']);
        $endTimestamp = $startTimestamp + $offsetTime;
        $currentTimestamp = time();
        if($endTimestamp > $currentTimestamp)
        {
            $this->data['ENDDATE'] = date('Y-m-d\TH:i:s\Z',$currentTimestamp);
            $offsetTime = $currentTimestamp - $startTimestamp;
        }
        else
        {
            $this->data['ENDDATE'] = date('Y-m-d\TH:i:s\Z',$endTimestamp);
        }
        if($this->sendHttpRequest())
        {
            return true;
        }
        else
        {
            if($count > 0)
            {
                $offsetTime = round($offsetTime/2);
                $count--;
                return $this->sendHttpRequestMany($offsetTime,$count);
            }
            else
            {
                date_default_timezone_set('Asia/Shanghai');
                return false;
            }
        }
    }

    public function getResponse()
    {
        return $this->response;
    }
}