<?php 


/**
 * Yyb api class
 * @author Mark lin
 *
 */


class YybLogis extends Ilogis{
	


	private $account;
	
	private $password;
	
	private $api_url;
	
	private $token = 'MzAxNTE2OjI4MjI2ODIx';
	
	protected $size = 'CnMiniParcel10x10';
	
	public function __construct($config)
	{
	
		foreach ($config as $key=>$v)
		{
			$this->{$key} = $v?$v:$this->{$key};
		}
		
	}
	
	
	
	public function getHeaders()
	{
		return array(
		        "Authorization" =>  "basic ".$this->token,
				"Content-Type" => "text/xml;charset=utf-8",
		);
	}
	
	/**
	 * upload message to eub
	 * @see Ilogis::upload()
	 */
	
	public function upload($data,$packageid,$abs = '')
	{
		$url = $this->api_url . '/Users/' .$this->account . '/Expresses';
		$xml = $this->buildXml($data);
		return $this->_exec($url, $xml, 'post');
	}
	
	public function check($packageInfo)
	{
		$url = $this->api_url . '/Users/' .$this->account . '/Expresses?code='.$packageInfo['package_id'];
		echo $url;
		echo '<hr/>';
		return $this->_exec($url, '', 'get');
	}
	
	/**
	 * download a file
	 * @see Ilogis::download()
	 */
	
	public function getLabels($nums)
	{	
		return true;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Ilogis::getALabel()
	 */
	
	public function getALabel($mailnum)
	{
		$url = $this->api_url . '/Users/'.$this->Account.'/Expresses/'.$mailnum.'/'.$this->size.'Label';
		return $this->_exec($url, array(),'get');
	}
	
	
	/**
	 * (non-PHPdoc)
	 * @see Ilogis::trace()
	 */
	
	public function trace($no,$lang='cn')
	{
		return true;
	}
	
	
	/**
	 * (non-PHPdoc)
	 * @see Ilogis::cancel()
	 */
	
	public function cancel($no)
	{
		return true;
	}
	
	
	/**
	 * 确认发货
	 * @param array $data
	 * @param int $packageid
	 */
	
	public function validate($data,$packageid)
	{
		return true;
	}
	
	
	/**
	 * 运单信息
	 * @param string $mailnum
	 * 
	 */
	
	public function order($mailnum)
	{
		return true;
	}
	
	
	/**
	 * 创建xml
	 * @param array $data
	 * @return string
	 */
	
	public function buildXml($filterarray)
	{
		$xmlfilter = "<ExpressType>";
	
		foreach ($filterarray as $key=>$value) {
			if(is_array($value)) {
				$xmlfilter .= " <$key>\n".$this->buildXMLFilter($value)."</$key>\n";
			}else {
				$xmlfilter .= " <$key>$value</$key>\n";	
			}
		}
		$xmlfilter .= "</ExpressType>";

		return $xmlfilter;
	}
	
	
	
    /**
     * request api
     * 
     */
    
    private function _exec($url,$data,$method='get')
    {
    	return parent::execute($url, $data, $method);
    }
	
	function buildXMLFilter ($filterarray) {
		  $xmlfilter = "";
		
		  foreach ($filterarray as $key=>$value) {
		  	if(is_array($value)) {
		  		$xmlfilter .= " <$key>\n".buildXMLFilter($value)."</$key>\n";
		    }else {
		    	
		        $xmlfilter .= " <$key>$value</$key>\n";
		    	
		    }
		  }
	  return $xmlfilter;
	}
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
	
}