<?php

/**
 * 入参信息
 * @author auto create
 */
class WaybillCloudPrintApplyNewRequest
{
	
	/** 
	 * <a href="http://open.taobao.com/doc2/detail.htm?spm=a219a.7629140.0.0.8cf9Nj&treeId=17&articleId=105085&docType=1#1">物流公司Code</a>
	 **/
	public $cp_code;
	
	/** 
	 * 产品类型编码,<a href="http://open.taobao.com/doc2/detail.htm?spm=a219a.7629140.0.0.haIJwt&treeId=17&articleId=105050&docType=1">链接</a>
	 **/
	public $product_code;
	
	/** 
	 * 发货人信息
	 **/
	public $sender;
	
	/** 
	 * 请求面单信息，数量限制为10
	 **/
	public $trade_order_info_dtos;	
}
?>