<?php
/**
 * 对整个SDK进行配置的基类
 * @package 
 * @license 
 * @author seaqi
 * @contact 980522557@qq.com / xiayouqiao2008@163.com
 * @version $Id: class.config.php 2011-07-20 15:56:00
 */

class Configuration {
// 	//在线订单操作
// 	const ORDERS_OPERATION_URLS = 'http://apisandbox.4pxtech.com:8090/OrderOnline/ws/OrderOnlineService.dll?wsdl';
// 	//在线订单工具
// 	const ORDERS_TOOLS_URLS = 'http://apisandbox.4pxtech.com:8090/OrderOnlineTool/ws/OrderOnlineToolService.dll?wsdl';
// 	const AUTHTOKEN = 'CD29AD1E6703C0DB57271CA42B87A7D9';
// 	const DEBUG = true;
	
	//在线订单操作
	const ORDERS_OPERATION_URLS = 'http://api.4px.com:8058/OrderOnline/ws/OrderOnlineService.dll?wsdl';
	//在线订单工具
	const ORDERS_TOOLS_URLS = 'http://api.4px.com:8059/OrderOnlineTool/ws/OrderOnlineToolService.dll?wsdl';
	const AUTHTOKEN = '08ECA1DD42D9D68EAC84D326EFFF767A';
	const DEBUG = true;
	
/* 	
	public static function debug($debugMessage) {
		$debug_logfile_path = 'D:/xampp/www/4px_sdk/log/myDEBUG-' . time() . '-' . mt_rand(1000,999999) . '.log';;
		if(self::DEBUG) {
			@ini_set('log_errors', 1);          // store to file
			@ini_set('log_errors_max_len', 0);  // unlimited length of message output
			@ini_set('display_errors', 1);      // do not output errors to screen/browser/client
			@ini_set('error_log', $debug_logfile_path);  // the filename to log errors into
			@ini_set('error_reporting', $debugMessage );
		} else {
			@ini_set('display_errors', 0);
		}
	}
*/
}
