<?php 

/**
 * The default template
 *
 * @author mrlina <714480119@qq.com>
 */
Class AmazonGourmet extends AmazonUpload implements IAmazonUpload
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
			$array = $this->_Gourmet();	
			$this->removeEmptyItemByKey($array['Gourmet']['ProductType'], 'GourmetMisc');

			$data['ProductData'] = $array;
			$xmls[$this->product->sku] = $this->arr2xml->buildXML($this->removeEmptyItem($data), 'Product');

		} else if ($this->product->product_is_multi == 2){
			$array = $this->_Gourmet('parent', $this->product->variation_theme);
			$this->removeEmptyItemByKey($array['Gourmet']['ProductType']['GourmetMisc'], 'VariationData');
			$this->removeEmptyItemByKey($array['Gourmet']['ProductType'], 'GourmetMisc');

			$data['ProductData'] = $array;
			$xmls[$this->product->sku] = $this->arr2xml->buildXML($this->removeEmptyItem($data), 'Product');

			foreach ($this->product->sonskues as $sonprd) {
				$data['DescriptionData']['MfrPartNumber'] = $sonprd->mfr;说
				$child = $data;

				$variations = json_decode($sonprd->variations, true);
				$suffix     = sprintf('(%s)', implode('-', array_values($variations)));

				//修改子sku产品sku码
				$child['SKU'] = $sonprd->seller_sku;

				//修改子sku UPC码
				$child['StandardProductID']['Type']  = $sonprd['standard_product_id_type'];
				$child['StandardProductID']['Value'] = $sonprd['standard_product_id'];

				//修改子sku产品的标题
				$child['DescriptionData']['Title'] = $child['DescriptionData']['Title']. $suffix;

				$array = $this->_Gourmet('child', $this->product->variation_theme);

				foreach ($variations as $name => $val) {
					$array['Gourmet']['ProductType']['VariationData'][$name] = $val;
				}

				$this->removeEmptyItemByKey($array['Gourmet']['ProductType']['GourmetMisc'], 'VariationData');
				$this->removeEmptyItemByKey($array['Gourmet']['ProductType'], 'GourmetMisc');

				$data['ProductData'] = $array;
				$xmls[$sonprd->sku] = $this->arr2xml->buildXML($this->removeEmptyItem($child), 'Product');
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

	protected function _Gourmet($relative = '', $theme = '')
	{
		return array(
			'Gourmet' => array(
				'ProductType' => array(
					'GourmetMisc' => array(
						'VariationData' => array(
							'Parentage' => $relative,
							'VariationTheme' => $theme,
							'Size' => '',
							'Color' => '',
							'Flavor' => '',
							'StyleName' => '',
						),
						'CanShipInOriginalContainer' => '',
						'CustomerPackageType' => '',
						'IdentityPackageType' => '',
						'CountryProducedIn' => '',
						'Ingredients' => '',
						'NutritionalFacts' => '',
						'KosherCertification' => '',
						'OrganicCertification' => '',
						'ItemSpecialty' => '',
						'Warnings' => '',
						'Directions' => '',
						'ItemPackageQuantity' => '',
						'NumberOfItems' => '',
						'DisplayLength' => array(
							'@unitOfMeasure' => 'CM',
							'%' => '',
							),
						'DisplayWeight' => array(
							'@unitOfMeasure' => 'CM',
							'%' => '',
							),
						'DisplayVolume' => array(
							'@unitOfMeasure' => 'CM',
							'%' => '',
							),
						'UnitCount' => array(
							'@unitOfMeasure' => 'oz',
							'%' => '24',
							),
					),
				),
			),
		);
	}
}