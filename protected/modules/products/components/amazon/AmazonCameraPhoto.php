<?php 

/**
 * The default template
 *
 * @author mrlina <714480119@qq.com>
 */
Class AmazonCameraPhoto extends AmazonUpload implements IAmazonUpload
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
			$array = $this->$method();
			$this->removeEmptyItemByKey($array, $xsd2->category);

			$array = $this->_CameraPhoto($array);
			$this->removeEmptyItemByKey($array, 'CameraPhoto');

			$data['ProductData'] = $array;
			$xmls[$this->product->sku] = $this->arr2xml->buildXML($this->removeEmptyItem($data), 'Product');
		}
		//多属性
		else if ($this->product->product_is_multi == 2) {
			//父体设置
			$array = $this->$method();
			$this->removeEmptyItemByKey($array, $xsd2->category);

			$array = $this->_CameraPhoto($array, 'parent', $this->mapThemeVaris($this->product->variation_theme));
			$this->removeEmptyItemByKey($array, 'CameraPhoto');

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

				$array = $this->_CameraPhoto($array, 'child', $this->mapThemeVaris($this->product->variation_theme));

				//先修改子sku变体内容
				foreach ($variations as $name => $val) {
					if (isset($array['CameraPhoto'][$this->mapThemeVaris($name)])) {
						$array['CameraPhoto'][$this->mapThemeVaris($name)] = $val;
					}
				}

				$this->removeEmptyItemByKey($array, 'CameraPhoto');
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
			'Color' => 'CustomerPackageType',
			'Size' => 'SizeName',
			'Size-Color' => 'SizeName-CustomerPackageType',
		);

		if (isset($array[$key])) {
			return $array[$key];
		} else {
			return $key;
		}
	}

	protected function _CameraPhoto(array $category, $relative = '', $theme = '')
	{
		return array(
			'CameraPhoto' => array(
				'ProductType' => $category,
				'Battery' => array(
					'AreBatteriesIncluded' => '',
					'AreBatteriesRequired' => '',
					'BatterySubgroup' => array(
						'BatteryType' => '',
						'NumberOfBatteries' => '',
						),
					),
				'BatteryCellType' => '',
				'CountryOfOrigin' => '',
				'Manufacturer' => '',
				'ModelName' => '',
				'ModelNumber' => '',
				'MfrPartNumber' => '',
				'CustomerPackageType' => '',
				'CanShipInOriginalContainer' => '',
				'IdentityPackageType' => '',
				'Color' => '',
				'Rebate' => array(
					'RebateStartDate' => '',
					'RebateEndDate' => '',
					'RebateMessage' => '',
					),
				'ColorMap' => '',
				'ItemsIncluded' => '',
				'Keywords' => '',
				'PlatinumKeywords' => '',
				'AudioInput' => '',
				'AutoFocusTechnology' => '',
				'BatteryTypeLithiumIon' => '',
				'BatteryTypeLithiumMetal' => '',
				'CompatibleDevices' => '',
				'CompatibleMountings' => '',
				'DeviceType' => '',
				'DisplayTechnology' => '',
				'DisplayType' => '',
				'FilmSpeedRange' => '',
				'Finderscope' => '',
				'FormFactor' => '',
				'GuideNumber' => '',
				'HardwarePlatform' => '',
				'HasImageStabilizer' => '',
				'ImageArea' => '',
				'ItemDisplayDiameter' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'ItemDisplayDepth' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'ItemDisplayHeight' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'ItemDisplayLength' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'ItemDisplayWeight' => array(
					'@unitOfMeasure' => 'KG',
					'%' => '',
					),
				'ItemDisplayWidth' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'ItemThickness' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'LensCoverage' => '',
				'LensSystemSpecialFunctions' => '',
				'LightSensitivity' => '',
				'LithiumBatteryEnergyContent' => '',
				'LithiumBatteryPackaging' => '',
				'LithiumBatteryVoltage' => '',
				'LithiumBatteryWeight' => '',
				'ManufacturerWarrantyDescription' => '',
				'ManufacturerWarrantyType' => '',
				'MaximumApertureRange' => '',
				'MaximumHeight' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'MaximumLifetimeCharges' => '',
				'MaximumManufacturerWeightRecommended' => array(
					'@unitOfMeasure' => 'KG',
					'%' => '',
					),
				'MediaType' => '',
				'MemoryStorageCapacity' => array(
					'@unitOfMeasure' => 'MB',
					'%' => '',
					),
				'MemoryTechnology' => '',
				'MfrWarrantyDescriptionLabor' => '',
				'MfrWarrantyDescriptionParts' => '',
				'MicrophoneOperationMode' => '',
				'MinFocalRange' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'MinimumHeight' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'MountingType' => '',
				'NumberOfLithiumIonCells' => '',
				'NumberOfLithiumMetalCells' => '',
				'OpticalSensorResolution' => array(
					'@unitOfMeasure' => 'pixels',
					'%' => '',
					),
				'OpticalSensorSize' => '',
				'OpticalSensorTechnology' => '',
				'Parentage' => $relative,
				'VariationTheme' => $theme,
				'RangefinderType' => '',
				'RechargeableBatteryIncluded' => '',
				'RemoteControlDescription' => '',
				'RemovableStorageInterface' => '',
				'RollQuantity' => '',
				'SellerWarrantyDescription' => '',
				'SizeName' => '',
				'ShootingModes' => '',
				'SupportedImageType' => '',
				'StyleName' => '',
				'VideoInput' => '',
				'VideoInputFormat' => '',
				'VideoInputSpecialEffects' => '',				
			),
		);
	}

	protected function _FilmCamera()
	{
		return array(
			'FilmCamera' => array(
				'CameraType' => 'other',
				'Durability' => '',
				'Features' => '',
				'FilmFormat' => '',
				'FilmManagementFeatures' => '',
				'FixedFocalLength' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'FocusType' => '',
				'LensThread' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'LensType' => '',
				'OpticalZoomRange' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'MacroFocus' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'MinFocalLength' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'MaxFocalLength' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'MinAperture' => array(
					'@unitOfMeasure' => 'f',
					'%' => '',
					),
				'MaxAperture' => array(
					'@unitOfMeasure' => 'f',
					'%' => '',
					),
				'MinShutterSpeed' => '',
				'MaxShutterSpeed' => '',
				'MeteringMethods' => '',
				'ISORange' => '',
				'FlashType' => '',
				'FlashModes' => '',
				'HotShoe' => '',
				'FlashSynchronization' => '',
				'Red-EyeReduction' => '',
				'DiopterAdjustment' => '',
				'Viewfinder' => '',
				'LCD' => '',
				'DateImprint' => '',
				'MidrollChange' => '',
				'MidrollRewind' => '',
				'AutoRewind' => '',
				'AutoFilmAdvance' => '',
				'AutoFilmLoad' => '',
				'SelfTimer' => array(
					'@unitOfMeasure' => 'hours',
					'%' => '',
					),
				'RemoteIncluded' => '',
				'ContinuousShooting' => array(
					'@unitOfMeasure' => 'frames',
					'%' => '',
					),
				'BatteryType' => '',
				'BatteryIncluded' => '',
				'ExposureControl' => '',
				'Size' => '',
				'PackageType' => '',
				'Remote' => '',
				'WaterResistanceDepth' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'WaterResistanceLevel' => '',
				'WeightLimit' => array(
					'@unitOfMeasure' => 'KG',
					'%' => '',
					),
				'ImageStabilization' => '',
				'WirelessTechnology' => '',
				'IncludedFeatures' => '',
			),
		);
	}

	protected function _Camcorder()
	{
		return array(
			'Camcorder' => array(
				'AnalogFormats' => 'general',
				'DigitalFormats' => 'general',
				'SensorType' => '',
				'FilmFormats' => '',
				'LensType' => '',
				'OpticalZoom' => array(
					'@unitOfMeasure' => 'x',
					'%' => '',
					),
				'DigitalZoom' => array(
					'@unitOfMeasure' => 'x',
					'%' => '',
					),
				'VideoResolution' => array(
					'@unitOfMeasure' => 'pixels',
					'%' => '',
					),
				'ThreeDTechnology' => '',
				'AlarmClock' => '',
				'AnalogRBGInput' => '',
				'Audio' => '',
				'LCDScreenSize' => '',
				'LCDSwivel' => '',
				'Viewfinder' => '',
				'MinAperture' => array(
					'@unitOfMeasure' => 'f',
					'%' => '',
					),
				'MaxAperture' => array(
					'@unitOfMeasure' => 'f',
					'%' => '',
					),
				'ImageStabilization' => '',
				'InfraredCapability' => '',
				'FirewireOutput' => '',
				"S-VideoOutput" => '',
				'USBOutput' => '',
				'AVOutput' => '',
				'Connectivity' => '',
				'DigitalStillCapability' => '',
				'DigitalStillResolution' => '',
				'Durability' => '',
				'ExternalMemoryType' => '',
				'ExternalMemoryIncluded' => '',
				'ExternalMemorySize' => array(
					'@unitOfMeasure' => 'MB',
					'%' => '',
					),
				'FixedFocalLength' => '',
				'FocusFeatures' => '',
				'ImageFormat' => '',
				'InternalMemorySize' => array(
					'@unitOfMeasure' => 'MB',
					'%' => '',
					),
				'InternalMemoryType' => '',
				'ISORange' => '',
				'MaximumFocalLength' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'MediaStorage' => '',
				'MinimumFocalLength' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'MPEGMovieMode' => '',
				'USBStreaming' => '',
				'HeadphoneJack' => '',
				'FlyingEraseHeads' => '',
				'Autolight' => '',
				'HotShoe' => '',
				'ComputerPlatform' => '',
				'SoftwareIncluded' => '',
				'BatteryType' => '',
				'RechargeableBatteryIncluded' => '',
				'ACAdapterIncluded' => '',
				'Remote' => '',
				'RemoteIncluded' => '',
				'PlaybackFormat' => '',
				'Features' => '',
				'TotalFirewirePorts' => '',
				'TotalNumberOfHDMIPorts' => '',
				"TotalUSB1.0Ports" => '',
				"TotalUSB1.1Ports" => '',
				"TotalUSB2.0Ports" => '',
				"TotalUSB3.0Ports" => '',
				'TotalUSBPorts' => '',
				'Touchscreen' => '',
				'UseModes' => '',
				'WaterResistanceDepth' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'WaterResistanceLevel' => '',
				'WirelessTechnology' => '',
				'WeightLimit' => array(
					'@unitOfMeasure' => 'KG',
					'%' => '',
					),
				'MinShutterSpeed' => '',
				'ContinuousShooting' => '',
				'CamcorderImageStabilization' => '',
				'DisplaySize' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'ItemShape' => '',
				'RamMemoryMaximumSize' => array(
					'@unitOfMeasure' => 'MB',
					'%' => '',
					),
				'ZoomRatio' => '',
				'PhotographicResolution' => '',
				'MediaFormat' => '',
				'InternationalProtectionRating' => '',
				'IncludedFeatures' => '',
				'ViewfinderMagnification' => '',
				'HasViewfinder' => '',
			),
		);
	}

	protected function _DigitalCamera()
	{
		return array(
			'DigitalCamera' => array(
				'Megapixels' => array(
					'@unitOfMeasure' => 'pixels',
					'%' => '',
					),
				'OpticalZoom' => array(
					'@unitOfMeasure' => 'x',
					'%' => '',
					),
				'DigitalZoom' => array(
					'@unitOfMeasure' => 'x',
					'%' => '',
					),
				'InternalMemorySize' => array(
					'@unitOfMeasure' => 'MB',
					'%' => '',
					),
				'ExternalMemorySize' => array(
					'@unitOfMeasure' => 'MB',
					'%' => '',
					),
				'CameraType' => 'other',
				'FocusType' => '',
				'SensorType' => '',
				'LCDScreenSize' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'LCDSwivel' => '',
				'Viewfinder' => '',
				'MinAperture' => array(
					'@unitOfMeasure' => 'f',
					'%' => '',
					),
				'MaxAperture' => array(
					'@unitOfMeasure' => 'f',
					'%' => '',
					),
				'ImageStabilization' => '',
				'InfraredCapability' => '',
				'FirewireOutput' => '',
				'S-VideoOutput' => '',
				'USBOutput' => '',
				'AVOutput' => '',
				'DigitalStillCapability' => '',
				'DigitalStillResolution' => '',
				'ExternalMemoryType' => '',
				'ExternalMemoryIncluded' => '',
				'Features' => '',
				'USBStreaming' => '',
				'HeadphoneJack' => '',
				'FlyingEraseHeads' => '',
				'Autolight' => '',
				'HotShoe' => '',
				'LensThread' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'ComputerPlatform' => '',
				'SoftwareIncluded' => '',
				'BatteryType' => '',
				'RechargeableBatteryIncluded' => '',
				'ACAdapterIncluded' => '',
				'RemoteIncluded' => '',
				'Connectivity' => '',
				'InternalMemoryType' => '',
				'MaxImageResolution' => array(
					'@unitOfMeasure' => 'pixels',
					'%' => '',
					),
				'UncompressedMode' => '',
				'ThreeDTechnology' => '',
				'AnalogRBGInput' => '',
				'Audio' => '',
				'Durability' => '',
				'ExposureControl' => '',
				'FixedFocalLength' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'FlashModes' => '',
				'FocusFeatures' => '',
				'GeotaggingOrGPSFunctionality' => '',
				'ImageFormat' => '',
				'ISOEquivalency' => '',
				'ISORange' => '',
				'LensType' => '',
				'MacroFocus' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'MinShutterSpeed' => '',
				'MaxShutterSpeed' => '',
				'ManualExposureMode' => '',
				'MaximumFocalLength' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'MeteringMethods' => '',
				'MinimumFocalLength' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'MovieMode' => '',
				'MaxMovieLength' => array(
					'@unitOfMeasure' => 'hours',
					'%' => '',
					),
				'AudioRecording' => '',
				'ContinuousShooting' => array(
					'@unitOfMeasure' => 'frames',
					'%' => '',
					),
				'NoiseReductionLevel' => '',
				'Remote' => '',
				'SelfTimer' => array(
					'@unitOfMeasure' => 'hours',
					'%'  => '',
					),
				'Size' => '',
				'TotalFirewirePorts' => '',
				'TotalNumberOfHDMIPorts' => '',
				'TotalUSB1.0Ports' => '',
				'TotalUSB1.1Ports' => '',
				'TotalUSB2.0Ports' => '',
				'TotalUSB3.0Ports' => '',
				'TotalUSBPorts' => '',
				'Touchscreen' => '',
				'UseModes' => '',
				'VideoResolution' => '',
				'WaterResistanceDepth' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'WaterResistanceLevel' => '',
				'WirelessTechnology' => '',
				'WeightLimit' => '',
				'DigitalCameraImageStabilization' => '',
				'DisplaySize' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'RamMemoryMaximumSize' => array(
					'@unitOfMeasure' => 'MB',
					'%' => '',
					),
				'ZoomRatio' => '',
				'PhotographicResolution' => '',
				'MediaStorage' => '',
				'MediaFormat' => '',
				'InternationalProtectionRating' => '',
				'IncludedFeatures' => '',
				'ViewfinderMagnification' => '',
				'HasViewfinder' => '',
			),
		);
	}

	protected function _Binocular()
	{
		return array(
			'Binocular' => array(
				'BinocularType' => 'binoculars',
				'FocusType' => '',
				'PrismType' => '',
				'ObjectiveLensDiameter' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'ExitPupilDiameter' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'FieldOfView' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'ApparentAngleOfView' => array(
					'@unitOfMeasure' => 'degrees',
					'%' => '',
					),
				'RealAngleOfView' => array(
					'@unitOfMeasure' => 'degrees',
					'%' => '',
					),
				'EyeRelief' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'DiopterAdjustmentRange' => '',
				'Coating' => '',
				'EyepieceLensConstruction' => '',
				'ObjectiveLensConstruction' => '',
				'TripodReady' => '',
				'Features' => '',
				'Magnification' => array(
					'@unitOfMeasure' => 'x',
					'%' => '',
					),
				'SpecificUses' => '',
				'WeightLimit' => array(
					'@unitOfMeasure' => 'KG',
					'%' => '',
					),
				'WirelessTechnology' => '',
				'ZoomRatio' => '',
				'ItemTypeName' => '',
				'InternationalProtectionRating' => '',
				'IncludedFeatures' => '',				
			),
		);
	}

	protected function _SurveillanceSystem()
	{
		return array(
			'SurveillanceSystem' => array(
				'SurveillanceSystemType' => 'cameras',
				'CameraType' => 'security-cameras',
				'AlarmClock' => '',
				'BodyType' => '',
				'CompatibleCameraMount' => '',
				'Durability' => '',
				'Features' => '',
				'CameraAccessories' => '',
				'ImageSensorType' => '',
				'MaximumPanAngle' => array(
					'@unitOfMeasure' => '',
					'%' => '',
					),
				'MediaStorage' => '',
				'MotorCapabilities' => '',
				'MountType' => '',
				'NightVision' => '',
				'NumberofIncludedCameras' => '',
				'Remote' => '',
				'SignalType' => '',
				'VideoResolution' => '',
				'WirelessTechnology' => '',
				'ZoomRatio' => '',
			),
		);
	}

	protected function _Telescope()
	{
		return array(
			'Telescope' => array(
				'TelescopeType' => 'general',
				'TelescopeEyepiece' => '',
				'MinAperture' => '',
				'MaxAperture' => '',
				'PrimaryAperture' => '',
				'FocalLength' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'ResolvingPower' => array(
					'@unitOfMeasure' => 'arcs-per-sec',
					'%' => '',
					),
				'Mount' => '',
				'HighestUsefulMagnification' => array(
					'@unitOfMeasure' => 'x',
					'%' => '',
					),
				'LCDTiltAngle' => '',
				'LowestUsefulMagnification' => array(
					'@unitOfMeasure' => 'x',
					'%' => '',
					),
				'OpticalTubeLength' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'OpticalTubeDiameter' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'OpticalCoatings' => '',
				'MotorizedControls' => '',
				'Viewfinder' => '',
				'EyepieceType' => '',
				'OutdoorUse' => '',
				'PhotographicResolution' => '',
				'DawesLimit' => array(
					'@unitOfMeasure' => 'arc*sec',
					'%' => '',
					),
				'ComputerPlatform' => '',
				'BatteryType' => '',
				'BatteryIncluded' => '',
				'WeightLimit' => array(
					'@unitOfMeasure' => 'KG',
					'%' => '',
					),
				'WirelessTechnology' => '',
				'ZoomRatio' => '',
				'SupportedStandards' => '',
				'ObjectiveLensDiameter' => '',
				'PowerType' => '',
				'ItemTypeName' => '',
				'IncludedFeatures' => '',
				'ItemDiameter' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
			),
		);
	}

	protected function _Microscope()
	{
		return array(
			'Microscope' => array(
				'MicroscopeType' => 'other',
				'Features' => 'goods',
			),
		);
	}

	protected function _Darkroom()
	{
		return array(
			'Darkroom' => array(
				'Chemicals' => 'other-chemicals',
				'Enlargers' => '',
				'Easels' => '',
				'EnlargingHeadAndAccessories' => '',
				'OtherEnlargerAccessories' => '',
				'AnalyzersAndExposureMeters' => '',
				'SafelightsAndAccessories' => '',
				'AirRegulators' => '',
				'WaterControlsAndFilters' => '',
				'SinksAndAccessories' => '',
				'MixingEquipment' => '',
				'GeneralDevelopingAndProcessingSupplies' => '',
				'FilmProcessingSupplies' => '',
				'PaperProcessingSupplies' => '',
				'TabletopProcessingSupplies' => '',
			),
		);
	}

	protected function _Lens()
	{
		return array(
			'Lens' => array(
				'CameraType' => 'camcorder',
				'CompatibleCameraMount' => '',
				'FixedFocalLength' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'FocalType' => '',
				'MinFocalLength' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'MaxFocalLength' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'LensType' => '',
				'FocusType' => '',
				'Features' => '',
				'WaterResistanceDepth' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'WaterResistanceLevel' => '',
				'WeightLimit' => '',
				'WirelessTechnology' => '',
				'ZoomRatio' => '',
				'ItemTypeName' => '',
				'IncludedFeatures' => '',
				'CameraLens' => '',
			),
		);
	}

	protected function _LensAccessory()
	{
		return array(
			'LensAccessory' => array(
				'ForUseWith' => 'microscopes',
				'AccessoryType' => 'lens-caps-55mm',
			),
		);
	}

	protected function _Filter()
	{
		return array(
			'Filter' => array(
				'ForUseWith' => 'microscopes',
				'PackageType' => '',
				'MountType' => '',
				'ThreadSize' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'DropInSize' => '',
				'BayonetSize' => '',
				'Durability' => '',
				'FilterType' => '',
				'FilterColor' => '',
				'LightingType' => '',
				'SpecialEffect' => '',
				'SpecificUses' => '',
				'WaterResistanceDepth' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'WaterResistanceLevel' => '',
			),
		);
	}

	protected function _Film()
	{
		return array(
			'Film' => array(
				'FilmColor' => 'color',
				'FilmType' => '',
				'Format' => '',
				'ASA-ISO' => '',
				'ExposureCount' => '',
				'LightingType' => '',
			),
		);
	}

	protected function _BagCase()
	{
		return array(
			'BagCase' => array(
				'BagCaseType' => 'camcorder-cases',
				'CompartmentQuantity' => '',
				'HoodType' => '',
				'RollingFeatures' => '',
				'SecurityFeatures' => '',
				'Style' => '',
				'MaterialType' => '',
				'Features' => '',
				'SpecificUses' => '',
				'WaterResistanceDepth' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'WaterResistanceLevel' => '',
				'WeightLimit' => array(
					'@unitOfMeasure' => 'KG',
					'%' => '',
					),
				'WirelessTechnology' => '',
				'ItemTypeName' => '',
				'InternationalProtectionRating' => '',
				'IncludedFeatures' => '',
			),
		);
	}

	protected function _BlankMedia()
	{
		return array(
			'BlankMedia' => array(
				'AnalogFormats' => 'reel-tapes',
				'DigitalFormats' => '',
				'MotionFilmFormats' => '',
				'MediaColor' => '',
				'Count' => '',
			),
		);
	}

	protected function _PhotoPaper()
	{
		return array(
			'PhotoPaper' => array(
				'PaperType' => 'other',
				'PaperBase' => '',
				'PaperSurface' => '',
				'PaperGrade' => '',
				'PaperSize' => '',
			),
		);
	}

	protected function _Cleaner()
	{
		return array(
			'Cleaner' => array(
				'CleanerType' => 'brushes',
			),
		);
	}

	protected function _Flash()
	{
		return array(
			'Flash' => array(
				'FlashType' => 'macro',
				'SlaveFlashes' => 'optical-slaves',
				'Dedication' => 'dedicated',
			),
		);
	}

	protected function _TripodStand()
	{
		return array(
			'TripodStand' => array(
				'ForUseWith' => 'camcorders',
				'StandType' => 'tripods',
				'SpecificUses' => 'tabletop',
				'Material' => 'aluminum',
				'HeadType' => 'geared-heads',
				'PackageType' => 'head-only',
			),
		);
	}

	protected function _LightingType()
	{
		return array(
			'LightingType' => array(
				'ForUseWith' => 'umbrellas',
				'LightingType' => '',
				'PowerType' => '',
				'Power' => '',
				'LightingSourceType' => '',
				'SpecialtyUse' => '',
			), 
		);
	}

	protected function _Projection()
	{
		return array(
			'Projection' => array(
				'ProjectionType' => 'loupes',
				'LoupeMagnification' => '',
				'ProjectorLenses' => '',
				'ProjectionScreens' => '',
				'AudioVisualProductAccessories' => '',
			),
		);
	}

	protected function _PhotoStudio()
	{
		return array(
			'PhotoStudio' => array(
				'StorageAndPresentationMaterials' => 'hanging-bars',
				'StudioSupplies' => '',
				'PhotoBackgrounds' => '',
				'PhotoBackgroundAccessories' => '',
				'PhotoBackgroundFabrics' => '',
				'PhotoStudioAccessories' => '',
			),
		);
	}

	protected function _LightMeter()
	{
		return array(
			'LightMeter' => array(
				'CameraType' => 'universal',
				'MeterType' => '',
				'MeterDisplay' => '',
			),
		);
	}

	protected function _PowerSupply()
	{
		return array(
			'PowerSupply' => array(
				'ForUseWith' => 'other-products',
				'CameraPowerSupplyType' => '',
				'BatteryChemicalType' => '',
				'PowerSupplyAccessories' => '',
			),
		);
	}

	protected function _DigitalFrame()
	{
		return array(
			'DigitalFrame' => array(
				'DisplaySize' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'Touchscreen' => 'touch_screen',
			),
		);
	}

	protected function _OtherAccessory()
	{
		return array(
			'OtherAccessory' => array(
				'CameraAccessories' => 'other-camera-accessories',
				'CamcorderAccessories' => 'other-camcorder-accessories',
				'CleanerAccessory' => '',
				'LightingAccessoryType' => '',
				'TelescopeAccessories' => '',
				'TelescopeEyepiece' => '',
				'MicroscopeAccessories' => '',
				'FilterAccessories' => '',
				'FilmAccessories' => '',
				'FlashAccessories' => '',
				'BagCaseAccessories' => '',
				'UnderwaterPhotographyAccessories' => '',
				'LightMeterAccessories' => '',
				'TripodStandAccessories' => '',
				'BinocularAccessories' => '',
				'CableLength' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'ForUseWith' => '',
				'NightVision' => '',
				'Mountingpattern' => '',
				'NoiseReductionLevel' => '',
				'Features' => '',
				'OutdoorUse' => '',
				'Durability' => '',
				'WaterResistanceDepth' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'WaterResistanceLevel' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'SpecificUses' => '',
				'MaterialComposition' => '',
				'WeightLimit' => array(
					'@unitOfMeasure' => 'KG',
					'%' => '',
					),
				'BayonetSize' => '',
				'DisplaySize' => array(
					'@unitOfMeasure' => 'CM',
					'%' => '',
					),
				'WirelessTechnology' => '',
				'ItemShape' => '',
				'ZoomRatio' => '',
				'HeadType' => '',
				'ItemTypeName' => '',
				'IsExpirationDatedProduct' => '',
				'InternationalProtectionRating' => '',
				'IncludedFeatures' => '',
				'ExtensionLength' => '',
				'HandleType' => '',
			),
		);
	}
}