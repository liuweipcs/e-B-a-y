<?php

/**
 * The default template
 *
 * @author mrlina <714480119@qq.com>
 */
Class AmazonCoins extends AmazonUpload implements IAmazonUpload
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

        //单品
        $arrt=unserialize($this->product->productdata->product_data);
        $year=explode('-',$arrt['ModelYear'])[0];

        if ($this->product->product_is_multi == 0) {
            //仅指定分类信息
            $data['ProductData'] = array(
                'Coins' => array(
                    'ProductType' =>array(
                        $xsd2->category=>array(
                            //其他没注释的必填项，为select下拉选项值
                            "ModelYear"=>date('Y',time()),//An numeric string in YYYY format
                            "MintMark"=>'MintMark',
                            "DenominationUnit"=>'DenominationUnit',
                            "SeriesTitle"=>'SeriesTitle',
                            "Variety"=>$arrt[''],
                            "SubVariety"=>$arrt[''],
                            "ItemStyling"=>$arrt[''],
                            "GradedBy"=>'GradedBy',
                            "GradeRating"=>'GradeRating',
                            "CertificateNumber"=>$arrt[''],
                            "UnitGrouping"=>'UnitGrouping',
                            "EachUnitCount"=>$arrt[''],
                            "StyleName"=>$arrt[''],
                            "Designation"=>$arrt[''],
                            "EdgeStyle"=>$arrt[''],
                            "Designer"=>$arrt[''],
                            "ArtistID"=>$arrt[''],
                            "EstatePeriod"=>$arrt[''],
                            "UnitCoint"=>$arrt[''],//十进制的，整数或小数
                            "MaterialType"=>'MaterialType',
                            "ItemDiameter"=>array(
                                '@unitOfMeasure' => 'CM',
                                '%' => '',
                            ),
                            "TotalMetalWeight"=> array(
                                '@unitOfMeasure' => 'KG',
                                '%' => '',
                            ),
                            "MetalStamp"=>$arrt[''],
                            "CountryOfOrigin"=>'CountryOfOrigin',
                            "LabelDescription"=>$arrt[''],
                        )
                    )
                ),
            );

            $xmls[$this->product->sku] = $this->arr2xml->buildXML($this->trimTier($data,'Coins',$xsd2->category), 'Product');
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
}
