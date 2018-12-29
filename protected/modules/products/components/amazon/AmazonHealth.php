<?php 

/**
 * The default template
 *
 * @author mrlina <714480119@qq.com>
 */
Class AmazonHealth extends AmazonUpload implements IAmazonUpload
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

		$method = sprintf('_%s', $xsd2->category);

		//单品
		if ($this->product->product_is_multi == 0) {
			//仅指定分类信息
			$array = $this->$method();
			$this->removeEmptyItemByKey($array, $xsd2->category);
			
			$array = $this->_Health($array);
			$this->removeEmptyItemByKey($array, 'Health');

			$data['ProductData'] = $array;
			$xmls[$this->product->sku] = $this->arr2xml->buildXML($this->removeEmptyItem($data), 'Product');
		}
		//多属性
		else if($this->product->product_is_multi == 2) {
			// PrescriptionDrug 要作特殊处理
			if ($xsd2->category == 'PrescriptionDrug') {
				$data['ProductData'] = $this->_PrescriptionDrug();

				foreach ($this->product->sonskues as $sonprd) {
					$variations = json_decode($sonprd->variations, true);
					$suffix = sprintf('(%s)', implode('-', array_values($variations)));

					//修改子sku
					$data['SKU'] = $sonprd->sku;

					//修改子sku UPC码
					$data['StandardProductID']['Type']  = $sonprd['standard_product_id_type'];
					$data['StandardProductID']['Value'] = $sonprd['standard_product_id'];

					$data['DescriptionData']['MfrPartNumber'] = $sonprd['mfr'];
					$data['DescriptionData']['Title']         = $data['DescriptionData']['Title']. $suffix;

					$xmls[$sonprd->sku] = $this->arr2xml->buildXML($this->removeEmptyItem($data), 'Product');
				}
			} else {
				$array = $this->$method('parent', $this->product->variation_theme);
				$this->removeEmptyItemByKey($array[$xsd2->category], 'VariationData');
				$this->removeEmptyItemByKey($array, $xsd2->category);

				$array = $this->_Health($array);
				$this->removeEmptyItemByKey($array, 'Health');

				//父体变体设置
				$data['ProductData'] = $array;
				$xmls[$this->product->sku] = $this->arr2xml->buildXML($this->removeEmptyItem($data), 'Product');

				//循环所有子sku产品
				foreach ($this->product->sonskues as $sonprd) {
					$child = $data;

					$variations = json_decode($sonprd->variations, true);
					$suffix     = sprintf('(%s)', implode('-', array_values($variations)));

					//修改子sku产品sku码
					$child['SKU'] = $sonprd->seller_sku;

					//修改子sku UPC码
					$child['StandardProductID']['Type']        = $sonprd['standard_product_id_type'];
					$child['StandardProductID']['Value']       = $sonprd['standard_product_id'];
					$child['DescriptionData']['MfrPartNumber'] = $sonprd['mfr'];

					//修改子sku产品的标题
					$child['DescriptionData']['Title'] = $child['DescriptionData']['Title']. $suffix;

					//修改子sku变体内容

					$array = $this->$method('child', $this->product->variation_theme);

					foreach ($variations as $name => $val) {
						$array[$xsd2->category]['VariationData'][$name] = $val;
					}

					$this->removeEmptyItemByKey($array[$xsd2->category], 'VariationData');
					$this->removeEmptyItemByKey($array, $xsd2->category);

					$array = $this->_Health($array);
					$this->removeEmptyItemByKey($array, 'Health');

					$child['ProductData'] = $array;
					$xmls[$sonprd->sku]  = $this->arr2xml->buildXML($this->removeEmptyItem($child), 'Product');
				}
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

	protected function _Health(array $category)
	{
		return array(
			'Health' => array(
				'ProductType' => $category,
				'Axis' => '',
				'BatteryAverageLife' => '',
				'BatteryCellComposition' => '',
				'BatteryAverageLifeStandby' => '',
				'BatteryChargeTime' => '',
				'BatteryTypeLithiumIon' => '',
				'BatteryTypeLithiumMetal' => '',
				'BatteryDescription' => '',
				'BatteryFormFactor' => '',
				'BatteryPower' => array(
						'@unitOfMeasure' => 'amp_hours',
						'%' => '',
					),
				'CountryOfOrigin' => '',
				'Cylinder' => '',
				'ItemDiameterString' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'FcShelfLife' => array(
					'@unitOfMeasure' => 'days',
					'%' => '',
					),
				'LensAdditionPower' => array(
					'@unitOfMeasure' => 'unknown_modifier',
					'%' => '',
					),
				'LithiumBatteryEnergyContent' => '',
				'LithiumBatteryPackaging' => '',
				'LithiumBatteryVoltage' => '',
				'LithiumBatteryWeight' => '',
				'MfrWarrantyDescriptionLabor' => '',
				'MfrWarrantyDescriptionParts' => '',
				'IncludedComponents' => '',
				'ManufacturerWarrantyType' => '',
				'ModelNumber' => '',
				'SpecificUsesForProduct' => '',
				'Certification' => '',
				'Wattage' => array(
					'@unitOfMeasure' => 'watts',
					'%' => '',
					),
				'Voltage' => array(
					'@unitOfMeasure' => 'volts',
					'%' => '',
					),
				'NumberOfPieces' => '',
				'PlugType' => '',
				'HarmonizedCode' => '',
				'NumberOfLithiumIonCells' => '',
				'NumberOfLithiumMetalCells' => '',
				'RegionOfOrigin' => '',
				'SellerWarrantyDescription' => '',
				'WeightRecommendation' => array(
					'MaximumWeightRecommendation' => array(
						'@unitOfMeasure' => 'KG',
						'%' => '',
						),
					'MinimumWeightRecommendation' => array(
						'@unitOfMeasure' => 'KG',
						'%' => '',
						),
					),
				'DeliveryScheduleGroupId' => '',
				'ContainsFoodOrBeverage' => '',
				'MedicineClassification' => '',
			),
		);
	}

	protected function _HealthMisc($relative = '', $theme = '')
	{
		return array(
			'HealthMisc' => array(
				'VariationData' => array(
					'Parentage' => $relative,
					'VariationTheme' => $theme,
					'Size' => '',
					'SizeMap' => '',
					'Color' => '',
					'ColorMap' => '',
					'Count' => '',
					'NumberOfItems' => '',
					'Flavor' => '',
					'Scent' => '',
					'StyleName' => '',
					'CustomerPackageType' => '',
				),
				'CanShipInOriginalContainer' => '',
				'IdentityPackageType' => '',
				'UnitCount' => array(
					'@unitOfMeasure' => 'oz',
					'%' => '24',
					),
				'DisplayLength' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'DisplayWeight' => array(
					'@unitOfMeasure' => 'KG',
					'%' => '',
					),
				'DisplayVolume' => array(
					'@unitOfMeasure' => '',
					'%' => '',
					),
				'Indications' => 'That\'s it!',
				'HairType' => '',
				'SkinType' => '',
				'SkinTone' => '',
				'Ingredients' => '',
				'MaterialType' => '',
				'Directions' => '',
				'Warnings' => '',
				'ItemForm' => '',
				'Coverage' => '',
				'FinishType' => '',
				'ItemSpecialty' => '',
				'IsAdultProduct' => '',
				'SpecialFeatures' => '',
				'SpecificUsedKeywords' => '',
				'SunProtection' => array(
					'@unitOfMeasure' => '',
					'%' => '',
					),
				'TargetAudience' => '',
				'TargetGender' => '',
				'LensType' => '',
				'LifeExpectancy' => '',
				'OccasionType' => '',
				'ItemDiameter' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'OpticalPower' => array(
					'@unitOfMeasure' => 'unknown_modifier',
					'%' => '',
					),
				'BaseCurveRadius' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'CIPCode' => '',
				'Pattern' => '',
				'PowerSource' => '',
				'IsACAdapterIncluded' => '',
				'Battery' => '',
				'IsExpirationDatedProduct' => '',
				'ShaftLength' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'ShaftDiameter' => '',
				'ItemGender' => '',
				'CalciumString' => '',
				'EnergyString' => '',
				'TotalFatString' => '',
				'SaturatedFatString' => '',
				'MonounsaturatedFatString' => '',
				'PolyunsaturatedFatString' => '',
				'TotalCarbohydrateString' => '',
				'SugarAlcoholString' => '',
				'Starch' => '',
				'DietaryFiberString' => '',
				'ProteinString' => '',
				'SaltPerServingString' => '',
				'VitaminAString' => '',
				'VitaminCString' => '',
				'VitaminDString' => '',
				'VitaminEString' => '',
				'VitaminKString' => '',
				'ThiaminString' => '',
				"VitaminB2" => '',
				'Niacin' => '',
				"VitaminB6" => '',
				'FolicAcid' => '',
				"VitaminB12" => '',
				'Biotin' => '',
				'PantothenicAcid' => '',
				'PotassiumString' => '',
				'Chloride' => '',
				'PhosphorusString' => '',
				'Magnesium' => '',
				'IronString' => '',
				'Zinc' => '',
				'Copper' => '',
				'Manganese' => '',
				'Fluoride' => '',
				'Selenium' => '',
				'Chromium' => '',
				'Molybdenum' => '',
				'Iodine' => '',
				'CholesterolString' => '',
				'SodiumString' => '',
				'ManufacturerContactInformation' => '',
				'BandSizeNum' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'CupSize' => '',
				'InnerMaterialType' => '',
				'OuterMaterialType' => '',
				'MaterialComposition' => '',
				'TemperatureRating' => '',
				'WeightRange' => '',
				'SolidNetWeight' => array(
					'@unitOfMeasure' => 'KG',
					'%' => '',
					),
				'CountryString' => '',
				'ItemTypeName' => '',
				'AllergenInformation' => '',
				'SpecialIngredients' => '',
				'PrimaryIngredientCountryOfOrigin' => '',
				'PrimaryIngredientLocationProduced' => '',
				'StorageInstructions' => '',
				'ServingRecommendation' => '',
				'UseByRecommendation' => '',
				'ServingSize' => array(
					'@unitOfMeasure' => 'mg',
					'%' => '',
					),
				'CustomerRestrictionType' => '',
			),
		);
	}

	protected function _PersonalCareAppliances($relative = '', $theme = '')
	{
		return array(
			'PersonalCareAppliances' => array(
				'VariationData' => array(
					'Parentage' => $relative,
					'VariationTheme' => $theme,
					'Size' => '',
					'SizeMap' => '',
					'Color' => '',
					'ColorMap' => '',
					'Count' => '',
					'NumberOfItems' => '',
					'Scent' => '',
				),
				'UnitCount' => array(
					'@unitOfMeasure' => 'oz',
					'%' => '24',
					),
				'DisplayLength' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'DisplayWeight' => array(
					'@unitOfMeasure' => 'KG',
					'%' => '',
					),
				'DisplayVolume' => array(
					'@unitOfMeasure' => 'cubic-cm',
					'%' => '',
					),
				'Indications' => 'That\'s it!',
				'HairType' => '',
				'SkinType' => '',
				'SkinTone' => '',
				'MaterialType' => '',
				'Directions' => '',
				'Warnings' => '',
				'ItemForm' => '',
				'Flavor' => '',
				'Coverage' => '',
				'FinishType' => '',
				'ItemSpecialty' => '',
				'ItemPackageQuantity' => '',
				'IsAdultProduct' => '',
				'TargetGender' => '',
				'ItemDiameter' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'Pattern' => '',
				'PowerSource' => '',
				'IsACAdapterIncluded' => '',
				'Battery' => '',
				'IsExpirationDatedProduct' => '',
				'SpecialFeatures' => '',
				'HandOrientation' => '',
				'Ingredients' => '',
				'BaseCurveRadius' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'ShaftLength' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'ShaftDiameter' => '',
				'ItemGender' => '',
				'AnnualEnergyConsumption' => array(
					'@unitOfMeasure' => 'kilowatt_hours',
					'%' => '',
					),
				'EuEnergyLabelEfficiencyClass' => '',
				'SolidNetWeight' => array(
					'@unitOfMeasure' => 'KG',
					'%' => '',
					),
				'CountryString' => '',
				'ItemTypeName' => '',
				'AllergenInformation' => '',
				'SpecialIngredients' => '',
				'PrimaryIngredientCountryOfOrigin' => '',
				'PrimaryIngredientLocationProduced' => '',
				'StorageInstructions' => '',
				'ServingRecommendation' => '',
				'UseByRecommendation' => '',
				'ServingSize' => array(
					'@unitOfMeasure' => 'mg',
					'%' => '',
					),
				'ManufacturerContactInformation' => '',
				'EnergyString' => '',
				'TotalFatString' => '',
				'SaturatedFatString' => '',
				'MonounsaturatedFatString' => '',
				'PolyunsaturatedFatString' => '',
				'TotalCarbohydrateString' => '',
				'SugarsString' => '',
				'SugarAlcoholString' => '',
				'Starch' => '',
				'DietaryFiberString' => '',
				'ProteinString' => '',
				'SaltPerServingString' => '',
				'VitaminAString' => '',
				'VitaminCString' => '',
				'VitaminDString' => '',
				'VitaminEString' => '',
				'VitaminKString' => '',
				'ThiaminString' => '',
				"VitaminB2" => '',
				'Niacin' => '',
				"VitaminB6" => '',
				'FolicAcid' => '',
				"VitaminB12" => '',
				'Biotin' => '',
				'PantothenicAcid' => '',
				'PotassiumString' => '',
				'Chloride' => '',
				'CalciumString' => '',
				'PhosphorusString' => '',
				'Magnesium' => '',
				'IronString' => '',
				'Zinc' => '',
				'Copper' => '',
				'Manganese' => '',
				'Fluoride' => '',
				'Selenium' => '',
				'Chromium' => '',
				'Molybdenum' => '',
				'Iodine' => '',
				'CholesterolString' => '',
				'SodiumString' => '',
				'InnerMaterialType' => '',
				'OuterMaterialType' => '',
				'MaterialComposition' => '',
				'BandSizeNum' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'CupSize' => '',

			),
		);
	}

	protected function _PrescriptionDrug()
	{
		return array(
			'PrescriptionDrug' => array(
				'Indications' => 'That\'s it!',
			),
		);
	}
}
