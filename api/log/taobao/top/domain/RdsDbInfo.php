<?php

/**
 * rds创建、查询、删除返回结果数据结构
 * @author auto create
 */
class RdsDbInfo
{
	
	/** 
	 * 数据库编码
	 **/
	public $charset;
	
	/** 
	 * 备注
	 **/
	public $comment;
	
	/** 
	 * 数据库id
	 **/
	public $db_id;
	
	/** 
	 * 数据库名称
	 **/
	public $db_name;
	
	/** 
	 * 数据库状态 0：创建中 ；1：激活；3：正在删除
	 **/
	public $db_status;
	
	/** 
	 * 数据库类型，mysql或者mssql
	 **/
	public $db_type;
	
	/** 
	 * rds实例id
	 **/
	public $instance_id;
	
	/** 
	 * rds实例名
	 **/
	public $instance_name;
	
	/** 
	 * rds实例类型,s:共享型，x:专享型
	 **/
	public $instance_type;
	
	/** 
	 * 最大帐号数，1个数据库最多可以创建的账户数目
	 **/
	public $max_account;
	
	/** 
	 * 数据库登录密码
	 **/
	public $password;
	
	/** 
	 * 用户id
	 **/
	public $uid;
	
	/** 
	 * 登录数据库的帐号
	 **/
	public $user_name;	
}
?>