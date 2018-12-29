<?php

/**
 * The default template
 *
 * @author mrlina <714480119@qq.com>
 */
Class AmazonOffice extends AmazonUpload implements IAmazonUpload
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

        //子分类方法名
        $method ='_'.$xsd2->category;
        $this->$method($data,$xsd2);
        return true;
    }

    /**
     * 写入日志
     * @return mixed
     */
    private function Insertlog(){
        $log = new AmazonUpLog();
        $log->account_id = $this->product->account_id;
        $log->amz_product_id = $this->product->id;
        $log->operator = empty($found)?1:2;
        $log->title = empty($found)?"上传产品":"更新产品";
        $log->content = '';
        $log->type = self::PRODUCT;
        $log->num = 1;
        $log->creator = Yii::app()->user->id?Yii::app()->user->id:$this->uid;
        $log->create_date = time();
        $log->save();
        return $log->id;
    }

    /**
     * 保存要上传到Amazon的产品
     * @param $found 模型
     * @param $sku
     * @param $xml
     */
    private function Saves($found,$sku,$xml){
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
        return $model->id;
    }

    /**
     * 获取子分类名称
     * @return string 分类名称
     * @throws Exception
     */
    private function Classname(){
        $xsd2 = UebModel::model('AmazonProdataxsd')->findByPk($this->product->xsd_type[1]);
        if (empty($xsd2)) {
            throw new Exception("缺少精确的XSD模板信息", 1);
        }
        //子分类方法名
        return '_'.$xsd2->category;
    }

    /**
     * 移除值为空的项
     *
     * @return array
     */
    protected function removeEmptyFloor(array $data,$category,$childcate)
    {
        //handle descript first
        foreach ($data['DescriptionData'] as $key => $value) {
            if ($this->isEmpty($value)) {
                unset($data['DescriptionData'][$key]);
            }
        }

        foreach ($data['ProductData'][$category]['ProductType'][$childcate] as $key => $value) {
            if ($this->isEmpty($value)) {
                unset($data['ProductData'][$category]['ProductType'][$childcate][$key]);
            }
        }

        foreach ($data['ProductData'][$category] as $key => $value) {
            if ($this->isEmpty($value)) {
                unset($data['ProductData'][$category][$key]);
            }
        }

        foreach ($data as $key => $value) {
            if ($this->isEmpty($value)) {
                unset($data[$key]);
            }
        }

        return $data;
    }

    /* 以下为子分类 */
    public function _ArtSupplies($data,$xsd2)
    {       //单品
        if ($this->product->product_is_multi==0) {
            //指定产品分类
            $data['ProductData'] = array(
                'Office' => array(
                    'ProductType' => array(
                        $xsd2->category=>array(
                            'NumberOfItems'=>1
                        )
                    ),
                ),
            );
            $xmls[$this->product->sku] = $this->arr2xml->buildXML($this->removeEmptyItem($data),'Product');
        }
        //多属性
        else if($this->product->product_is_multi == 2) {
            //父体变体设置
            $data['ProductData'] = array(
                'Office' => array(
                    'ProductType' => array(
                        $xsd2->category=>array(
                            'VariationData' => array(
                                'Parentage' => 'parent',
                                'VariationTheme' => str_replace('-','',$this->product->variation_theme),
                            ),
                        )
                    ),
                ),
            );

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
                $color=$variations['Color'];
                $size=$variations['Size'];

                $child['ProductData'] = array(
                    'Office' => array(
                        'ProductType' => array(
                            $xsd2->category=>array(
                                'VariationData' => array(
                                    'Parentage' => 'child', //指定为子体
                                    'VariationTheme' => str_replace('-','',$this->product->variation_theme), //指定与父体相同的Theme
                                ),
                                'ColorSpecification'=>array(
                                    'Color'=>$color,
                                    'ColorMap'=>'ColorMap'
                                ),
                            )
                        ),
                        'Size'=>$size
                    ),
                );

                $xmls[$sonprd->sku] = $this->arr2xml->buildXML($this->removeEmptyFloor($child,'Office',$xsd2->category), 'Product');
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

            $mid=$this->Saves($found,$sku,$xml);
            if (empty($mid)) {
                throw new Exception("保存{$sku}产品XML数据出错", 1);
            }
        }
        //上传操作记录日志
        if(!empty($mid) && !empty($this->product->id)){
            $relog=$this->Insertlog();
        }

        if (empty($relog)) {
            throw new Exception("记录上传{$this->Classname()}分类产品日志异常", 1);
        }
    }

    public function _EducationalSupplies($data,$xsd2)
    {       //单品
        if ($this->product->product_is_multi==0) {
            //指定产品分类
            $data['ProductData'] = array(
                'Office' => array(
                    'ProductType' => array(
                        $xsd2->category=>array(
                            'NumberOfItems'=>1
                        )
                    ),
                ),
            );
            $xmls[$this->product->sku] = $this->arr2xml->buildXML($this->removeEmptyItem($data),'Product');
        }
        //多属性
        else if($this->product->product_is_multi == 2) {
            //父体变体设置
            $data['ProductData'] = array(
                'Office' => array(
                    'ProductType' => array(
                        $xsd2->category=>array(
                            'VariationData' => array(
                                'Parentage' => 'parent',
                                'VariationTheme' => str_replace('-','',$this->product->variation_theme),
                            ),
                        )
                    ),
                ),
            );

            $xmls[$this->product->sku] = $this->arr2xml->buildXML($this->removeEmptyItem($data), 'Product');

            //循环所有子sku产品
            foreach ($this->product->sonskues as $sonprd) {
                $data['DescriptionData']['MfrPartNumber'] = $sonprd->mfr;
                //clone父体信息
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
                $color=$variations['Color'];
                $size=$variations['Size'];

                $child['ProductData'] = array(
                    'Office' => array(
                        'ProductType' => array(
                            $xsd2->category=>array(
                                'VariationData' => array(
                                    'Parentage' => 'child', //指定为子体
                                    'VariationTheme' => str_replace('-','',$this->product->variation_theme), //指定与父体相同的Theme
                                ),
                                'ColorSpecification'=>array(
                                    'Color'=>$color,
                                    'ColorMap'=>'ColorMap'
                                ),
                            )
                        ),
                        'Size'=>$size
                    ),
                );
                $xmls[$sonprd->sku] = $this->arr2xml->buildXML($this->removeEmptyFloor($child,'Office',$xsd2->category), 'Product');
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

            $mid=$this->Saves($found,$sku,$xml);
            if (empty($mid)) {
                throw new Exception("保存{$sku}产品XML数据出错", 1);
            }
        }
        //上传操作记录日志
        if(!empty($mid) && !empty($this->product->id)){
            $relog=$this->Insertlog();
        }

        if (empty($relog)) {
            throw new Exception("记录上传{$this->Classname()}分类产品日志异常", 1);
        }
    }

    public function _OfficeProducts($data,$xsd2)
    {       //单品
        if ($this->product->product_is_multi==0) {
            //指定产品分类
            $data['ProductData'] = array(
                'Office' => array(
                    'ProductType' => array(
                        $xsd2->category=>array(
                            'NumberOfItems'=>1
                        )
                    ),
                ),
            );
            $xmls[$this->product->sku] = $this->arr2xml->buildXML($this->removeEmptyItem($data),'Product');
        }
        //多属性
        else if($this->product->product_is_multi == 2) {
            //父体变体设置
            $data['ProductData'] = array(
                'Office' => array(
                    'ProductType' => array(
                        $xsd2->category=>array(
                            'VariationData' => array(
                                'Parentage' => 'parent',
                                'VariationTheme' => str_replace('-','',$this->product->variation_theme),
                            ),
                        )
                    ),
                ),
            );

            $xmls[$this->product->sku] = $this->arr2xml->buildXML($this->removeEmptyItem($data), 'Product');

            //循环所有子sku产品
            foreach ($this->product->sonskues as $sonprd) {
                $data['DescriptionData']['MfrPartNumber'] = $sonprd->mfr;
                //clone父体信息
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
                $color=$variations['Color'];
                $size=$variations['Size'];

                $child['ProductData'] = array(
                    'Office' => array(
                        'ProductType' => array(
                            $xsd2->category=>array(
                                'VariationData' => array(
                                    'Parentage' => 'child', //指定为子体
                                    'VariationTheme' => str_replace('-','',$this->product->variation_theme), //指定与父体相同的Theme
                                ),
                                'ColorSpecification'=>array(
                                    'Color'=>$color,
                                    'ColorMap'=>'ColorMap'
                                ),
                            )
                        ),
                        'Size'=>$size
                    ),
                );

                $xmls[$sonprd->sku] = $this->arr2xml->buildXML($this->removeEmptyFloor($child,'Office',$xsd2->category), 'Product');
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

            $mid=$this->Saves($found,$sku,$xml);
            if (empty($mid)) {
                throw new Exception("保存{$sku}产品XML数据出错", 1);
            }
        }
        //上传操作记录日志
        if(!empty($mid) && !empty($this->product->id)){
            $relog=$this->Insertlog();
        }

        if (empty($relog)) {
            throw new Exception("记录上传{$this->Classname()}分类产品日志异常", 1);
        }
    }

    public function _PaperProducts($data,$xsd2)
    {       //单品
        if ($this->product->product_is_multi==0) {
            //指定产品分类
            $data['ProductData'] = array(
                'Office' => array(
                    'ProductType' => array(
                        $xsd2->category=>array(
                            'NumberOfItems'=>1
                        )
                    ),
                ),
            );
            $xmls[$this->product->sku] = $this->arr2xml->buildXML($this->removeEmptyItem($data),'Product');
        }
        //多属性
        else if($this->product->product_is_multi == 2) {
            //父体变体设置
            $data['ProductData'] = array(
                'Office' => array(
                    'ProductType' => array(
                        $xsd2->category=>array(
                            'VariationData' => array(
                                'Parentage' => 'parent',
                                'VariationTheme' => str_replace('-','',$this->product->variation_theme),
                            ),
                        )
                    ),
                ),
            );

            $xmls[$this->product->sku] = $this->arr2xml->buildXML($this->removeEmptyItem($data), 'Product');

            //循环所有子sku产品
            foreach ($this->product->sonskues as $sonprd) {
                $data['DescriptionData']['MfrPartNumber'] = $sonprd->mfr;
                //clone父体信息
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
                $color=$variations['Color'];
                $size=$variations['Size'];

                $child['ProductData'] = array(
                    'Office' => array(
                        'ProductType' => array(
                            $xsd2->category=>array(
                                'VariationData' => array(
                                    'Parentage' => 'child', //指定为子体
                                    'VariationTheme' => str_replace('-','',$this->product->variation_theme), //指定与父体相同的Theme
                                ),
                                'ColorSpecification'=>array(
                                    'Color'=>$color,
                                    'ColorMap'=>'ColorMap'
                                ),
                            )
                        ),
                        'Size'=>$size
                    ),
                );

                $xmls[$sonprd->sku] = $this->arr2xml->buildXML($this->removeEmptyFloor($child,'Office',$xsd2->category), 'Product');
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

            $mid=$this->Saves($found,$sku,$xml);
            if (empty($mid)) {
                throw new Exception("保存{$sku}产品XML数据出错", 1);
            }
        }
        //上传操作记录日志
        if(!empty($mid) && !empty($this->product->id)){
            $relog=$this->Insertlog();
        }

        if (empty($relog)) {
            throw new Exception("记录上传{$this->Classname()}分类产品日志异常", 1);
        }
    }

    public function _WritingInstruments($data,$xsd2)
    {       //单品
        if ($this->product->product_is_multi==0) {
            //指定产品分类
            $data['ProductData'] = array(
                'Office' => array(
                    'ProductType' => array(
                        $xsd2->category=>array(
                            'NumberOfItems'=>1
                        )
                    ),
                ),
            );
            $xmls[$this->product->sku] = $this->arr2xml->buildXML($this->removeEmptyItem($data),'Product');
        }
        //多属性
        else if($this->product->product_is_multi == 2) {
            //父体变体设置
            $data['ProductData'] = array(
                'Office' => array(
                    'ProductType' => array(
                        $xsd2->category=>array(
                            'VariationData' => array(
                                'Parentage' => 'parent',
                                'VariationTheme' => str_replace('-','',$this->product->variation_theme),
                            ),
                        )
                    ),
                ),
            );

            $xmls[$this->product->sku] = $this->arr2xml->buildXML($this->removeEmptyItem($data), 'Product');

            //循环所有子sku产品
            foreach ($this->product->sonskues as $sonprd) {
                $data['DescriptionData']['MfrPartNumber'] = $sonprd->mfr;
                //clone父体信息
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
                $color=$variations['Color'];
                $size=$variations['Size'];

                $child['ProductData'] = array(
                    'Office' => array(
                        'ProductType' => array(
                            $xsd2->category=>array(
                                'VariationData' => array(
                                    'Parentage' => 'child', //指定为子体
                                    'VariationTheme' => str_replace('-','',$this->product->variation_theme), //指定与父体相同的Theme
                                ),
                                'ColorSpecification'=>array(
                                    'Color'=>$color,
                                    'ColorMap'=>'ColorMap'
                                ),
                            )
                        ),
                        'Size'=>$size
                    ),
                );

                $xmls[$sonprd->sku] = $this->arr2xml->buildXML($this->removeEmptyFloor($child,'Office',$xsd2->category), 'Product');
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

            $mid=$this->Saves($found,$sku,$xml);
            if (empty($mid)) {
                throw new Exception("保存{$sku}产品XML数据出错", 1);
            }
        }
        //上传操作记录日志
        if(!empty($mid) && !empty($this->product->id)){
            $relog=$this->Insertlog();
        }

        if (empty($relog)) {
            throw new Exception("记录上传{$this->Classname()}分类产品日志异常", 1);
        }
    }

    /*  以下子分类Color都是和变体层平级 */

    public function _BarCode($data,$xsd2)
    {       //单品
        if ($this->product->product_is_multi==0) {
            //指定产品分类
            $data['ProductData'] = array(
                'Office' => array(
                    'ProductType' => array(
                        $xsd2->category=>array(
                            'NoiseAttenuation'=>'1'
                        )
                    ),
                ),
            );
            $xmls[$this->product->sku] = $this->arr2xml->buildXML($this->removeEmptyItem($data),'Product');
        }
        //多属性
        else if($this->product->product_is_multi == 2) {
            //父体变体设置
            $data['ProductData'] = array(
                'Office' => array(
                    'ProductType' => array(
                        $xsd2->category=>array(
                            'VariationData' => array(
                                'Parentage' => 'parent',
                                'VariationTheme' => str_replace('-','',$this->product->variation_theme),
                            ),
                        )
                    ),
                ),
            );

            $xmls[$this->product->sku] = $this->arr2xml->buildXML($this->removeEmptyItem($data), 'Product');

            //循环所有子sku产品
            foreach ($this->product->sonskues as $sonprd) {
                $data['DescriptionData']['MfrPartNumber'] = $sonprd->mfr;
                //clone父体信息
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
                $color=$variations['Color'];
                $size=$variations['Size'];

                $child['ProductData'] = array(
                    'Office' => array(
                        'ProductType' => array(
                            $xsd2->category=>array(
                                'VariationData' => array(
                                    'Parentage' => 'child', //指定为子体
                                    'VariationTheme' => str_replace('-','',$this->product->variation_theme), //指定与父体相同的Theme
                                ),
                                'Color'=>$color,
                            )
                        ),
                        'Size'=>$size
                    ),
                );

                $xmls[$sonprd->sku] = $this->arr2xml->buildXML($this->removeEmptyFloor($child,'Office',$xsd2->category), 'Product');
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

            $mid=$this->Saves($found,$sku,$xml);
            if (empty($mid)) {
                throw new Exception("保存{$sku}产品XML数据出错", 1);
            }
        }
        //上传操作记录日志
        if(!empty($mid) && !empty($this->product->id)){
            $relog=$this->Insertlog();
        }

        if (empty($relog)) {
            throw new Exception("记录上传{$this->Classname()}分类产品日志异常", 1);
        }
    }

    public function _Calculator($data,$xsd2)
    {       //单品
        if ($this->product->product_is_multi==0) {
            //指定产品分类
            $data['ProductData'] = array(
                'Office' => array(
                    'ProductType' => array(
                        $xsd2->category=>array(
                            'Color'=>'1'
                        )
                    ),
                ),
            );
            $xmls[$this->product->sku] = $this->arr2xml->buildXML($this->removeEmptyItem($data),'Product');
        }
        //多属性
        else if($this->product->product_is_multi == 2) {
            //父体变体设置
            $data['ProductData'] = array(
                'Office' => array(
                    'ProductType' => array(
                        $xsd2->category=>array(
                            'VariationData' => array(
                                'Parentage' => 'parent',
                                'VariationTheme' => str_replace('-','',$this->product->variation_theme),
                            ),
                        )
                    ),
                ),
            );

            $xmls[$this->product->sku] = $this->arr2xml->buildXML($this->removeEmptyItem($data), 'Product');

            //循环所有子sku产品
            foreach ($this->product->sonskues as $sonprd) {
                $data['DescriptionData']['MfrPartNumber'] = $sonprd->mfr;
                //clone父体信息
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
                $color=$variations['Color'];
                $size=$variations['Size'];

                $child['ProductData'] = array(
                    'Office' => array(
                        'ProductType' => array(
                            $xsd2->category=>array(
                                'VariationData' => array(
                                    'Parentage' => 'child', //指定为子体
                                    'VariationTheme' => str_replace('-','',$this->product->variation_theme), //指定与父体相同的Theme
                                ),
                                'Color'=>$color,
                            )
                        ),
                        'Size'=>$size
                    ),
                );

                $xmls[$sonprd->sku] = $this->arr2xml->buildXML($this->removeEmptyFloor($child,'Office',$xsd2->category), 'Product');
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

            $mid=$this->Saves($found,$sku,$xml);
            if (empty($mid)) {
                throw new Exception("保存{$sku}产品XML数据出错", 1);
            }
        }
        //上传操作记录日志
        if(!empty($mid) && !empty($this->product->id)){
            $relog=$this->Insertlog();
        }

        if (empty($relog)) {
            throw new Exception("记录上传{$this->Classname()}分类产品日志异常", 1);
        }
    }

    public function _InkToner($data,$xsd2)
    {       //单品
        if ($this->product->product_is_multi==0) {
            //指定产品分类
            $data['ProductData'] = array(
                'Office' => array(
                    'ProductType' => array(
                        $xsd2->category=>array(
                            'Color'=>'1'
                        )
                    ),
                ),
            );
            $xmls[$this->product->sku] = $this->arr2xml->buildXML($this->removeEmptyItem($data),'Product');
        }
        //多属性
        else if($this->product->product_is_multi == 2) {
            //父体变体设置
            $data['ProductData'] = array(
                'Office' => array(
                    'ProductType' => array(
                        $xsd2->category=>array(
                            'VariationData' => array(
                                'Parentage' => 'parent',
                                'VariationTheme' => str_replace('-','',$this->product->variation_theme),
                            ),
                        )
                    ),
                ),
            );

            $xmls[$this->product->sku] = $this->arr2xml->buildXML($this->removeEmptyItem($data), 'Product');

            //循环所有子sku产品
            foreach ($this->product->sonskues as $sonprd) {
                $data['DescriptionData']['MfrPartNumber'] = $sonprd->mfr;
                //clone父体信息
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
                $color=$variations['Color'];
                $size=$variations['Size'];

                $child['ProductData'] = array(
                    'Office' => array(
                        'ProductType' => array(
                            $xsd2->category=>array(
                                'VariationData' => array(
                                    'Parentage' => 'child', //指定为子体
                                    'VariationTheme' => str_replace('-','',$this->product->variation_theme), //指定与父体相同的Theme
                                ),
                                'Color'=>$color,
                            )
                        ),
                        'Size'=>$size
                    ),
                );

                $xmls[$sonprd->sku] = $this->arr2xml->buildXML($this->removeEmptyFloor($child,'Office',$xsd2->category), 'Product');
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

            $mid=$this->Saves($found,$sku,$xml);
            if (empty($mid)) {
                throw new Exception("保存{$sku}产品XML数据出错", 1);
            }
        }
        //上传操作记录日志
        if(!empty($mid) && !empty($this->product->id)){
            $relog=$this->Insertlog();
        }

        if (empty($relog)) {
            throw new Exception("记录上传{$this->Classname()}分类产品日志异常", 1);
        }
    }

    public function _MultifunctionDevice($data,$xsd2)
    {       //单品
        if ($this->product->product_is_multi==0) {
            //指定产品分类
            $data['ProductData'] = array(
                'Office' => array(
                    'ProductType' => array(
                        $xsd2->category=>array(
                            'Color'=>'1'
                        )
                    ),
                ),
            );
            $xmls[$this->product->sku] = $this->arr2xml->buildXML($this->removeEmptyItem($data),'Product');
        }
        //多属性
        else if($this->product->product_is_multi == 2) {
            //父体变体设置
            $data['ProductData'] = array(
                'Office' => array(
                    'ProductType' => array(
                        $xsd2->category=>array(
                            'VariationData' => array(
                                'Parentage' => 'parent',
                                'VariationTheme' => str_replace('-','',$this->product->variation_theme),
                            ),
                        )
                    ),
                ),
            );

            $xmls[$this->product->sku] = $this->arr2xml->buildXML($this->removeEmptyItem($data), 'Product');

            //循环所有子sku产品
            foreach ($this->product->sonskues as $sonprd) {
                $data['DescriptionData']['MfrPartNumber'] = $sonprd->mfr;
                //clone父体信息
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
                $color=$variations['Color'];
                $size=$variations['Size'];

                $child['ProductData'] = array(
                    'Office' => array(
                        'ProductType' => array(
                            $xsd2->category=>array(
                                'VariationData' => array(
                                    'Parentage' => 'child', //指定为子体
                                    'VariationTheme' => str_replace('-','',$this->product->variation_theme), //指定与父体相同的Theme
                                ),
                                'Color'=>$color,
                            )
                        ),
                        'Size'=>$size
                    ),
                );

                $xmls[$sonprd->sku] = $this->arr2xml->buildXML($this->removeEmptyFloor($child,'Office',$xsd2->category), 'Product');
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

            $mid=$this->Saves($found,$sku,$xml);
            if (empty($mid)) {
                throw new Exception("保存{$sku}产品XML数据出错", 1);
            }
        }
        //上传操作记录日志
        if(!empty($mid) && !empty($this->product->id)){
            $relog=$this->Insertlog();
        }

        if (empty($relog)) {
            throw new Exception("记录上传{$this->Classname()}分类产品日志异常", 1);
        }
    }

    public function _OfficeElectronics($data,$xsd2)
    {       //单品
        if ($this->product->product_is_multi==0) {
            //指定产品分类
            $data['ProductData'] = array(
                'Office' => array(
                    'ProductType' => array(
                        $xsd2->category=>array(
                            'Color'=>'1'
                        )
                    ),
                ),
            );
            $xmls[$this->product->sku] = $this->arr2xml->buildXML($this->removeEmptyItem($data),'Product');
        }
        //多属性
        else if($this->product->product_is_multi == 2) {
            //父体变体设置
            $data['ProductData'] = array(
                'Office' => array(
                    'ProductType' => array(
                        $xsd2->category=>array(
                            'VariationData' => array(
                                'Parentage' => 'parent',
                                'VariationTheme' => str_replace('-','',$this->product->variation_theme),
                            ),
                        )
                    ),
                ),
            );

            $xmls[$this->product->sku] = $this->arr2xml->buildXML($this->removeEmptyItem($data), 'Product');

            //循环所有子sku产品
            foreach ($this->product->sonskues as $sonprd) {
                $data['DescriptionData']['MfrPartNumber'] = $sonprd->mfr;
                //clone父体信息
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
                $color=$variations['Color'];
                $size=$variations['Size'];

                $child['ProductData'] = array(
                    'Office' => array(
                        'ProductType' => array(
                            $xsd2->category=>array(
                                'VariationData' => array(
                                    'Parentage' => 'child', //指定为子体
                                    'VariationTheme' => str_replace('-','',$this->product->variation_theme), //指定与父体相同的Theme
                                ),
                                'Color'=>$color,
                            )
                        ),
                        'Size'=>$size
                    ),
                );

                $xmls[$sonprd->sku] = $this->arr2xml->buildXML($this->removeEmptyFloor($child,'Office',$xsd2->category), 'Product');
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

            $mid=$this->Saves($found,$sku,$xml);
            if (empty($mid)) {
                throw new Exception("保存{$sku}产品XML数据出错", 1);
            }
        }
        //上传操作记录日志
        if(!empty($mid) && !empty($this->product->id)){
            $relog=$this->Insertlog();
        }

        if (empty($relog)) {
            throw new Exception("记录上传{$this->Classname()}分类产品日志异常", 1);
        }
    }

    /* 从OfficePone分类开始，以下都变体层只有一个Color变体 */

    public function _OfficePhone($data,$xsd2)
    {       //单品
        if ($this->product->product_is_multi==0) {
            //指定产品分类
            $data['ProductData'] = array(
                'Office' => array(
                    'ProductType' => array(
                        $xsd2->category=>array(
                            'Color'=>'1'
                        )
                    ),
                ),
            );
            $xmls[$this->product->sku] = $this->arr2xml->buildXML($this->removeEmptyItem($data),'Product');
        }
        //多属性
        else if($this->product->product_is_multi == 2) {
            //父体变体设置
            $theme=str_replace('-','',$this->product->variation_theme);
            if($theme=='Size' || $theme=='SizeColor'){
                $theme='Color';
            }

            $data['ProductData'] = array(
                'Office' => array(
                    'ProductType' => array(
                        $xsd2->category=>array(
                            'VariationData' => array(
                                'Parentage' => 'parent',
                                'VariationTheme' => $theme,
                            ),
                        )
                    ),
                ),
            );

            $xmls[$this->product->sku] = $this->arr2xml->buildXML($this->removeEmptyItem($data), 'Product');

            //循环所有子sku产品
            foreach ($this->product->sonskues as $sonprd) {
                $data['DescriptionData']['MfrPartNumber'] = $sonprd->mfr;
                //clone父体信息
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
                $color=$variations['Color'];
                $size=$variations['Size'];

                $child['ProductData'] = array(
                    'Office' => array(
                        'ProductType' => array(
                            $xsd2->category=>array(
                                'VariationData' => array(
                                    'Parentage' => 'child', //指定为子体
                                    'VariationTheme' => $theme, //指定与父体相同的Theme
                                ),
                                'Color'=>$color ? $color : $size,
                            )
                        ),
                        'Size'=>$size
                    ),
                );

                $xmls[$sonprd->sku] = $this->arr2xml->buildXML($this->removeEmptyFloor($child,'Office',$xsd2->category), 'Product');
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

            $mid=$this->Saves($found,$sku,$xml);
            if (empty($mid)) {
                throw new Exception("保存{$sku}产品XML数据出错", 1);
            }
        }
        //上传操作记录日志
        if(!empty($mid) && !empty($this->product->id)){
            $relog=$this->Insertlog();
        }

        if (empty($relog)) {
            throw new Exception("记录上传{$this->Classname()}分类产品日志异常", 1);
        }
    }

    public function _OfficePrinter($data,$xsd2)
    {       //单品
        if ($this->product->product_is_multi==0) {
            //指定产品分类
            $data['ProductData'] = array(
                'Office' => array(
                    'ProductType' => array(
                        $xsd2->category=>array(
                            'Color'=>'1'
                        )
                    ),
                ),
            );
            $xmls[$this->product->sku] = $this->arr2xml->buildXML($this->removeEmptyItem($data),'Product');
        }
        //多属性
        else if($this->product->product_is_multi == 2) {
            //父体变体设置
            $theme=str_replace('-','',$this->product->variation_theme);
            if($theme=='Size' || $theme=='SizeColor'){
                $theme='Color';
            }

            $data['ProductData'] = array(
                'Office' => array(
                    'ProductType' => array(
                        $xsd2->category=>array(
                            'VariationData' => array(
                                'Parentage' => 'parent',
                                'VariationTheme' => $theme,
                            ),
                        )
                    ),
                ),
            );

            $xmls[$this->product->sku] = $this->arr2xml->buildXML($this->removeEmptyItem($data), 'Product');

            //循环所有子sku产品
            foreach ($this->product->sonskues as $sonprd) {
                $data['DescriptionData']['MfrPartNumber'] = $sonprd->mfr;
                //clone父体信息
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
                $color=$variations['Color'];
                $size=$variations['Size'];

                $child['ProductData'] = array(
                    'Office' => array(
                        'ProductType' => array(
                            $xsd2->category=>array(
                                'VariationData' => array(
                                    'Parentage' => 'child', //指定为子体
                                    'VariationTheme' => $theme, //指定与父体相同的Theme
                                ),
                                'Color'=>$color ? $color : $size,
                            )
                        ),
                        'Size'=>$size
                    ),
                );

                $xmls[$sonprd->sku] = $this->arr2xml->buildXML($this->removeEmptyFloor($child,'Office',$xsd2->category), 'Product');
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

            $mid=$this->Saves($found,$sku,$xml);
            if (empty($mid)) {
                throw new Exception("保存{$sku}产品XML数据出错", 1);
            }
        }
        //上传操作记录日志
        if(!empty($mid) && !empty($this->product->id)){
            $relog=$this->Insertlog();
        }

        if (empty($relog)) {
            throw new Exception("记录上传{$this->Classname()}分类产品日志异常", 1);
        }
    }

    public function _OfficeScanner($data,$xsd2)
    {       //单品
        if ($this->product->product_is_multi==0) {
            //指定产品分类
            $data['ProductData'] = array(
                'Office' => array(
                    'ProductType' => array(
                        $xsd2->category=>array(
                            'Color'=>'1'
                        )
                    ),
                ),
            );
            $xmls[$this->product->sku] = $this->arr2xml->buildXML($this->removeEmptyItem($data),'Product');
        }
        //多属性
        else if($this->product->product_is_multi == 2) {
            //父体变体设置
            $theme=str_replace('-','',$this->product->variation_theme);
            if($theme=='Size' || $theme=='SizeColor'){
                $theme='Color';
            }

            $data['ProductData'] = array(
                'Office' => array(
                    'ProductType' => array(
                        $xsd2->category=>array(
                            'VariationData' => array(
                                'Parentage' => 'parent',
                                'VariationTheme' => $theme,
                            ),
                        )
                    ),
                ),
            );

            $xmls[$this->product->sku] = $this->arr2xml->buildXML($this->removeEmptyItem($data), 'Product');

            //循环所有子sku产品
            foreach ($this->product->sonskues as $sonprd) {
                $data['DescriptionData']['MfrPartNumber'] = $sonprd->mfr;
                //clone父体信息
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
                $color=$variations['Color'];
                $size=$variations['Size'];

                $child['ProductData'] = array(
                    'Office' => array(
                        'ProductType' => array(
                            $xsd2->category=>array(
                                'VariationData' => array(
                                    'Parentage' => 'child', //指定为子体
                                    'VariationTheme' => $theme, //指定与父体相同的Theme
                                ),
                                'Color'=>$color ? $color : $size,
                            )
                        ),
                        'Size'=>$size
                    ),
                );

                $xmls[$sonprd->sku] = $this->arr2xml->buildXML($this->removeEmptyFloor($child,'Office',$xsd2->category), 'Product');
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

            $mid=$this->Saves($found,$sku,$xml);
            if (empty($mid)) {
                throw new Exception("保存{$sku}产品XML数据出错", 1);
            }
        }
        //上传操作记录日志
        if(!empty($mid) && !empty($this->product->id)){
            $relog=$this->Insertlog();
        }

        if (empty($relog)) {
            throw new Exception("记录上传{$this->Classname()}分类产品日志异常", 1);
        }
    }

    public function _VoiceRecorder($data,$xsd2)
    {       //单品
        if ($this->product->product_is_multi==0) {
            //指定产品分类
            $data['ProductData'] = array(
                'Office' => array(
                    'ProductType' => array(
                        $xsd2->category=>array(
                            'Color'=>'1'
                        )
                    ),
                ),
            );
            $xmls[$this->product->sku] = $this->arr2xml->buildXML($this->removeEmptyItem($data),'Product');
        }
        //多属性
        else if($this->product->product_is_multi == 2) {
            //父体变体设置
            $theme=str_replace('-','',$this->product->variation_theme);
            if($theme=='Size' || $theme=='SizeColor'){
                $theme='Color';
            }

            $data['ProductData'] = array(
                'Office' => array(
                    'ProductType' => array(
                        $xsd2->category=>array(
                            'VariationData' => array(
                                'Parentage' => 'parent',
                                'VariationTheme' => $theme,
                            ),
                        )
                    ),
                ),
            );

            $xmls[$this->product->sku] = $this->arr2xml->buildXML($this->removeEmptyItem($data), 'Product');

            //循环所有子sku产品
            foreach ($this->product->sonskues as $sonprd) {
                $data['DescriptionData']['MfrPartNumber'] = $sonprd->mfr;
                //clone父体信息
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
                $color=$variations['Color'];
                $size=$variations['Size'];

                $child['ProductData'] = array(
                    'Office' => array(
                        'ProductType' => array(
                            $xsd2->category=>array(
                                'VariationData' => array(
                                    'Parentage' => 'child', //指定为子体
                                    'VariationTheme' => $theme, //指定与父体相同的Theme
                                ),
                                'Color'=>$color ? $color : $size,
                            )
                        ),
                        'Size'=>$size
                    ),
                );

                $xmls[$sonprd->sku] = $this->arr2xml->buildXML($this->removeEmptyFloor($child,'Office',$xsd2->category), 'Product');
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

            $mid=$this->Saves($found,$sku,$xml);
            if (empty($mid)) {
                throw new Exception("保存{$sku}产品XML数据出错", 1);
            }
        }
        //上传操作记录日志
        if(!empty($mid) && !empty($this->product->id)){
            $relog=$this->Insertlog();
        }

        if (empty($relog)) {
            throw new Exception("记录上传{$this->Classname()}分类产品日志异常", 1);
        }
    }
}