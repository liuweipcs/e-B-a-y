<?php
/**
 * TOP API: taobao.oc.eservice.appoint.list request
 * 
 * @author auto create
 * @since 1.0, 2016.05.09
 */
class OcEserviceAppointListRequest
{
	/** 
	 * 预约信息唯一编码(code, customerNick, customerPhone, houseAddressCity, mallCode 调用时五个可选参数中任选一个作为输入参数)
	 **/
	private $code;
	
	/** 
	 * 买家客户nick(code, customerNick, customerPhone, houseAddressCity, mallCode 调用时五个可选参数中任选一个作为输入参数)
	 **/
	private $customerNick;
	
	/** 
	 * 买家客户电话号码(code, customerNick, customerPhone, houseAddressCity, mallCode 调用时五个可选参数中任选一个作为输入参数)
	 **/
	private $customerPhone;
	
	/** 
	 * 买家客户装修房屋所在的市(code, customerNick, customerPhone, houseAddressCity, mallCode 调用时五个可选参数中任选一个作为输入参数)
	 **/
	private $houseAddressCity;
	
	/** 
	 * 门店编码(code, customerNick, customerPhone, houseAddressCity, mallCode 调用时五个可选参数中任选一个作为输入参数)
	 **/
	private $mallCode;
	
	/** 
	 * 卖家主账号id
	 **/
	private $sellerId;
	
	/** 
	 * 返回结果按预约时间排序，指示升序还是降息，取值asc和desc
	 **/
	private $sortOrder;
	
	/** 
	 * 查询预约的起始时间，格式yyyyMMddHHmmss，默认为当前时间
	 **/
	private $startAppointTime;
	
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

	public function setCustomerNick($customerNick)
	{
		$this->customerNick = $customerNick;
		$this->apiParas["customer_nick"] = $customerNick;
	}

	public function getCustomerNick()
	{
		return $this->customerNick;
	}

	public function setCustomerPhone($customerPhone)
	{
		$this->customerPhone = $customerPhone;
		$this->apiParas["customer_phone"] = $customerPhone;
	}

	public function getCustomerPhone()
	{
		return $this->customerPhone;
	}

	public function setHouseAddressCity($houseAddressCity)
	{
		$this->houseAddressCity = $houseAddressCity;
		$this->apiParas["house_address_city"] = $houseAddressCity;
	}

	public function getHouseAddressCity()
	{
		return $this->houseAddressCity;
	}

	public function setMallCode($mallCode)
	{
		$this->mallCode = $mallCode;
		$this->apiParas["mall_code"] = $mallCode;
	}

	public function getMallCode()
	{
		return $this->mallCode;
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

	public function setSortOrder($sortOrder)
	{
		$this->sortOrder = $sortOrder;
		$this->apiParas["sort_order"] = $sortOrder;
	}

	public function getSortOrder()
	{
		return $this->sortOrder;
	}

	public function setStartAppointTime($startAppointTime)
	{
		$this->startAppointTime = $startAppointTime;
		$this->apiParas["start_appoint_time"] = $startAppointTime;
	}

	public function getStartAppointTime()
	{
		return $this->startAppointTime;
	}

	public function getApiMethodName()
	{
		return "taobao.oc.eservice.appoint.list";
	}
	
	public function getApiParas()
	{
		return $this->apiParas;
	}
	
	public function check()
	{
		
		RequestCheckUtil::checkNotNull($this->sellerId,"sellerId");
	}
	
	public function putOtherTextParam($key, $value) {
		$this->apiParas[$key] = $value;
		$this->$key = $value;
	}
}
