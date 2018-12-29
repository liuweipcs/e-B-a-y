<?php

/**
 * xlogis classs
 * @author Mark lin
 * factory mode
 */

/**
 * 
 * how to use 
 * Yii::app()->xlogis->load('eub')->upload(array $data, int $packageid);
 * Yii::app()->xlogis->load('yyb')->upload(array $data, int $packageid);
 * Yii::app()->xlogis->load('bls')->upload(array $data, int $packageid);
 * 
 ****************
    	$data['ShipToAddress'] = array(
				'Country'	 => 'United Kingdom',  
				'Contact'	 => 'mark lin',
				'Postcode'   => '44630',  
				'Phone'	 	 => '135865845',
				'Province'   => 'Swansea',
				'City' 	     => 'leicester',
				'Street' 	 => 'Esq. Bogota  Col. Providencia'
    	);
    	//线下 e邮宝
    	$data['Item'] = array(
    		array('SKU'=>array('CustomsTitleCN'=>'abcde','CustomsTitleEN'=>'this is test','DeclaredValue'=>9,'Weight'=>8),'SoldQTY'=>2,'Note'=>'test'),
    		array('SKU'=>array('CustomsTitleCN'=>'efadad','CustomsTitleEN'=>'a test','DeclaredValue'=>9,'Weight'=>8),'SoldQTY'=>1,'Note'=>'test')
    	);
    	//ebay eub
    	$data['customerCode'] = 'x90dy79';
    	$x = Yii::app()->xlogis->load('eub')->upload($data,1009);
    	if($x):
    		echo 'ok';
    	else:
    		echo 'not';
    	endif;
    	
 ***************
 */


class Xlogis extends CComponent
{
	
	
	
	private static $instance = array();
	
	public $configs;
	
	public $version;
	
	protected static $_configs = array();
	protected static $_version = array();
	
	
	public function init()
	{
		self::$_configs = $this->configs;
		self::$_version = $this->version;
	}
	
	
	/**
	 * load a logistics
	 * @param string $class
	 */
	
	public static function load($class)
	{
		if (isset(self::$_version[$class]))
		{
			$class = self::$_version[$class];
		}
		Yii::import("ext.xlogis.$class.*");
		if (!isset(self::$instance[$class])) 
		{
			$className = ucfirst($class) . 'Logis';
			self::$instance[$class] = new $className(self::$_configs[$class]);
		}
		return self::$instance[$class];
		
	}
	
}



/**
 * abstract
 * @author Mark lin
 * 
 */

abstract class Ilogis 
{
	
	
	/**
	 * config infomation
	 * @var array
	 */
	public $configs = array();
	
	public $ShipTypeKey;
	public $PickUpTypeKey;
	public $ReturnTypeKey;
	/**
	 * 初始化
	 */
	abstract public function __construct($config);
	
	/**
	 * 发货
	 * @param array $data
	 */
	abstract public function upload($data,$label_info,$packageid);
	
	/**
	 * 批理获取标签
	 * @param array $num
	 */
	abstract public function getALabel($num);
	
	/**
	 * 获取一个标签
	 * @param string $nums
	 */
	abstract public function getLabels($nums);
	
	
	/**
	 * 跟踪订单信息
	 * @param string $no
	 * @param string $lang
	 */
	abstract public function trace($no,$lang);
	
	
	/**
	 * 取消发货
	 * @param string $no
	 */
	abstract public function cancel($no);
	
	
	
	/**
	 * get Curl header
	 * 
	 */
	
	abstract public function getHeaders();
	
	
	
	/**
	 * get addr by type key
	 * @param string $type
	 * @return array
	 */
	
	public function getAddress($type)
	{
		$res = array();
		switch($type){
			case 'from':
				$add = SysConfig::getPairByType($this->ShipTypeKey);
				break;
			case 'pickup':
				$add = SysConfig::getPairByType($this->PickupTypeKey);
				break;
			case 'return':
				$add = SysConfig::getPairByType($this->ReturnTypeKey);
				break;
			case 'onlinefrom':
				$add = SysConfig::getPairByType($this->OnlineShipTypeKey);
				break;
			case 'onlinepickup':
				$add = SysConfig::getPairByType($this->OnlinePickupTypeKey);
				break;
			case 'onlinereturn':
				$add = SysConfig::getPairByType($this->OnlineReturnTypeKey);
				break;
		}
		foreach ($this->mapKey() as $k=>$v){
			$res[$k] = $add[$v];
		}
		$res['CountryCode'] = 'CN';
		return $res;
	}
	/**
	 * map key
	 */
	
	protected function mapKey()
	{
		return array(
				'Contact'    => 'contact',
				'Company'	 => 'company',
				'Country'	 => 'country',  
				'Province'   => 'province',
				'City' 	     => 'city',
				'District'   => 'district',
				'Street' 	 => 'street',
				'Postcode'   => 'zip',
				'Email'	     => 'email',  
				'Mobile'	 => 'mobile',
				'Phone'	     => 'phone',
		);
	}
	
	
	/**
	 * Curl request api
	 * @param string $url
	 * @param array $data
	 * @param array $method
	 */
	
	protected function execute($url,$data=array(),$method='post')
	{
		$arr = $this->getHeaders();
		Yii::app()->curl->setHeaders($arr);

		return Yii::app()->curl->{$method}($url,$data);
	}
	
	
}