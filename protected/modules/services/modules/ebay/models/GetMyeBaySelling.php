<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/8/3 0003
 * Time: 下午 7:35
 */
class GetMyeBaySelling extends EbayApiAbstract
{
    public $type = 'ActiveList';
    public $ActiveList;
    public $DetailLevel;//取值：ReturnAll、ReturnSummary

    public $ebayAccountModel;

    protected $sendXml;

    public function __construct()
    {
        if(func_num_args() > 0)
            $this->ini(func_get_arg(0));
    }

    public function ini($account)
    {
        if(is_numeric($account) && $account > 0 && $account%1 === 0)
        {
            $account = UebModel::model('Ebay')->findByPk((int)$account);
        }
        if($account instanceof Ebay)
        {
            $this->ebayAccountModel = $account;
        }
        else
        {
            throw new Exception('ebay账号数据未找到');
        }
    }

    public function sendRequest()
    {
        $this->response = '';
        $this->setRequest()->sendHttpRequest();
    }

    public function handleResponse()
    {
        $this->sendRequest();
        findClass($this->response,1);
    }

    public function setRequest()
    {
        $this->setUserToken($this->ebayAccountModel->user_token);
        $ebayKeys = ConfigFactory::getConfig('ebayKeys');
        $this->appID = $ebayKeys['appID'];
        $this->devID = $ebayKeys['devID'];
        $this->certID = $ebayKeys['certID'];
        $this->serverUrl = $ebayKeys['serverUrl'];
        $this->siteID = 0;
        $this->compatabilityLevel = 983;
        $this->verb = get_class($this);
        return $this;
    }

    public function requestXmlBody()
    {
        $this->sendXml = '<?xml version="1.0" encoding="utf-8" ?>';
        $this->sendXml .= '<GetMyeBaySellingRequest xmlns="urn:ebay:apis:eBLBaseComponents">';
        $this->sendXml .= "<RequesterCredentials><eBayAuthToken>{$this->ebayAccountModel->user_token}</eBayAuthToken></RequesterCredentials>";

        switch($this->type)
        {
            case 'ActiveList':
                $this->sendXml .= '<ActiveList>';
                if(isset($this->ActiveList['Include']))
                    $this->sendXml .= "<Include>{$this->ActiveList['Include']}</Include>";
                if(isset($this->ActiveList['IncludeNotes']))
                    $this->sendXml .= "<Include>{$this->ActiveList['IncludeNotes']}</Include>";
                if(isset($this->ActiveList['ListingType']))
                    $this->sendXml .= "<Include>{$this->ActiveList['ListingType']}</Include>";
                if(isset($this->ActiveList['Pagination']))
                {
                    $this->sendXml .= '<Pagination>';
                    $this->ActiveList['Pagination']['EntriesPerPage'] = isset($this->ActiveList['Pagination']['EntriesPerPage']) ? ($this->ActiveList['Pagination']['EntriesPerPage'] > 200 ? 200:$this->ActiveList['Pagination']['EntriesPerPage']):25;
                    $this->sendXml .= "<EntriesPerPage>{$this->ActiveList['Pagination']['PageNumber']}</EntriesPerPage>";
                    $this->ActiveList['Pagination']['PageNumber'] = isset($this->ActiveList['Pagination']['PageNumber']) ? ($this->ActiveList['Pagination']['PageNumber'] < 1 ? 1:$this->ActiveList['Pagination']['PageNumber']):1;
                    $this->sendXml .= "<PageNumber>{$this->ActiveList['Pagination']['PageNumber']}</PageNumber>";
                    $this->sendXml .= '</Pagination>';
                }
                $this->ActiveList['Sort'] = isset($this->ActiveList['Sort']) ? $this->ActiveList['Sort']:'StartTime';
                $this->sendXml .= "<Sort>{$this->ActiveList['Sort']}</Sort>";
                $this->sendXml .= '</ActiveList>';
        }
        if(isset($this->DetailLevel))
        {
            $this->sendXml .= "<DetailLevel>{$this->DetailLevel}</DetailLevel>";
        }
        $this->sendXml .= '</GetMyeBaySellingRequest>';
    }
}