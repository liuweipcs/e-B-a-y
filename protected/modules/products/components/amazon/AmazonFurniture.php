<?php 

/**
 * The default template
 *
 * @author mrlina <714480119@qq.com>
 */
Class AmazonFurniture extends AmazonUpload implements IAmazonUpload
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
			$array = $this->_Furniture();
			$this->removeEmptyItemByKey($array, 'Furniture');

			$data['ProductData'] = $array;
			$xmls[$this->product->sku] = $this->arr2xml->buildXML($this->removeEmptyItem($data), 'Product');

		} else if ($this->product->product_is_multi == 2) {
			$data['ProductData'] = $this->_Furniture();
			$this->removeEmptyItemByKey($data['ProductData'], 'Furniture');

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

	protected function _Furniture($relative = '', $theme = '')
	{
		return array(
			'Furniture' => array(
				'ProductType' => 'Furniture',
				'VariationData' => array(
					'Parentage' => $relative,
					'VariationTheme' => $theme,
					),

				'Battery' => array(
					'AreBatteriesIncluded' => '',
					'AreBatteriesRequired' => '',
					'BatterySubgroup' => array(
						'BatteryType' => '',
						'NumberOfBatteries' => '',
						),
					),
				'Model' => '',
				'FabricWeight' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'Occasion' => '',
				'IncludedComponents' => '',
				'Shape' => '',
				'Pattern' => '',
				'SpecialFeatures' => '',
				'Wattage' => '',
				'Style' => '',
				'NumberOfPieces' => '',
				'IsStainResistant' => '',
				'MaximumCoverageArea' => array(
					'@unitOfMeasure' => 'square-cm',
					'%' => '',
					),
				'LightSourceType' => '',
				'PowerSource' => '',
				'SeatHeight' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'PaintType' => '',
				'IsFragile' => '',
				'Framed' => '',
				'FrameMaterial' => '',
				'FrameColor' => '',
				'DoorType' => '',
				'ItemTypeName' => '',
				'TopMaterial' => '',
				'NumberOfPanels' => '',
				'NumberOfRails' => '',
				'CoverMaterial' => '',
				'DesignName' => '',
				'ShelfType' => '',
				'InstallationType' => '',
				'BackMaterial' => '',
				'LegFinish' => '',
				'FormFactor' => '',
				'TopFinish' => '',
				'FrameType' => '',
				'NumberOfHooks' => '',
				'LockType' => '',
				'ItemForm' => '',
				'MetalType' => '',
				'MaterialType' => '',
				'FootboardUpholstery' => '',
				'NumberOfSinks' => '',
				'MaximumCompatibleThickness' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'MaximumSupportedScreenSize' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'StorageType' => '',
				'SinkMaterial' => '',
				'BaseMaterial' => '',
			),
		);
	}
}