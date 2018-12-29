<?php 

/**
 * The default template
 *
 * @author mrlina <714480119@qq.com>
 */
Class AmazonHomeImprovement extends AmazonUpload implements IAmazonUpload
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

        foreach ($data['ProductData'][$pcate]['ProductType'][$chcate] as $key => $value) {
            if ($this->isEmpty($value)) {
                unset($data['ProductData'][$pcate]['ProductType'][$chcate][$key]);
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

            $array = $this->_Catergorys($array);
            $data['ProductData'] = $array;

            //$xmls[$this->product->sku] = $this->arr2xml->buildXML($this->removeEmptyItem($data), 'Product');
            $xmls[$this->product->sku] = $this->arr2xml->buildXML($this->trimTier($data,'HomeImprovement',$xsd2->category), 'Product');

        } else if ($this->product->product_is_multi == 2) {
            $array = $this->$method('parent', $this->product->variation_theme);
            $this->removeEmptyItemByKey($array, $xsd2->category);

            $array = $this->_Catergorys($array);
            $data['ProductData'] = $array;

            //$xmls[$this->product->sku] = $this->arr2xml->buildXML($this->removeEmptyItem($data), 'Product');
            $xmls[$this->product->sku] = $this->arr2xml->buildXML($this->trimTier($data,'HomeImprovement',$xsd2->category), 'Product');


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

                $array = $this->$method('child', $this->product->variation_theme, $variations);
                foreach ($variations as $name => $val) {
                    if (isset($array[$xsd2->category][$name])) {
                        $array[$xsd2->category][$name] = $val;
                    }
                }

                $this->removeEmptyItemByKey($array, $xsd2->category);
                $array = $this->_Catergorys($array);
                $child['ProductData'] = $array;
                //$xmls[$sonprd->sku] = $this->arr2xml->buildXML($this->removeEmptyItem($child), 'Product');
                $xmls[$sonprd->sku] = $this->arr2xml->buildXML($this->trimTier($child,'HomeImprovement',$xsd2->category), 'Product');

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
            'HomeImprovement' =>array(
                'ProductType' => $category,
                'HICommon'=>array(
                    'Finish'=>'Finish',
                    'IncludedComponent'=>'inc',
                    'InstallationMethod'=>'InstallationMethod',
                ),
            ),
        );
    }

    protected function _BuildingMaterials($relative = '', $theme = '', $variations='')
    {
        return array(
            'BuildingMaterials' => array(
                'VariationData' => array(
                    'Parentage' => $relative,
                    'VariationTheme' => $theme,
                ),
                'Size'=>$variations['Size'],
                'Color'=>$variations['Color'],
                'Material'=>'Material',
                'PowerSource'=>'PowerSource',
                'Voltage'=>'1',
            ),
        );
    }

    protected function _Hardware($relative = '', $theme = '', $variations='')
    {
        return array(
            'Hardware' => array(
                'VariationData' => array(
                    'Parentage' => $relative,
                    'VariationTheme' => $theme,
                ),
                'Size'=>$variations['Size'],
                'Color'=>$variations['Color'],
                'Material'=>'Material',
                'PowerSource'=>'PowerSource',
                'Voltage'=>'1',
            ),
        );
    }

    protected function _Electrical($relative = '', $theme = '', $variations='')
    {
        return array(
            'Electrical' => array(
                'VariationData' => array(
                    'Parentage' => $relative,
                    'VariationTheme' => $theme,
                ),
                'Size'=>$variations['Size'],
                'Color'=>$variations['Color'],
                'Material'=>'Material',
                'PowerSource'=>'PowerSource',
                'Voltage'=>'1',
            ),
        );
    }

    protected function _PlumbingFixtures($relative = '', $theme = '', $variations='')
    {
        return array(
            'PlumbingFixtures' => array(
                'VariationData' => array(
                    'Parentage' => $relative,
                    'VariationTheme' => $theme,
                ),
                'Size'=>$variations['Size'],
                'Color'=>$variations['Color'],
                'Material'=>'Material',
                'PowerSource'=>'PowerSource',
                'Voltage'=>'1',
            ),
        );
    }

    protected function _Tools($relative = '', $theme = '', $variations='')
    {
        return array(
            'Tools' => array(
                'VariationData' => array(
                    'Parentage' => $relative,
                    'VariationTheme' => $theme,
                ),
                'AccessoryConnectionType'=>'',
                'BladeEdge'=>'',
                'BladeLength'=> array(
                    '@unitOfMeasure' => 'CM',
                    '%' => '',
                ),
                'Brightness'=> array(
                    '@unitOfMeasure' => 'Lumens',
                    '%' => '',
                ),
                'BulbType'=>'',
                'CenterLength'=> array(
                    '@unitOfMeasure' => 'CM',
                    '%' => '',
                ),
                'CompatibleDevices'=>'',
                'Coverage'=>'',
                'CompatibleFastenerRange'=>'',
                'CoolingMethod'=>'',
                'CoolingWattage'=> array(
                    '@unitOfMeasure' => 'watts',
                    '%' => '',
                ),
                'CornerRadius'=> array(
                    '@unitOfMeasure' => 'CM',
                    '%' => '',
                ),
                'CuttingDiameter'=> array(
                    '@unitOfMeasure' => 'CM',
                    '%' => '',
                ),
                'CutType'=>'',
                'CuttingWidth'=> array(
                    '@unitOfMeasure' => 'CM',
                    '%' => '',
                ),
                'DeviceType'=>'',
                'DisplayStyle'=>'',
                'EnergyConsumption'=>'',
                'EnergyEfficiencyRatioCooling'=> '',
                'EnvironmentalDescription'=>'',
                'EuEnergyEfficiencyClassHeating'=>'',
                'EuEnergyLabelEfficiencyClass'=>'',
                'ExternalTestingCertification'=>'',
                'FlushType'=>'',
                'HeadStyle'=>'',
                'HeaterWattage'=> array(
                    '@unitOfMeasure' => 'watts',
                    '%' => '',
                ),
                'InsideDiameter'=> array(
                    '@unitOfMeasure' => 'CM',
                    '%' => '',
                ),
                'NumberOfBasins'=>'',
                'NumberOfHoles'=>'',
                'OutsideDiameter'=> array(
                    '@unitOfMeasure' => 'CM',
                    '%' => '',
                ),
                'PlugFormat'=>'',
                'PlugProfile'=>'',
                'RecycledContentPercentage'=>'',
                'RoughIn'=> array(
                    '@unitOfMeasure' => 'CM',
                    '%' => '',
                ),
                'SpoutHeight'=> array(
                    '@unitOfMeasure' => 'CM',
                    '%' => '1',
                ),
                'SpoutReach'=> array(
                    '@unitOfMeasure' => 'CM',
                    '%' => '',
                ),
                'SwitchStyle'=>'',
                'SwitchType'=>'',
                'ThreadSize'=> array(
                    '@unitOfMeasure' => 'CM',
                    '%' => '',
                ),
                'Size'=>$variations['Size'],
                'Color'=>$variations['Color'],
                // 'ColorMap'=>'ColorMap',
                'CustomerPackageType'=>'',
                'DisplayDiameter'=> array(
                    '@unitOfMeasure' => 'CM',
                    '%' => '',
                ),
                'DisplayVolume'=> array(
                    '@unitOfMeasure' => 'pint',
                    '%' => '',
                ),
                'DisplayWeight'=> array(
                    '@unitOfMeasure' => 'KG',
                    '%' => '',
                ),
                'DisplayLength'=> array(
                    '@unitOfMeasure' => 'CM',
                    '%' => '',
                ),
                'DisplayWidth'=> array(
                    '@unitOfMeasure' => 'CM',
                    '%' => '',
                ),
                'DisplayHeight'=> array(
                    '@unitOfMeasure' => 'CM',
                    '%' => '',
                ),
                'FoldedKnifeSize'=>'',
                'HandleMaterial'=>'',
                'PowerSource'=>'unknow',
                'Horsepower'=> array(
                    '@unitOfMeasure' => 'watts',
                    '%' => '',
                ),
                'LaserBeamColor'=>'',
                'Material'=>'',
                'MaximumPower'=> array(
                    '@unitOfMeasure' => 'W',
                    '%' => '',
                ),
                'MeasurementAccuracy'=>'',
                'MeasurementSystem'=>'',
                'Wattage'=> array(
                    '@unitOfMeasure' => 'watts',
                    '%' => '',
                ),
                'Voltage'=>'',
                'BatteryCapacity'=> array(
                    '@unitOfMeasure' => 'watt_hours',
                    '%' => '',
                ),
                'GritRating'=>'',
                'NumberOfItems'=>'',
                'MinimumAge'=> array(
                    '@unitOfMeasure' => 'months',
                    '%' => '',
                ),
                'ManufacturerWarrantyDescription'=>'',
                'PerformanceDescription'=>'',
                'Speed'=> array(
                    '@unitOfMeasure' => 'miles_per_hour',
                    '%' => '',
                ),
                'SellerWarrantyDescription'=>'',
                'ToolTipDescription'=>'',
                'Torque'=> array(
                    '@unitOfMeasure' => 'foot_pounds',
                    '%' => '',
                ),
                'UVProtection'=>'',
                'ViewingArea'=>'',
                'MinimumEfficiencyReportingValue'=>'',
                'BaseDiameter'=> array(
                    '@unitOfMeasure' => 'CM',
                    '%' => '',
                ),
                'BeamAngle'=> array(
                    '@unitOfMeasure' => 'arc_minute',
                    '%' => '',
                ),
                'BladeColor'=>'',
                'CircuitBreakerType'=>'',
                'Efficiency'=>'',
                'InternationalProtectionRating'=>'',
                'LightSourceOperatingLife'=> array(
                    '@unitOfMeasure' => 'hours',
                    '%' => '',
                ),
                'LightingMethod'=>'',
                'MaximumCompatibleLightSourceWattage'=> array(
                    '@unitOfMeasure' => 'watts',
                    '%' => '',
                ),
                'NumberOfBlades'=>'',
                'NumberOfLightSources'=>'',
                'ShadeDiameter'=> array(
                    '@unitOfMeasure' => 'CM',
                    '%' => '',
                ),
                'ShadeMaterialType'=>'',
                'ShortProductDescription'=>'',
                'StartUpTimeDescription'=>'',
                'Strands'=>'',
                'TubingOutsideDiameter'=> array(
                    '@unitOfMeasure' => 'CM',
                    '%' => '',
                ),
                'CustomerRestrictionType'=>'',
            ),
        );
    }
    protected function _OrganizersAndStorage($relative = '', $theme = '', $variations='')
    {
        return array(
            'OrganizersAndStorage' => array(
                'VariationData' => array(
                    'Parentage' => $relative,
                    'VariationTheme' => $theme,
                ),
                'Size'=>$variations['Size'],
                'Color'=>$variations['Color'],
                'Material'=>'Material',
                'PowerSource'=>'PowerSource',
                'Voltage'=>'1',
            ),
        );
    }

    protected function _MajorHomeAppliances($relative = '', $theme = '', $variations='')
    {
        return array(
            'MajorHomeAppliances' => array(
                'VariationData' => array(
                    'Parentage' => $relative,
                    'VariationTheme' => $theme,
                ),
                'Size'=>$variations['Size'],
                'Color'=>$variations['Color'],
                // 'ColorMap'=>'ColorMap',
                'Material'=>'Material',
                'MinimumEfficiencyReportingValue'=>'1',
                'PowerSource'=>'PowerSource',
                'Voltage'=>'1',
                'Wattage'=> '2',
                'BatteryCapacity'=> array(
                    '@unitOfMeasure' => 'watt_hours',
                    '%' => '2',
                ),
                'ManufacturerWarrantyDescription'=>'ManufacturerWarrantyDescription',
                'SellerWarrantyDescription'=>'SellerWarrantyDescription',
                'CustomerPackageType'=>'CustomerPackageType',
            ),
        );
    }

    protected function _SecurityElectronics($relative = '', $theme = '', $variations='')
    {
        return array(
            'SecurityElectronics' => array(
                'VariationData' => array(
                    'Parentage' => $relative,
                    'VariationTheme' => $theme,
                ),
                'Size'=>$variations['Size'],
                'Color'=>$variations['Color'],
                'Material'=>'Material',
                'PowerSource'=>'PowerSource',
                'Voltage'=>'1',
            ),
        );
    }

}
