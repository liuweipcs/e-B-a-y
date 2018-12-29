<?php 

/**
 * The default template
 *
 * @author mrlina <714480119@qq.com>
 */
Class AmazonFoodAndBeverages extends AmazonUpload implements IAmazonUpload
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
	 * 移除值为空的项
	 * 
	 * @return array
	 */
    protected function trimTier(array $data,$pcate,$chcate)
    {
        //handle descript first
        foreach ($data['DescriptionData'] as $key => $value) {
            if ($this->isEmpty($value)) {
                unset($data['DescriptionData'][$key]);
            }
        }

        foreach ($data['ProductData'][$pcate]['ProductType'][$chcate]['VariationData'] as $key => $value) {
            if ($this->isEmpty($value)) {
                unset($data['ProductData'][$pcate]['ProductType'][$chcate]['VariationData'][$key]);
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
     *变体映射-转换
     * @param $key
     * @return mixed
     */
    protected function mapThemeVaris($key)
    {
        $array = array(
            'Color' => 'Flavor',
            'Size' => 'Size',
            'Size-Color' => 'Flavor-Size',
        );

        return $array[$key];
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
        $theme=$this->mapThemeVaris($this->product->variation_theme);

        if ($this->product->product_is_multi == 0) {
            $array = $this->$method();
            $this->removeEmptyItemByKey($array, $xsd2->category);

            $array = $this->_Catergorys($array);
            $data['ProductData'] = $array;

            $xmls[$this->product->sku] = $this->arr2xml->buildXML($this->removeEmptyItem($data), 'Product');

        } else if ($this->product->product_is_multi == 2) {
            $array = $this->$method('parent', $theme);

            $array = $this->_Catergorys($array);
            $data['ProductData'] = $array;

            $xmls[$this->product->sku] = $this->arr2xml->buildXML($this->trimTier($data,'FoodAndBeverages',$xsd2->category), 'Product');


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

                $array = $this->$method('child', $theme, $variations);
                foreach ($variations as $name => $val) {
                    if (isset($array[$xsd2->category][$name])) {
                        $array[$xsd2->category][$name] = $val;
                    }
                }

                $array = $this->_Catergorys($array);
                $child['ProductData'] = $array;
                $xmls[$sonprd->sku] = $this->arr2xml->buildXML($this->trimTier($child,'FoodAndBeverages',$xsd2->category), 'Product');

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

    protected function _Catergorys(array $category)
    {
        return array(
            'FoodAndBeverages' => array(
                'ProductType' => $category,
                'Battery'=>array(
                        'AreBatteriesIncluded'=>true,
                        'AreBatteriesRequired'=>true,
                        'BatterySubgroup'=>array(
                            'BatteryType'=>'battery_type_aa',
                            'NumberOfBatteries'=>'1',
                        ),
                ),
                'BatteryAverageLife'=>'1',
                'BatteryAverageLifeStandby'=>'1',
                'BatteryChargeTime'=>'1',
                'Color'=>'Color',
                'ColorMap'=>'ColorMap',
                'IsAdultProduct'=>true,
                'LithiumBatteryEnergyContent'=>'1',
                'LithiumBatteryPackaging'=>'batteries_only',
                'LithiumBatteryVoltage'=>'1',
                'LithiumBatteryWeight'=>'1',
                'MfrWarrantyDescriptionLabor'=>'MfrWarrantyDescriptionLabor',
                'MfrWarrantyDescriptionParts'=>'MfrWarrantyDescriptionParts',
                'MfrWarrantyDescriptionType'=>'MfrWarrantyDescriptionType',
                'NumberOfLithiumIonCells'=>'1',
                'NumberOfLithiumMetalCells'=>'1',
                'PowerSource'=>'PowerSource',
                'SellerWarrantyDescription'=>'SellerWarrantyDescription',
                'TargetGender'=>'male',
                'DeliveryScheduleGroupId'=>'DeliveryScheduleGroupId',
            ),
        );
    }

    protected function _Food($relative = '', $theme = '', $variations='')
    {
        return array(
            'Food' => array(
                'VariationData' => array(
                    'Parentage' => $relative,
                    'VariationTheme' => $theme,
                    'Size'=>$variations['Size'],
                    'Flavor'=>$variations['Color'],
                    'PatternName'=>'PatternName',
                    'StyleName'=>'StyleName',
                ),
                'CountryProducedIn'=>'CountryProducedIn',
                'ContainsFoodOrBeverage'=>true,
                'CountryOfOrigin'=>'CountryOfOrigin',
                'MedicineClassification'=>'MedicineClassification',
                'RegionOfOrigin'=>'RegionOfOrigin',
                'Prefecture'=>'Prefecture',
                'ItemForm'=>'ItemForm',
                'Ingredients'=>'Ingredients',
                'NutritionalFacts'=>'NutritionalFacts',
                'KosherCertification'=>'KosherCertification',
                'OrganicCertification'=>'OrganicCertification',
                'TokuhoCertification'=>'TokuhoCertification',
                'ItemSpecialty'=>'ItemSpecialty',
                'CaffeineContent'=>'CaffeineContent',
                'Warnings'=>'Warnings',
                'IsPerishable'=>true,
                'StorageInstructions'=>'StorageInstructions',
                'Directions'=>'Directions',
                'RecommendedServingInstructions'=>'RecommendedServingInstructions',
                'Occasion'=>'Occasion',
                'OccasionType'=>'OccasionType',
                'AwardsWon'=>'AwardsWon',
                'AgeRangeDescription'=>'AgeRangeDescription',
                'ItemPackageQuantity'=>'1',
                'NumberOfItems'=>'1',
                'DisplayLength'=>array(
                    '@unitOfMeasure' => 'CM',
                    '%' => '1',
                ),
                'DisplayWeight'=> array(
                    '@unitOfMeasure' => 'KG',
                    '%' => '1',
                ),
                'DisplayVolume'=> array(
                    '@unitOfMeasure' => 'pint',
                    '%' => '1',
                ),
                'ContainerType'=>'ContainerType',
                'UnitCount'=>array(
                    '@unitOfMeasure'=>'UnitCountType',
                    '%'=>'1'
                ),
                'NutritionalFactsGroup'=>array(
                    'AllergenInformation'=>'fish',
                    'Calcium'=>array(
                        '@unitOfMeasure'=>'mg',
                        '%'=>'1'
                    ),
                    'Cholesterol'=>array(
                        '@unitOfMeasure'=>'kg',
                        '%'=>'1'
                    ),
                    'DietaryFiber'=>array(
                        '@unitOfMeasure'=>'ml',
                        '%'=>'1'
                    ),
                    'EnergyContent'=>array(
                        '@unitOfMeasure'=>'watt_hours',
                        '%'=>'1'
                    ),
                    'EnergyContentFromFat'=>array(
                        '@unitOfMeasure'=>'watt_hours',
                        '%'=>'1'
                    ),
                    'InsolubleFiber'=>array(
                        '@unitOfMeasure'=>'ml',
                        '%'=>'1'
                    ),
                    'Iron'=>array(
                        '@unitOfMeasure'=>'ml',
                        '%'=>'1'
                    ),
                    'MonounsaturatedFat'=>array(
                        '@unitOfMeasure'=>'ml',
                        '%'=>'1'
                    ),
                    'OtherCarbohydrate'=>array(
                        '@unitOfMeasure'=>'ml',
                        '%'=>'1'
                    ),
                    'phosphorus'=>array(
                        '@unitOfMeasure'=>'ml',
                        '%'=>'1'
                    ),
                    'polyunsaturateDfat'=>array(
                        '@unitOfMeasure'=>'ml',
                        '%'=>'1'
                    ),
                    'potassium'=>array(
                        '@unitOfMeasure'=>'ml',
                        '%'=>'1'
                    ),
                    'protein'=>array(
                        '@unitOfMeasure'=>'ml',
                        '%'=>'1'
                    ),
                    'saturateDfat'=>array(
                        '@unitOfMeasure'=>'ml',
                        '%'=>'1'
                    ),
                    'servinGsize'=>array(
                        '@unitOfMeasure'=>'ml',
                        '%'=>'1'
                    ),
                    'servinGsizEdescription'=>'servinGsizEdescription',
                    'sodium'=>array(
                        '@unitOfMeasure'=>'ml',
                        '%'=>'1'
                    ),
                    'solublEfiber'=>array(
                        '@unitOfMeasure'=>'ml',
                        '%'=>'1'
                    ),
                    'speciaLingredients'=>'speciaLingredients',
                    'sugaRalcohol'=>array(
                        '@unitOfMeasure'=>'ml',
                        '%'=>'1'
                    ),
                    'sugars'=>array(
                        '@unitOfMeasure'=>'ml',
                        '%'=>'1'
                    ),
                    'temperaturErating'=>'temperaturErating',
                    'thiamin'=>array(
                        '@unitOfMeasure'=>'ml',
                        '%'=>'1'
                    ),
                    'totaLcarbohydrate'=>array(
                        '@unitOfMeasure'=>'ml',
                        '%'=>'1'
                    ),
                    'totaLfat'=>array(
                        '@unitOfMeasure'=>'ml',
                        '%'=>'1'
                    ),
                    'tranSfat'=>array(
                        '@unitOfMeasure'=>'ml',
                        '%'=>'1'
                    ),
                    'vitamiNA'=>array(
                        '@unitOfMeasure'=>'ml',
                        '%'=>'1'
                    ),
                    'vitamiNC'=>array(
                        '@unitOfMeasure'=>'ml',
                        '%'=>'1'
                    ),
                    'CalciumString'=>'String',
                    'EnergyString'=>'String',
                    'TotalFatString'=>'String',
                    'SaturatedFatString'=>'String',
                    'MonounsaturatedFatString'=>'String',
                    'PolyunsaturatedFatString'=>'String',
                    'TotalCarbohydrateString'=>'String',
                    'SugarsString'=>'String',
                    'SugarAlcoholString'=>'String',
                    'Starch'=>'String',
                    'DietaryFiberString'=>'String',
                    'ProteinString'=>'String',
                    'VitaminAString'=>'String',
                    'VitaminCString'=>'String',
                    'VitaminDString'=>'String',
                    'VitaminEString'=>'String',
                    'VitaminKString'=>'String',
                    'ThiaminString'=>'String',
                    'VitaminB2'=>'String',
                    'Niacin'=>'String',
                    'VitaminB6'=>'String',
                    'FolicAcid'=>'String',
                    'VitaminB12'=>'String',
                    'Biotin'=>'String',
                    'PantothenicAcid'=>'String',
                    'PotassiumString'=>'String',
                    'Chloride'=>'String',
                    'PhosphorusString'=>'String',
                    'Magnesium'=>'String',
                    'IronString'=>'String',
                    'Zinc'=>'String',
                    'Copper'=>'String',
                    'Manganese'=>'String',
                    'Fluoride'=>'String',
                    'Selenium'=>'String',
                    'Chromium'=>'String',
                    'Molybdenum'=>'String',
                    'Iodine'=>'String',
                    'CholesterolString'=>'String',
                    'SodiumString'=>'String',
                ),
                'ContainerMaterialType'=>'ContainerMaterialType',
                'ContainerVolume'=>'1',
                'Cuisine'=>'Cuisine',
                'UseByRecommendation'=>'UseByRecommendation',
                'IsExpirationDatedProduct'=>true,
                'Vintage'=>'nv',
                'AlcoholContent'=>array(
                    '@unitOfMeasure'=>'percent_by_weight',
                    '%'=>'1'
                ),
                'VarietalComposition'=>'VarietalComposition',
                'VarietalDesignation'=>'VarietalDesignation',
                'AlcoholType'=>'AlcoholType',
                'BarrelAgingTime'=>array(
                    '@unitOfMeasure'=>'months',
                    '%'=>'1'
                ),
                'SourceAnimal'=>'SourceAnimal',
                'CutType'=>'CutType',
                'SaltPerServing'=>array(
                    '@unitOfMeasure' => 'KG',
                    '%' => '1'
                ),
                'SaltPerServingString'=>'SaltPerServingString',
                'PrimaryIngredientCountryOfOrigin'=>'PrimaryIngredientCountryOfOrigin',
                'PrimaryIngredientLocationProduced'=>'PrimaryIngredientLocationProduced',
                'SolidNetWeight'=>array(
                    '@unitOfMeasure' => 'KG',
                    '%' => '1'
                ),
                'MaterialFeatures'=>'MaterialFeatures',
                'ManufacturerContactInformation'=>'ManufacturerContactInformation',
            ),
        );
    }

    protected function _HouseholdSupplies($relative = '', $theme = '', $variations='')
    {
        return array(
            'HouseholdSupplies' => array(
                'VariationData' => array(
                    'Parentage' => $relative,
                    'VariationTheme' => $theme,
                    'Size'=>$variations['Size'],
                    'Flavor'=>$variations['Color'],
                    'PatternName'=>'PatternName',
                ),
                'CountryProducedIn'=>'CountryProducedIn',
                'CountryOfOrigin'=>'CountryOfOrigin',
                'RegionOfOrigin'=>'RegionOfOrigin',
                'Prefecture'=>'Prefecture',
                'ItemForm'=>'ItemForm',
                'Ingredients'=>'Ingredients',
                'NutritionalFacts'=>'NutritionalFacts',
                'KosherCertification'=>'KosherCertification',
                'OrganicCertification'=>'OrganicCertification',
                'TokuhoCertification'=>'TokuhoCertification',
                'ItemSpecialty'=>'ItemSpecialty',
                'CaffeineContent'=>'CaffeineContent',
                'Warnings'=>'Warnings',
                'IsPerishable'=>true,
                'StorageInstructions'=>'StorageInstructions',
                'Directions'=>'Directions',
                'Occasion'=>'Occasion',
                'AwardsWon'=>'AwardsWon',
                'AgeRangeDescription'=>'AgeRangeDescription',
                'ItemPackageQuantity'=>'1',
                'NumberOfItems'=>'1',
                'DisplayLength'=>array(
                    '@unitOfMeasure' => 'CM',
                    '%' => '1',
                ),
                'DisplayWeight'=> array(
                    '@unitOfMeasure' => 'KG',
                    '%' => '1',
                ),
                'DisplayVolume'=> array(
                    '@unitOfMeasure' => 'pint',
                    '%' => '1',
                ),
                'ContainerType'=>'ContainerType',
                'UnitCount'=>array(
                    '@unitOfMeasure'=>'UnitCountType',
                    '%'=>'1'
                ),
                'NutritionalFactsGroup'=>array(
                    'AllergenInformation'=>'fish',
                    'Calcium'=>array(
                        '@unitOfMeasure'=>'mg',
                        '%'=>'1'
                    ),
                    'Cholesterol'=>array(
                        '@unitOfMeasure'=>'kg',
                        '%'=>'1'
                    ),
                    'DietaryFiber'=>array(
                        '@unitOfMeasure'=>'ml',
                        '%'=>'1'
                    ),
                    'EnergyContent'=>array(
                        '@unitOfMeasure'=>'watt_hours',
                        '%'=>'1'
                    ),
                    'EnergyContentFromFat'=>array(
                        '@unitOfMeasure'=>'watt_hours',
                        '%'=>'1'
                    ),
                    'InsolubleFiber'=>array(
                        '@unitOfMeasure'=>'ml',
                        '%'=>'1'
                    ),
                    'Iron'=>array(
                        '@unitOfMeasure'=>'ml',
                        '%'=>'1'
                    ),
                    'MonounsaturatedFat'=>array(
                        '@unitOfMeasure'=>'ml',
                        '%'=>'1'
                    ),
                    'OtherCarbohydrate'=>array(
                        '@unitOfMeasure'=>'ml',
                        '%'=>'1'
                    ),
                    'phosphorus'=>array(
                        '@unitOfMeasure'=>'ml',
                        '%'=>'1'
                    ),
                    'polyunsaturateDfat'=>array(
                        '@unitOfMeasure'=>'ml',
                        '%'=>'1'
                    ),
                    'potassium'=>array(
                        '@unitOfMeasure'=>'ml',
                        '%'=>'1'
                    ),
                    'protein'=>array(
                        '@unitOfMeasure'=>'ml',
                        '%'=>'1'
                    ),
                    'saturateDfat'=>array(
                        '@unitOfMeasure'=>'ml',
                        '%'=>'1'
                    ),
                    'servinGsize'=>array(
                        '@unitOfMeasure'=>'ml',
                        '%'=>'1'
                    ),
                    'servinGsizEdescription'=>'servinGsizEdescription',
                    'sodium'=>array(
                        '@unitOfMeasure'=>'ml',
                        '%'=>'1'
                    ),
                    'solublEfiber'=>array(
                        '@unitOfMeasure'=>'ml',
                        '%'=>'1'
                    ),
                    'speciaLingredients'=>'speciaLingredients',
                    'sugaRalcohol'=>array(
                        '@unitOfMeasure'=>'ml',
                        '%'=>'1'
                    ),
                    'sugars'=>array(
                        '@unitOfMeasure'=>'ml',
                        '%'=>'1'
                    ),
                    'temperaturErating'=>'temperaturErating',
                    'thiamin'=>array(
                        '@unitOfMeasure'=>'ml',
                        '%'=>'1'
                    ),
                    'totaLcarbohydrate'=>array(
                        '@unitOfMeasure'=>'ml',
                        '%'=>'1'
                    ),
                    'totaLfat'=>array(
                        '@unitOfMeasure'=>'ml',
                        '%'=>'1'
                    ),
                    'tranSfat'=>array(
                        '@unitOfMeasure'=>'ml',
                        '%'=>'1'
                    ),
                    'vitamiNA'=>array(
                        '@unitOfMeasure'=>'ml',
                        '%'=>'1'
                    ),
                    'vitamiNC'=>array(
                        '@unitOfMeasure'=>'ml',
                        '%'=>'1'
                    ),
                    'CalciumString'=>'String',
                    'EnergyString'=>'String',
                    'TotalFatString'=>'String',
                    'SaturatedFatString'=>'String',
                    'MonounsaturatedFatString'=>'String',
                    'PolyunsaturatedFatString'=>'String',
                    'TotalCarbohydrateString'=>'String',
                    'SugarsString'=>'String',
                    'SugarAlcoholString'=>'String',
                    'Starch'=>'String',
                    'DietaryFiberString'=>'String',
                    'ProteinString'=>'String',
                    'VitaminAString'=>'String',
                    'VitaminCString'=>'String',
                    'VitaminDString'=>'String',
                    'VitaminEString'=>'String',
                    'VitaminKString'=>'String',
                    'ThiaminString'=>'String',
                    'VitaminB2'=>'String',
                    'Niacin'=>'String',
                    'VitaminB6'=>'String',
                    'FolicAcid'=>'String',
                    'VitaminB12'=>'String',
                    'Biotin'=>'String',
                    'PantothenicAcid'=>'String',
                    'PotassiumString'=>'String',
                    'Chloride'=>'String',
                    'PhosphorusString'=>'String',
                    'Magnesium'=>'String',
                    'IronString'=>'String',
                    'Zinc'=>'String',
                    'Copper'=>'String',
                    'Manganese'=>'String',
                    'Fluoride'=>'String',
                    'Selenium'=>'String',
                    'Chromium'=>'String',
                    'Molybdenum'=>'String',
                    'Iodine'=>'String',
                    'CholesterolString'=>'String',
                    'SodiumString'=>'String',
                ),
                'ContainerMaterialType'=>'ContainerMaterialType',
                'ContainerVolume'=>'1',
                'Cuisine'=>'Cuisine',
                'UseByRecommendation'=>'UseByRecommendation',
                'IsExpirationDatedProduct'=>true,
            ),
        );
    }

    protected function _Beverages($relative = '', $theme = '', $variations='')
    {
        return array(
            'Beverages' => array(
                'VariationData' => array(
                    'Parentage' => $relative,
                    'VariationTheme' => $theme,
                    'Size'=>$variations['Size'],
                    'Flavor'=>$variations['Color'],
                    'PatternName'=>'PatternName',
                ),
                'CountryProducedIn'=>'CountryProducedIn',
                'CountryOfOrigin'=>'CountryOfOrigin',
                'RegionOfOrigin'=>'RegionOfOrigin',
                'Prefecture'=>'Prefecture',
                'ItemForm'=>'ItemForm',
                'Ingredients'=>'Ingredients',
                'NutritionalFacts'=>'NutritionalFacts',
                'KosherCertification'=>'KosherCertification',
                'OrganicCertification'=>'OrganicCertification',
                'TokuhoCertification'=>'TokuhoCertification',
                'ItemSpecialty'=>'ItemSpecialty',
                'BodyDescription'=>'BodyDescription',
                'TasteDescription'=>'TasteDescription',
                'CaffeineContent'=>'CaffeineContent',
                'Warnings'=>'Warnings',
                'IsPerishable'=>true,
                'StorageInstructions'=>'StorageInstructions',
                'Directions'=>'Directions',
                'RecommendedServingInstructions'=>'RecommendedServingInstructions',
                'Occasion'=>'Occasion',
                'OccasionType'=>'OccasionType',
                'AwardsWon'=>'AwardsWon',
                'AgeRangeDescription'=>'AgeRangeDescription',
                'ItemPackageQuantity'=>'1',
                'NumberOfItems'=>'1',
                'DisplayLength'=>array(
                    '@unitOfMeasure' => 'CM',
                    '%' => '1',
                ),
                'DisplayWeight'=> array(
                    '@unitOfMeasure' => 'KG',
                    '%' => '1',
                ),
                'DisplayVolume'=> array(
                    '@unitOfMeasure' => 'pint',
                    '%' => '1',
                ),
                'ContainerType'=>'ContainerType',
                'UnitCount'=>array(
                    '@unitOfMeasure'=>'UnitCountType',
                    '%'=>'1'
                ),
                'NutritionalFactsGroup'=>array(
                    'AllergenInformation'=>'fish',
                    'Calcium'=>array(
                        '@unitOfMeasure'=>'mg',
                        '%'=>'1'
                    ),
                    'Cholesterol'=>array(
                        '@unitOfMeasure'=>'kg',
                        '%'=>'1'
                    ),
                    'DietaryFiber'=>array(
                        '@unitOfMeasure'=>'ml',
                        '%'=>'1'
                    ),
                    'EnergyContent'=>array(
                        '@unitOfMeasure'=>'watt_hours',
                        '%'=>'1'
                    ),
                    'EnergyContentFromFat'=>array(
                        '@unitOfMeasure'=>'watt_hours',
                        '%'=>'1'
                    ),
                    'InsolubleFiber'=>array(
                        '@unitOfMeasure'=>'ml',
                        '%'=>'1'
                    ),
                    'Iron'=>array(
                        '@unitOfMeasure'=>'ml',
                        '%'=>'1'
                    ),
                    'MonounsaturatedFat'=>array(
                        '@unitOfMeasure'=>'ml',
                        '%'=>'1'
                    ),
                    'OtherCarbohydrate'=>array(
                        '@unitOfMeasure'=>'ml',
                        '%'=>'1'
                    ),
                    'phosphorus'=>array(
                        '@unitOfMeasure'=>'ml',
                        '%'=>'1'
                    ),
                    'polyunsaturateDfat'=>array(
                        '@unitOfMeasure'=>'ml',
                        '%'=>'1'
                    ),
                    'potassium'=>array(
                        '@unitOfMeasure'=>'ml',
                        '%'=>'1'
                    ),
                    'protein'=>array(
                        '@unitOfMeasure'=>'ml',
                        '%'=>'1'
                    ),
                    'saturateDfat'=>array(
                        '@unitOfMeasure'=>'ml',
                        '%'=>'1'
                    ),
                    'servinGsize'=>array(
                        '@unitOfMeasure'=>'ml',
                        '%'=>'1'
                    ),
                    'servinGsizEdescription'=>'servinGsizEdescription',
                    'sodium'=>array(
                        '@unitOfMeasure'=>'ml',
                        '%'=>'1'
                    ),
                    'solublEfiber'=>array(
                        '@unitOfMeasure'=>'ml',
                        '%'=>'1'
                    ),
                    'speciaLingredients'=>'speciaLingredients',
                    'sugaRalcohol'=>array(
                        '@unitOfMeasure'=>'ml',
                        '%'=>'1'
                    ),
                    'sugars'=>array(
                        '@unitOfMeasure'=>'ml',
                        '%'=>'1'
                    ),
                    'temperaturErating'=>'temperaturErating',
                    'thiamin'=>array(
                        '@unitOfMeasure'=>'ml',
                        '%'=>'1'
                    ),
                    'totaLcarbohydrate'=>array(
                        '@unitOfMeasure'=>'ml',
                        '%'=>'1'
                    ),
                    'totaLfat'=>array(
                        '@unitOfMeasure'=>'ml',
                        '%'=>'1'
                    ),
                    'tranSfat'=>array(
                        '@unitOfMeasure'=>'ml',
                        '%'=>'1'
                    ),
                    'vitamiNA'=>array(
                        '@unitOfMeasure'=>'ml',
                        '%'=>'1'
                    ),
                    'vitamiNC'=>array(
                        '@unitOfMeasure'=>'ml',
                        '%'=>'1'
                    ),
                    'CalciumString'=>'String',
                    'EnergyString'=>'String',
                    'TotalFatString'=>'String',
                    'SaturatedFatString'=>'String',
                    'MonounsaturatedFatString'=>'String',
                    'PolyunsaturatedFatString'=>'String',
                    'TotalCarbohydrateString'=>'String',
                    'SugarsString'=>'String',
                    'SugarAlcoholString'=>'String',
                    'Starch'=>'String',
                    'DietaryFiberString'=>'String',
                    'ProteinString'=>'String',
                    'VitaminAString'=>'String',
                    'VitaminCString'=>'String',
                    'VitaminDString'=>'String',
                    'VitaminEString'=>'String',
                    'VitaminKString'=>'String',
                    'ThiaminString'=>'String',
                    'VitaminB2'=>'String',
                    'Niacin'=>'String',
                    'VitaminB6'=>'String',
                    'FolicAcid'=>'String',
                    'VitaminB12'=>'String',
                    'Biotin'=>'String',
                    'PantothenicAcid'=>'String',
                    'PotassiumString'=>'String',
                    'Chloride'=>'String',
                    'PhosphorusString'=>'String',
                    'Magnesium'=>'String',
                    'IronString'=>'String',
                    'Zinc'=>'String',
                    'Copper'=>'String',
                    'Manganese'=>'String',
                    'Fluoride'=>'String',
                    'Selenium'=>'String',
                    'Chromium'=>'String',
                    'Molybdenum'=>'String',
                    'Iodine'=>'String',
                    'CholesterolString'=>'String',
                    'SodiumString'=>'String',
                ),
                'ContainerMaterialType'=>'ContainerMaterialType',
                'ContainsFoodOrBeverage'=>true,
                'ContainerVolume'=>'1',
                'Cuisine'=>'Cuisine',
                'UseByRecommendation'=>'UseByRecommendation',
                'IsExpirationDatedProduct'=>true,
                'Vintage'=>'nv',
                'AlcoholContent'=>array(
                    '@unitOfMeasure'=>'percent_by_weight',
                    '%'=>'1'
                ),
                'VarietalComposition'=>'VarietalComposition',
                'VarietalDesignation'=>'VarietalDesignation',
                'AlcoholType'=>'AlcoholType',
                'BarrelAgingTime'=>array(
                    '@unitOfMeasure'=>'months',
                    '%'=>'1'
                ),
                'ItemTypeName'=>'ItemTypeName',
                'SaltPerServing'=>array(
                    '@unitOfMeasure' => 'KG',
                    '%' => '1'
                ),
                'PrimaryIngredientCountryOfOrigin'=>'PrimaryIngredientCountryOfOrigin',
                'PrimaryIngredientLocationProduced'=>'PrimaryIngredientLocationProduced',
                'SolidNetWeight'=>array(
                    '@unitOfMeasure' => 'KG',
                    '%' => '1'
                ),
                'MaterialFeatures'=>'MaterialFeatures',
                'ManufacturerContactInformation'=>'ManufacturerContactInformation',
            ),
        );
    }

    protected function _HardLiquor($relative = '', $theme = '', $variations='')
    {
        return array(
            'HardLiquor' => array(
                'VariationData' => array(
                    'Parentage' => $relative,
                    'VariationTheme' => $theme,
                    'Size'=>$variations['Size'],
                    'Flavor'=>$variations['Color'],
                    'PatternName'=>'PatternName',
                ),
                'CountryProducedIn'=>'CountryProducedIn',
                'CountryOfOrigin'=>'CountryOfOrigin',
                'RegionOfOrigin'=>'RegionOfOrigin',
                'Prefecture'=>'Prefecture',
                'ItemForm'=>'ItemForm',
                'Ingredients'=>'Ingredients',
                'NutritionalFacts'=>'NutritionalFacts',
                'KosherCertification'=>'KosherCertification',
                'OrganicCertification'=>'OrganicCertification',
                'TokuhoCertification'=>'TokuhoCertification',
                'ItemSpecialty'=>'ItemSpecialty',
                'VarietalComposition'=>'VarietalComposition',
                'Vintage'=>'nv',
                'BodyDescription'=>'BodyDescription',
                'TasteDescription'=>'TasteDescription',
                'AlcoholContent'=>array(
                    '@unitOfMeasure'=>'percent_by_weight',
                    '%'=>'1'
                ),
                'CaffeineContent'=>'CaffeineContent',
                'Warnings'=>'Warnings',
                'IsPerishable'=>true,
                'StorageInstructions'=>'StorageInstructions',
                'Directions'=>'Directions',
                'RecommendedServingInstructions'=>'RecommendedServingInstructions',
                'Occasion'=>'Occasion',
                'AwardsWon'=>'AwardsWon',
                'AgeRangeDescription'=>'AgeRangeDescription',
                'ItemPackageQuantity'=>'1',
                'NumberOfItems'=>'1',
                'DisplayLength'=>array(
                    '@unitOfMeasure' => 'CM',
                    '%' => '1',
                ),
                'DisplayWeight'=> array(
                    '@unitOfMeasure' => 'KG',
                    '%' => '1',
                ),
                'DisplayVolume'=> array(
                    '@unitOfMeasure' => 'pint',
                    '%' => '1',
                ),
                'ContainerType'=>'ContainerType',
                'UnitCount'=>array(
                    '@unitOfMeasure'=>'UnitCountType',
                    '%'=>'1'
                ),
                'NutritionalFactsGroup'=>array(
                    'AllergenInformation'=>'fish',
                    'SpecialIngredients'=>'SpecialIngredients',
                ),
                'Cuisine'=>'Cuisine',
                'UseByRecommendation'=>'UseByRecommendation',
                'VarietalDesignation'=>'VarietalDesignation',
                'IsExpirationDatedProduct'=>true,
                'ContainerMaterialType'=>'ContainerMaterialType',
            ),
        );
    }

    protected function _AlcoholicBeverages($relative = '', $theme = '', $variations='')
    {
        return array(
            'AlcoholicBeverages' => array(
                'VariationData' => array(
                    'Parentage' => $relative,
                    'VariationTheme' => $theme,
                    'Size'=>$variations['Size'],
                    'Flavor'=>$variations['Color'],
                    'PatternName'=>'PatternName',
                ),
                'CountryProducedIn'=>'CountryProducedIn',
                'CountryOfOrigin'=>'CountryOfOrigin',
                'RegionOfOrigin'=>'RegionOfOrigin',
                'Prefecture'=>'Prefecture',
                'ItemForm'=>'ItemForm',
                'Ingredients'=>'Ingredients',
                'NutritionalFacts'=>'NutritionalFacts',
                'KosherCertification'=>'KosherCertification',
                'OrganicCertification'=>'OrganicCertification',
                'ItemSpecialty'=>'ItemSpecialty',
                'VarietalComposition'=>'VarietalComposition',
                'Vintage'=>'nv',
                'BodyDescription'=>'BodyDescription',
                'TasteDescription'=>'TasteDescription',
                'AlcoholContent'=>array(
                    '@unitOfMeasure'=>'percent_by_weight',
                    '%'=>'1'
                ),
                'CaffeineContent'=>'CaffeineContent',
                'Warnings'=>'Warnings',
                'IsPerishable'=>true,
                'StorageInstructions'=>'StorageInstructions',
                'Directions'=>'Directions',
                'RecommendedServingInstructions'=>'RecommendedServingInstructions',
                'Occasion'=>'Occasion',
                'AwardsWon'=>'AwardsWon',
                'AgeRangeDescription'=>'AgeRangeDescription',
                'ItemPackageQuantity'=>'1',
                'NumberOfItems'=>'1',
                'DisplayLength'=>array(
                    '@unitOfMeasure' => 'CM',
                    '%' => '1',
                ),
                'DisplayWeight'=> array(
                    '@unitOfMeasure' => 'KG',
                    '%' => '1',
                ),
                'DisplayVolume'=> array(
                    '@unitOfMeasure' => 'pint',
                    '%' => '1',
                ),
                'ContainerType'=>'ContainerType',
                'UnitCount'=>array(
                    '@unitOfMeasure'=>'UnitCountType',
                    '%'=>'1'
                ),
                'NutritionalFactsGroup'=>array(
                    'AllergenInformation'=>'fish',
                    'SpecialIngredients'=>'SpecialIngredients',
                ),
                'Cuisine'=>'Cuisine',
                'UseByRecommendation'=>'UseByRecommendation',
                'IsExpirationDatedProduct'=>true,
                'ContainerMaterialType'=>'ContainerMaterialType',
            ),
        );
    }

    protected function _Wine($relative = '', $theme = '', $variations='')
    {
        return array(
            'Wine' => array(
                'VariationData' => array(
                    'Parentage' => $relative,
                    'VariationTheme' => $theme,
                    'Size'=>$variations['Size'],
                    'Flavor'=>$variations['Color'],
                    'PatternName'=>'PatternName',
                    'StyleName'=>'StyleName',
                ),
                'CountryProducedIn'=>'CountryProducedIn',
                'CountryOfOrigin'=>'CountryOfOrigin',
                'RegionOfOrigin'=>'RegionOfOrigin',
                'Prefecture'=>'Prefecture',
                'ItemForm'=>'ItemForm',
                'Ingredients'=>'Ingredients',
                'ContainsFoodOrBeverage'=>true,
                'MedicineClassification'=>'MedicineClassification',
                'NutritionalFacts'=>'NutritionalFacts',
                'KosherCertification'=>'KosherCertification',
                'OrganicCertification'=>'OrganicCertification',
                'TokuhoCertification'=>'TokuhoCertification',
                'ItemSpecialty'=>'ItemSpecialty',
                'VarietalComposition'=>'VarietalComposition',
                'Vintage'=>'nv',
                'BodyDescription'=>'BodyDescription',
                'TasteDescription'=>'TasteDescription',
                'AlcoholContent'=>array(
                    '@unitOfMeasure'=>'percent_by_weight',
                    '%'=>'1'
                ),
                'CaffeineContent'=>'CaffeineContent',
                'Warnings'=>'Warnings',
                'IsPerishable'=>true,
                'StorageInstructions'=>'StorageInstructions',
                'Directions'=>'Directions',
                'RecommendedServingInstructions'=>'RecommendedServingInstructions',
                'Occasion'=>'Occasion',
                'OccasionType'=>'OccasionType',
                'AwardsWon'=>'AwardsWon',
                'AgeRangeDescription'=>'AgeRangeDescription',
                'ItemPackageQuantity'=>'1',
                'NumberOfItems'=>'1',
                'DisplayLength'=>array(
                    '@unitOfMeasure' => 'CM',
                    '%' => '1',
                ),
                'DisplayWeight'=> array(
                    '@unitOfMeasure' => 'KG',
                    '%' => '1',
                ),
                'DisplayVolume'=> array(
                    '@unitOfMeasure' => 'pint',
                    '%' => '1',
                ),
                'ContainerType'=>'ContainerType',
                'UnitCount'=>array(
                    '@unitOfMeasure'=>'UnitCountType',
                    '%'=>'1'
                ),
                'NutritionalFactsGroup'=>array(
                    'AllergenInformation'=>'fish',
                    'SpecialIngredients'=>'SpecialIngredients',
                ),
                'Cuisine'=>'Cuisine',
                'UseByRecommendation'=>'UseByRecommendation',
                'VarietalDesignation'=>'VarietalDesignation',
                'IsExpirationDatedProduct'=>true,
                'ContainerMaterialType'=>'ContainerMaterialType',
                'AlcoholType'=>'AlcoholType',
                'BarrelAgingTime'=>array(
                    '@unitOfMeasure'=>'months',
                    '%'=>'1'
                ),
                'PackageContentType'=>'PackageContentType',
                'ItemTypeName'=>'ItemTypeName',
                'SaltPerServing'=>array(
                    '@unitOfMeasure' => 'KG',
                    '%' => '1'
                ),
                'PrimaryIngredientCountryOfOrigin'=>'PrimaryIngredientCountryOfOrigin',
                'PrimaryIngredientLocationProduced'=>'PrimaryIngredientLocationProduced',
                'SolidNetWeight'=>array(
                    '@unitOfMeasure' => 'KG',
                    '%' => '1'
                ),
                'ManufacturerContactInformation'=>'ManufacturerContactInformation',
                'LiquidPackagingSeal'=>'LiquidPackagingSeal',
                'Designation'=>'Designation',
                'JamesHallidayRating'=>'JamesHallidayRating',
                'JamesSucklingRating'=>'JamesSucklingRating',
                'SweetnessDescription'=>'SweetnessDescription',
                'WineSpiritsRating'=>'WineSpiritsRating',
            ),
        );
    }

    protected function _Beer($relative = '', $theme = '', $variations='')
    {
        return array(
            'Beer' => array(
                'VariationData' => array(
                    'Parentage' => $relative,
                    'VariationTheme' => $theme,
                    'Size'=>$variations['Size'],
                    'Flavor'=>$variations['Color'],
                    'StyleName'=>'StyleName',
                ),
                'Ingredients'=>'Ingredients',
                'ContainsFoodOrBeverage'=>true,
                'MedicineClassification'=>'MedicineClassification',
                'Directions'=>'Directions',
                'Prefecture'=>'Prefecture',
                'StorageInstructions'=>'StorageInstructions',
                'ItemSpecialty'=>'ItemSpecialty',
                'KosherCertification'=>'KosherCertification',
                'Occasion'=>'Occasion',
                'OccasionType'=>'OccasionType',
                'ItemForm'=>'ItemForm',
                'CaffeineContent'=>'CaffeineContent',
                'ContainerType'=>'ContainerType',
                'AgeRangeDescription'=>'AgeRangeDescription',
                'IsPerishable'=>true,
                'BodyDescription'=>'BodyDescription',
                'TasteDescription'=>'TasteDescription',
                'AwardsWon'=>'AwardsWon',
                'RecommendedServingInstructions'=>'RecommendedServingInstructions',
                'Cuisine'=>'Cuisine',
                'UseByRecommendation'=>'UseByRecommendation',
                'NutritionalFactsGroup'=>array(
                    'AllergenInformation'=>'fish',
                    'Calcium'=>array(
                        '@unitOfMeasure'=>'mg',
                        '%'=>'1'
                    ),
                    'Cholesterol'=>array(
                        '@unitOfMeasure'=>'kg',
                        '%'=>'1'
                    ),
                    'DietaryFiber'=>array(
                        '@unitOfMeasure'=>'ml',
                        '%'=>'1'
                    ),
                    'EnergyContent'=>array(
                        '@unitOfMeasure'=>'watt_hours',
                        '%'=>'1'
                    ),
                    'EnergyContentFromFat'=>array(
                        '@unitOfMeasure'=>'watt_hours',
                        '%'=>'1'
                    ),
                    'InsolubleFiber'=>array(
                        '@unitOfMeasure'=>'ml',
                        '%'=>'1'
                    ),
                    'Iron'=>array(
                        '@unitOfMeasure'=>'ml',
                        '%'=>'1'
                    ),
                    'MonounsaturatedFat'=>array(
                        '@unitOfMeasure'=>'ml',
                        '%'=>'1'
                    ),
                    'OtherCarbohydrate'=>array(
                        '@unitOfMeasure'=>'ml',
                        '%'=>'1'
                    ),
                    'phosphorus'=>array(
                        '@unitOfMeasure'=>'ml',
                        '%'=>'1'
                    ),
                    'polyunsaturateDfat'=>array(
                        '@unitOfMeasure'=>'ml',
                        '%'=>'1'
                    ),
                    'potassium'=>array(
                        '@unitOfMeasure'=>'ml',
                        '%'=>'1'
                    ),
                    'protein'=>array(
                        '@unitOfMeasure'=>'ml',
                        '%'=>'1'
                    ),
                    'saturateDfat'=>array(
                        '@unitOfMeasure'=>'ml',
                        '%'=>'1'
                    ),
                    'servinGsize'=>array(
                        '@unitOfMeasure'=>'ml',
                        '%'=>'1'
                    ),
                    'servinGsizEdescription'=>'servinGsizEdescription',
                    'sodium'=>array(
                        '@unitOfMeasure'=>'ml',
                        '%'=>'1'
                    ),
                    'solublEfiber'=>array(
                        '@unitOfMeasure'=>'ml',
                        '%'=>'1'
                    ),
                    'speciaLingredients'=>'speciaLingredients',
                    'sugaRalcohol'=>array(
                        '@unitOfMeasure'=>'ml',
                        '%'=>'1'
                    ),
                    'sugars'=>array(
                        '@unitOfMeasure'=>'ml',
                        '%'=>'1'
                    ),
                    'temperaturErating'=>'temperaturErating',
                    'thiamin'=>array(
                        '@unitOfMeasure'=>'ml',
                        '%'=>'1'
                    ),
                    'totaLcarbohydrate'=>array(
                        '@unitOfMeasure'=>'ml',
                        '%'=>'1'
                    ),
                    'totaLfat'=>array(
                        '@unitOfMeasure'=>'ml',
                        '%'=>'1'
                    ),
                    'tranSfat'=>array(
                        '@unitOfMeasure'=>'ml',
                        '%'=>'1'
                    ),
                    'vitamiNA'=>array(
                        '@unitOfMeasure'=>'ml',
                        '%'=>'1'
                    ),
                    'vitamiNC'=>array(
                        '@unitOfMeasure'=>'ml',
                        '%'=>'1'
                    ),
                    'CalciumString'=>'String',
                    'EnergyString'=>'String',
                    'TotalFatString'=>'String',
                    'SaturatedFatString'=>'String',
                    'MonounsaturatedFatString'=>'String',
                    'PolyunsaturatedFatString'=>'String',
                    'TotalCarbohydrateString'=>'String',
                    'SugarsString'=>'String',
                    'SugarAlcoholString'=>'String',
                    'Starch'=>'String',
                    'DietaryFiberString'=>'String',
                    'ProteinString'=>'String',
                    'VitaminAString'=>'String',
                    'VitaminCString'=>'String',
                    'VitaminDString'=>'String',
                    'VitaminEString'=>'String',
                    'VitaminKString'=>'String',
                    'ThiaminString'=>'String',
                    'VitaminB2'=>'String',
                    'Niacin'=>'String',
                    'VitaminB6'=>'String',
                    'FolicAcid'=>'String',
                    'VitaminB12'=>'String',
                    'Biotin'=>'String',
                    'PantothenicAcid'=>'String',
                    'PotassiumString'=>'String',
                    'Chloride'=>'String',
                    'PhosphorusString'=>'String',
                    'Magnesium'=>'String',
                    'IronString'=>'String',
                    'Zinc'=>'String',
                    'Copper'=>'String',
                    'Manganese'=>'String',
                    'Fluoride'=>'String',
                    'Selenium'=>'String',
                    'Chromium'=>'String',
                    'Molybdenum'=>'String',
                    'Iodine'=>'String',
                    'CholesterolString'=>'String',
                    'SodiumString'=>'String',
                ),
                'UnitCount'=>array(
                    '@unitOfMeasure'=>'UnitCountType',
                    '%'=>'1'
                ),
                'ContainerMaterialType'=>'ContainerMaterialType',
                'Vintage'=>'nv',
                'AlcoholContent'=>array(
                    '@unitOfMeasure'=>'percent_by_weight',
                    '%'=>'1'
                ),
                'VarietalComposition'=>'VarietalComposition',
                'VarietalDesignation'=>'VarietalDesignation',
                'AlcoholType'=>'AlcoholType',
                'BarrelAgingTime'=>array(
                    '@unitOfMeasure'=>'months',
                    '%'=>'1'
                ),
                'Warnings'=>'Warnings',
                'CountryProducedIn'=>'CountryProducedIn',
                'RegionOfOrigin'=>'RegionOfOrigin',
                'DisplayLength'=>array(
                    '@unitOfMeasure' => 'CM',
                    '%' => '1',
                ),
                'DisplayWeight'=> array(
                    '@unitOfMeasure' => 'KG',
                    '%' => '1',
                ),
                'DisplayVolume'=> array(
                    '@unitOfMeasure' => 'pint',
                    '%' => '1',
                ),
                'ItemTypeName'=>'ItemTypeName',
                'SaltPerServing'=>array(
                    '@unitOfMeasure' => 'KG',
                    '%' => '1'
                ),
                'PrimaryIngredientCountryOfOrigin'=>'PrimaryIngredientCountryOfOrigin',
                'PrimaryIngredientLocationProduced'=>'PrimaryIngredientLocationProduced',
                'SolidNetWeight'=>array(
                    '@unitOfMeasure' => 'KG',
                    '%' => '1'
                ),
                'ManufacturerContactInformation'=>'ManufacturerContactInformation',
                'LiquidPackagingSeal'=>'LiquidPackagingSeal',
                'Designation'=>'Designation',
                'JamesHallidayRating'=>'JamesHallidayRating',
                'JamesSucklingRating'=>'JamesSucklingRating',
                'SweetnessDescription'=>'SweetnessDescription',
                'WineSpiritsRating'=>'WineSpiritsRating',
            ),
        );
    }

    protected function _Spirits($relative = '', $theme = '', $variations='')
    {
        return array(
            'Spirits' => array(
                'VariationData' => array(
                    'Parentage' => $relative,
                    'VariationTheme' => $theme,
                    'Size'=>$variations['Size'],
                    'Flavor'=>$variations['Color'],
                    'StyleName'=>'StyleName',
                ),
                'Ingredients'=>'Ingredients',
                'Directions'=>'Directions',
                'Prefecture'=>'Prefecture',
                'StorageInstructions'=>'StorageInstructions',
                'ItemSpecialty'=>'ItemSpecialty',
                'KosherCertification'=>'KosherCertification',
                'Occasion'=>'Occasion',
                'OccasionType'=>'OccasionType',
                'ItemForm'=>'ItemForm',
                'CaffeineContent'=>'CaffeineContent',
                'ContainsFoodOrBeverage'=>true,
                'MedicineClassification'=>'MedicineClassification',
                'ContainerType'=>'ContainerType',
                'AgeRangeDescription'=>'AgeRangeDescription',
                'IsPerishable'=>true,
                'BodyDescription'=>'BodyDescription',
                'TasteDescription'=>'TasteDescription',
                'AwardsWon'=>'AwardsWon',
                'RecommendedServingInstructions'=>'RecommendedServingInstructions',
                'Cuisine'=>'Cuisine',
                'UseByRecommendation'=>'UseByRecommendation',
                'NutritionalFactsGroup'=>array(
                    'AllergenInformation'=>'fish',
                    'Calcium'=>array(
                        '@unitOfMeasure'=>'mg',
                        '%'=>'1'
                    ),
                    'Cholesterol'=>array(
                        '@unitOfMeasure'=>'kg',
                        '%'=>'1'
                    ),
                    'DietaryFiber'=>array(
                        '@unitOfMeasure'=>'ml',
                        '%'=>'1'
                    ),
                    'EnergyContent'=>array(
                        '@unitOfMeasure'=>'watt_hours',
                        '%'=>'1'
                    ),
                    'EnergyContentFromFat'=>array(
                        '@unitOfMeasure'=>'watt_hours',
                        '%'=>'1'
                    ),
                    'InsolubleFiber'=>array(
                        '@unitOfMeasure'=>'ml',
                        '%'=>'1'
                    ),
                    'Iron'=>array(
                        '@unitOfMeasure'=>'ml',
                        '%'=>'1'
                    ),
                    'MonounsaturatedFat'=>array(
                        '@unitOfMeasure'=>'ml',
                        '%'=>'1'
                    ),
                    'OtherCarbohydrate'=>array(
                        '@unitOfMeasure'=>'ml',
                        '%'=>'1'
                    ),
                    'phosphorus'=>array(
                        '@unitOfMeasure'=>'ml',
                        '%'=>'1'
                    ),
                    'polyunsaturateDfat'=>array(
                        '@unitOfMeasure'=>'ml',
                        '%'=>'1'
                    ),
                    'potassium'=>array(
                        '@unitOfMeasure'=>'ml',
                        '%'=>'1'
                    ),
                    'protein'=>array(
                        '@unitOfMeasure'=>'ml',
                        '%'=>'1'
                    ),
                    'saturateDfat'=>array(
                        '@unitOfMeasure'=>'ml',
                        '%'=>'1'
                    ),
                    'servinGsize'=>array(
                        '@unitOfMeasure'=>'ml',
                        '%'=>'1'
                    ),
                    'servinGsizEdescription'=>'servinGsizEdescription',
                    'sodium'=>array(
                        '@unitOfMeasure'=>'ml',
                        '%'=>'1'
                    ),
                    'solublEfiber'=>array(
                        '@unitOfMeasure'=>'ml',
                        '%'=>'1'
                    ),
                    'speciaLingredients'=>'speciaLingredients',
                    'sugaRalcohol'=>array(
                        '@unitOfMeasure'=>'ml',
                        '%'=>'1'
                    ),
                    'sugars'=>array(
                        '@unitOfMeasure'=>'ml',
                        '%'=>'1'
                    ),
                    'temperaturErating'=>'temperaturErating',
                    'thiamin'=>array(
                        '@unitOfMeasure'=>'ml',
                        '%'=>'1'
                    ),
                    'totaLcarbohydrate'=>array(
                        '@unitOfMeasure'=>'ml',
                        '%'=>'1'
                    ),
                    'totaLfat'=>array(
                        '@unitOfMeasure'=>'ml',
                        '%'=>'1'
                    ),
                    'tranSfat'=>array(
                        '@unitOfMeasure'=>'ml',
                        '%'=>'1'
                    ),
                    'vitamiNA'=>array(
                        '@unitOfMeasure'=>'ml',
                        '%'=>'1'
                    ),
                    'vitamiNC'=>array(
                        '@unitOfMeasure'=>'ml',
                        '%'=>'1'
                    ),
                    'CalciumString'=>'String',
                    'EnergyString'=>'String',
                    'TotalFatString'=>'String',
                    'SaturatedFatString'=>'String',
                    'MonounsaturatedFatString'=>'String',
                    'PolyunsaturatedFatString'=>'String',
                    'TotalCarbohydrateString'=>'String',
                    'SugarsString'=>'String',
                    'SugarAlcoholString'=>'String',
                    'Starch'=>'String',
                    'DietaryFiberString'=>'String',
                    'ProteinString'=>'String',
                    'VitaminAString'=>'String',
                    'VitaminCString'=>'String',
                    'VitaminDString'=>'String',
                    'VitaminEString'=>'String',
                    'VitaminKString'=>'String',
                    'ThiaminString'=>'String',
                    'VitaminB2'=>'String',
                    'Niacin'=>'String',
                    'VitaminB6'=>'String',
                    'FolicAcid'=>'String',
                    'VitaminB12'=>'String',
                    'Biotin'=>'String',
                    'PantothenicAcid'=>'String',
                    'PotassiumString'=>'String',
                    'Chloride'=>'String',
                    'PhosphorusString'=>'String',
                    'Magnesium'=>'String',
                    'IronString'=>'String',
                    'Zinc'=>'String',
                    'Copper'=>'String',
                    'Manganese'=>'String',
                    'Fluoride'=>'String',
                    'Selenium'=>'String',
                    'Chromium'=>'String',
                    'Molybdenum'=>'String',
                    'Iodine'=>'String',
                    'CholesterolString'=>'String',
                    'SodiumString'=>'String',
                ),
                'UnitCount'=>array(
                    '@unitOfMeasure'=>'UnitCountType',
                    '%'=>'1'
                ),
                'ContainerMaterialType'=>'ContainerMaterialType',
                'Vintage'=>'nv',
                'AlcoholContent'=>array(
                    '@unitOfMeasure'=>'percent_by_weight',
                    '%'=>'1'
                ),
                'VarietalComposition'=>'VarietalComposition',
                'VarietalDesignation'=>'VarietalDesignation',
                'AlcoholType'=>'AlcoholType',
                'BarrelAgingTime'=>array(
                    '@unitOfMeasure'=>'months',
                    '%'=>'1'
                ),
                'Warnings'=>'Warnings',
                'CountryProducedIn'=>'CountryProducedIn',
                'RegionOfOrigin'=>'RegionOfOrigin',
                'DisplayLength'=>array(
                    '@unitOfMeasure' => 'CM',
                    '%' => '1',
                ),
                'DisplayWeight'=> array(
                    '@unitOfMeasure' => 'KG',
                    '%' => '1',
                ),
                'DisplayVolume'=> array(
                    '@unitOfMeasure' => 'pint',
                    '%' => '1',
                ),
                'ItemTypeName'=>'ItemTypeName',
                'SaltPerServing'=>array(
                    '@unitOfMeasure' => 'KG',
                    '%' => '1'
                ),
                'PrimaryIngredientCountryOfOrigin'=>'PrimaryIngredientCountryOfOrigin',
                'PrimaryIngredientLocationProduced'=>'PrimaryIngredientLocationProduced',
                'SolidNetWeight'=>array(
                    '@unitOfMeasure' => 'KG',
                    '%' => '1'
                ),
                'ManufacturerContactInformation'=>'ManufacturerContactInformation',
                'LiquidPackagingSeal'=>'LiquidPackagingSeal',
                'Designation'=>'Designation',
                'JamesHallidayRating'=>'JamesHallidayRating',
                'JamesSucklingRating'=>'JamesSucklingRating',
                'SweetnessDescription'=>'SweetnessDescription',
                'WineSpiritsRating'=>'WineSpiritsRating',
            ),
        );
    }



}
