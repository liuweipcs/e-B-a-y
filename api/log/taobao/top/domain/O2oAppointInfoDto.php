<?php

/**
 * 预约信息结构
 * @author auto create
 */
class O2oAppointInfoDto
{
	
	/** 
	 * 预约信息唯一编码，如果填写为修改，否则为创建
	 **/
	public $appoint_code;
	
	/** 
	 * 预约信息属性列表
	 **/
	public $appoint_info_params;	
}
?>