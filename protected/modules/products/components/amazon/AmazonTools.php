<?php 

/**
 * The default template
 *
 * @author mrlina <714480119@qq.com>
 */
Class AmazonTools extends AmazonUpload implements IAmazonUpload
{

	/**
	 * @inheridoc
	 * 
	 * @noreturn
	 */
	public function init()
	{
		parent::init();
	}

	/**
	 * 上转产品
	 *
	 * @return bool
	 */
	protected function createProduct()
	{
		$data = $this->_getBaseInfo();
		$xsd2 = UebModel::model('AmazonProdataxsd')->findByPk($this->product->xsd_type[1]);

		if (empty($xsd2)) {
			throw new Exception("缺少精确的XSD模板信息", 1);
		}

		if ($this->product->product_is_multi == 0) {
			$array = $this->_Tools();
			$this->removeEmptyItemByKey($array, 'Tools');

			$data['ProductData'] = $array;
			$xmls[$this->product->sku] = $this->arr2xml->buildXML($this->removeEmptyItem($data), 'Product');

		} else if ($this->product->product_is_multi == 2) {

			$data['ProductData'] = $this->_Tools();
			$this->removeEmptyItemByKey($data['ProductData'], 'Tools');

			foreach ($this->product->sonskues as $sonprd) {
				$data['DescriptionData']['MfrPartNumber'] = $sonprd->mfr;
				$variations = json_decode($sonprd->variations, true);
				$suffix = sprintf('(%s)', implode('-', array_values($variations)));

				//修改子sku
				$data['SKU'] = $sonprd->sku;

				//修改子sku UPC码
				$data['StandardProductID']['Type']  = $sonprd['standard_product_id_type'];
				$data['StandardProductID']['Value'] = $sonprd['standard_product_id'];

				$data['DescriptionData']['Title'] = $data['DescriptionData']['Title']. $suffix;
				$xmls[$sonprd->sku] = $this->arr2xml->buildXML($this->removeEmptyItem($data), 'Product');
			} 
			//将其推到任务队列
			foreach ($xmls as $sku => $xml) {
				//查找是否已经存在
				$found = UebModel::model('AmazonProductTask')->find("account_id=:aid AND amz_product_id=:id AND type=:type AND sku=:sku",
					array(
						':id' => $this->product->id,
						':aid' => $this->product->account_id,
						':type' => self::PRODUCT,
						':sku' => $sku,
						));

				$model = !empty($found) ? $found : new AmazonProductTask();

				$model->flow_no = $this->genUniqidId();
				$model->account_id = $this->product->account_id;
				$model->amz_product_id = $this->product->id;
				$model->sku = $sku;
				$model->type = self::PRODUCT;
				$model->xml = $this->getRealXML(array($xml), SubmitFeedRequest::NEW_PRODUCT);
				$model->status = 1;
				$model->creator = Yii::app()->user->id?Yii::app()->user->id:$this->uid;
				$model->create_date = time();	

				$model->save();

				if (empty($model->id)) {
					throw new Exception("保存{$sku}产品XML数据出错", 1);
				}
			}

			//记录日志
			$this->addLog(array(
				'account_id' => $this->product->account_id,
				'amz_product_id' => $this->product->id,
				'title' => empty($found) ? '添加产品' : '更新产品',
				'content' => '',
				'type' => self::PRODUCT,
				'num' => 1,
				'operator' => empty($found) ? 1 : 2,
			));	
		}
	}

	/**
	 * do nothing
	 * 
	 * @noreturn 
	 */
	protected function establishRelationships() {}

	protected function _Tools()
	{
		return array(
			'Tools' => array(
				'GritRating' => '',
				'Horsepower' => '',
				'StyleName' => '',
				'FinishTypes' => '',
				'Diameter' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'Length' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'Width' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'Height' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'Weight' => array(
					'@unitOfMeasure' => 'KG',
					'%' => '',
					),
				'PowerSource' => 'battery-powered',
				'Wattage' => '',
				'Voltage' => '',
				'NumberOfItemsInPackage' => '',
				'BatteryTypeLithiumIon' => '',
				'BatteryTypeLithiumMetal' => '',
				'LithiumBatteryEnergyContent' => '',
				'LithiumBatteryPackaging' => '',
				'LithiumBatteryVoltage' => '',
				'LithiumBatteryWeight' => '',
				'NumberOfLithiumIonCells' => '',
				'NumberOfLithiumMetalCells' => '',			
			),
		);
	}
}
