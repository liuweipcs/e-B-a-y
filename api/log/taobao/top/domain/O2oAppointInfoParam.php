<?php

/**
 * 预约信息属性列表
 * @author auto create
 */
class O2oAppointInfoParam
{
	
	/** 
	 * 预约信息属性名,如必填的有seller_id（卖家主账号）, customer_nick（客户淘宝nick）, mall_code（门店编码）, appoint_time（预约时间，格式yyyyMMddHHmmss）, ww_nick（卖家客服子账号nick）, ww_user_id（卖家客服子账号用户id），mall_address_city(门店所在的市)，customer_phone(买家客户电话)等。可选的有appoint_status(默认0，预约状态：0-未确认，1-已确认，2-已到店)，mall_name(门店名称), mall_phone(门店电话),mall_address_province(门店所在的省),  mall_address_area(门店所在的街道村镇),mall_address_detail(门店所在的详细街道地址),designer_code(设计师编码),designer_name（设计师名称）,designer_phone（设计师电话）,designer_image(设计师图像url地址)，designer_introduction(设计师描述)，ww_phone(旺旺客服电话)，house_address_province(装修房屋所在的省), house_address_city(装修房屋所在的市), house_address_area(装修房屋所在的街道), house_address_detail(装修房屋所在的详细街道地址),house_area(装修面积), house_decoration(装修类型，如新房装修，二手房装修等), remark(备注信息)等，可以根据自己的需要增加属性字段。
	 **/
	public $param_name;
	
	/** 
	 * 属性值
	 **/
	public $param_value;	
}
?>