<?php

/**
 * Environment config
 *      
 * @author Bob <Foxzeng>
 */
if (get_magic_quotes_gpc()) {

    function stripslashes_gpc(&$value) {
        $value = stripslashes($value);
    }

    array_walk_recursive($_GET, 'stripslashes_gpc');
    array_walk_recursive($_POST, 'stripslashes_gpc');
    array_walk_recursive($_COOKIE, 'stripslashes_gpc');
}

class Env {

    const DEVELOPMENT = 'development';
    const TEST = 'test';
    const PRODUCTION = 'production';

    private $_mode = 0;
    private $_debug;
    private $_trace_level;
    private static $_config;

    /**
     * Main configuration
     * This is the general configuration that uses all environments
     */
    private function _main() {
        return array(
            'basePath' => dirname(__FILE__) . DIRECTORY_SEPARATOR . '..',
            'name' => 'Universal E-Business Management platform',
            'language'=>'zh_cn',
            // preloading 'log' component
            'preload' => array('log', 'ulog'),
            // autoloading model and component classes
            'import' => array(
                'application.extensions.*',
                'application.models.*',
                'application.components.*',
                'application.modules.logs.components.*',
                'application.vendors.*',
            	'application.extensions.debug.*'
            ),
            'modules' => array(
                'users',
                'products',
            	'purchases',
            	'orders',
                'systems',
                'logs',
                'purchases',
            	'warehouses',
            	'logistics',
                'commons',
                'services' => array( 
                    'modules' => array(
                        'ebay',
                        'aliexpress',
                        'amazon',
                    	'wish',
                        'website',
                    	'valsun',
						'warehouse',
                    ),
                ),
            	'pda',
            	'report',
				'websites'=> array(
            		'modules' => array(
            					'newfrog',
            					'ecoolbuy',
            		)
				),
            ),
            'defaultController' => 'site',
            'behaviors' => array('ApplicationBehavior'),
            // application components
            'components' => array(
                'user' => array(
                    // enable cookie-based authentication
                    'allowAutoLogin' => true,
					'class' => 'WebUser',
				),  
                'request' => array(
                    'class' => 'application.components.HttpRequest',
                    'enableCsrfValidation' => false,
                ),
                'session' => array(
                    'timeout' => 86400,
                ),
                'authManager' => array(
                    'class'=>'CDbAuthManager',
                    'connectionID'=>'db',
                    'defaultRoles' => array('guest', 'authenticated', 'admin'),
                    'itemTable' => 'ueb_auth_item',
                    'itemChildTable' => 'ueb_auth_item_child',
                    'assignmentTable' => 'ueb_auth_assignment',
                ),
                'errorHandler' => array(
                    // use 'site/error' action to display errors
                    'errorAction' => 'site/error',
                ),
                'urlManager' => array(
                    'urlFormat' => 'path',
                    'showScriptName' => false,
                    'caseSensitive' => false,
                    'rules' => array(                        
                        '<controller:\w+>/<action:\w+>' => '<controller>/<action>',                             
                    ),
                ),
             /*   'log' => array(
                    'class' => 'CLogRouter',
                    'routes' => array(
                        array(
                            'class' => 'CFileLogRoute',
                            'levels' => 'trace,info,error, warning',
                        ),  
                    	array( // configuration for the toolbar
                    		'class'=>'ext.debug.XWebDebugRouter',
                    		'config'=>'alignLeft, opaque, runInDebug, fixedPos, collapsed, yamlStyle',
                    		'levels'=>'error, warning, trace, profile, info',
                    		'allowedIPs'=>array('127.0.0.1'),
                    	),
                    ),
                ),*/
               'ulog' => array(
                    'class' => 'ULogRouter',
                    'routes' => array(
                        array(
                            'class'     => 'UFileLogRoute',
                            'levels'    => 'error,failure,success,info',
                            'types'     => 'ebay,amazon,aliexpress',
                        ),
                        array(
                            'class' => 'UDbLogRoute',
                            'levels' => 'error,failure,success,info',
                            'types' => 'operation,profile',                        
                        ),
                    ),
                ),
                'memcache' => array(
                    'class' => 'system.caching.CMemCache',
                    'servers' => array(
                        array('host' => 'localhost', 'port' => 11211, 'weight' => 100),                      
                    ),
                ),
                 'cache' => array (
                    'class'          => 'system.caching.CFileCache',
                    'directoryLevel' => 2,
                ),
                'curl' => array(
                    'class' => 'ext.Curl',
                	'options'=>array()               
                ),
                'xlogis' => array(
                 	'class' => 'ext.xlogis.Xlogis',
                	'version' => array(),
                	'configs' => array(
	                	'eub' => array(
	                		'api_url' => '',
	                		'label_domain' => '',
	                		'version' => '',
	                		'authenticate' => '',
	                		'print_code' => '4_4'
	                	),
	                	'ebayeub' => array(
	                		'Wsdl' => 'http://epacketws.pushauction.net/v3/orderservice.asmx?WSDL',
	                		'Version' => '3.1.0',
	                		'APIDevUserID' => '',
	                		'APISellerUserToken' => '',
	                		'AppID' => '',
	                		'AppCert' => '',
	                		'APISellerUserID' => '',
	                		'Carrier' => 'CNPOST',
	                		'EMSPickUpType' => '01'
	                	),
	                	'bls' => array(
	                		'api_url' => 'http://42.121.252.25/api/',
	                		'account' => 'huanqiu',
	                		'password' => 'bpost',
	                		'type' => 'LvsParcels'
	                	),
	                	'yyb' => array(
	                		'api_url' => 'http://online.yw56.com.cn/service',
	                		'account' => '301516',
	                		'password' => '28226821',
	                		'token' => 'MzAxNTE2OjI4MjI2ODIx',
	                		'size' => 'CnMiniParcel10x10'
	                	),
                		'byb'=>array(
                		    '_apiSite' => 'http://www.ppbyb.com/api.asp',
                			'account' => '',
                			'password' => '',
                			'_apiKey' => 'dd5d52830588a11b98265a23c0021a3f97',
                			//'size' => 'CnMiniParcel10x10'
                        ),
						'ow' => array(
                				'api_url' => 'http://api.oneworldexpress.cn/',
                		),
                               //顺友API
                		'sy'=>array(
                			//'api_url'=>'http://www.sunyou.hk/api',
							'api_url'=>'http://api.sunyou.hk/order',
                			'account'=> 'SZUNI',
                		    'password'=> '123456',
                			'token'=>'Jn50oIqc/tcRcicVbcLR5g==',
                		)
                			
                	)
                ),
				'db'=>array(
					'enableParamLogging' => false,
                ),
            ),           
            'params' => require(dirname(__FILE__) . '/params.php'),
        );      
    }
    
    /**
     * Development configuration   
     */
    private function _development() {           
        $servers[0] = array( 
            'driver'                => 'mysql',
            'host' 					=> '172.16.1.16',
        	'port' 					=> '3306',
            'class'                 => 'CDbConnection',
            'emulatePrepare'        => true,
            'username'              => 'root',
            'password'              => 'Uebnew_2015',
            'charset'               => 'utf8',
            'tablePrefix'           => 'ueb_',
            'schemaCachingDuration' => 3600,
            'database' => array(
                'db'            => 'ueb_system',
                'db_product'    => 'ueb_product',
                'db_purchase'   => 'ueb_purchase',
            	'db_warehouse'  => 'ueb_warehouse',
            	'db_logistics'  => 'ueb_logistics',
        		'db_order'		=> 'ueb_order',
        		'db_website'    => 'ueb_website',
                'db_crm'        =>'ueb_crm',
            ),
        );
        
        return $servers;
    }
    

    /**
     * Test configuration  
     */
    private function _test() {      
        $servers[0] = array( 
            'driver'                => 'mysql',
            'host' 					=> '172.16.1.16',
        	'port' 					=> '3306',
            'class'                 => 'CDbConnection',
            'emulatePrepare'        => true,
            'username'              => 'root',
            'password'              => 'Uebnew_2015',
            'charset'               => 'utf8',
            'tablePrefix'           => 'ueb_',
            'schemaCachingDuration' => 3600,
            'database' => array(
                'db'            => 'ueb_system',
                'db_product'    => 'ueb_product',
                'db_purchase'   => 'ueb_purchase',
            	'db_warehouse'  => 'ueb_warehouse',
            	'db_logistics'  => 'ueb_logistics',
        		'db_order'		=> 'ueb_order',
        		'db_website'    => 'ueb_website',
                'db_crm'        =>'ueb_crm',
            ),
        );
        
        return $servers;
    }

    
    /**
     * Production configuration
     */
    private function _production() {
        $servers[0] = array(
            'driver' => 'mysql',
            'host' 					=> '172.16.1.16',
        	'port' 					=> '3306',
            'class' => 'CDbConnection',
            'emulatePrepare' => true,
            'username' => 'root',
            'password'              => 'Uebnew_2015',
            'charset' => 'utf8',
            'tablePrefix' => 'ueb_',
            'schemaCachingDuration' => 3600,
            'database' => array(
                'db' => 'ueb_system',
                'db_product' => 'ueb_product',
                'db_purchase' => 'ueb_purchase',
            	'db_warehouse'  => 'ueb_warehouse',
            	'db_logistics'  => 'ueb_logistics',
        		'db_order'		=> 'ueb_order',
        		'db_website'    => 'ueb_website',
                'db_crm'        =>'ueb_crm',
            ),
        );

        return $servers;
    }
    
    /**
     * parse db components
     * 
     * @param array $servers
     * @return array
     */
    protected function _parseDbComponets($servers) {
        $components = array();
         foreach ($servers as $server) {
            foreach ($server['database'] as $dbKey => $val) {
                $components[$dbKey]['class'] =  $server['class'];
                $connectionString = $server['driver'].':host='.$server['host'].';port='.$server['port'].';dbname='.$val;
                $components[$dbKey]['connectionString'] = $connectionString;
                $components[$dbKey]['username'] =  $server['username'];
                $components[$dbKey]['password'] =  $server['password'];
                $components[$dbKey]['charset'] =  $server['charset'];
                $components[$dbKey]['tablePrefix'] =  $server['tablePrefix'];
                $components[$dbKey]['schemaCachingDuration'] =  $server['schemaCachingDuration'];
            }
        }       
        
        return array( 'components' => $components);  
    }

    /**
     * Returns the debug mode
     * @return Bool
     */
    public function getDebug() {
        return $this->_debug;
    }

    /**
     * Returns the trace level for YII_TRACE_LEVEL
     * @return int
     */
    public function getTraceLevel() {
        return $this->_trace_level;
    }

    /**
     * Returns the configuration array depending on the mode
     * you choose
     * @return array
     */
    public static function getConfig() {
        return self::$_config;
    }

    /**
     * Initilizes the Environment class with the given mode
     * @param constant $mode
     */
    function __construct($mode) {
        $this->_mode = $mode;
        $this->setConfig();
    }

    /**
     * Sets the configuration for the choosen environment
     * @param constant $mode
     */
    private function setConfig() {
        
        switch ($this->_mode) {
            case self::DEVELOPMENT:
                self::$_config = array_merge_recursive($this->_main(), $this->_parseDbComponets($this->_development()));
                $this->_debug = TRUE;
                $this->_trace_level = 3;
                break;
            case self::TEST:
                self::$_config = array_merge_recursive($this->_main(), $this->_test());
                $this->_debug = FALSE;
                $this->_trace_level = 0;
                break;           
            case self::PRODUCTION:
                self::$_config = array_merge_recursive($this->_main(), $this->_production());
                $this->_debug = FALSE;
                $this->_trace_level = 0;
                break;
            default:
                self::$_config = $this->_main();
                $this->_debug = TRUE;
                $this->_trace_level = 0;
                break;
        }
    }
    /**
     * 
     */
    public function getDbNameByDbKey($dbKey = 'db'){
    	$modeConfig = array();
    	$mode = isset($this->_mode) ? $this->_mode : '';
    	switch ($mode) {
    		case self::DEVELOPMENT:
    			$modeConfig = $this->_development();
    			break;
    		case self::TEST:
    			$modeConfig = $this->_test();
    			break;
    		case self::PRODUCTION:
    			$modeConfig = $this->_production();
    			break;
    		default:
    			$modeConfig = $this->_production();
    			break;
    	}
    	$dbName = $modeConfig[0]['database'][$dbKey];
    	return $dbName;
    }

}
