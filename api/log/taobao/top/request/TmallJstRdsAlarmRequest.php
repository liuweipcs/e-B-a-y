<?php
/**
 * TOP API: tmall.jst.rds.alarm request
 * 
 * @author auto create
 * @since 1.0, 2016.03.05
 */
class TmallJstRdsAlarmRequest
{
	/** 
	 * 具体信息
	 **/
	private $info;
	
	/** 
	 * rds实例名
	 **/
	private $insname;
	
	/** 
	 * 预警时间
	 **/
	private $time;
	
	private $apiParas = array();
	
	public function setInfo($info)
	{
		$this->info = $info;
		$this->apiParas["info"] = $info;
	}

	public function getInfo()
	{
		return $this->info;
	}

	public function setInsname($insname)
	{
		$this->insname = $insname;
		$this->apiParas["insname"] = $insname;
	}

	public function getInsname()
	{
		return $this->insname;
	}

	public function setTime($time)
	{
		$this->time = $time;
		$this->apiParas["time"] = $time;
	}

	public function getTime()
	{
		return $this->time;
	}

	public function getApiMethodName()
	{
		return "tmall.jst.rds.alarm";
	}
	
	public function getApiParas()
	{
		return $this->apiParas;
	}
	
	public function check()
	{
		
		RequestCheckUtil::checkNotNull($this->info,"info");
		RequestCheckUtil::checkNotNull($this->insname,"insname");
		RequestCheckUtil::checkNotNull($this->time,"time");
	}
	
	public function putOtherTextParam($key, $value) {
		$this->apiParas[$key] = $value;
		$this->$key = $value;
	}
}
