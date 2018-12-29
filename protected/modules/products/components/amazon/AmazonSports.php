<?php 

/**
 * The default template
 *
 * @author mrlina <714480119@qq.com>
 */
Class AmazonSports extends AmazonUpload implements IAmazonUpload
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

		//单品
		if ($this->product->product_is_multi == 0) {
			//仅指定分类信息
			$data['ProductData'] = array(
				'Sports' => array(
					'ProductType' => $xsd2->category,
				),
			);

			$xmls[$this->product->sku] = $this->arr2xml->buildXML($this->removeEmptyItem($data), 'Product');
		}
		//多属性
		else if($this->product->product_is_multi == 2) {
			//父体变体设置
		
			if($this->product->variation_theme=="Size-Color"){
				$theme = "ColorSize";
			}else{
				$theme = $this->product->variation_theme;
			}
			$data['ProductData'] = array(
				'Sports' => array(
					'ProductType' => $xsd2->category,
					'VariationData' => array(
						'Parentage' => 'parent',
						'VariationTheme' => $theme,
					),
				),
			);
			// echo "<pre>";
			// var_dump($data);die;

			$xmls[$this->product->sku] = $this->arr2xml->buildXML($this->removeEmptyItem($data), 'Product');

			//循环所有子sku产品
			foreach ($this->product->sonskues as $sonprd) {
				//clone父体信息
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

				//修改子sku变体内容
				$child['ProductData'] = array(
					'Sports' => array(
						'ProductType' => $xsd2->category,
						'VariationData' => array(
							'Parentage' => 'child', //指定为子体
							'VariationTheme' => $theme, //指定与父体相同的Theme
						),
					),
				);

				foreach (array_reverse(array_keys($variations)) as $name) {
					$child['ProductData']['Sports']['VariationData'][$name] = $variations[$name];
				}

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

	/**
	 * 其它产品属性
	 * 
	 * @return array
	 */
	protected function otherAttributes()
	{
		$attr = array();
		$data = unserialize($this->product->productdata->product_data);

		foreach ($data as $key => $value) {

		}

		return $attr;
	}

	protected function _Sports($productType, $relative = '', $theme = '')
	{
		if($theme=="Size-Color"){
			$theme="ColorSize";
		}
		return array(
			'Sports' => array(
				'ProductType' => $productType,
				'VariationData' => array(
					'Parentage' => $relative,
					'VariationTheme' => $theme,
						'AgeGenderCategory' => '',
						'Amperage' => '',
						'BikeRimSize' => '',
						'BootSize' => '',
						'Bounce' => '',
						'CalfSize' => '',
						'Caliber' => '',
						'Capacity' => '',
						'Club' => '',
						'Color' => '',
						'Curvature' => '',
						'CustomerPackageType' => '',
						'Department' => '',
						'Design' => '',
						'Diameter' => '',
						'DivingHoodThickness' => '',
						'FencingPommelType' => '',
						'Flavor' => '',
						'GolfFlex' => '',
						'GolfLoft' => '',
						'GripSize' => '',
						'GripType' => '',
						'Hand' => '',
						'HeadSize' => '',
						'Height' => '',
						'Irons' => '',
						'ItemThickness' => '',
						'Length' => '',
						'LensColor' => '',
						'LieAngle' => '',
						'LineCapacity' => '',
						'LineWeight' => '',
						'Material' => '',
						'Model' => '',
						'NumberOfItems' => '',
						'Occupancy' => '',
						'Quantity' => '',
						'Rounds' => '',
						'ShaftLength' => '',
						'ShaftMaterial' => '',
						'ShaftType' => '',
						'Shape' => '',
						'Size' => '',
						'Style' => '',
						'TemperatureRating' => '',
						'TensionLevel' => '',
						'Volume' => '',
						'Wattage' => '',
						'Weight' => '',
						'WeightSupported' => '',
						'WheelSize' => '',
						'Width' => '',
						'Wood' => '',
				),
				'MaterialComposition' => '',
				'Packaging' => '',
				'IsCustomizable' => '',
				'CustomizableTemplateName' => '',
				'IsAdultProduct' => '',
				'ModelYear' => '',
				'Season' => '',
				'AccessLocation' => '',
				'Action' => '',
				'ActiveIngredients' => '',
				'Alarm' => '',
				'ApparentScaleSize' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'AvailableCourses' => '',
				'BackingLineCapacity' => '',
				'BaseLength' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'Battery' => '',
				'BatteryAverageLife' => '',
				'BatteryAverageLifeStandby' => '',
				'BatteryChargeTime' => '',
				'BatteryLife' => array(
					'@unitOfMeasure' => 'hours',
					'%' => '',
					),
				'BatteryTypeLithiumIon' => '',
				'BatteryTypeLithiumMetal' => '',
				'BeamWidth' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'BearingMaterialType' => '',
				'BeltStyle' => '',
				'BikeWeight' => array(
					'@unitOfMeasure' => 'KG',
					'%' => '',
					),
				'BladeGrind' => '',
				'BladeLength' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'BladeShape' => '',
				'BladeType' => '',
				'BMXBikeType' => '',
				'BoatFenderDiameter' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'BoilRateDescription' => '',
				'BoomLength' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'BottomStyle' => '',
				'BrakeType' => '',
				'BrakeWidth' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'BreakingStrength' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'BTUs' => '',
				'Buildup' => '',
				'BulbType' => '',
				'BurnTime' => '',
				'CanShipInOriginalContainer' => '',
				'Capability' => '',
				'CapType' => '',
				'CareInstructions' => '',
				'CenterlineLength' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'ClosureType' => '',
				'CollarType' => '',
				'ColorMap' => '',
				'CompatibleDevices' => '',
				'CompatibleHoseDiameter' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'CompatibleRopeDiameter' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'Construction' => '',
				'ControlProgramName' => '',
				'CoreMaterialType' => '',
				'CountryAsLabeled' => '',
				'CountryOfOrigin' => '',
				'CourseCapacity' => '',
				'CoverageArea' => '',
				'CrankLength' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'CuffType' => '',
				'CupSize' => '',
				'Cycles' => '',
				'DeckLength' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'DeckWidth' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'Directions' => '',
				'DisplayFeatures' => '',
				'DisplayLength' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'DisplaySize' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'DisplayType' => '',
				'DisplayVolume' => '',
				'DisplayWeight' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'EffectiveEdgeLength' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'EngineDisplacement' => array(
					'@unitOfMeasure' => 'cc',
					'%' => '',
					),
				'EventName' => '',
				'Eye' => '',
				'FabricType' => '',
				'FabricWash' => '',
				'FillMaterialType' => '',
				'FishingLineType' => '',
				'FishType' => '',
				'FittingType' => '',
				'FitType' => '',
				'FloorArea' => array(
					'@unitOfMeasure' => 'square-cm',
					'%' => '',
					),
				'FloorLength' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'FloorWidth' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'FlyLineNumber' => '',
				'FoldedLength' => '',
				'FrameHeight' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'FrameMaterial' => '',
				'FrameType' => '',
				'FrequencyBand' => '',
				'FrontPleatType' => '',
				'FuelCapacity' => '',
				'FuelType' => '',
				'Functions' => '',
				'FurDescription' => '',
				'GearDirection' => '',
				'GeographicCoverage' => '',
				'GloveType' => '',
				'GripMaterialType' => '',
				'GuardMaterialType' => '',
				'HandleMaterial' => '',
				'HandleType' => '',
				'HeatRating' => array(
					'@unitOfMeasure' => 'degrees-celsius',
					'%' => '',
					),
				'HP' => '',
				'HullShape' => '',
				'IdentityPackageType' => '',
				'ImportDesignation' => '',
				'ImpactForce' => '',
				'Ingredients' => '',
				'Inseam' => array(
					'@unitOfMeasure' => 'candela',
					'%' => '',
					),
				'InsulationType' => '',
				'Intensity' => array(
					'@unitOfMeasure' => 'candela',
					'%' => '',
					),
				'IsAssemblyRequired' => '',
				'ItemTypeName' => '',
				'IsSigned' => '',
				'JerseyType' => '',
				'KnifeFunction' => '',
				'LampType' => '',
				'LaptopCapacity' => array(
					'@unitOfMeasure' => 'KG',
					'%' => '',
					),
				'LashLength' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'LeagueName' => '',
				'LegStyle' => '',
				'LensMaterial' => '',
				'LensShape' => '',
				'LifeVestType' => '',
				'LightIntensity' => '',
				'LineWeight' => '',
				'LiningMaterial' => '',
				'LithiumBatteryEnergyContent' => array(
					'@unitOfMeasure' => 'watt_hours',
					'%' => '',
					),
				'LithiumBatteryPackaging' => '',
				'LithiumBatteryVoltage' => array(
					'@unitOfMeasure' => 'volts',
					'%' => '',
					),
				'LithiumBatteryWeight' => array(
					'@unitOfMeasure' => 'KG',
					'%' => '',
					),
				'LoadCapacity' => '',
				'LockType' => '',
				'Loudness' => '',
				'LureWeight' => array(
					'@unitOfMeasure' => 'KG',
					'%' => '',
					),
				'ManufacturerDefinedQualityDescription' => '',
				'MartialArtsType' => '',
				'MaximumCompatibleBootSize' => array(
					'@unitOfMeasure' => 'adult_us',
					'%' => '',
					),
				'MaximumCompatibleRopeDiameter' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'MaximumHeight' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'MaximumInclinePercentage' => '',
				'MaximumLegSize' => '',
				'MaximumMagnification' => '',
				'MaximumPitchSpeed' => '',
				'MaximumResistance' => array(
					'@unitOfMeasure' => 'KG',
					'%' => '',
					),
				'MaximumStrideLength' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'MaximumTensionRating' => array(
					'@unitOfMeasure' => 'KG',
					'%' =>'',
					),
				'MaximumUserWeight' => array(
					'@unitOfMeasure' => 'KG',
					),
				'MaximumWeight' => array(
					'@unitOfMeasure' => 'KG',
					'%' => '',
					),
				'MaxWeightRecommendation' => array(
					'@unitOfMeasure' => 'KG', 
					'%' => '',
					),
				'MechanicalStructure' => '',
				'Memory' => array(
					'@unitOfMeasure' => 'MB',
					'%' => '',
					),
				'MfrWarrantyDescriptionLabor' => '',
				'MfrWarrantyDescriptionParts' => '',
				'MfrWarrantyDescriptionType' => '',
				'MinimumCompatibleBootSize' => array(
					'@unitOfMeasure' => 'adult_us',
					'%' => '',
					),
				'MinimumCompatibleRopeDiameter' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'MinimumLegSize' => '',
				'MinimumMagnification' => '',
				'MinimumTensionRating' => array(
					'@unitOfMeasure' => 'KG',
					'%' => '',
					),
				'MinimumTorsoFit' => '',
				'MinimumWeightRecommendation' => array(
					'@unitOfMeasure' => 'KG',
					'%' => '',
					),
				'MonitorFeatures' => '',
				'MotorSize' => '',
				'MountainBikeProportionalFrameSize' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'MountainBikeType' => '',
				'MountType' => '',
				'MovementType' => '',
				'NeckStyle' => '',
				'NumberOfBlades' => '',
				'NumberOfCarriagePositions' => '',
				'NumberOfDoors' => '',
				'NumberOfExercises' => '',
				'NumberOfFootPositions' => '',
				'NumberOfGearLoops' => '',
				'NumberOfHeadPositions' => '',
				'NumberOfHolds' => '',
				'NumberOfHorses' => '',
				'NumberOfLevels' => '',
				'NumberOfLithiumIonCells' => '',
				'NumberOfLithiumMetalCells' => '',
				'NumberOfPages' => '',
				'NumberOfPieces' => '',
				'NumberOfPockets' => '',
				'NumberOfPoles' => '',
				'NumberOfPrograms' => '',
				'NumberOfResistanceLevels' => '',
				'NumberOfSpeeds' => '',
				'NumberOfSprings' => '',
				'ObjectiveLensSize' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'OperationMode' => '',
				'Orientation' => '',
				'OuterMaterialType' => '',
				'PackedSize' => '',
				'PadType' => '',
				'PatternStyle' => '',
				'PeakHeight' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'Pixels' => array(
					'@unitOfMeasure' => 'pixels',
					'%' => '',
					),
				'PlayerName' => '',
				'PocketDescription' => '',
				'PositionAccuracy' => '',
				'PowerSource' => '',
				'PPUCount' => '',
				'PPUCountType' => '',
				'ProportionalFrameSize' => '',
				'PullType' => '',
				'Range' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'RearDerailleurCompatibleChainSize' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'RecommendedWorkoutSpace' => '',
				'ReelDiameter' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'ReelModel' => '',
				'Region' => '',
				'Resistance' => '',
				'ResistanceMechanism' => '',
				'Resolution' => '',
				'RiseStyle' => '',
				'RoadBikeProportionalFrameSize' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'RoadBikeType' => '',
				'RodLength' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'RodWeight' => array(
					'@unitOfMeasure' => 'KG',
					'%' => '',
					),
				'Routes' => '',
				'R-Value' => '',
				'Scale' => '',
				'ScreenColor' => '',
				'ScreenSize' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'SeatHeight' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'SeatingCapacity' => '',
				'SellerWarrantyDescription' => '',
				'ShellMaterial' => '',
				'ShirtType' => '',
				'ShoeWidth' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'SizeMap' => '',
				'SkillLevel' => '',
				'SkiStyle' => '',
				'SleepingCapacity' => '',
				'SleeveLength' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'SleeveType' => '',
				'SnowboardStyle' => '',
				'SockHeight' => '',
				'SockStyle' => '',
				'SonarType' => '',
				'SpecialFeatures' => '',
				'SpecificUsageForProduct' => '',
				'Speed' => '',
				'SpeedRating' => '',
				'Sport' => '',
				'State' => '',
				'StaticElongationPercentage' => '',
				'StaticWeight' => array(
					'@unitOfMeasure' => 'KG',
					'%' => '',
					),
				'StrapType' => '',
				'Strength' => array(
					'@unitOfMeasure' => 'KG',
					'%' => '',
					),
				'StyleKeywords' => '',
				'SupportType' => '',
				'SuspensionType' => '',
				'TargetGender' => '',
				'TargetZones' => '',
				'TeamName' => '',
				'TensionSupported' => array(
					'@unitOfMeasure' => 'KG',
					'%' => '',
					),
				'Theme' => '',
				'ThreadSize' => '',
				'TopStyle' => '',
				'TopTubeLength' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'TrailerType' => '',
				'TurnRadius' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'UIAAFallRating' => '',
				'UnderwireType' => '',
				'UniformNumber' => '',
				'UsageCapacity' => array(
					'@unitOfMeasure' => 'KG',
					'%' => '',
					),
				'UVProtection' => '',
				'VolumeCapacityName' => array(
					'@unitOfMeasure' => 'cubic-cm',
					'%' => '',
					),
				'WaistSize' => '',
				'WaistWidth' => '',
				'WarmthRating' => array(
					'@unitOfMeasure' => 'degrees-celsius',
					'%' => '',
					),
				'Warnings' => '',
				'Warranty' => '',
				'WaterBottleCapType' => '',
				'WaterResistanceRating' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'WaterType' => '',
				'Wattage' => '',
				'Watts' => array(
					'@unitOfMeasure' => 'watts',
					'%' => '',
					),
				'WayPoints' => '',
				'WeightCapacity' => array(
					'@unitOfMeasure' => 'KG',
					'%' => '',
					),
				'WhatsInTheBox' => '',
				'WheelType' => '',
				'ThreadPitch' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'DriveSystem' => '',
				'BladeMaterialType' => '',
				'SportsNumberOfPockets' => '',
				'WorkingLoadLimit' => '',
				'WatchMovementType' => '',
				'TankVolume' => array(
					'@unitOfMeasure' => 'cubic-cm',
					'%' => '',
					),
				'PowerRating' => '',
				'PatternType' => '',
				'OutputPower' => array(
					'@unitOfMeasure' => 'watts',
					'%' => '',
					),
				'OpticalPower' => array(
					'@unitOfMeasure' => 'diopters',
					'%' => '',
					),
				'MinimumHeightRecommendation' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'ItemDiameter' => '',
				'AgeRangeDescription' => '',
				'CollectionName' => '',
				'BandSizeNumber' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'BatteryCellComposition' => '',
				'BatteryDescription' => '',
				'BatteryFormFactor' => '',
				'DistributionDesignation' => '',
				'CustomerRestrictionType' => '',
			),
		);
	}
}
