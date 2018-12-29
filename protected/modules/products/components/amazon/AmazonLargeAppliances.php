<?php 

/**
 * The default template
 *
 * @author mrlina <714480119@qq.com>
 */
class AmazonLargeAppliances extends AmazonUpload implements IAmazonUpload
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

		if ($this->product->product_is_multi == 0) {
			$array = $this->method();
			$this->removeEmptyItemByKey($array, $xsd2->category);

			$array = $this->_LargeAppliances($array);
			$this->removeEmptyItemByKey($array, 'LargeAppliances');

			$data['ProductData'] = $array;
			$xmls[$this->product->sku] = $this->arr2xml->buildXML($this->removeEmptyItem($data), 'Product');			

		} else if ($this->product->product_is_multi == 2) {
			$array = $this->method();
			$this->removeEmptyItemByKey($array, $xsd2->category);

			$array = $this->_LargeAppliances($array, 'parent', $this->mapThemeVaris($this->product->variation_theme));
			$array = $this->removeEmptyItemByKey($array, 'LargeAppliances');

			$data['ProductData'] = $array;
			$xmls[$this->product->sku] = $this->arr2xml->buildXML($this->removeEmptyItem($data), 'Product');

			foreach ($this->product->sonskues as $sonprd) {
				$data['DescriptionData']['MfrPartNumber'] = $sonprd->mfr;
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

				$array = $this->$method();			
				$this->removeEmptyItemByKey($array, $xsd2->category);

				$array = $this->_LargeAppliances($array, 'child', $this->mapThemeVaris($this->product->variation_theme),$variations);

				foreach ($variations as $name => $val) {
					if (isset($array['LargeAppliances'][$this->mapThemeVaris($name)])) {
						$array['LargeAppliances'][$this->mapThemeVaris($name)] = $val;
					}
				}

				$this->removeEmptyItemByKey($array, 'LargeAppliances');
				$child['ProductData'] = $array;
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

	protected function mapThemeVaris($key)
	{
		$array = array(
			'Color' => 'Color',
			'Size' => 'Size',
			'Size-Color' => 'SizeColor',
		);

		return $array[$key];
	}

	protected function _LargeAppliances(array $category, $relative = '', $theme = '',$variations='')
	{
		return array(
			'LargeAppliances' => array(
				'ProductType' => $category,
				'Battery' => array(
						'AreBatteriesIncluded' => '',
						'AreBatteriesRequired' => '',
						'BatterySubgroup' => array(
							'BatteryType' => '',
							'NumberOfBatteries' => '',
							),
					),
				'BatteryAverageLifeStandby' => '',
				'BatteryLife' => array(
					'@unitOfMeasure' => 'hours',
					'%' => '',
					),
				'Capacity' => array(
					'@unitOfMeasure' => 'cubic-cm',
					'%' => '',
					),
				'ColorMap' => '',
                'ColorSpecification'=>array(
                    'Color'=>$variations['Color'],
                    'ColorMap'=>'beige'
                ),
				'CountryOfOrigin' => '',
				'Diameter' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'DisplayVolume' => array(
					'@unitOfMeasure' => 'cubic-cm',
					'%' => '',
					),
				'Efficiency' => '',
				'EuEnergyLabelEfficiencyClass' => '',
				'EnergyConsumption' => array(
					'@unitOfMeasure' => 'kilowatt_hours',
					'%' => '',
					),
				'FilterPoreSize' => array(
					'@unitOfMeasure' => 'micrometer',
					'%' => '',
					),
				'FormFactor' => '',
				'FrontStyle' => '',
				'IncludedComponents' => '',
				'InnerMaterialType' => '',
				'InstallationType' => '',
				'IsWhiteGloveRequired' => '',
				'ItemThickness' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'LithiumBatteryEnergyContent' => '',
				'LithiumBatteryPackaging' => '',
				'LithiumBatteryVoltage' => '',
				'LithiumBatteryWeight' => '',
				'MaterialType' => '',
				'MfgWarrantyDescriptionLabor' => '',
				'MfgWarrantyDescriptionParts' => '',
				'MfrPartNumber' => '',
				'ModelNumber' => '',
				'NoiseLevel' => array(
					'@unitOfMeasure' => 'dBA',
					'%' => '',
					),
				'NumberOfLithiumIonCells' => '',
				'NumberOfLithiumMetalCells' => '',
				'VariationData' => array(
					'Parentage' => $relative,
					'VariationTheme' => $theme,
					),
				'ProductGrade' => '',
				'SellerWarrantyDescription' => '',
				'SidePanelColor' => '',
				'Size' => $variations['Size'],
				'SizeMap' => '',
				'SpecialFeatures' => '',
				'SpecificationsMet' => '',
				'TemperatureRating' => '',
				'Voltage' => array(
					'@unitOfMeasure' => 'millivolts',
					'%' => '',
					),
				'Warnings' => '',
				'Warranty' => '',
				'Wattage' => array(
					'@unitOfMeasure' => 'watts',
					'%' => '',
					),				
			)
		);
	}

	protected function _AirConditioner()
	{
		return array(
			'AirConditioner' => array(
				'AirConditionCoverageCooling' => '',
				'AirConditionCoverageHeating' => '',
				'ConnectorType' => '',
				'ControlsType' => '',
				'CoolingVents' => '',
				'DryerPowerSource' => '',
				'EnergyConsumptionEfficiencyRateAPF' => '',
				'HoodDescription' => '',
				'IceCapacity' => array(
					'@unitOfMeasure' => 'pounds',
					'%' => '',
					),
				'ItemDimensionsIndoor' => '',
				'ItemDimensionsOutdoor' => '',
				'IsPortable' => '',
				'LightingMethod' => '',
				'MaximumPipeDifferenceInHeight' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'OperatingNoiseIndoorEquipment' => array(
					'@unitOfMeasure' => 'dBA',
					'%' => '',
					),
				'OperatingNoiseOutdoorEquipment' => array(
					'@unitOfMeasure' => 'dBA',
					'%' => '',
					),
				'PipeDiameter' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'PipeLength' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'PowerConsumptionWattageCooling' => array(
					'@unitOfMeasure' => 'kilowatt_hours',
					'%' => '',
					),
				'PowerConsumptionWattageHeating' => array(
					'@unitOfMeasure' => 'kilowatt_hours',
					'%' => '',
					),
				'PowerPlugCapacity' => array(
					'@unitOfMeasure' => 'amps',
					'%' => '',
					),
				'RatedCoolingCapacity' => array(
					'@unitOfMeasure' => 'kilowatt_hours',
					'%' => '',
					),
				'RatedHeatingCapacity' => array(
					'@unitOfMeasure' => 'kilowatt_hours',
					'%' => '',
					),
				'RecommendedProductUses' => '',
				'ShelfType' => '',
				'TrayType' => '',
				'VoltageType' => '',
				'WasherArms' => '',
				'WeightIndoorEquipment' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'WeightOutdoorEquipment' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
			),
		);
	}

	protected function _ApplianceAccessory()
	{
		return array(
			'ApplianceAccessory' => array(
				'ConnectorType' => '',
				'ControlsType' => '',
				'CoolingVents' => '',
				'DryerPowerSource' => '',
				'IceCapacity' => array(
					'@unitOfMeasure' => 'pounds',
					'%' => '',
					),
				'IsPortable' => '',
				'LightingMethod' => '',
				'RecommendedProductUses' => '',
				'ShelfType' => '',
				'TrayType' => '',
				'WasherArms' => '',
			),
		);
	}

	protected function _CookingOven()
	{
		return array(
			'CookingOven' => array(
				'BurnerType' => '',
				'ChamberVolume' => array(
					'@unitOfMeasure' => 'liters',
					'%' => '',
					),
				'ControlsType' => '',
				'CooktopMaterialType' => '',
				'DrawerType' => '',
				'EnergyConsumptionConvection' => array(
					'@unitOfMeasure' => 'kilowatt_hours',
					'%' => '',
					),
				'EnergyConsumptionStandard' => array(
					'@unitOfMeasure' => 'kilowatt_hours',
					'%' => '',
					),
				'FuelType' => '',
				'HeatingElements' => '',
				'HeatingMode' => '',
				'HoodDescription' => '',
				'LightingMethod' => '',
				'MaxEnergyOutput' => array(
					'@unitOfMeasure' => 'btus',
					'%' => '',
					),
				'RecommendedProductUses' => '',
				'Racks' => '',
				'ShelfType' => '',
				'TopStyle' => '',
				'TrayType' => '',
				'VolumeCapacityName' => '',
			),
		);
	}

	protected function _Cooktop()
	{
		return array(
			'Cooktop' => array(
				'AirflowDisplacement' => array(
					'@unitOfMeasure' => 'cubic_feet_per_minute',
					'%' => '',
					),
				'BurnerType' => '',
				'ChamberVolume' => array(
					'@unitOfMeasure' => 'liters',
					'%' => '',
					),
				'ControlsType' => '',
				'CooktopMaterialType' => '',
				'DrawerType' => '',
				'DryerPowerSource' => '',
				'EnergyConsumptionConvection' => array(
					'@unitOfMeasure' => 'kilowatt_hours',
					'%' => '',
					),
				'EnergyConsumptionStandard' => array(
					'@unitOfMeasure' => 'kilowatt_hours',
					'%' => '',
					),
				'FuelType' => '',
				'HeatingElements' => '',
				'HoodDescription' => '',
				'LightingMethod' => '',
				'MaxEnergyOutput' => array(
					'@unitOfMeasure' => 'btus',
					'%' => '',
					),
				'ShelfType' => '',
				'TopStyle' => '',
				'TrayType' => '',
				'VolumeCapacityName' => '',
				'WasherArms' => '',
			),
		);
	}

	protected function _Dishwasher()
	{
		return array(
			'Dishwasher' => array(
				'AnnualEnergyConsumption' => array(
					'@unitOfMeasure' => 'kilowatt_hours',
					'%' => '',
					),
				'AnnualWaterConsumption' => array(
					'@unitOfMeasure' => 'liters',
					'%' => '',
					),
				'CompatibleDevice' => '',
				'ControlsType' => '',
				'CoolingVents' => '',
				'CounterDepth' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'DryerPowerSource' => '',
				'DryingPerformanceRating' => '',
				'IceCapacity' => array(
					'@unitOfMeasure' => 'pounds',
					'%' => '',
					),
				'IsPortable' => '',
				'LightingMethod' => '',
				'OptionCycles' => '',
				'RecommendedProductUses' => '',
				'ShelfType' => '',
				'StandardCycleCapacity' => array(
					'@unitOfMeasure' => 'kg',
					'%' => '',
					),
				'StandardCycles' => '',
				'TrayType' => '',
				'WasherArms' => '',
				'WashingPerformanceRating' => '',
				'WaterConsumption' => array(
					'@unitOfMeasure' => 'liters',
					'%' => '',
					),
			),
		);
	}

	protected function _LaundryAppliance()
	{
		return array(
			'LaundryAppliance' => array(
				'AccessLocation' => '',
				'AnnualEnergyConsumption' => array(
					'@unitOfMeasure' => 'kilowatt_hours',
					'%' => '',
					),
				'AnnualEnergyConsumptionCycle' => array(
					'@unitOfMeasure' => 'kilowatt_hours',
					'%' => '',
					),
				'AnnualEnergyConsumptionWashing' => array(
					'@unitOfMeasure' => 'kilowatt_hours',
					'%' => '',
					),
				'AnnualWaterConsumption' => array(
					'@unitOfMeasure' => 'liters',
					'%' => '',
					),
				'AnnualWaterConsumptionCycle' => array(
					'@unitOfMeasure' => 'liters',
					'%' => '',
					),
				'AnnualWaterConsumptionWashing' => array(
					'@unitOfMeasure' => 'liters',
					'%' => '',
					),
				'ClothesCapacity' => '',
				'CompatibleDevice' => '',
				'ConnectorType' => '',
				'ControlsType' => '',
				'CoolingVents' => '',
				'CounterDepth' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'DryerPowerSource' => '',
				'DryingCapacity' => array(
					'@unitOfMeasure' => 'kg',
					'%' => '',
					),
				'DryingPerformanceRating' => '',
				'DryingTechnology' => '',
				'EnergyConsumptionCycle' => array(
					'@unitOfMeasure' => 'kilowatt_hours',
					'%' => '',
					),
				'EnergyConsumptionWashing' => array(
					'@unitOfMeasure' => 'kilowatt_hours',
					'%' => '',
					),
				'IceCapacity' => array(
					'@unitOfMeasure' => 'pounds',
					'%' => '',
					),
				'IsPortable' => '',
				'LightingMethod' => '',
				'MaxRotationSpeed' => array(
					'@unitOfMeasure' => 'rpm',
					'%' => '',
					),
				'NoiseLevelDraining' => array(
					'@unitOfMeasure' => 'dBA',
					'%' => '',
					),
				'NoiseLevelDrying' => array(
					'@unitOfMeasure' => 'dBA',
					'%' => '',
					),
				'NoiseLevelDryingCottonDryMode' => array(
					'@unitOfMeasure' => 'dBA',
					'%' => '',
					),
				'NoiseLevelDryingStandardMode' => array(
					'@unitOfMeasure' => 'dBA',
					'%' => '',
					),
				'NoiseLevelSpinning' => array(
					'@unitOfMeasure' => 'dBA',
					'%' => '',
					),
				'NoiseLevelSpinningCottonDryMode' => array(
					'@unitOfMeasure' => 'dBA',
					'%' => '',
					),
				'NoiseLevelSpinningStandardMode' => array(
					'@unitOfMeasure' => 'dBA',
					'%' => '',
					),
				'NoiseLevelWashing' => array(
					'@unitOfMeasure' => 'dBA',
					'%' => '',
					),
				'NoiseLevelWashingCottonDryMode' => array(
					'@unitOfMeasure' => 'dBA',
					'%' => '',
					),
				'NoiseLevelWashingStandardMode' => array(
					'@unitOfMeasure' => 'dBA',
					'%' => '',
					),
				'OptionCycles' => '',
				'RecommendedProductUses' => '',
				'ResidualMoisturePercentage' => '',
				'ShelfType' => '',
				'SpinningPerformanceRating' => '',
				'StandardCycleCapacity' => array(
					'@unitOfMeasure' => 'kg',
					'%' => '',
					),
				'StandardCycles' => '',
				'TrayType' => '',
				'WasherArms' => '',
				'WashingCapacity' => array(
					'@unitOfMeasure' => 'kg',
					'%' => '',
					),
				'WashingPerformanceRating' => '',
				'WaterConsumption' => array(
					'@unitOfMeasure' => 'liters',
					'%' => '',
					),
				'WaterConsumptionCycle' => array(
					'@unitOfMeasure' => 'liters',
					'%' => '',
					),
				'WaterConsumptionWashing' => '',
				'WeightedAnnualEnergyConsumption' => array(
					'@unitOfMeasure' => 'kilowatt_hours',
					'%' => '',
					),
				'WeightedAnnualWaterConsumption' => array(
					'@unitOfMeasure' => 'liters',
					'%' => '',
					),				
			),
		);
	}

	protected function _MicrowaveOven()
	{
		return array(
			'MicrowaveOven' => array(
				'ChamberVolume' => array(
					'@unitOfMeasure' => 'liters',
					'%' => '',
					),
				'ControlsType' => '',
				'EnergyConsumptionConvection' => array(
					'@unitOfMeasure' => 'kilowatt_hours',
					'%' => '',
					),
				'EnergyConsumptionStandard' => array(
					'@unitOfMeasure' => 'kilowatt_hours',
					'%' => '',
					),
				'FuelType' => '',
				'LightingMethod' => '',
				'Racks' => '',
				'RecommendedProductUses' => '',
				'ShelfType' => '',
				'VolumeCapacityName' => '',
			),
		);
	}

	protected function _Range()
	{
		return array(
			'Range' => array(
				'AirflowDisplacement' => array(
					'@unitOfMeasure' => 'cubic_feet_per_minute',
					'%' => '',
					),
				'BurnerType' => '',
				'ChamberVolume' => array(
					'@unitOfMeasure' => 'liters',
					'%' => '',
					),
				'ControlsType' => '',
				'CooktopMaterialType' => '',
				'DrawerType' => '',
				'EnergyConsumptionConvection' => array(
					'@unitOfMeasure' => 'kilowatt_hours',
					'%' => '',
					),
				'EnergyConsumptionStandard' => array(
					'@unitOfMeasure' => 'kilowatt_hours',
					'%' => '',
					),
				'FuelType' => '',
				'HeatingElements' => '',
				'HeatingMode' => '',
				'HoodDescription' => '',
				'LightingMethod' => '',
				'MaxEnergyOutput' => array(
					'@unitOfMeasure' => 'btus',
					'%' => '',
					),
				'Racks' => '',
				'RecommendedProductUses' => '',
				'ShelfType' => '',
				'TopStyle' => '',
				'TrayType' => '',
				'VolumeCapacityName' => '',
			),
		);
	}

	protected function _RefrigerationAppliance()
	{
		return array(
			'RefrigerationAppliance' => array(
				'AdditionalProductInformation' => '',
				'AnnualEnergyConsumption' => array(
					'@unitOfMeasure' => 'kilowatt_hours',
					'%' => '',
					),
				'BottleCount' => '',
				'CompatibleDevice' => '',
				'ConnectorType' => '',
				'ControlsType' => '',
				'CoolingVents' => '',
				'CounterDepth' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'DefrostSystemType' => '',
				'DoorMaterialType' => '',
				'DoorOrientation' => '',
				'Drawers' => '',
				'DryerPowerSource' => '',
				'EuEnergyLabelEfficiencyClass1992' => '',
				'FilterPoreSize' => array(
					'@unitOfMeasure' => 'micrometer',
					'%' => '',
					),
				'FreezerCapacity' => array(
					'@unitOfMeasure' => 'cubic-cm',
					'%' => '',
					),
				'FreshFoodCapacity' => array(
					'@unitOfMeasure' => 'cubic-cm',
					'%' => '',
					),
				'FreezerLocation' => array(
					'@unitOfMeasure' => 'cubic-cm',
					'%' => '',
					),
				'IceCapacity' => array(
					'@unitOfMeasure' => 'pounds',
					'%' => '',
					),
				'LightingMethod' => '',
				'MaximumHorsepower' => array(
					'@unitOfMeasure' => 'horsepower',
					'%' => '',
					),
				'RecommendedProductUses' => '',
				'RefrigerationClimateClassification' => '',
				'ShelfType' => '',
				'Shelves' => '',
				'StorageVolume' => array(
					'@unitOfMeasure' => 'cubic-cm',
					'%' => '',
					),
				'TrayType' => '',
				'VegetableCompartmentCapacity' => '',
				'WasherArms' => '',
			),
		);
	}

	protected function _TrashCompactor()
	{
		return array(
			'TrashCompactor' => array(
				'CompactRatio' => '',
				'ControlsType' => '',
				'LightingMethod' => '',
				'RecommendedProductUses' => '',
			),
		);
	}

	protected function _VentHood()
	{
		return array(
			'VentHood' => array(
				'AirflowDisplacement' => array(
					'@unitOfMeasure' => 'cubic_feet_per_minute',
					'%' => '',
					),
				'ChamberVolume' => array(
					'@unitOfMeasure' => 'liters',
					'%' => '',
					),
				'ControlsType' => '',
				'EnergyConsumptionConvection' => array(
					'@unitOfMeasure' => 'kilowatt_hours',
					'%' => '',
					),
				'EnergyConsumptionStandard' => array(
					'@unitOfMeasure' => 'kilowatt_hours',
					'%' => '',
					),
				'FuelType' => '',
				'HoodDescription' => '',
				'LightingMethod' => '',
				'RecommendedProductUses' => '',
				'VolumeCapacityName' => '',
			),
		);
	}
}