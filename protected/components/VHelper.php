<?php

/**
 * View Helper Class
 * @package Application.components
 * @auther Bob <Foxzeng>
 */
class VHelper{
	const APPLY_NO 		= 0;//采购未请款
	const APPLY_PART	= 1;//部分请款
	const PAY_NO 		= 2;//全额请款\未付款
	const PAY_HAVE 		= 3;//全付款
	const PAY_PART 		= 4;//部分付款

	public $_disable = 0;
	public $_enable = 1;


    /**
     * get status lable
     *
     * @param type $status
     */
    public static function getStatusLable($status) {
        if ($status == 1) {
            echo '<font color="green" >' . Yii::t('system', 'Enable') . '</font>';
        } else {
            echo '<font color="red" >' . Yii::t('system', 'Disable') . '</font>';
        }
    }

    public static function getFinishLabel($status){
    	if ($status == 1) {
    		echo '<font color="green" >' . Yii::t('system', 'Finished') . '</font>';
    	} else {
    		echo '<font color="red" >' . Yii::t('system', 'Unfinish') . '</font>';
    	}
    }

    public static function getTaoShipStatusLable($status,$shipping_warning){
		if($shipping_warning==1){
			$color = 'blue';
		}elseif ($shipping_warning==3){
			$color = 'yellow';
		}elseif ($shipping_warning==5){
			$color = 'red';
		}
    	if ($status == 1) {
    		echo '<font color="'.$color.'" >' . Yii::t('logistics', '已发货') . '</font>';
		}else {
    		echo '<font color="'.$color.'" >' . Yii::t('logistics', 'NOT Shiped') . '</font>';
    	}
    }
	public static function getTaoReceiptStatusLable($status,$sign_for_warning){
		if($sign_for_warning==1){
			$color = 'blue';
		}elseif ($sign_for_warning==3){
			$color = 'yellow';
		}elseif ($sign_for_warning==5){
			$color = 'red';
		}
    	if ($status == 1) {
    		echo '<font color="'.$color.'" >' . Yii::t('logistics', 'Receipt Finished') . '</font>';
		} else {
    		echo '<font color="'.$color.'" >' . Yii::t('logistics', 'NOT Receipt') . '</font>';
    	}
    }

	public function getTaobaoStatusLable($status){
		if($status == 'TRADE_NO_CREATE_PAY'){
			echo Yii::t('logistics', 'TRADE NO CREATE PAY');
		}elseif($status == 'WAIT_BUYER_PAY'){
			echo Yii::t('logistics', 'WAIT BUYER PAY');
		}elseif($status == 'SELLER_CONSIGNED_PART'){
			echo Yii::t('logistics', 'SELLER CONSIGNED PART');
		}elseif($status == 'WAIT_SELLER_SEND_GOODS'){
			echo Yii::t('logistics', 'WAIT SELLER SEND GOODS');
		}elseif($status == 'WAIT_BUYER_CONFIRM_GOODS'){
			echo Yii::t('logistics', 'WAIT BUYER CONFIRM GOODS');
		}elseif($status == 'TRADE_BUYER_SIGNED'){
			echo Yii::t('logistics', 'TRADE BUYER SIGNED');
		}elseif($status == 'TRADE_FINISHED'){
			echo Yii::t('logistics', 'TRADE FINISHED');
		}elseif($status == 'TRADE_CLOSED'){
			echo Yii::t('logistics', 'TRADE CLOSED');
		}elseif($status == 'TRADE_CLOSED_BY_TAOBAO'){
			echo Yii::t('logistics', 'TRADE CLOSED BY TAOBAO');
		}elseif($status == 'PAY_PENDING'){
			echo Yii::t('logistics', 'PAY PENDING');
		}elseif($status == 'WAIT_PRE_AUTH_CONFIRM') {
			echo Yii::t('logistics', 'WAIT PRE AUTH CONFIRM');
		}elseif($status == 'WAIT_BUYER_RECEIVE'){
			echo Yii::t('logistics', '等待买家确认收货');
		}elseif($status == 'CANCEL'){
			echo Yii::t('logistics', '交易关闭');
		}elseif($status == 'SUCCESS'){
			echo Yii::t('logistics', '交易成功');
		}elseif($status == 'WAIT_BUYER_PAY'){
			echo Yii::t('logistics', '等待买家付款');
		}elseif($status == 'WAIT_SELLER_SEND'){
			echo Yii::t('logistics', '等待卖家发货');
		}elseif($status == 'WAIT_SELLER_ACT'){
			echo Yii::t('logistics', '分阶段等待卖家操作');
		}elseif($status == 'WAIT_BUYER_CONFIRM_ACTION'){
			echo Yii::t('logistics', '分阶段等待买家确认卖家操作');
		}elseif($status == 'WAIT_SELLER_PUSH'){
			echo Yii::t('logistics', '分阶段等待卖家推进');
		}elseif($status == 'WAIT_LOGISTICS_TAKE_IN'){
			echo Yii::t('logistics', '等待物流公司揽件COD');
		}elseif($status == 'WAIT_BUYER_SIGN'){
			echo Yii::t('logistics', '等待买家签收COD');
		}elseif($status == 'SIGN_IN_SUCCESS'){
			echo Yii::t('logistics', '买家已签收COD');
		}elseif($status == 'SIGN_IN_FAILED'){
			echo Yii::t('logistics', '签收失败COD');
		}else{
			echo Yii::t('logistics', 'NOT GET STATUS');
		}
	}

    /**
     * get log status lable
     *
     * @param type $status
     */
    public static function getLogStatusLable($status) {
        switch (strtolower($status)) {
            case 'success':
                echo '<font color="green" >' . Yii::t('system', 'Success') . '</font>';
                break;
            case 'info':
                echo '<font color="green" >' . Yii::t('system', 'Info') . '</font>';
                break;
            case 'failure':
                echo '<font color="red" >' . Yii::t('system', 'Failure') . '</font>';
                break;
            case 'error':
                echo '<font color="red" >' . Yii::t('system', 'Error') . '</font>';
                break;

        }
    }

    public static function getUserLoginLocationLable($location) {
        switch (strtolower($location)) {
            case 'wide':
                echo '<font color="red" >' . Yii::t('system', 'Wide') . '</font>';
                break;
            case 'local':
                echo '<font color="green" >' . Yii::t('system', 'Local') . '</font>';
                break;
           }
    }


    /**
     * get all log status list
     *
     * @param type $status
     * @author Nick 2013-10-9
     */
    public static function getAllLogStatusList() {
    	return array(
    			'success' => Yii::t('system', 'Success'),
    			'info'    => Yii::t('system', 'Info'),
    			'failure' => Yii::t('system', 'Failure'),
    			'error'   => Yii::t('system', 'Error')
    	);
    }

    /**
     * get status config;
     *
     * @return type
     */
    public static function getStatusConfig($type = null) {
        $config = array(
            1 => Yii::t('system', 'Enable'),
            0 => Yii::t('system', 'Disable')
        );
        if($type != null) return $config[$type];
        return $config;
    }

    public static function getshStatusConfig($type = null) {
        $config = array(
            1 => Yii::t('purchases', 'Hasbeenon'),
            0 => Yii::t('purchases', 'pendingtrial'),
            2 => Yii::t('purchases', 'noHasbeenon'),
            3 => Yii::t('purchases', 'blacklist'),
            4 => Yii::t('purchases', 'nocooperation'),
            5 => Yii::t('purchases', 'nonUse'),
            6 => Yii::t('purchases', '历史供应商')
        );
        if($type != null) return $config[$type];
        return $config;
    }


    public static function getSendTypeConfig() {
        return array(
            1 => Yii::t('system', 'System Message'),
        );
    }

    /**
     * get msg status config
     */
    public static function getMsgStatusConfig() {
        return array(
            0  => Yii::t('system', Yii::t('system', 'Unread')),
            1  => Yii::t('system', Yii::t('system', 'Reading')),
            2  => Yii::t('system', Yii::t('system', 'Read')),
        );
    }

    /**
     * get increate type config
     */
    public static function getIncreateTypeConfig() {
        return array(
            0 => Yii::t('system', 'Default'),
            1 => Yii::t('system', 'By The Month'),
            2 => Yii::t('system', 'By The Day'),
            3 => Yii::t('system', 'By The Hour'),
        );
    }

    /**
     * get increate type config label
     */
    public static function getIncreateTypeLabel($status) {
        $config = self::getIncreateTypeConfig();

        return $config[$status];
    }



    /**
     * get messages status label
     *
     */
    public static function getMsgStatusLable($status) {
        if ($status == '0') {
            echo '<font color="red" >' . Yii::t('system', 'Unread') . '</font>';
        } else if ( $status == '1') {
            echo '<font color="blue" >' . Yii::t('system', 'Reading') . '</font>';
        } else {
            echo '<font color="green" >' . Yii::t('system', 'Read') . '</font>';
        }
    }

    /**
     * get settlement type config
     */
    public static function getSettlementTypeConfig($type=null) {
        $payType = array(
		    30  => Yii::t('system', Yii::t('purchases', 'Monthly statement')),
			14  => Yii::t('system', Yii::t('purchases', 'Half month settlement')),
			7   => Yii::t('system', Yii::t('purchases', 'Week of settlement')),
            1   => Yii::t('system', Yii::t('purchases', 'Cash settlement')),
        	99=> Yii::t('system', Yii::t('purchases', '货到付款')),
        	101=> Yii::t('system', Yii::t('purchases', '其它')),
        );
        if($type !== null){
        	return $payType[$type];
        }
        return $payType;
    }

    public static function getSexConfig() {
        return array(
            '0' => Yii::t('users', 'Man'),
            '1' => Yii::t('users', 'Female')
        );
    }

    /**
     * get product status label
     */
    public static function getProductStatusLabel($status) {
        $config = UebModel::model('product')->getProductStatusConfig();
        return isset($config[$status]) ? $config[$status] : 'unknow';
    }/**
     * get product status label
     */
    public static function getProductBakStatuesLabel($status) {
        $config = UebModel::model('product')->getProductBakStatues();
        return isset($config[$status]) ? $config[$status] : 'unknow';
    }


    /**
     * product type config
     *
     * @return data
     */
    public static function getProductTypeConfig($type=null) {
        $data = array(
            '1' => Yii::t('products', 'Common'),
            '2' => Yii::t('products', 'Bundle Sales')
        );
        if($type !== null){
        	return $data[$type];
        }else{
        	return $data;
        }
    }
    public static function getProductHotRankConfig($type=null) {
        $data = array(
            '2' => Yii::t('products', '高'),
            '1' => Yii::t('products', '中'),
			'0' => Yii::t('products', '低')
        );
        if($type !== null){
        	return $data[$type];
        }else{
        	return $data;
        }
    }
    /**
     * get product type label
     */
    public static function getProductTypeLabel($type) {
        $config = self::getProductTypeConfig();

        return $config[$type];
    }

    /**
     * product attribute show type config
     */
    public static function getAttributeShowTypeConfig($type = null) {
        $show =  array(
            'list_box'  => Yii::t('products', 'List box'),
            'check_box' => Yii::t('products', 'Check box'),
            'input'     => Yii::t('products', 'Input'),
        );
        if ( $type !== null ) {
        	return $show[$type];
        }
        return $show;
    }

    /**
     * product attribute input type config
     */
    public static function getAttributeInputTypeConfig($type = null) {
        $input = array(
            'STRING'    => Yii::t('products', 'STRING'),
            'NUMBER'    => Yii::t('products', 'NUMBER'),
            'NULL'      => Yii::t('products', 'NULL'),
        );
        if ( $type !== null ) {
        	return $input[$type];
        }
        return $input;
    }

    public static function getProductMultiConfig($type = null) {
        $config = array(
            '0'    => Yii::t('products', 'Normal'),
            '1'    => Yii::t('products', 'Multiple Attribute Item'),
            '2'    => Yii::t('products', 'Multiple Attribute Combine'),
        );
        if ( $type !== null ) {
            return $config[$type];
        }

        return $config;
    }

    public static function getProductOriginalMaterialTypeConfig($type = null){
    	$config = array(
    			'0'    => Yii::t('system', 'Please Select'),
    			'1'    => Yii::t('products', 'Packing materials'),
    			'2'    => Yii::t('products', 'Packaging'),
    	);
    	if ( $type !== null ) {
    		return $config[$type];
    	}

    	return $config;
    }

    /**
     *
     * @param string $type
     * @return 供应商类型
     * @author super
     */
    public static function getProviderTypeConfig($type = null){

    	$config = array(
    			// '1'    => Yii::t('purchases', 'Provider'),
    			// '2'    => Yii::t('purchases', 'DropShipping'),
				
    			'3'    => Yii::t('purchases', '工厂型'),
    			'4'    => Yii::t('purchases', '贸易型'),
    			'5'    => Yii::t('purchases', '个体户'),
    			'6'    => Yii::t('purchases', '其它'),
    	);
    	if ( $type !== null ) {
    		return isset($config[$type]) ? $config[$type] : 'unknow';
    	}
    	return $config;
    }
	
    public static function getProviderTypename($id){

    	$config = array(
    			'1'    => Yii::t('purchases', 'Provider'),
    			'2'    => Yii::t('purchases', 'DropShipping'),
				
    			'3'    => Yii::t('purchases', '工厂型'),
    			'4'    => Yii::t('purchases', '贸易型'),
    			'5'    => Yii::t('purchases', '个体户'),
    			'6'    => Yii::t('purchases', '其它'),
    	);
    	 // file_put_contents('tongtu.php',$id.PHP_EOL,FILE_APPEND );
    	 // file_put_contents('tongtu.php',$config[$id].PHP_EOL,FILE_APPEND );
    	return $config[$id];
    }

    public static function getYesOrNoConfig($type = null) {
    	$config = array(
    		'0'    => Yii::t('common', 'Yes'),
    		'1'    => Yii::t('common', 'No'),
    	);
    	if ( $type !== null ) {
    		return $config[$type];
    	}
    	return $config;
    }
	
	public static function getProvidersupplytype($type = null){

    	$config = array(
    			'国内小包'    => Yii::t('purchases', '国内小包'),
    			'海外仓'    => Yii::t('purchases', '海外仓'),
    			'FBA'    => Yii::t('purchases', 'FBA'),
    			
    	);
    	if ( $type !== null ) {
    		return isset($config[$type]) ? $config[$type] : 'unknow';
    	}
    	return $config;
    }
	
	public static function getProviderbusinesscategory($type = null){

    	$config = array(
    			'1'    => Yii::t('purchases', '手机及配件'),
    			'2'    => Yii::t('purchases', '电脑及周边配件'),
    			'3'    => Yii::t('purchases', '消费电子'),
    			'4'    => Yii::t('purchases', '宠物用品'),
    			'5'    => Yii::t('purchases', '工业用品'),
    			'6'    => Yii::t('purchases', '户外运动'),
    			'7'    => Yii::t('purchases', '美容健康'),
    			'9'    => Yii::t('purchases', '汽摩配'),
    			'168'    => Yii::t('purchases', '家居园艺'),
    			'266'    => Yii::t('purchases', '母婴用品'),
    			'317'    => Yii::t('purchases', '工艺用品'),
    			'465'    => Yii::t('purchases', '影视摄影'),
    			'502'    => Yii::t('purchases', '模型玩具'),
    			'527'    => Yii::t('purchases', '服装服饰'),
    			'528'    => Yii::t('purchases', 'LED'), 
    			
    	);
    	if ( $type !== null ) {
    		return isset($config[$type]) ? $config[$type] : '';
    	}
    	return $config;
    }
    /**
     * 列表页显示输入框
    * $columnName:input text name
    * $value :    input value
    * $id : key
    * $options :other option
    * author:ethan
    */
    public function showInput($columnName,$value,$id,$options){
    	echo CHTML::openTag("span", array("class"=>"left")).CHtml::textField($columnName."[$id]",$value, $options).CHTML::closeTag("span");
    }

    public function showSpan($title,$options){
    	echo CHTML::openTag("span", $options).$title.CHTML::closeTag("span");
    }

    /**
     * select button
     * $msg:string
     * $documentId :string
    * $sku:string
    * $id: string
    * selectInquireButton('click me','/purchases/purchaseinquire/index',array('class' =>'btnLook','target' =>'dialog');
    * return html
    */

    public function selectButton($msg,$url,$options=array()){
    	$string = '';
    	$string .= CHTML::openTag("span", array("class"=>"left"));
    	$string .= CHtml::link($msg, $url, $options);
    	$string .= CHTML::closeTag("span");
    	echo $string;
    }
    /**
     * 显示供应商id 隐藏域,purchases/views/purchaserequire/index.php
    * $columnName:input text name
    * $value :    input value
    * $id : key
    * $options :other option
    */
    public function showTextHidden($columnName,$value,$id,$options){
    	echo CHtml::hiddenField($columnName."[$id]",$value, $options);
    }

    /**
     * pay status
     * APPLY_NO 	= 0;
     * APPLY_PART 	= 1;
     * PAY_NO 		= 2;
     * PAY_HAVE 	= 3;
     * PAY_PART 	= 4;
     */
    public static function getPayStatus($type = null){
    	$config = array(
    		self::APPLY_NO    	=> Yii::t('purchases', 'To be applypayment'),
    		self::APPLY_PART 	=> Yii::t('purchases', 'Apply part'),
    		self::PAY_NO    	=> Yii::t('purchases', 'No pay'),
    		self::PAY_HAVE   	=> Yii::t('purchases', 'Have pay'),
    		self::PAY_PART    	=> Yii::t('purchases', 'Part pay'),


    	);
    	if ( $type !== null ) {
    		return $config[$type];
    	}
    	return $config;
    }
    /**
     * pay status
     * PAY_NO    未付款
     * PAY_HAVE  已付款
     * @author Derek
     */
    public static function getPayStatusPart($type = null){
    	$config = array(
    			self::PAY_NO    	=> Yii::t('purchases', 'No pay'),
    			self::PAY_HAVE   	=> Yii::t('purchases', 'Have pay'),

    	);
    	if ( $type !== null ) {
    		return $config[$type];
    	}
    	return $config;
    }
    /**
     * split the date format
     * @param date $date
     * @param $ymd:true or false ,get Y-m-d only
     * @return string
     */
    public function splitDate($date='0000-00-00 00:00:00',$ymd=false){
    	if(empty($date) || $date =='0000-00-00 00:00:00') return '-';
    	$dateArr = explode(' ',$date);
    	return $ymd==true ? $dateArr[0] : $dateArr[0].'<br>'.$dateArr[1];
    }

    /**
     *
     * @param arrat $val
     * @return string
     */
    public function showHtmlByDataType($val=array()){
    	$html = '';
    	$html .= CHtml::label($val['column_title'].': ',$val['column_field'],array('style'=>'width:auto;text-align:right;font-weight:bold;'));
    	switch ($val['data_type']){
    		case ExcelSchemeColumn::_DATETIME:
    			if ($val['default_date']>0){
    				$default_date = MHelper::getDateDiff($val['default_date']);
    			}
    			$default_date_s = $_REQUEST['is_condition'][$val['db_name'].'.'.$val['table_name']][$val['column_field']][0]
    							? $_REQUEST['is_condition'][$val['db_name'].'.'.$val['table_name']][$val['column_field']][0] : $default_date[0];
    			$default_date_e = $_REQUEST['is_condition'][$val['db_name'].'.'.$val['table_name']][$val['column_field']][1]
    							? $_REQUEST['is_condition'][$val['db_name'].'.'.$val['table_name']][$val['column_field']][1] : $default_date[1];
    			$html .= CHtml::textField('is_condition['.$val['db_name'].'.'.$val['table_name'].']['.$val['column_field'].']'.'[0]', $default_date_s,
    			array('class'=>'date textInput','dateFmt'=>'yyyy-MM-dd HH:mm:ss'));
    			$html .= '<span style="padding:4px;">-</span>';
    			$html .= CHtml::textField('is_condition['.$val['db_name'].'.'.$val['table_name'].']['.$val['column_field'].']'.'[1]', $default_date_e,
    					array('class'=>'date textInput','dateFmt'=>'yyyy-MM-dd HH:mm:ss'));
    			$html .= CHtml::hiddenField('is_condition['.$val['db_name'].'.'.$val['table_name'].']['.$val['column_field'].']'.'[2]', 'timestamp',
    					array('class'=>'textInput','readonly'=>'readonly'));
    			break;
    		case ExcelSchemeColumn::_CHECKBOX:
    			$curModel = MHelper::getModelByTableName($val['table_name']);
    			$data = $curModel->queryPairs(array($val['column_field'],$val['column_field']));
    			if($data){
    				foreach ($data as $k=>$v){
    					$flag = isset($_REQUEST['is_checkbox'][$val['db_name'].'.'.$val['table_name']][$val['column_field']][$k]) ? true :false;
    					$html .= CHtml::checkBox( 'is_checkbox['.$val['db_name'].'.'.$val['table_name'].']['.$val['column_field'].']['.$k.']', $flag,
    							array('value' =>$v,'id' =>$val['column_field'].$k) );	//tan 9.19
    					$html .= $v.'&nbsp;&nbsp;';
    				}
    			}
    			break;
    		case ExcelSchemeColumn::_SELECT:
    			$curModel = MHelper::getModelByTableName($val['table_name']);
    			//$primaryKey = $curModel->getMetaData()->tableSchema->primaryKey;
    			$data = $curModel->queryPairs(array($val['column_field'],$val['column_field']));
    			$html .= CHtml::dropDownList('is_condition['.$val['db_name'].'.'.$val['table_name'].']['.$val['column_field'].']',
    					$_REQUEST['is_condition'][$val['db_name'].'.'.$val['table_name']][$val['column_field']],
    					$data,array('empty' => Yii::t('system','Please Select')));
    			break;
    		case ExcelSchemeColumn::_NUMS:
    			$html .= CHtml::textField('is_condition['.$val['table_name'].']['.$val['column_field'].']'.'[0]',
    					$_REQUEST['is_condition'][$val['db_name'].'.'.$val['table_name']][$val['column_field']][0],array('class'=>'textInput'));
    			$html .= '<span style="padding:4px;">-</span>';
    			$html .= CHtml::textField('is_condition['.$val['db_name'].'.'.$val['table_name'].']['.$val['column_field'].']'.'[1]',
    					$_REQUEST['is_condition'][$val['db_name'].'.'.$val['db_name'].'.'.$val['table_name']][$val['column_field']][1],array('class'=>'textInput'));
    			break;
    		case ExcelSchemeColumn::_INPUT:
    			$html .= CHtml::textField('is_condition['.$val['db_name'].'.'.$val['table_name'].']['.$val['column_field'].']',
    			$_REQUEST['is_condition'][$val['db_name'].'.'.$val['table_name']][$val['column_field']],array('class'=>'textInput'));
    			break;
    		default:
    			$html = '';
    	}
    	return $html;
    }





    	/**
    	 * 检测字符串是否包含某语言字符
    	 * @author Gordon
    	 * 2013-11-07
    	 */
    	public function checkLang($string,$lang='China'){
    		$pattern = $this->getLangPreg($lang);//获取正则表达式
    		if(!$pattern){
    			return false;
    		}
    		preg_match($pattern,$string,$arr);
    		if(empty($arr)){//如果数组为空，则没匹配到
    			return false;
    		}else{
    			return true;
    		}
    	}

    	/**
    	 * 根据Unicode范围获取语言的正则表达式
    	 * 西里尔文(俄文):0400-052f;中文:u4e00-u9fa5;日文:u0800-u4e00;
    	 * @author Gordon
    	 * 2013-11-07
    	 */
    	public function getLangPreg($lang){
    		$preg_arr = array(
    				'Russia' => '/[\x{0400}-\x{052f}]+/siu',
    				//'China' => '/^[\x{4e00}-\x{9fa5}]+$/u',
    				'China' => '/[\x{4e00}-\x{9fa5}]+/u',
    		);
    		return $preg_arr[$lang];
    	}

	/** 获取任务类型
	 * @return array $config
	 */
	public static  function getTaskType($type = null)
	{
		$result = [
			'1' => Yii::t('system', '需求'),
			'2'	=> Yii::t('system', '开发'),
			'3'	=> Yii::t('system', '设计'),
			'4'	=> Yii::t('system', '研究'),
			'5'	=> Yii::t('system', '测试'),
            '6' => Yii::t('system', '其他'),

		];
		if (isset($type))
		{
			return $result[$type];
		}
		return $result;
	}
	/**
	 *
	 * 获取任务状态
	 */

	public static function  getTaskStatus()
	{
		$result = [
			'1' => Yii::t('system', '未开始'),
			'2' => Yii::t('system', '开发中'),
			'3' => Yii::t('system', '待验收'),
			'4' => Yii::t('system', '已关闭'),
			'5' => Yii::t('system', '已取消'),
			'6' => Yii::t('system', '已上线'),
			'7' => Yii::t('system', '验收中'),
		];

		return $result;
	}
	/**
	 *
	 * 获取任务状态与颜色
	 *
	 */
	public  static  function  getTaskStatusColor($status)
	{
		$Color = self::getTaskStatus();
		if (isset($status))
		{
			if ($status == '2')
			{
					echo '<font color="red" >' . $Color[$status] . '</font>';
			} elseif ($status == '3') {
					echo '<font color="green" >' . $Color[$status] . '</font>';
			} elseif ($status == '4') {
					echo '<font color="blue" >' . $Color[$status] . '</font>';
			} elseif ($status == '5') {
					echo '<font color="#a52a2a" >' . $Color[$status] . '</font>';
			} elseif ($status == '6') {
					echo '<font color="#8a2be2" >' .$Color[$status].'</font>';
			} else {
					echo $Color[$status];
			}

		}
	}
    /**
	 *
	 * 获取解决方法
	 */

	public  static  function  getRelution($type=null)
	{
		$result = [
			'1' => Yii::t('system', '设计如此'),
			'2' => Yii::t('system', '重复任务'),
			'4' => Yii::t('system', '外部原因'),
			'5' => Yii::t('system', '已解决'),
			'6' => Yii::t('system', '无法重现'),
			'7' => Yii::t('system', '延期处理'),
			'8' => Yii::t('system', '不予解决'),
		];
		if (isset($type))
		{
			return $result[$type];
		}
		return $result;

	}


	/**
	 * 代码调试
	 */
	public static function dump()
	{
		$args = func_get_args();
		header('Content-type: text/html; charset=utf-8');
		echo "<pre>\n---------------------------------调试信息---------------------------------\n";
		foreach ($args as $value) {
			if (is_null($value)) {
				echo '[is_null]';
			} elseif (is_bool($value) || empty($value)) {
				var_dump($value);
			} else {
				print_r($value);
			}
			echo "\n";
		}
		$trace = debug_backtrace();
		$next = array_merge(
			array(
				'line' => '??',
				'file' => '[internal]',
				'class' => null,
				'function' => '[main]'
			), $trace[0]
		);

		/* if(strpos($next['file'], ZEQII_PATH) === 0){
          $next['file'] = str_replace(ZEQII_PATH, DS . 'library' . DS, $next['file']);
          }elseif (strpos($next['file'], ROOT_PATH) === 0){
          $next['file'] = str_replace(ROOT_PATH, DS . 'public' . DS, $next['file']);
          } */
		echo "\n---------------------------------输出位置---------------------------------\n\n";
		echo $next['file'] . "\t第" . $next['line'] . "行.\n";
		if (in_array('debug', $args)) {
			echo "\n<pre>";
			echo "\n---------------------------------跟踪信息---------------------------------\n";
			print_r($trace);
		}
		echo "\n---------------------------------调试结束---------------------------------\n";
		exit();
	}


	public  static  function  publishedStatus($type)
	{
		$result = [
			'0' => Yii::t('system', '待刊登'),
			'1' => Yii::t('system', '刊登中'),
            '2' => Yii::t('system', '已刊登'),
            '3' => Yii::t('system', '刊登失败')
		];
		return $result[$type];

	}

	public static function formatDate($date){
		if($date){
			$date = date('Y-m-d H:i:s',$date);
		}else{
			$date = '';
		}
		return $date;
	}
	/*产品下架原因*/
	public static function offTheShelf($wsDisplay){
		$str = '';
		switch ($wsDisplay){
			case'expire_offline':
				$str = '过期下架';
				break;
			case'user_offline':
				$str = '用户下架';
				break;
			case 'violate_offline':
				$str = '违规下架';
				break;
			case 'punish_offline':
				$str = '交易违规下架';
				break;
			case 'degrade_offline':
				$str = '降级下架';
				break;
			default:
				$str = '无';
				break;
		}
		return $str;
	}

	public static function  sortArrByField(&$array, $field, $desc = false){
		$fieldArr = array();
		foreach ($array as $k => $v) {
			$fieldArr[$k] = $v[$field];
		}
		$sort = $desc == false ? SORT_ASC : SORT_DESC;
		array_multisort($fieldArr, $sort, $array);
	}

	public static function getEbayListingStatusCode($type)
	{
		$status = array(
			0=>'子sku不直接刊登',
			1=>'待编辑',
			2=>'待刊登',
			3=>'刊登失败',
			4=>'已刊登有警告(通过立即刊登方式刊登)',
			5=>'已刊登有警告(通过刊登队列刊登)',
			6=>'已刊登无警告(通过立即刊登方式刊登)',
			7=>'已刊登无警告(通过刊登队列刊登)',
			9=>'刊登中',   //任务在执行组装xml和提交ebay中
			11=> '正在排队刊登'
		);
		if(!isset($type))
			return $status;
		if(is_numeric($type))
			return $status[$type];
		return null;

	}

	public static function selectAsArray($model,$field = '*',$condition = '',$distinct=false,$group = '',$order = '',$limit='',$having='')
	{
		if(is_string($model))
		{
			$model = UebModel::model($model);
		}
		if(!($model instanceof CActiveRecord))
			return array();

		if(array_key_exists('is_delete',$model->attributes))
			$defaultCondition = 'is_delete=0';
		else
			$defaultCondition = 1;
		if(!empty($condition))
		{
			$defaultCondition = $defaultCondition == 'is_delete=0' ? $defaultCondition.' and '.$condition : $condition;
		}
		$command = $model->getDbConnection()->createCommand();
		if($distinct)
			$command->selectDistinct($field);
		else
			$command->select($field);
		$command->from($model->tableName())
			->where($defaultCondition);
		if(!empty($group))
		{
			$command->group($group);
			if(!empty($having))
			{
				$command->having($having);
			}
		}
		if(!empty($order))
			$command->order($order);
        if(!empty($limit))
            $command->limit($limit);
		return $command->queryAll();
	}

	public static function getEbayPlatformBySku($sku,$type)
	{
		$platforms = array_flip(array_column(self::selectAsArray('Productskuplatform','platform','sku="'.$sku.'" and platform in ("ebay","ebayout")'),'platform'));
		$platforms = array_intersect_key(Productskuplatform::model()->getPlatformLableName(),$platforms);
		ksort($platforms);
		switch(strtolower($type))
		{
			case 'string':
				return implode('<br/>',$platforms);
			case 'array':
				return $platforms;
		}
	}

	public static function transformByIds($model,$ids,$field,$separator=',')
	{
		if(is_string($ids))
		{
			$condition = 'id in ("'.str_replace(',','","',$ids).'")';
		}
		if(is_array($ids))
		{
			$condition = 'id in ("'.explode('","',$ids).'")';
		}
		if(isset($condition))
		{
			return implode($separator,array_column(self::selectAsArray($model,$field,$condition),$field));
		}
		return null;
	}


	/* 计算sku产品的销售价格
	 * @param1 string sku或ProductModel
	 * @param2 array array('siteid'=0,'currency'=>'USD')
	 * return array array('price'=>12,'currency'=>USD)
	 */
	public static function calculatePrice($skuOrModel,$siteArray)
	{
		if(is_string($skuOrModel))
			$productModel = UebModel::model('Product')->find('sku=:sku',array(':sku'=>$skuOrModel));
		else
			$productModel = $skuOrModel;
		if(!($productModel instanceof Product))
			return false;
		$weight = $productModel->gross_product_weight < $productModel->product_weight ? $productModel->product_weight:$productModel->gross_product_weight;
		if($weight < 200)
		{
			$weight = $weight + 15;
		}
		else
		{
			$weight = $weight +20;
		}
		$weight = $weight<50 ? 50 : $weight;
		if($siteArray['siteid'] == 2 || $siteArray['siteid'] == 210)
		{
			$currency = 'CAD';
		}
		else
		{
			if(isset($siteArray['currency']))
			{
				$currency = $siteArray['currency'];
			}
			else
			{
				$currency = UebModel::model('EbaySites')->find('siteid=:siteid',array(':siteid'=>$siteArray['siteid']))->currency;
			}
		}
		$rate = EbayProductsAssign::$currencyRate[$currency];
		if(empty($rate))
			return array('price'=>0,'currency'=>$currency);
		$cost = (empty($productModel->last_price) || $productModel->last_price == 0) ? $productModel->product_cost:$productModel->last_price;

		$standard = ($cost+$weight/1000*80)/0.85/0.8;
		$reference = 8*(EbayProductsAssign::$currencyRate['USD']); //8美元乘以汇率
		if($standard >= $reference)
			$price = round(($cost+$weight/1000*80)/0.86/$rate/0.8+0.3,2);
		else
			$price = round(($cost+$weight/1000*80)/0.83/$rate/0.8,2);
		return array('price'=>$price,'currency'=>$currency);
	}

	//获取关键词和5点
	public static function getKeywordAndFiveAndTitle($skuOrModel,$separate='\n',$language='english')
	{
		if(is_string($skuOrModel))
			$productDescModel = UebModel::model('Productdesc')->find('language_code=:language_code and sku=:sku',array(':sku'=>$skuOrModel,':language_code'=>$language));
		else
			$productDescModel = $skuOrModel;
		if(!($productDescModel instanceof Productdesc))
			return false;
		if(empty($productDescModel))
		{
			return null;
		}
		$return['title'] = trim($productDescModel->title);
		$return['keyword'] = trim($productDescModel->included);
		$sequence = 1;
		if(!empty($productDescModel->amazon_keyword1))
		{
			$return['five_name'] = $sequence.'、'.trim($productDescModel->amazon_keyword1);
			$sequence++;
		}

		if(!empty($productDescModel->amazon_keyword2))
		{
			if(empty($return['five_name']))
				$return['five_name'] = $sequence.'、'.trim($productDescModel->amazon_keyword2);
			else
				$return['five_name'] .= $separate.$sequence.'、'.trim($productDescModel->amazon_keyword2);
			$sequence++;
		}
		if(!empty($productDescModel->amazon_keyword3))
		{
			if(empty($return['five_name']))
				$return['five_name'] = $sequence.'、'.trim($productDescModel->amazon_keyword3);
			else
				$return['five_name'] .= $separate.$sequence.'、'.trim($productDescModel->amazon_keyword3);
			$sequence++;
		}
		if(!empty($productDescModel->amazon_keyword4))
		{
			if(empty($return['five_name']))
				$return['five_name'] = $sequence.'、'.$productDescModel->amazon_keyword4;
			else
				$return['five_name'] .= $separate.$sequence.'、'.trim($productDescModel->amazon_keyword4);
			$sequence++;
		}
		if(!empty($productDescModel->amazon_keyword5))
		{
			if(empty($return['five_name']))
				$return['five_name'] = $sequence.'、'.$productDescModel->amazon_keyword5;
			else
				$return['five_name'] .= $separate.$sequence.'、'.trim($productDescModel->amazon_keyword5);
		}
		return $return;
	}
	/*获取绑定的账号*/
	public static function getAccountList($depid,$leader = 0){
		$account = array();
		if(!UebModel::model('user')->isAdmin() && !$leader){
			$account_config = Yii::app()->db->createCommand()
				->select('account_id')
				->from('ueb_system.ueb_aliexpress_account_config')
				->where("user_id = {$depid}")
				->queryAll();
			if(!empty($account_config)){
				foreach ($account_config as $value){
					$ids[] = $value['account_id'];
				}
			}
			if(!empty($ids)){
				$ids = rtrim(implode(',',$ids),',');
				/*获取绑定的速卖通账号*/
				$account_list = Yii::app()->db->createCommand()
					->select('account,id')
					->from('ueb_system.ueb_aliexpress_account')
					->where("id IN ($ids) AND status = 1")
					->queryAll();
				if(!empty($account_list)){
					foreach ($account_list as &$value){
						$account[$value['id']] = $value['account'];
					}
				}
			}

		}else{
			$account_list = Yii::app()->db->createCommand()
				->select('id,account')
				->from('ueb_system.ueb_aliexpress_account')
				->where('status = 1')
				->queryAll();
			if(!empty($account_list)){
				foreach ($account_list as $value){
					$account[$value['id']] = $value['account'];
				}
			}
		}
		return $account;
	}
	//如果是管理员则查看所有信息，如果不是则查看个人信息
	public  static function getCrentDep($depid){
		$ids = array();
		if(!UebModel::model('user')->isAdmin()){
			$account_config = Yii::app()->db->createCommand()
				->select('account_id')
				->from('ueb_system.ueb_aliexpress_account_config')
				->where('user_id = '.$depid)
				->queryAll();

			if(!empty($account_config)){
				foreach ($account_config as $value){
					$ids[] = $value['user_id'];
				}
			}
		}else{
			$account_list = Yii::app()->db->createCommand()
				->select('id')
				->from('ueb_system.ueb_aliexpress_account')
				->queryAll();
			if(!empty($account_list)){
				foreach ($account_list as $value){
					$ids[] = $value['id'];
				}
			}
		}
		return $ids;
	}


	//model的rules验证出错时，将错误转化提示到前端页面。
	public static function getModelErrors($model,&$errorInfo)
	{
		$error = $model->getErrors();
		if(!empty($error))
		{
			foreach ($error as $field=>$content)
			{
				$errorInfo .= $model->tableName().'表的字段'.$field.'出错：'.implode('',$content);
			}
		}
	}
	//获取星期方法
	public static function getWeek($date){
		//强制转换日期格式
		$date_str=date('Y-m-d',strtotime($date));
		//封装成数组
		$arr=explode("-", $date_str);
		//参数赋值
		//年
		$year=$arr[0];
		//月，输出2位整型，不够2位右对齐
		$month=sprintf('%02d',$arr[1]);
		//日，输出2位整型，不够2位右对齐
		$day=sprintf('%02d',$arr[2]);
		//时分秒默认赋值为0；
		$hour = $minute = $second = 0;
		//转换成时间戳
		$strap = mktime($hour,$minute,$second,$month,$day,$year);
		//获取数字型星期几
		$number_wk=date("w",$strap);
		//自定义星期数组
		$weekArr=array("星期日","星期一","星期二","星期三","星期四","星期五","星期六");
		//获取数字对应的星期
		return $number_wk;
	}
	/*缩小图片大小，不是尺寸大小，是存储大小
	 *@param1 $path string 图片路径
	 *@param2 $maxSize number 图片缩小后不能大于此值，单位 bytes,默认1M=1048576bytes
	 *@return bool|string 缩小成功时返回缩小后图片的路径,不成功时返回false
	*/
	public static function decreaseImageMemorySize($path,$newPath = null,$maxSize = 1048576)
	{
		if(is_file($path))
		{
			$actualSize = filesize($path);
			if($actualSize > $maxSize)
			{
				$pathInfo = pathinfo($path);
				if(empty($newPath))
				{
					$footNames = explode('-',$pathInfo['filename']);
					$newPath = 'upload/image/decrease_size/'.$footNames[0].'/'.$pathInfo['filename'].image_type_to_extension(IMAGETYPE_JPEG,true);
				}
				if(is_file($newPath) && filesize($newPath) <= $maxSize)
				{
					return $newPath;
				}
				$newDir = dirname($newPath);
				if(!file_exists($newDir))
				{
					mkdir($newDir,0760,true);
				}
				else
					chmod($newDir,0760);
				$name = strtolower($pathInfo['extension']);
				$name = $name == 'jpg' ? 'jpeg':$name;
				$name = 'imagecreatefrom'.$name;
				$im = $name($path);
				$quality = 100;
				do
				{
					imagejpeg($im,$newPath,$quality);
					$actualSize = filesize($newPath);
					$quality--;
				}while($actualSize > $maxSize && $quality > -1);
				imagedestroy($im);
				if($actualSize > $maxSize)
					return false;
				else
					return $newPath;
			}
			else
				return $path;
		}
		else
			return false;
	}

	/*生成分页条
	 *@param array 分页信息 格式array('totalNum'=>总条数,'totalPage'=>总页数,'currentPage'=>当前页数);
	 *@return string 分页条的HTML
	 */
	public static function renderPaging($pagingInfo)
	{
		$return = <<<ABC
		<div class="paging_area">
			<span>一共<span class="total_num">{$pagingInfo['totalNum']}</span>条,共<span class="total_page">{$pagingInfo['totalPage']}</span>页</span>
			<button class="get_info_by_page" mark="first">首页</button>
			<button class="get_info_by_page" mark="prev">上一页</button>
			<span class="detail_page_code">
ABC;

		$return .= self::getDetailPageCodes($pagingInfo);
		$return .= <<<ABC
        </span>
			<button class="get_info_by_page" mark="next">下一页</button>
			<button class="get_info_by_page" mark="last">尾页</button>
			<input type="text" name="page_code" style="width:20px;margin: 0px 3px 0px 0px">
			<button class="get_info_by_page" mark="jump">GO</button>
		</div>
ABC;
		return $return;
	}
	/*
	 * 分页条中的数字获取
	 * @param1 $pagingInfo array 分页信息 格式 array('totalPage'=>总页数,'currentPage'=>当前页数);
	 * @param2 $type string 取值 html|array ,当html时，返回html;当array,返回数组；
	 * @return string|array 根据$type返回相应的数据。当返回数组时返回的格式是 array('code'=>array(1,2,3...),'current'=>3)。
	 */
	public static function getDetailPageCodes($pagingInfo,$type = 'html')
	{
		switch($type)
		{
			case 'html':
				$return = '';
				break;
			case 'array':
				$return = array();
				break;
		}
		if($pagingInfo['totalPage'] < 1)
			return $return;
		$nextNum = $pagingInfo['totalPage'] - $pagingInfo['currentPage'];
		if($pagingInfo['currentPage'] > 5)
		{
			if($nextNum > 5)
			{
				$preCount = 4;
				$nextCount = 5;
			}
			else
			{
				$nextCount = $nextNum;
				$supplement = 5 - $nextNum;
				$preMax = 4 + $supplement;
				$preCount = ($pagingInfo['currentPage']- $preMax) > 0 ? $preMax : $pagingInfo['currentPage'] - 1;
			}
		}
		else
		{
			if($nextNum > 5)
			{
				$preCount = $pagingInfo['currentPage'] - 1;
				$supplement = 4 - $preCount;
				$nextMax = 5 + $supplement;
				$nextCount = ($nextNum - $nextMax) > 0 ? $nextMax : $nextNum;
			}
			else
			{
				$preCount = $pagingInfo['currentPage'] - 1;
				$nextCount = $nextNum;
			}
		}
		$preValue = $pagingInfo['currentPage']-1;

		While($preCount > 0)
		{
			switch($type)
			{
				case 'html':
					$return = "<a class=\"get_info_by_page\">{$preValue}</a>".$return;
					break;
				case 'array':
					$return['code'][] = $preValue;
					break;
			}
			$preCount--;
			$preValue--;
		}
		switch($type)
		{
			case 'html':
				$return .= "<a class='current_page get_info_by_page'>{$pagingInfo['currentPage']}</a>";
				break;
			case 'array':
				$return['code'][] = $pagingInfo['currentPage'];
				$return['current'] = $pagingInfo['currentPage'];
				break;
		}
		$nextValue = $pagingInfo['currentPage']+1;
		while ($nextCount > 0)
		{
			switch($type)
			{
				case 'html':
					$return .= "<a class=\"get_info_by_page\">{$nextValue}</a>";
					break;
				case 'array':
					$return['code'][] = $nextValue;
					break;
			}
			$nextCount--;
			$nextValue++;
		}
		return $return;
	}

    /**
	 * UTC时间转其他当地时区时间
	 * eg:switchTime('2017-06-19 21:30:00', 'UTC', 'US/Pacific-New')
     * @param $time
     * @param $stz
     * @param $tdz
     * @param string $fmt
     * @return string
     */
    public static function switchTime($time, $stz, $tdz, $fmt = 'Y-m-d H:i:s')
    {
        $dt = new DateTime($time, new DateTimeZone($stz));
        $dt->setTimezone(new DateTimeZone($tdz));

        return $dt->format($fmt);
    }

    /**
	 * 转换货币符号
     * @param $id 账号ID
     * @return string
     */
    public static function getCurrencySymbol($id){
		$site=AmazonAccount::model('AmazonAccount')->findByPk($id)->site;
        $europe=array('de', 'sp', 'fr', 'it');

        switch ($site){
            case in_array($site,$europe):
                $cs='EUR';
                break;
            case 'us';
                $cs='USD';
                break;
            case 'jp';
                $cs='JPY';
                break;
            case 'ca';
                $cs='CAD';
                break;
            case 'uk';
                $cs='GBP';
                break;
			case 'mx';
                $cs='MXN';
                break;
            case 'au';
                $cs='AUD';
                break;
			default:
                $cs='USD';
        }
        return $cs;
    }
    public static function getCurrencySymbolbysite($site){
        $europe=array('de', 'sp', 'fr', 'it');

        switch ($site){
            case in_array($site,$europe):
                $cs='EUR';
                break;
            case 'us';
                $cs='USD';
                break;
            case 'jp';
                $cs='JPY';
                break;
            case 'ca';
                $cs='CAD';
                break;
            case 'uk';
                $cs='GBP';
                break;
            case 'mx';
                $cs='MXN';
                break;
            case 'au';
                $cs='AUD';
                break;
            default:
                $cs='USD';
        }
        return $cs;
    }
	/**
	 * @desc 同仓库数据交互的解密签名算法
	 * @param unknown $param
	 * @param string $sign
	 */
	public static function stockUnAuth($param = array(), $sign = ''){
		$data = array('error'=>-1);
		// 检查 key值
		if($param['key'] != Yii::app()->params['UEB_STOCK_KEYID'])
		{
			$data['message'] = 'key有错';
			return $data;
		}
		// 查询时间
		if(abs(time() - $param['timestamp']) > Yii::app()->params['UEB_STOCK_TIMESTAMP'])
		{
			$data['message'] = '时间超时';
			return $data;
		}
		ksort($param,SORT_REGULAR);
		$urlStr = http_build_query($param,'yibai_','&',PHP_QUERY_RFC1738);
		$securityStr = md5(Yii::app()->params['UEB_STOCK_SECURIRY'].$urlStr.Yii::app()->params['UEB_STOCK_SECURIRY'], false);

		if($securityStr != $sign)
		{
			$data['message'] = '签名出错';
			return $data;
		}
		$data = array('error'=>0,'message'=>'success');
		return $data;
	}
	/**
	 * @desc 同仓库数据交互的加密签名算法
	 * @return array
	 */
	public static function stockAuth(){
		$data = array('error'=>-1);

		//设置param数组的值
		$param['key'] = Yii::app()->params['UEB_STOCK_KEYID'];
		$param['timestamp'] = time();
		$param['ip'] = '';

		ksort($param,SORT_REGULAR);
		$urlStr = http_build_query($param,'yibai_','&',PHP_QUERY_RFC1738);
		$securityStr = md5(Yii::app()->params['UEB_STOCK_SECURIRY'].$urlStr.Yii::app()->params['UEB_STOCK_SECURIRY'], false);

		if(!empty($securityStr)){
			$data['param'] = $param;
			$data['sign'] = $securityStr;
			$data['error'] = 1;
		}

		return $data;
	}

	public static function arrayMultisort($data){
//		$data = array_flip($data);
//		foreach ($data as $k =>$v) {
//			$w[$k] = self::getStrOne($v);
//		}
//		array_multisort($w,SORT_FLAG_CASE,SORT_ASC,$data);
//		$data = array_flip($data);
		return $data;
	}
	/**
	 * 取汉字首字母
	 * @param string $str 字符串
	 * @return array
	 */
	public static function getStrOne($str){
		if(empty($str)) return '';

		$fchar = ord($str{0});
		if($fchar >= ord('A') && $fchar <= ord('z')) return strtoupper($str{0});

		$s1 = iconv('UTF-8','gb2312',$str);
		$s2 = iconv('gb2312','UTF-8',$s1);
		$s = $s2==$str ? $s1 : $str;
		$asc = ord($s{0})*256+ord($s{1})-65536;

		if($asc>=-20319 && $asc<=-20284) return 'A';
		if($asc>=-20283 && $asc<=-19776) return 'B';
		if($asc>=-19775 && $asc<=-19219) return 'C';
		if($asc>=-19218 && $asc<=-18711) return 'D';
		if($asc>=-18710 && $asc<=-18527) return 'E';
		if($asc>=-18526 && $asc<=-18240) return 'F';
		if($asc>=-18239 && $asc<=-17923) return 'G';
		if($asc>=-17922 && $asc<=-17418) return 'H';
		if($asc>=-17417 && $asc<=-16475) return 'J';
		if($asc>=-16474 && $asc<=-16213) return 'K';
		if($asc>=-16212 && $asc<=-15641) return 'L';
		if($asc>=-15640 && $asc<=-15166) return 'M';
		if($asc>=-15165 && $asc<=-14923) return 'N';
		if($asc>=-14922 && $asc<=-14915) return 'O';
		if($asc>=-14914 && $asc<=-14631) return 'P';
		if($asc>=-14630 && $asc<=-14150) return 'Q';
		if($asc>=-14149 && $asc<=-14091) return 'R';
		if($asc>=-14090 && $asc<=-13319) return 'S';
		if($asc>=-13318 && $asc<=-12839) return 'T';
		if($asc>=-12838 && $asc<=-12557) return 'W';
		if($asc>=-12556 && $asc<=-11848) return 'X';
		if($asc>=-11847 && $asc<=-11056) return 'Y';
		if($asc>=-11055 && $asc<=-10247) return 'Z';
		return '~';
	}

	/*
	 * 判断Ebay listing标题是否重复
	 * @param1 string $title 标题
	 * @param2 int $siteid ebay站点
	 * @param3 string $idType 第4个参数的类型，取值：ebaylisting、ebayonlinelisting、itemid
	 * @param4 int|string $id 当$idType为ebaylisting，此参数为EbayListing模型ID；当$idType为ebayonlinelisting，此参数为Ebayonlinelisting模型ID;当$idType为itemid，此参数为ItemID值
	 * 注意：$title和$siteid 与 $idType和$id 二组参数必需至少传一组参数，当传入$idType和$id时会排除自己这条数据判断其他数据标题是否存在。
	 * @return bool 存在返回true,不存在返回false
	 */
	public static function ebayTitleExist($title,$siteid,$idType,$id)
	{
		switch(strtolower($idType))
		{
			case 'ebaylisting':
				if(!empty($id))
				{
					if(empty($title) || !is_numeric($siteid))
					{
						$ebayListingModel = UebModel::model('EbayListing')->find(array('select'=>'sell_title,siteid,item_id','condition'=>'id=:id','params'=>array(':id'=>$id)));
						if(empty($ebayListingModel))
						{
							return false;
						}
						$title = $ebayListingModel->sell_title;
						$siteid = $ebayListingModel->siteid;
					}
					if(UebModel::model('EbayListing')->exists('sell_title=:sell_title and siteid=:siteid and id<>:id',array(':sell_title'=>$title,':siteid'=>$siteid,':id'=>$id)))
					{
						return true;
					}
					else
					{
						if(empty($ebayListingModel))
							$ebayListingModel = UebModel::model('EbayListing')->find(array('select'=>'item_id','condition'=>'id=:id','params'=>array(':id'=>$id)));
						$itemId = $ebayListingModel->item_id;
					}
				}
				else
				{
					if(UebModel::model('EbayListing')->exists('sell_title=:sell_title and siteid=:siteid',array(':sell_title'=>$title,':siteid'=>$siteid)))
					{
						return true;
					}
				}
				if(empty($itemId))
				{
					$itemIdCondition = '';
				}
				else
				{
					$itemIdCondition = " and itemid='$itemId'";
				}
				return UebModel::model('Ebayonlinelisting')->exists('siteid=:siteid and title=:title'.$itemIdCondition,array(':siteid'=>$siteid,':title'=>$title));

				break;
			case 'ebayonlinelisting':
				if(empty($id))
				{
					if(UebModel::model('Ebayonlinelisting')->exists('title=:title and siteid=:siteid',array(':title'=>$title,':siteid'=>$siteid)))
					{
						return true;
					}
				}
				else
				{
					if(empty($title) || !is_numeric($siteid))
					{
						$ebayonlinelistingModel = UebModel::model('Ebayonlinelisting')->find(array('select'=>'title,siteid,itemid','condition'=>'id=:id','params'=>array(':id'=>$id)));
						if(empty($ebayListingModel))
						{
							return false;
						}
						$title = empty($title) ? $ebayonlinelistingModel->sell_title:$title;
						$siteid = is_numeric($siteid) ? $siteid:$ebayonlinelistingModel->siteid;
					}
					if(UebModel::model('Ebayonlinelisting')->exists('title=:title and siteid=:siteid and id<>:id',array(':title'=>$title,':siteid'=>$siteid,':id'=>$id)))
					{
						return true;
					}
					else
					{
						if(empty($ebayonlinelistingModel))
							$ebayonlinelistingModel = UebModel::model('Ebayonlinelisting')->find(array('select'=>'itemid','condition'=>'id=:id','params'=>array(':id'=>$id)));
						$itemId = $ebayonlinelistingModel->itemid;
					}
				}
				if(empty($itemId))
				{
					$itemIdCondition = '';
				}
				else
				{
					$itemIdCondition = " and item_id='$itemId'";
				}
				return UebModel::model('EbayListing')->exists('siteid=:siteid and sell_title=:sell_title'.$itemIdCondition,array(':siteid'=>$siteid,':sell_title'=>$title));
				break;
			case 'itemid':
				if(!empty($id))
				{
					if(empty($title) || !is_numeric($siteid))
					{
						$dataModel = UebModel::model('EbayListing')->find(array('select'=>'sell_title,siteid','condition'=>'item_id=:item_id','params'=>array(':item_id'=>$id)));
						if(empty($dataModel))
						{
							$dataModel = UebModel::model('Ebayonlinelisting')->find(array('select'=>'title,siteid','condition'=>'itemid=:itemid','params'=>array(':itemid'=>$id)));
							if(empty($dataModel))
							{
								return false;
							}
							else
							{
								$title = empty($title) ? $dataModel->title:$title;
								$siteid = is_numeric($siteid) ? $siteid:$dataModel->siteid;
							}
						}
						else
						{
							$title = empty($title) ? $dataModel->sell_title:$title;
							$siteid = is_numeric($siteid) ? $siteid:$dataModel->siteid;
						}
					}
					if(UebModel::model('EbayListing')->exists('sell_title=:sell_title and siteid=:siteid and item_id<>:item_id',array(':sell_title'=>$title,':siteid'=>$siteid,':item_id'=>$id)))
					{
						return true;
					}
					else
					{
						return UebModel::model('Ebayonlinelisting')->exists('title=:title and siteid=:siteid and itemid=:itemid',array(':title'=>$title,':siteid'=>$siteid,':itemid'=>$id));
					}
				}
				else
				{
					if(UebModel::model('EbayListing')->exists('sell_title=:sell_title and siteid=:siteid',array(':sell_title'=>$title,':siteid'=>$siteid)))
					{
						return true;
					}
					else
					{
						return UebModel::model('Ebayonlinelisting')->exists('title=:title and siteid=:siteid',array(':title'=>$title,':siteid'=>$siteid));
					}
				}
				break;
			default:
				if(empty($title) || !is_numeric($siteid))
				{
					return false;
				}
				else
				{
					if(UebModel::model('EbayListing')->exists('sell_title=:sell_title and siteid=:siteid',array(':sell_title'=>$title,':siteid'=>$siteid)))
					{
						return true;
					}
					else
					{
						return UebModel::model('Ebayonlinelisting')->exists('title=:title and siteid=:siteid',array(':title'=>$title,':siteid'=>$siteid));
					}
				}
		}
	}


	public static function resourceLinkTransformHttps($content)
	{
		try{
			return preg_replace_callback('/(\\s)(src=)(["\'])([^\\3]+?)\\3/',function($match){
				$americaImageUrl = 'https://image-us.bigbuy.win';
				$match[4] = trim($match[4]);
				if(strpos($match[4],'http') === 0)
				{
					$americaImageUrlInfo = parse_url($americaImageUrl);
					$currentHost = parse_url(Yii::app()->request->hostInfo);
					$urlInfo = parse_url($match[4]);
					if($urlInfo['scheme'] == 'https')
						return $match[0];
					switch($urlInfo['host'])
					{
						case '47.88.35.136':  //美国图片服务器地址
							if($urlInfo['scheme'] == 'http')
								return str_replace('http://47.88.35.136',$americaImageUrl,$match[0]);
							else
								return str_replace('https://47.88.35.136',$americaImageUrl,$match[0]);
							break;
						case $currentHost['host']:    //本地地址
							if($urlInfo['scheme'] == 'http')
								return str_replace('http://'.$currentHost['host'],$americaImageUrl,$match[0]);
							else
								return str_replace('https://'.$currentHost['host'],$americaImageUrl,$match[0]);
							break;
						case $americaImageUrlInfo['host']:		//美国服务器域名地址
							if($urlInfo['scheme'] == 'http')
								return str_replace('http://','https://',$match[0]);
							else
								return $match[0];
							break;
						default:     //第三方图片
							$thirdResourceModel = UebModel::model('ThirdResource')->find('source_url=:source_url',array(':source_url'=>$match[4]));
							if(empty($thirdResourceModel))
							{
								$extend = end(explode('.',$urlInfo['path']));
								$destinationPath = 'upload/third_resource/'.date('Y/m/d/');
								if(!is_dir($destinationPath))
								{
									mkdir($destinationPath,0777,true);
								}
								$destination = $destinationPath.uniqid().'.'.$extend;
								if(file_put_contents($destination,file_get_contents($match[4])))
								{
									$thirdResourceModel = new ThirdResource();
									$thirdResourceModel->source_url = $match[4];
									$thirdResourceModel->destination_path = $destination;
									$thirdResourceModel->create_time = date('Y-m-d H:i:s');
									$thirdResourceModel->save();
								}
								else
								{
									throw new Exception($match[4].'资源下载不成功。');
								}
							}
							return $match[1].$match[2].$match[3].$americaImageUrl.'/'.$thirdResourceModel->destination_path.$match[3];
					}
				}
				else if(strpos($match[4],'/') === 0)
				{
					return $match[1].$match[2].$match[3].$americaImageUrl.$match[4].$match[3];
				}
				else
				{
					return $match[0];
				}
			},$content);
		}catch(Exception $e)
		{
			return ['status'=>false,'info'=>$e->getMessage()];
		}
	}

	public static function imageLinkTransformHttps($url)
	{
		if(empty($url))
		{
			return null;
		}
		else
		{
			$result = self::resourceLinkTransformHttps(" src='{$url}'");
			if($result['status'] !== false)
			{
				$url = str_replace(" src='",'',$result);
				$url = substr($url,0,strlen($url) -1);
			}
			return $url;
		}
	}
    /**
     * 获取某年第几周的开始日期和结束日期
     * @param int $year
     * @param int $week 第几周;
     */
    public function weekday($year,$week=1){
        $year_start = mktime(0,0,0,1,1,$year);
        $year_end = mktime(0,0,0,12,31,$year);

        // 判断第一天是否为第一周的开始
        if (intval(date('W',$year_start))===1){
            $start = $year_start;//把第一天做为第一周的开始
        }else{
            $week++;
            $start = strtotime('+1 monday',$year_start);//把第一个周一作为开始
        }

        // 第几周的开始时间
        if ($week===1){
            $weekday['start'] = $start;
        }else{
            $weekday['start'] = strtotime('+'.($week-0).' monday',$start);
        }

        // 第几周的结束时间
        $weekday['end'] = strtotime('+1 sunday',$weekday['start']);
        if (date('Y',$weekday['end'])!=$year){
            $weekday['end'] = $year_end;
        }
        return $weekday;
    }
}
?>
