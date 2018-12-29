<?php 

/**
 * The default template
 *
 * @author mrlina <714480119@qq.com>
 */
Class AmazonLabSupplies extends AmazonUpload implements IAmazonUpload
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
    //变体类型映射
    protected function getVariation_theme($key){
	    $variation_theme=array(
	        'Size'=>'SizeName',
            'Size-Color'=>'SizeName-ColorName',
            'Color'=>'ColorName'
        );
	    return isset($variation_theme[$key])?$variation_theme[$key]:'unknown';
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
			$array = $this->$method();
			$this->removeEmptyItemByKey($array, $xsd2->category);

			$array = $this->_LabSupplies($array);
			$data['ProductData'] = $array;

			$xmls[$this->product->sku] = $this->arr2xml->buildXML($this->removeEmptyItem($data), 'Product');

		} else if ($this->product->product_is_multi == 2) {
			$array = $this->$method('parent', $this->getVariation_theme($this->product->variation_theme));
			$this->removeEmptyItemByKey($array, $xsd2->category);

			$array = $this->_LabSupplies($array);
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

				$array = $this->$method('child', $this->getVariation_theme($this->product->variation_theme));
				foreach ($variations as $name => $val) {
					if (isset($array[$xsd2->category][$this->getVariation_theme($name)])) {
						$array[$xsd2->category][$this->getVariation_theme($name)] = $val;
                    }
				}
                $array[$xsd2->category]['ColorMap']=empty($array[$xsd2->category]['ColorName'])?'':$array[$xsd2->category]['ColorName'];
				$this->removeEmptyItemByKey($array, $xsd2->category);
				$array = $this->_LabSupplies($array);
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

	protected function _LabSupplies(array $category)
	{
        return array(
            'LabSupplies' => array(
                'ProductType' =>$category,
            ),
        );
	}

	protected function _LabSupply($relative = '', $theme = '')
	{
		return array(
			'LabSupply' => array(
				'VariationData' => array(
					'Parentage' => $relative,
					'VariationTheme' => $theme,
				),
				'AgeRangeDescription' => '',
				'AirFlowCapacity' => '',
				'BulbDiameter' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'Capacity' => array(
					'@unitOfMeasure' => 'cubic-cm',
					'%' => '',
					),
				'CapSize' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'CapType' => '',
				'ChamberDepth' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'ChamberHeight' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'ChamberMaterialType' => '',
				'ChamberVolume' => '',
				'ChamberWidth' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'ClosureDiameter' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'Co2Range' => '',
				'CompressorHorsepower' => '',
				'CoolantCapacity' =>  array(
					'@unitOfMeasure' => 'cubic-cm',
					'%' => '',
					),
				'CoolantConsumptionRate' => array(
					'@unitOfMeasure' => 'liters',
					'%' => '',
					),
				'CountryOfOrigin' => '',
				'CurrentRating' => array(
					'@unitOfMeasure' => 'A',
					'%' => '',
					),
				'DisplayType' => '',
				'DrawVolume' => '',
				'DropsPerMilliliter' => '',
				'DropVolume' =>  array(
					'@unitOfMeasure' => 'cubic-cm',
					'%' => '',
					),
				'ExtensionLength' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'FuelType' => '',
				'GraduationInterval' => '',
				'GraduationRange' => '',
				'HeatedElementDimensions' => '',
				'HeaterSurfaceMaterialType' => '',
				'HeatingElementType' => '',
				'HeatTime' => array(
					'@unitOfMeasure' => 'hours',
					'%' => '',
					),
				'HoldingTime' => array(
					'@unitOfMeasure' => 'hours',
					'%' => '',
					),
				'Horsepower' => '',
				'ImmersionDepth' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'InletConnectionType' => '',
				'InletOutsideDimensions' => '',
				'InsideDepth' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'InsideDiameterString' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'InsideHeightString' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'InsideLengthString' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'InsideWidthString' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'ItemLengthString' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'ItemWidthString' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'ItemHeightString' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'ItemDepth' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'ItemDiameter' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'ItemShape' => '',
				'ItemThickness' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'ItemVolume' =>  array(
					'@unitOfMeasure' => 'cubic-cm',
					'%' => '',
					),
				'ItemWeight' => array(
					'@unitOfMeasure' => 'KG',
					'%' => '',
					),
				'LightPathDistance' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'LowerTemperatureRating' => array(
					'@unitOfMeasure' => 'C',
					'%' => '',
					),
				'MarkingColor' => '',
				'MaterialType' => '',
				'MaximumDiameter' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'MaximumDispensingVolume' => array(
					'@unitOfMeasure' => 'liters',
					'%' => '',
					),
				'MaximumEnergyOutput' => array(
					'@unitOfMeasure' => 'PTU',
					'%' => '',
					),
				'MaximumInletPressure' => '',
				'MaximumRelativeCentrifugalForce' => '',
				'MaximumSampleVolume' => '',
				'MaximumSpeed' => '',
				'MaximumStirringSpeed' => '',
				'MaximumStirringVolume' => '',
				'MaximumTemperature' => array(
					'@unitOfMeasure' => 'C',
					'%' => '',
					),
				'MaximumWorkingVolume' => '',
				'MediaColor' => '',
				'MediaType' => '',
				'MinimumDispensingVolume' => '',
				'MinimumEnergyOutput' => array(
					'@unitOfMeasure' => 'PTU',
					'%' => '',
					),
				'MinimumInletWaterTemperature' => array(
					'@unitOfMeasure' => 'C',
					'%' => '',
					),
				'MinimumSampleVolume' => array(
					'@unitOfMeasure' => 'liters',
					'%' => '',
					),
				'MinimumSpeed' => '',
				'MinimumStirringSpeed' => '',
				'MinimumWorkingVolume' => '',
				'NarrowEndDiameter' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'NeckDiameter' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'NumberOfChannels' => '',
				'NumberOfHeaters' => '',
				'NumberOfRacks' => '',
				'NumberOfShelves' => '',
				'NumberOfTrays' => '',
				'NumberOfTubes' => '',
				'NumberOfWells' => '',
				'NumberOfWindows' => '',
				'NumberOfZones' => '',
				'OperatingFrequency' => '',
				'OperatingPressure' => '',
				'OrbitLength' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'OutputCapacity' =>  array(
					'@unitOfMeasure' => 'cubic-cm',
					'%' => '',
					),
				'OutsideDiameter' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'PlateArea' => '',
				'PlateOutsideDiameter' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'PoreSize' => array(
					'@unitOfMeasure' => 'liters',
					'%' => '',
					),
				'PressureFlowRate' => array(
					'@unitOfMeasure' => 'liters',
					'%' => '',
					),
				'ProductGrade' => '',
				'PurificationMethod' => '',
				'ReadoutAccuracy' => '',
				'RecoveryPercentage' => '',
				'ReservoirCapacity' =>  array(
					'@unitOfMeasure' => 'cubic-cm',
					'%' => '',
					),
				'SampleVolume' => array(
					'@unitOfMeasure' => 'liters',
					'%' => '',
					),
				'SeptaType' => '',
				'ShakingSpeed' => '',
				'StemOutsideDiameter' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'StopperNumber' => '',
				'StyleName' => '',
				'SuctionFlowRate' => array(
					'@unitOfMeasure' => 'liters',
					'%' => '',
					),
				'SupportedMediaSize' => '',
				'TemperatureAccuracy' => '',
				'TemperatureControlPrecision' => '',
				'TemperatureRange' => '',
				'TemperatureRecovery' => array(
					'@unitOfMeasure' => 'hours',
					'%' => '',
					),
				'TemperatureStability' => '',
				'TemperatureUniformity' => '',
				'TimerRange' => '',
				'TubeCapacity' =>  array(
					'@unitOfMeasure' => 'cubic-cm',
					'%' => '',
					),
				'TubeSize' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'UpperTemperatureRating' => array(
					'@unitOfMeasure' => 'C',
					'%' => '',
					),
				'VolumeAccuracy' => array(
					'@unitOfMeasure' => 'liters',
					'%' => '',
					),
				'VolumePrecision' => '',
				'VolumeTolerance' => array(
					'@unitOfMeasure' => 'liters',
					'%' => '',
					),
				'VolumetricToleranceClass' => '',
				'WaterRemovalCapacity' =>  array(
					'@unitOfMeasure' => 'cubic-cm',
					'%' => '',
					),
				'Wattage' => '',
				'WellShape' => '',
				'WellVolume' =>  array(
					'@unitOfMeasure' => 'cubic-cm',
					'%' => '',
					),
				'WideEndDiameter' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'Model' => '',
				'VolumeCapacityName' => '',
				'ItemDiameterString' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'SizeName' => '',
				'ColorName' =>'',
				'ColorMap' => 'unknown',
				'FrameMaterial' => '',
				'AdditionalFeatures' => '',
				'DisplayLength' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'DisplayHeight' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'DisplayWidth' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'DisplayWeight' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'DisplayDepth' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'DisplayDiameter' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'ItemThicknessString' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),				
			),
		);
	}

	protected function _SafetySupply($relative = '', $theme = '')
	{
		return array(
			'SafetySupply' => array(
				'VariationData' => array(
					'Parentage' => $relative,
					'VariationTheme' => $theme,
				),

				'Model' => '',
				'VolumeCapacityName' => '',
				'CountryOfOrigin' => '',
				'ItemDiameterString' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'SizeName' => '',
				'ColorName' => '',
				'ColorMap' => 'unknown',
				'FrameMaterial' => '',
				'ItemDiameter' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'ItemShape' => '',
				'ItemVolume' => '',
				'LightPathDistance' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'UpperTemperatureRating' => array(
					'@unitOfMeasure' => 'C',
					'%' => '',
					),
				'LowerTemperatureRating' => array(
					'@unitOfMeasure' => 'C',
					'%' => '',
					),
				'MarkingColor' => '',
				'HeatingElementType' => '',
				'MaximumTemperature' => array(
					'@unitOfMeasure' => 'C',
					'%' => '',
					),
				'Wattage' => '',
				'CapSize' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'CapType' => '',
				'Capacity' => array(
					'@unitOfMeasure' => 'cubic-cm',
					'%' => '',
					),
				'ChamberHeight' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'ChamberWidth' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'ClosureDiameter' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'CurrentRating' => array(
					'@unitOfMeasure' => 'A',
					'%' => '',
					),
				'DisplayType' => '',
				'DrawVolume' => '',
				'DropVolume' => array(
					'@unitOfMeasure' => 'cubic-cm',
					'%' => '',
					),
				'ExtensionLength' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'GraduationInterval' => '',
				'InsideDepth' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'NumberOfShelves' => '',
				'ItemDepth' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'AdditionalFeatures' => '',
				'AgeRangeDescription' => '',
				'AirFlowCapacity' => '',
				'ItemThickness' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'DisplayLength' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'DisplayHeight' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'DisplayWidth' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'DisplayWeight' => array(
					'@unitOfMeasure' => 'KG',
					'%' => '',
					),
				'DisplayDepth' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'DisplayDiameter' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'ItemThicknessString' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'BaseDiameter' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'BaseMaterialType' => '',
				'SinkMaterial' => '',
				'BatteryLife' => '',
				'BeltStyle' => '',
				'CaseMaterial' => '',
				'ChestSize' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'ClosureType' => '',
				'CollarType' => '',
				'DesignName' => '',
				'DietaryFiber' => '',
				'DoorMaterialType' => '',
				'EnergyContent' => '',
				'ExteriorFinish' => '',
				'FabricType' => '',
				'FcShelfLife' => array(
					'@unitOfMeasure' => 'days',
					'%' => '',
					),
				'FitType' => '',
				'Flavor' => '',
				'FrameMaterialType' => '',
				'HeelHeight' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'IncludedComponents' => '',
				'Inseam' => array(
					'@unitOfMeasure'=> 'CM',
					'%' => '',
					),
				'IsExpirationDatedProduct' => '',
				'ItemWeight' => '',
				'LampType' => '',
				'Coating' => '',
				'LensColor' => '',
				'LensMaterial' => '',
				'LiningDescription' => '',
				'LiquidPackagingType' => '',
				'LiquidVolume' => array(
					'@unitOfMeasure' => 'cubic-cm',
					'%' => '',
					),
				'LithiumBatteryPackaging' => '',
				'LithiumBatteryVoltage' => '',
				'LoadCapacity' => array(
					'@unitOfMeasure' => 'KG',
					'%' => '',
					),
				'LockType' => '',
				'MaterialType' => '',
				'MaximumWeight' => array(
					'@unitOfMeasure' => 'KG',
					'%' => '',
					),
				'FlyLineNumber' => '',
				'NumberOfDoors' => '',
				'NumberOfHeadPositions' => '',
				'NumberOfLithiumIonCells' => '',
				'NumberOfLithiumMetalCells' => '',
				'NumberOfPieces' => '',
				'OutsideDiameter' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'PatternStyle' => '',
				'SleeveLength' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'MaterialFeatures' => '',
				'MediaType' => '',
				'NoiseAttenuation' => '',
				'PoreSize' => '',
				'UseModes' => '',
				'RunTime' => '',
				'SeptaType' => '',
				'Sodium' => '',
				'SpecificUses' => '',
				'SpecificationMet' => '',
				'StringMaterial' => '',
				'StyleName' => '',
				'SugarAlcohol' => '',
				'Sugars' => '',
				'SupportedMediaSize' => '',
				'TasteDescription' => '',
				'Strength' => array(
					'@unitOfMeasure' => 'KG',
					'%' => '',
					),
				'PPUCount' => '',
				'PPUCountType' => '',
				'CuffType' => '',
				'ViewingAngle' => '',
				'WaterResistanceLevel' => 'waterproof',
				'ToeStyle' => '',

			),
		);
	}
}
