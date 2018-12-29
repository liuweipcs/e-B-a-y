<?php

/**
 * The default template
 *
 * @author mrlina <714480119@qq.com>
 */
Class AmazonMechanicalFasteners extends AmazonUpload implements IAmazonUpload
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

        $method ='_'.$xsd2->category;

        //单品
       // $arrt=unserialize($this->product->productdata->product_data);

        if ($this->product->product_is_multi == 0) {
            //仅指定分类信息
            $data['ProductData'] = array(
                'MechanicalFasteners' => array(
                    'ProductType' =>array(
                        $xsd2->category=>$this->$method(),
                    )
                ),
            );

            $xmls[$this->product->sku] = $this->arr2xml->buildXML($this->trimTier($data,'MechanicalFasteners',$xsd2->category), 'Product');
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
        if(!empty($this->product->id) && !empty($model->id)){
            $log = new AmazonUpLog();
            $log->account_id = $this->product->account_id;
            $log->amz_product_id = $this->product->id;
            $log->title = empty($found) ? '添加产品' : '更新产品';
            $log->content = '';
            $log->type = self::PRODUCT;
            $log->num = 1;
            $log->operator = empty($found) ? 1: 2;
            $log->creator = Yii::app()->user->id?Yii::app()->user->id:$this->uid;
            $log->create_date = time();
            $log->save();
        }

        if (empty($log->id)) {
            throw new Exception('添加产品日志出错', 1);
        }
    }

    protected function _MechanicalFasteners(){
        $item=array(
            'Color'=>'Color',
            'ColorMap'=>'',
            'CompatibleGrooveDepth'=>array(
                '@unitOfMeasure' => 'CM',
                '%' => '',
            ),
            'CompatibleGrooveDiameter'=>array(
                '@unitOfMeasure' => 'CM',
                '%' => ''
            ),
            'CompatibleGrooveWidth'=>array(
                '@unitOfMeasure' => 'CM',
                '%' => ''
            ),
            'CompatibleWithInsideDiameter'=>array(
                '@unitOfMeasure' => 'CM',
                '%' => ''
            ),
            'CompatibleWithPipeSize'=>'',  //boolean
            'CompatibleWithTorxWrench'=>'', //boolean
            'CountryOfOrigin'=>'', //String [a-zA-Z][a-zA-Z]|unknown
            'DriveSystem'=>'',
            'ExteriorFinish'=>'',
            'FastenerThreadCount'=>'',
            'GradeRating'=>'',
            'HeadDiameter'=>array(
                '@unitOfMeasure' => 'CM',
                '%' => ''
            ),
            'HeadDiameterTolerance'=>array(
                '@unitOfMeasure' => 'CM',
                '%' => ''
            ),
            'HeadHeight'=>array(
                '@unitOfMeasure' => 'CM',
                '%' => ''
            ),
            'HeadHeightTolerance'=>array(
                '@unitOfMeasure' => 'CM',
                '%' => ''
            ),
            'IndentationHardness'=>array(
                '@unitOfMeasure' => 'shore_a',
                '%' => ''
            ),
            'InsideDiameter'=>array(
                '@unitOfMeasure' => 'CM',
                '%' => ''
            ),
            'InsideDiameterTolerance'=>array(
                '@unitOfMeasure' => 'CM',
                '%' => ''
            ),
            'InsideThreadSize'=>array(
                '@unitOfMeasure' => 'CM',
                '%' => ''
            ),
            'ItemDepth'=>array(
                '@unitOfMeasure' => 'CM',
                '%' => ''
            ),
            'ItemDiameter'=>array(
                '@unitOfMeasure' => 'CM',
                '%' => ''
            ),
            'ItemThickness'=>array(
                '@unitOfMeasure' => 'CM',
                '%' => ''
            ),
            'ItemThicknessTolerance'=>array(
                '@unitOfMeasure' => 'CM',
                '%' => ''
            ),
            'LowerTemperatureRating'=>array(
                '@unitOfMeasure' => 'C',
                '%' => ''
            ),
            'MagneticPullCapacity'=>array(
                '@unitOfMeasure' => 'KG',
                '%' => ''
            ),
            'MaterialType'=>'',
            'MaximumCompatibleThickness'=>array(
                '@unitOfMeasure' => 'CM',
                '%' => ''
            ),
            'MaximumDoubleShearStrength'=>array(
                '@unitOfMeasure' => 'psi',
                '%' => ''
            ),
            'MaxShearStrength'=>array(
                '@unitOfMeasure' => 'psi',
                '%' => ''
            ),
            'MeasurementSystem'=>'',
            'MinimumCompatibleThickness'=>array(
                '@unitOfMeasure' => 'CM',
                '%' => ''
            ),
            'MinimumEmbedmentDepth'=>array(
                '@unitOfMeasure' => 'CM',
                '%' => ''
            ),
            'Model'=>'',
            'NominalOutsideDiameter'=>array(
                '@unitOfMeasure' => 'CM',
                '%' => ''
            ),
            'NumberOfStarts'=>'',//PositiveInteger
            'NumberOfTurns'=>'',//PositiveInteger
            'OutsideThreadSize'=>array(
                '@unitOfMeasure' => 'CM',
                '%' => ''
            ),
            'PointMaterialType'=>'',
            'ScrewHeadStyle'=>'',
            'ScrewPointStyle'=>'',
            'SelfLockingMechanismType'=>'',
            'ShoulderDiameter'=>array(
                '@unitOfMeasure' => 'CM',
                '%' => ''
            ),
            'ShoulderDiameterTolerance'=>array(
                '@unitOfMeasure' => 'CM',
                '%' => ''
            ),
            'ShoulderLength'=>array(
                '@unitOfMeasure' => 'CM',
                '%' => ''
            ),
            'ShoulderLengthTolerance'=>array(
                '@unitOfMeasure' => 'CM',
                '%' => ''
            ),
            'SizeName'=>'',
            'SpecificationMet'=>'',
            'ThreadCoverage'=>'',
            'ThreadLength'=>array(
                '@unitOfMeasure' => 'CM',
                '%' => ''
            ),
            'ThreadPitch'=>array(
                '@unitOfMeasure' => 'CM',
                '%' => ''
            ),
            'ThreadSize'=>'',
            'ThreadStyle'=>'',
            'ThreadType'=>'',
            'UncompressedDiameter'=>array(
                '@unitOfMeasure' => 'CM',
                '%' => ''
            ),
            'UpperTemperatureRating'=>array(
                '@unitOfMeasure' => 'C',
                '%' => ''
            ),
            'WasherType'=>'',
            'WingSpan'=>array(
                '@unitOfMeasure' => 'CM',
                '%' => ''
            ),
            'WorkingLoadLimit'=>array(
                '@unitOfMeasure' => 'KG',
                '%' => ''
            ),
            'StyleName'=>''
        );
        return $item;
    }
}
