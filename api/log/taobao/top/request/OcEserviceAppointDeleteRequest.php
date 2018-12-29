<?php
/**
 * TOP API: taobao.oc.eservice.appoint.delete request
 * 
 * @author auto create
 * @since 1.0, 2016.05.09
 */
class OcEserviceAppointDeleteRequest
{
	/** 
	 * 预约信息唯一编码
	 **/
	private $code;
	
	/** 
	 * 卖家的sellerId
	 **/
	private $sellerId;
	
	private $apiParas = array();
	
	public function setCode($code)
	{
		$this->code = $code;
		$this->apiParas["code"] = $code;
	}

	public function getCode()
	{
		return $this->code;
	}

	public function setSellerId($sellerId)
	{
		$this->sellerId = $sellerId;
		$this->apiParas["seller_id"] = $sellerId;
	}

	public function getSellerId()
	{
		return $this->sellerId;
	}

	public function getApiMethodName()
	{
		return "taobao.oc.eservice.appoint.delete";
	}
	
	public function getApiParas()
	{
		return $this->apiParas;
	}
	
	public function check()
	{
		
		RequestCheckUtil::checkNotNull($this->code,"code");
		RequestCheckUtil::checkNotNull($this->sellerId,"sellerId");
	}
	
	public function putOtherTextParam($key, $value) {
		$this->apiParas[$key] = $value;
		$this->$key = $value;
	}
}
