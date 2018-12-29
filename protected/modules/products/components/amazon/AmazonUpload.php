<?php

Yii::import('application.vendors.amazon.*');
Yii::import("application.modules.services.components.*");
Yii::import("application.modules.services.modules.amazon.models.*");
Yii::import("application.modules.services.modules.amazon.components.*");

/**
 * @author mrlina <714480119@qq.com>
 * @package ~.*
 */

Abstract class AmazonUpload
{
	const PRODUCT    = 1;
	const RELATETION = 2;
	const IMAGE      = 3;
	const PRICE      = 4;
	const INVENTORY  = 5;

	// 线上服务器图片路径
	const IMAGE_BASE_URL= 'http://lazada.doact.online';

	/**
	 * ueb_amazon_product id number
	 * 
	 * @var int 
	 */
	public $id = -1;
	public $uid;

	/**
	 * 产品
	 * 
	 * @var Object
	 */
	public $product = null;

	/**
	 * 产品信息
	 * 
	 * @var Object
	 */
	public $description = null;

	/**
	 * 产品描述
	 * 
	 * @var Object
	 */
	public $descinfo = null;

	/**
	 * 子sku产品
	 * 
	 * @var null
	 */
	public $sonskues = null;

	/**
	 * 其它产品明细
	 * 
	 * @var object
	 */
	public $productoth = null;

	/**
	 * covertor
	 * 
	 * @var ArrayToXML
	 */
	protected $arr2xml = null;

	/**
	 * __construct description
	 */
	public function __construct	($id,$userid)
	{	
		$this->id = $id;
		if($userid){
			$this->uid = $userid;
		} else {
			$this->uid = 1;
		}
		$this->init();
	}

	/**
	 * @inheridoc
	 * 
	 * @noreturn
	 */
	public function init()
	{
		$this->_prevHandleProduct();
		$this->arr2xml = new ArrayToXML();
		$this->arr2xml->withDeclaration(false);
	}

	/**
	 * @inheridoc
	 * 
	 * @return bool
	 */
	public function uploadProduct($id = null)	
	{
		$ret = new stdClass();
		$ret->errcode = true;

		// 开启事务
        $transaction = UebModel::model('AmazonProductMain')->getDbConnection()->beginTransaction();

		try {
			$this->createProduct();
			$this->updateQuantityAvaiable();
			$this->assignPrice();
			$this->sendProductImage();
			$this->establishRelationships();			

			//提交事务
			$transaction->commit();
		} catch (Exception $e) {
			$transaction->rollback();			

			$ret->errcode = false;
			$ret->message = $e->getMessage();
		}

		return $ret;
	}

	/**
	 * prev handle description
	 * 
	 * @noreturn
	 */
	protected function _prevHandleProduct()
	{
		$product = UebModel::model('AmazonProductMain')->findByPk($this->id);

		if (empty($product)) {
			throw new Exception("产品不存在", 1);
		}

		//转换产品一些字段

		$product->xsd_type = json_decode($product->xsd_type, true);

		$product->launch_date = $product->launch_date ? date('Y-m-d\TH:i:s', $product->launch_date) : '';
		$product->discontinue_date = $product->discontinue_date ? date('Y-m-d\TH:i:s', $product->discontinue_date) : '';
		$product->release_date = $product->release_date ? date('Y-m-d\TH:i:s', $product->release_date) : '';

		$product->rebate = unserialize($product->rebate);
		$product->rebate['RebateStartDate'] = date('Y-m-d\TH:i:s', $product->rebate['RebateStartDate']);
		$product->rebate['RebateEndDate'] = date('Y-m-d\TH:i:s', $product->rebate['RebateEndDate']);

		$product->effective_from_date = date('Y-m-d', $product->effective_from_date);
		$product->effective_through_date = date('Y-m-d', $product->effective_through_date);

		//产品的图片
		$product->upload_images = json_decode($product->upload_images, true);

		$this->product = $product;

		$description = $this->product->description;

		$temp = json_decode($description->item_dimensions, true);
		$temp['Weight'] = number_format(round($temp['Weight'] / 1000, 2), 2);
		$description->item_dimensions = $temp;

		$temp = json_decode($description->package_dimensions, true);
		$temp['Weight'] = number_format(round($temp['Weight'] / 1000, 2), 2);
		$description->package_dimensions = $temp;

		$description->package_dimensions = json_decode($description->package_dimensions, true);
		$description->package_weight     = json_decode($description->package_weight, true);
		$description->shipping_weight    = json_decode($description->shipping_weight, true);
		$description->delivery_channel   = json_decode($description->delivery_channel, true);
		$description->purchasing_channel = json_decode($description->purchasing_channel, true);

		$this->description = $description;

		$descinfo = UebModel::model('AmazonProductDescInfo')->find('main_id=:id AND language_code=:lan',
			array(
				':id' => $this->product->id,
				':lan' => $this->_getLanCodeBySite($this->_getWebSite()),
				)
			);

		if (!$descinfo) {
			throw new Exception("产品描述信息不完善", 1);
		}

		$i = 0;
		$temp = array();
		foreach (explode("###@", $descinfo->keywords) as $value) {
			if ($i >= 5)
				break;
			if(trim($value)){
				$temp[] = $value;
			}

			$i++;
		}
		
		$descinfo->keywords = $temp;

		$this->descinfo = $descinfo;

		//子sku列表
		$this->sonskues = $this->product->sonskues;
	}

	/**
	 *
	 * 用于判断数组是否存在空值
	 * 
	 * @param  array|string $var 
	 * @return boolean
	 */
	public function isEmpty($var)
	{
		if (is_array($var)) {
			foreach ($var as $value) {
				if ($this->isEmpty($value)) {
					return true;
				}
			}
			return false;
		} else {
			return empty($var);
		}
	}

	/**
	 * 生成唯一的流水号
	 * 
	 * @return string
	 */
	public function genUniqidId()
	{
		//default
		return substr(uniqid(mt_rand(), true),0, 11);
	}

	/**
	 * 将路径树转为递归数组
	 * 
	 * @param  array $array
	 * @return array
	 */
	public function formatArrayData( array $array)
	{
		$amz = array();

		foreach ($array as $key => $value) {
			//Reference the Array $amz
			$ref = & $amz;

			foreach (explode('-', $key) as $v) {
				if (!isset($ref[$v])) {
					$ref[$v] = '';
				}

				$ref = & $ref[$v];
			}

			$ref = $value;
		}

		return $amz;
	}

	/**
	 * 添加一行日志
	 * 
	 * @param array $array record
	 * @throws Exception
	 */
	public function addLog(array $array)
	{
		$log = new AmazonUpLog();

		$log->account_id = $array['account_id'];
		$log->amz_product_id = $array['amz_product_id'];
		$log->title = $array['title'];
		$log->content = $array['content'];
		$log->type = $array['type'];
		$log->num = $array['num'];
		$log->operator = $array['operator'];
		$log->creator = Yii::app()->user->id?Yii::app()->user->id:$this->uid;
		$log->create_date = time();

		$log->save();

		if (empty($log->id)) {
			throw new Exception('添加日志出错', 1);
		}
	}

	/**
	 * 获取账是哪个点
	 * 
	 * @return string
	 */
	protected function _getWebSite()
	{
		$accountId = $this->product->account_id;
		$account = UebModel::model('AmazonAccount')->findByPk($accountId);

		return $account->site;
	}

	/**
	 * get language code by site name
	 * 
	 * @param  string $site
	 * @return string
	 */
	protected function _getLanCodeBySite($site)
	{
		$map = array (
			'us' => 'english',
			'it' => 'Italia',
			'sp' => 'Spanish',
			'fr' => 'French',
			'de' => 'German',
			'uk' => 'english',
			'jp' => 'Japanese',
			'ca' => 'english',
			'mx' => 'Spanish',
			'au' => 'english'
		);

		return isset($map[$site]) ? $map[$site] : '';
	}

	/**
	 * get currency code by site name
	 * 
	 * @param  string $site
	 * @return string
	 */
	protected function _getCurrencyBySite($site)
	{
		$map = array(
			'us' => 'USD',
			'uk' => 'GBP',
			'jp' => 'JPY',
			'ca' => 'CAD',
			'fr' => 'EUR',
			'sp' => 'EUR',
			'de' => 'EUR',
			'it' => 'EUR',
			'au' => 'AUD',
		);

		return isset($map[$site]) ? $map[$site] : 'DEFAULT';
	}

	/**
	 * 美化XML数据
	 * 
	 * @param  string $xml
	 * @return string
	 */
	protected function prettyXML($xml)
	{
		$dom = new DOMDocument();
		$dom->formatOutput = true;
		$dom->loadXML($xml);

		return $dom->saveXML();
	}

	/**
	 * 得到实际产品上传到amazon的XML数据
	 *
	 * @param array $reqArrList
	 * 
	 * @return string
	 */
	protected function getRealXML(array $reqArrList, $type)
	{
		$xml = '';
		static $submitFeed = null;

		//siglon mod
		if ($submitFeed == null) {
			$submitFeed = new SubmitFeedRequest();
		}

        $xml = $submitFeed->setAccountName($this->product->account->account_name)
				->setServiceUrl()
				->setConfig()
				->setBusinessType($type)
				->setReqArrList($reqArrList)
				->getXML();

		if ($xml != '') {
			$xml = $this->prettyXML($xml);
		}

        return $xml;
	}

	/**
	 * 移除值为空的项
	 * 
	 * @return array
	 */
	protected function removeEmptyItem(array $data)
	{
		//handle descript first
		foreach ($data['DescriptionData'] as $key => $value) {
			if ($this->isEmpty($value)) {
				unset($data['DescriptionData'][$key]);
			}
		}

		foreach ($data as $key => $value) {
			if ($this->isEmpty($value)) {
				unset($data[$key]);
			}
		}

		return $data;
	}

	/**
	 * remove empty item for specified key
	 * 
	 * @param  array  &$array
	 * @param  string $key
	 * @noreturn
	 */
	protected function removeEmptyItemByKey(array &$array, $key)
	{
		if (!isset($array[$key])) return;

		foreach ($array[$key] as $k => $value) {
			if ($this->isEmpty($value)) {
				unset($array[$key][$k]);
			}
		}
	}

	/**
	 * 上传图片
	 * 
	 * @return bool
	 */
	protected function sendProductImage()
	{
		if($this->product->product_is_multi == 0){
			if (empty($this->product->upload_images)) {
			return;
			}
		
			foreach ($this->product->upload_images as $key => $id) {
				$type= ($key==0)?'Main':sprintf('PT%d', $key);
				$urldir=UebModel::model('AmazonImage')->findByPk($id)->image_url;
				$url = self::IMAGE_BASE_URL . $urldir;
				$reqArrList[] = array(
					'sku' => $this->product->seller_sku,
					'type' => $type,
					'url' => $url,
				);	
			}
		}

		//子sku图片上传
		if ($this->product->product_is_multi == 2) {
			foreach ($this->sonskues as $index => $sonprd) {

				$idArr= json_decode($sonprd->upload_images, true);

				if (empty($idArr)) {
					continue;
				}
				$images = array();
				foreach ($idArr as $key => $value) {
					$criteria = new CDbCriteria();
					$criteria->select = 'image_url';
					$criteria->condition = "id='".$value."'";
					$images[] = UebModel::model('AmazonImage')->find($criteria)->image_url;

					if (empty($images)) {
						continue;
					}
				}

				if (empty($images)) {
					continue;
				}


				$reqArrList [] = array(
					'sku' => $sonprd->seller_sku,
					'type' => 'Main',
					'url' => self::IMAGE_BASE_URL . $images[0],
				);

				$i = 0;

				foreach ($images as $val) {
					if ($i == 0) {
						$i++;
						continue;
					}
					if ($i > 8) break;

					$reqArrList[] = array(
						'sku' => $sonprd->seller_sku,
						'type' => sprintf('PT%d', $i),
						'url' => self::IMAGE_BASE_URL . $val,
					);

					$i++;
				}
			}
		}

		//一次性组装该XML, 不分开处理

		$found = UebModel::model('AmazonProductTask')->find("account_id=:aid AND amz_product_id=:id AND type=:type AND sku=:sku",
			array(
				':aid' => $this->product->account_id,
				':id' => $this->product->id,
				':type' => self::IMAGE,
				':sku' => $this->product->sku,
				));

		$model = !empty($found) ? $found : new AmazonProductTask();

		$model->flow_no = $this->genUniqidId();
		$model->account_id = $this->product->account_id;
		$model->amz_product_id = $this->product->id;
		$model->sku = $this->product->sku;
		$model->type = self::IMAGE;
		$model->xml = $this->getRealXML($reqArrList, SubmitFeedRequest::SEND_IMAGE);
		$model->status = 1;
		$model->creator = Yii::app()->user->id?Yii::app()->user->id:$this->uid;
		$model->create_date = time();	

		$model->save();

		if (empty($model->id)) {
			throw new Exception("保存图片XML数据出错", 1);
		}

		//日志
		$log = new AmazonUpLog();

		$log->account_id = $this->product->account_id;
		$log->amz_product_id = $this->product->id;
		$log->title = empty($found) ? '上传图片' : '更新图片';
		$log->content = '';
		$log->type = self::IMAGE;
		$log->num = count($reqArrList);
		$log->operator = empty($found) ? 1: 2;
		$log->creator = Yii::app()->user->id?Yii::app()->user->id:$this->uid;
		$log->create_date = time();

		$log->save();

		if (empty($log->id)) {
			throw new Exception('添加图片日志出错', 1);
		}
	}

	/**
	 * 上传库存
	 * 
	 * @return bool
	 */
	protected function updateQuantityAvaiable()
	{
		$xmls = array();

		//无论单品还是多属性都更父sku库存
		if($this->product->product_is_multi == 0){
			$reqArrList = array(
						array(
							'sku' => $this->product->seller_sku,
							'qty' => $this->product->inventory ? $this->product->inventory : 100,
							'latency' => 2,
						),
					);
		}
		

		//多属性
		if ($this->product->product_is_multi == 2) {
			foreach ($this->sonskues as $sonprd) {
				$reqArrList [] = array(
					'sku' => $sonprd->seller_sku,
					'qty' => $sonprd->inventory,
					'latency' => 2,
				);
			}
		}

		// $xmls[$this->product->sku] = $this->getRealXML($reqArrList, SubmitFeedRequest::AVAILABLE_INVENTORY);

		//一次性组装该XML, 不分开处理
		// echo 'kucun';
		// var_dump(Yii::app()->user->id?Yii::app()->user->id:$this->uid);
		$found = UebModel::model('AmazonProductTask')->find("account_id=:aid AND amz_product_id=:id AND type=:type AND sku=:sku",
			array(
				':id' => $this->product->id,
				':aid' => $this->product->account_id,
				':type' => self::INVENTORY,
				':sku' => $this->product->sku,
				));

		$model = !empty($found) ? $found : new AmazonProductTask();

		$model->flow_no = $this->genUniqidId();
		$model->account_id = $this->product->account_id;
		$model->amz_product_id = $this->product->id;
		$model->sku = $this->product->sku;
		$model->type = self::INVENTORY;
		$model->xml = $this->getRealXML($reqArrList, SubmitFeedRequest::AVAILABLE_INVENTORY);
		$model->status = 1;
		$model->creator = Yii::app()->user->id?Yii::app()->user->id:$this->uid;
		$model->create_date = time();	

		$model->save();

		if (empty($model->id)) {
			throw new Exception("保存库存XML数据出错", 1);
		}

		//日志

		$log = new AmazonUpLog();

		$log->account_id = $this->product->account_id;
		$log->amz_product_id = $this->product->id;
		$log->title = empty($found) ? '上传库存' : '更新库存';
		$log->content = '';
		$log->type = self::INVENTORY;
		$log->num = count($reqArrList);
		$log->operator = empty($found) ? 1: 2;
		$log->creator = Yii::app()->user->id?Yii::app()->user->id:$this->uid;
		$log->create_date = time();

		$log->save();

		if (empty($log->id)) {
			throw new Exception('添加库存日志出错', 1);
		}
	}

	/**
	 * 上传价格
	 * 
	 * @return bool
	 */
	protected function assignPrice()
	{
		$xmls  = array(); // ???

		$sdate    = date("Y-m-d\TH:i:s\Z", time());
		$edate    = date('Y-m-d\TH:i:s\Z', time() + 90* 24 * 60 * 60);
		$currency = $this->_getCurrencyBySite($this->_getWebSite());
		if($this->product->product_is_multi == 0){
				$reqArrList = array(
				array(
					'sku' => $this->product->seller_sku,
					'currency' => $currency, 
					'stdprice' => $this->product->price,
					'stime' => $sdate,
					'etime' => $edate,
					'saleprice' => round($this->product->price*$this->product->discountrate, 2),
				),
			);
		}
		


		if ($this->product->product_is_multi == 2) {
			foreach ($this->sonskues as $sonprd) {
				$reqArrList [] = array(
					'sku' => $sonprd->seller_sku,
					'currency' => $currency,
					'stdprice' => $sonprd->price,
					'stime' => $sdate,
					'etime' => $edate,
					'saleprice' => round($sonprd->price*$this->product->discountrate, 2),
				);
			}
		}

		//一次性组装该XML, 不分开处理

		$found = UebModel::model('AmazonProductTask')->find("account_id=:aid AND amz_product_id=:id AND type=:type AND sku=:sku",
			array(
				':id' => $this->product->id,
				':aid' => $this->product->account_id, 
				':type' => self::PRICE,
				':sku' => $this->product->sku,
				));

		$model = !empty($found) ? $found : new AmazonProductTask();

		$model->flow_no = $this->genUniqidId();
		$model->account_id = $this->product->account_id;
		$model->amz_product_id = $this->product->id;
		$model->sku = $this->product->sku;
		$model->type = self::PRICE;
		$model->xml = $this->getRealXML($reqArrList, SubmitFeedRequest::PRICE);
		$model->status = 1;
		$model->creator = Yii::app()->user->id?Yii::app()->user->id:$this->uid;
		$model->create_date = time();	

		$model->save();

		if (empty($model->id)) {
			throw new Exception("保存价格XML数据出错", 1);
		}

		//日志

		$log = new AmazonUpLog();

		$log->account_id = $this->product->account_id;
		$log->amz_product_id = $this->product->id;
		$log->title = empty($found) ? '上传价格' : '更新价格';
		$log->content = '';
		$log->type = self::PRICE;
		$log->num = count($reqArrList);
		$log->operator = empty($found) ? 1: 2;
		$log->creator = Yii::app()->user->id?Yii::app()->user->id:$this->uid;
		$log->create_date = time();

		$log->save();

		if (empty($log->id)) {
			throw new Exception('添加价格日志出错', 1);
		}
	}

	/**
	 * 上传关系
	 * 
	 * @return bool
	 */
	protected function establishRelationships()
	{
		//单品不用建立关系
		if ($this->product->product_is_multi == 2) {
			$skues = array();

			foreach ($this->sonskues as $sonprd) {
				$skues [] = $sonprd->seller_sku;
			}

			if (empty($skues)) return;

			$reqArrList = array(
				array(
					'parentsku' => $this->product->seller_sku,
					'type' => 'Variation',
					'sku' => $skues,
				),
			);

			$found = UebModel::model('AmazonProductTask')->find("account_id=:aid AND amz_product_id=:id AND type=:type AND sku=:sku", array(
				':id' => $this->product->id,
				':aid' => $this->product->account_id,
				':type' => self::RELATETION,
				':sku' => $this->product->sku,
				));

			$model = !empty($found) ? $found : new AmazonProductTask();

			$model->flow_no = $this->genUniqidId();
			$model->account_id = $this->product->account_id;
			$model->amz_product_id = $this->product->id;
			$model->sku = $this->product->sku;
			$model->type = self::RELATETION;
			$model->xml = $this->getRealXML($reqArrList, SubmitFeedRequest::ESTABLISH_PRODUCT);
			$model->status = 1;
			$model->creator = Yii::app()->user->id?Yii::app()->user->id:$this->uid;
			$model->create_date = time();	

			$model->save();

			if (empty($model->id)) {
				throw new Exception("保存关系XML数据出错", 1);
			}

			//日志

			$log = new AmazonUpLog();

			$log->account_id = $this->product->account_id;
			$log->amz_product_id = $this->product->id;
			$log->title = empty($found) ? '上传关系' : '更新关系';
			$log->content = '';
			$log->type = self::RELATETION;
			$log->num = 1;
			$log->operator = empty($found) ? 1: 2;
			$log->creator = Yii::app()->user->id?Yii::app()->user->id:$this->uid;
			$log->create_date = time();

			$log->save();

			if (empty($log->id)) {
				throw new Exception('添加关系日志出错', 1);
			}

		}
	}

	/**
	 * product feed schema base data
	 * 
	 * @throws Exception
	 * @return array
	 */
	protected function _getBaseInfo()
	{
		return array(
			'SKU' => $this->product->seller_sku,
			'StandardProductID' =>array(
				'Type' => $this->product->standard_product_id_type,
				'Value' => $this->product->standard_product_id,
			),

			'GtinExemptionReason' => $this->product->gtin_exemption_reason,

			'RelatedProductID' => array(
				'Type' => $this->product->related_product_id_type,
				'Value' => $this->product->related_product_id,
			),

			'ProductTaxCode' => $this->product->product_tax_code,

			'LaunchDate' => $this->product->launch_date,
			'DiscontinueDate' => $this->product->discontinue_date,
			'ReleaseDate' => $this->product->release_date,
			'ExternalProductUrl' => $this->product->external_product_url,
			'OffAmazonChannel' => $this->product->offamazon_channel,
			'OnAmazonChannel' => $this->product->on_amazon_channel,

			'Condition' => array(
				'ConditionType' => $this->product->condition_type,
				'ConditionNote' => $this->product->condition_note,
			),

			'Rebate' => array(
				0 => array(
					'RebateStartDate' => $this->product->rebate['RebateStartDate'],
					'RebateEndDate' => $this->product->rebate['RebateEndDate'],
					'RebateMessage' => $this->product->rebate['RebateMessage'],
					'RebateName' => $this->product->rebate['RebateName'],
				),
			), 

			'ItemPackageQuantity' => 1,
			'NumberOfItems' => $this->product->number_of_items,

			'LiquidVolume' => array(
				'@unitOfMeasure' => $this->product->liquid_volumey_type,
				'%' => $this->product->liquid_volumey,
			),

			'DescriptionData' => array(
				'Title' => $this->descinfo->title,
				'Brand' => $this->description->brand,
				'Designer' => $this->description->designer,
				'#Description' => strip_tags($this->descinfo->description,'<b><p><br/><br>'),

				'BulletPoint' => array(
					$this->descinfo->bullet_point1,
					$this->descinfo->bullet_point2,
					$this->descinfo->bullet_point3,
					$this->descinfo->bullet_point4,
					$this->descinfo->bullet_point5,
				),

				'ItemDimensions' => array(
					'Length' => array(
							'@unitOfMeasure' => 'CM',
							'%' => $this->description->item_dimensions['Length'],
						),
					'Width' => array(
							'@unitOfMeasure' => 'CM',
							'%' => $this->description->item_dimensions['Width'],
						),
					'Height' => array(
							'@unitOfMeasure' => 'CM',
							'%' => $this->description->item_dimensions['Height'],
						),
					'Weight' => array(
							'@unitOfMeasure' => 'KG',
							'%' => $this->description->item_dimensions['Weight'],
						),
				),

				'PackageDimensions' => array(
					'Length' => array(
							'@unitOfMeasure' => 'CM',
							'%' => $this->description->package_dimensions['Length'],
						),
					'Width' => array(
							'@unitOfMeasure' => 'CM',
							'%' => $this->description->package_dimensions['Width'],
						),
					'Height' => array(
							'@unitOfMeasure' => 'CM',
							'%' => $this->description->package_dimensions['Height'],
						),
					'Weight' => array(
							'@unitOfMeasure' => 'KG',
							'%' => $this->description->package_dimensions['Weight'],
						),
				),

				'PackageWeight' => array(
					'@unitOfMeasure' => $this->description->package_weight['unitOfMeasure'],
					'%' => $this->description->package_weight['Value'],
				),

				'ShippingWeight' => array(
					'@unitOfMeasure' => $this->description->shipping_weight['unitOfMeasure'],
					'%' => $this->description->shipping_weight['Value'],
				),

				'MerchantCatalogNumber' => $this->description->merchant_catalog_number,

				'MSRP' => array(
					'@currency' => $this->description->msrp_type,
					'%' => $this->description->msrp,
				),

				'MSRPWithTax' => array(
					'@currency' => $this->description->msrp_with_tax_type,
					'%' => $this->description->msrp_with_tax,
				),

				'MaxOrderQuantity' => $this->description->max_order_quantity,

				'SerialNumberRequired' => $this->description->serial_number_required,

				'Prop65' => $this->description->prop65,

				'CPSIAWarning' => $this->description->CPSIAWarning,

				'CPSIAWarningDescription' => $this->description->CPSIAWarning_description,

				'LegalDisclaimer' => $this->description->legal_disclaimer,

				'Manufacturer' => $this->description->manufacture,

				'MfrPartNumber' => $this->description->mfr_partnumber,

				'SearchTerms' => $this->descinfo->keywords,//explode(',', $this->descinfo->keywords),

				'PlatinumKeywords' => explode(',', $this->description->platinum_keywords),

				'Memorabilia' => $this->description->memorabilia,

				'Autographed' => $this->description->autographed,

				'UsedFor' => $this->description->used_for,

				'ItemType' => $this->description->item_type,

				'OtherItemAttributes' => $this->description->other_item_attributes,

				'TargetAudience' => $this->description->target_audience,

				'SubjectContent' => $this->description->subject_content,

				'IsGiftWrapAvailable' => $this->description->is_gift_wrap_available,

				'IsGiftMessageAvailable' => $this->description->is_gift_message_available,

				'PromotionKeywords' => explode(',', $this->description->promotion_keywords),

				'IsDiscontinuedByManufacturer' => $this->description->is_discontinued_by_manufacturer,

				'DeliveryScheduleGroupID' => $this->description->delivery_schedule_group_id,

				'DeliveryChannel' => $this->description->delivery_channel,

				'PurchasingChannel' => $this->description->purchasing_channel,

				'MaxAggregateShipQuantity' => $this->description->max_aggregate_ship_quantity,

				'IsCustomizable' => $this->description->is_customizable,

				'CustomizableTemplateName' => $this->description->customizable_template_name,

				'RecommendedBrowseNode' => $this->product->btg_node,

				'MerchantShippingGroupName' => $this->description->merchant_shipping_group_name,

				'FEDAS_ID' => $this->description->FEDAS_ID,

				'TSDAgeWarning' => $this->description->TSD_age_warning,

				'TSDWarning' => $this->description->TSD_warning,

				'TSDLanguage' => $this->description->TSD_language,

				'OptionalPaymentTypeExclusion' => $this->description->optional_payment_type_exclusion,

				'DistributionDesignation' => $this->description->distribution_designation,
			),

			'PromoTag' => array(
				'PromoTagType' => $this->product->promo_tag_type,
				'EffectiveFromDate' => $this->product->effective_from_date,
				'EffectiveThroughDate' => $this->product->effective_through_date,
			),

			'DiscoveryData' => array(
				'Priority' => $this->product->priority,
				'BrowseExclusion' => $this->product->browse_exclusion,
				'RecommendationExclusion' => $this->product->recommendation_exclusion,
			),

			'ProductData' => 'xxx', //占位
		);
	}


	/**
	 * create product schema
	 * 
	 * @noreturn
	 */
	protected abstract function createProduct();
}