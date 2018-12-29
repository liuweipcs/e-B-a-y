<?php
/**
 * TOP API: taobao.oc.eservice.appoint.save request
 * 
 * @author auto create
 * @since 1.0, 2016.05.06
 */
class OcEserviceAppointSaveRequest
{
	/** 
	 * 预约信息结构
	 **/
	private $paramO2oAppointInfoDTO;
	
	private $apiParas = array();
	
	public function setParamO2oAppointInfoDTO($paramO2oAppointInfoDTO)
	{
		$this->paramO2oAppointInfoDTO = $paramO2oAppointInfoDTO;
		$this->apiParas["param_o2o_appoint_info_d_t_o"] = $paramO2oAppointInfoDTO;
	}

	public function getParamO2oAppointInfoDTO()
	{
		return $this->paramO2oAppointInfoDTO;
	}

	public function getApiMethodName()
	{
		return "taobao.oc.eservice.appoint.save";
	}
	
	public function getApiParas()
	{
		return $this->apiParas;
	}
	
	public function check()
	{
		
	}
	
	public function putOtherTextParam($key, $value) {
		$this->apiParas[$key] = $value;
		$this->$key = $value;
	}
}
