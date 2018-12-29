<?php
/** 
 * Base generic controller class
 * 
 * @package UEB.controllers
 * 
 * @author Bob <Foxzeng>
 */

class UebController extends CController {
    /**
     * @var string the default layout for the controller view	
     */
    public $layout='main';

    /**
     * @var array context menu items. This property will be assigned to {@link CMenu::items}.
     */

    public $menu=array();
    /**
     * @var array the breadcrumbs of the current page. The value of this property will
     * be assigned to {@link CBreadcrumbs::links}. Please refer to {@link CBreadcrumbs::links}
     * for more details on how to specify this property.
     */
    public $breadcrumbs=array();
    
    public $orderField = null;
    
    public $orderDirection = 'Desc';
    
    /**
	 * Change the Tpl If the Request Client is Pda
	 * @author Gordon
	 * @see CController::init()
	 */
    public function init(){
    	parent::init();
    	
    	if( Yii::app()->request->getParam(PdaClientModel::getPdaUrlParam())==PdaClientModel::getPdaUrlParamValue() ){
    		$this->layout = '//layouts/pdamain';
    	}
    }
    
    public function successJson($data) {        
        $data['statusCode'] = 200;
        header("Content-Type:text/html; charset=utf-8");
        return json_encode($data);
    }
    
    public function failureJson($data) {
        $data['statusCode'] = 300;
        header("Content-Type:text/html; charset=utf-8");
        return json_encode($data);
    }     
    
    /**
     * rewirte render 
     * 
     * @param type $view
     * @param type $data
     * @param type $return
     */
    public function render($view, $data=null, $return = false){
    	//Add By Gordon 2014-02-07 
    	if( Yii::app()->request->getParam(PdaClientModel::getPdaUrlParam())==PdaClientModel::getPdaUrlParamValue() ){
    		if( !$this->getViewFile($view) ){
    			$view = 'pda'.ucwords($view);
    		}
    	}
        if (! empty($_REQUEST['orderField']) ) {
            $data['orderField'] = $_REQUEST['orderField'];
            $orderDirection = isset($_REQUEST['orderDirection']) ? $_REQUEST['orderDirection'] : 'DESC';
            $data['orderDirection'] = $orderDirection;
        }
        if($return == true){
            return parent::render($view, $data, $return);
        }else{
            parent::render($view, $data, $return);
        }
    }
   
    /**
     * before action check access
     * 
     * @param string $action
     * @return boolean
     * @throws CHttpException
     */
    protected function beforeAction($action = null) {
        if ( isset($this->module->id) ) {
        	$filterModules = Yii::app()->params['filter_modules'];
        	if ( isset($this->module->id)) {
        		if ( $parentModule = $this->module->getParentModule() ) {
        			$filterModuleId = $parentModule->id;
        		} else {
        			$filterModuleId = $this->module->id;
        		}
        	} else {
        		$filterModuleId = '';
        	}
        	if ( (isset($this->module->id) && in_array( $filterModuleId, preg_split('/\s*,\s*/',trim($filterModules),-1,PREG_SPLIT_NO_EMPTY)))) {
        		return true;
        	}
        	//过滤非业务请求
            if ( $this->filterAccessAction()) { 
                return true;          
            };
            $auth = Yii::app()->authManager;
            //检查特殊权限，并保存在session
			if( $checkAuth = Yii::app()->session->get('specialAuth') ){
				
			}else{
				$specialAuth = UebModel::model('specialAuth')->checkSpecialAuth($auth);
				Yii::app()->session->remove('specialAuth');
				Yii::app()->session->add('specialAuth', $specialAuth);
			}
           	
            $action = $this->module->id . '_' . strtolower($this->getId()) .'_' .  strtolower($this->getAction()->getId()); 
            //避免重复提交相同请求                 
            if ( stripos(Yii::app()->request->getRequestUri(), "?_=") !== false ) {
                $uri = explode("?_=", Yii::app()->request->getUrl()); 
                $uri[1] = substr($uri[1], 0, 13);
                $uniqueKey = 'submit_'.session_id().$uri[1];  
                       
                if (  $beginTime = Yii::app()->session->get($uniqueKey) ) {                 
                    $seconds = CHelper::diffSeconds($beginTime);
                    if ( $seconds > 5 ) {                   
                        Yii::app()->session->remove($uniqueKey);
                    } else {
                        throw new CHttpException(403, "Can't repeat submit.".$seconds);
                    }               
                }
                
                Yii::app()->session->add($uniqueKey, microtime());              
            } 
            
            $actionResource = 'resource_'.$action;
            //检测请求是否被规则允许
            $ruleSet = $this->accessRules();
            foreach($ruleSet as $rule){
            	if( current($rule)=='allow' && isset($rule['users']) ){
            		if( in_array('*',$rule['users']) ){
            			if( in_array(strtolower($this->getAction()->getId()),$rule['actions']) ){
            				//暂时将已有的option删除
            				$auth->removeAuthItem($actionResource);
            				return true;
            			}
            		}
            	}
            }
            //检查请求是否存在于rbac权限控制中
            $actionUrl = '/'. str_replace("_", "/", $action);
            //var_dump($actionUrl);die;
            $menuFlag = Menu::model()->exists("menu_url = '$actionUrl'");
            if (! AuthItem::model()->exists("name = '{$actionResource}'") && ! $menuFlag) {
				//$auth->createTask($actionResource, $action);
            	$auth->createOperation($actionResource, $action);
            }   
            if (Yii::app()->user->isGuest) {
               	Yii::app()->user->returnUrl = Yii::app()->request->requestUri;
               
            	if( $parms = str_replace(strtolower($actionUrl),'',strtolower(Yii::app()->user->returnUrl)) ){
	               	if ( stripos($parms,'autorun/true')!==false || stripos($parms,'autorun/1')!==false ){
	               		return true;
	               	}
               	}
               	$this->redirect($this->createUrl('/site/login'));
            } else if ( UebModel::checkAccess($actionResource) || $menuFlag || $flag ) {
            	if ( Yii::app()->request->getParam(PdaClientModel::getPdaUrlParam())==PdaClientModel::getPdaUrlParamValue() ){//请求为pda端请求
            		if( !isset($_GET['uid']) || !isset($_GET['pw']) ){
            			Yii::app()->user->returnUrl = Yii::app()->request->requestUri;
            			$this->redirect($this->createUrl('/pdaClient/login'));

            		}else{
            			return true;
            		}
            	}else{
	                return true;
            	}
            } else {
                throw new CHttpException(403, Yii::t('system', 'You are not authorized to perform this action.'));
            }
        }     
        
        return true;
    }
    
    /**
     * after action operation
     * 
     * @param type $action
     */
    protected function afterAction($action) { 
        parent::afterAction($action);
        if ( stripos(Yii::app()->request->getRequestUri(), "?_=") !== false ) {
            $uri = explode("?_=", Yii::app()->request->getUrl()); 
            $uri[1] = substr($uri[1], 0, 13);
            $uniqueKey = 'submit_'.session_id().$uri[1];
            
            if ( Yii::app()->session->get($uniqueKey) ) {         
                Yii::app()->session->remove($uniqueKey);
            }
            unset($_REQUEST['orderField']);
            CHelper::profilingTimeLog();
        }               
    }

    /**
     * filter check access action 
     */
    protected function filterAccessAction() {
        //filter modules
        //filter controllers
        if (in_array($this->id, array('msg','auto'))) {
            return true;
        }
        // filter actions    
        if (in_array($this->getAction()->getId(), array('sider'))) {
            return true;
        }
        return false;
    }
    
    /**
     * excel scheme column list
     */
    public function actionReportlist() { 
    	$schemeId = Yii::app()->request->getParam('scheme_id');
    	$className = Yii::app()->request->getParam('className');
    	$ids = Yii::app()->request->getParam('id');
    	$field = Yii::app()->request->getParam('field', 'id');
    	$subTitle = Yii::app()->request->getParam('subTitle');
    	$params = array();
    	$model = UebModel::model($className);
    
    	//get the column type
    	$columnNameArr = UebModel::model('ExcelSchemeColumn')->getSchemeColumnDataBySchemeId($schemeId);
    	$list = $model->reportColumnGroup($columnNameArr);

    	if($list){
    		if($list['is_condition']){
		    	foreach ($list['is_condition'] as $key=>$val){
		    		//$condArr[] = $val['column_field'];
		    		if ($val['default_value'] != '') {
		    			$defaultValue = explode(',', $val['default_value']);
		    			if (count($defaultValue) > 1) {
		    				$conditionArr[] = "{$val['table_name']}.{$val['column_field']} IN (".implode(',', $defaultValue).")";
		    			}else {
		    				$conditionArr[] = "{$val['table_name']}.{$val['column_field']}='{$defaultValue[0]}'";
		    			}
		    		}
		    	}
    		}
    		//Pleae not delete	9.19
//     		if($list['is_group']){
// 		    	foreach ($list['is_group'] as $key=>$val){
// 		    		$groupArr[] = $val['column_field'];
// 		    	}
//     		}
			
    	}
    	count($conditionArr) && $conditions = implode(' AND ', $conditionArr);
    	
    	$timeFormat = ExcelSchemeColumn::_MONTH;
    	$group_num = 0;
    	if ($_REQUEST['is_group']) {
	    	foreach ($_REQUEST['is_group'] as $key=>$val){
	    		foreach ($val as $k=>$v){
	    			if (is_array($v)){
		    			if (isset($v[0]) && isset($v['day_type']) && !empty($v['day_type'])) {
		    				$timeFormat = $v['day_type'];
		    			}
	    			}
	    			$group_num++;
	    		}
	    	}	
    	}
    	
    	$queryColumnIds = array_keys($_REQUEST['is_value']);
    	$queryColumn = UebModel::model('ExcelSchemeColumn')->getShowColumnGroup($queryColumnIds,$schemeId,$timeFormat);
    	$total= 0;
    	$data = array();
    	if (isset($_REQUEST['ac'])){
	    	//$total	= UebModel::model('ExcelSchemeColumn')->getJoinDataBySchemeId($schemeId, $conditions,$className,true);
	    	$datas	= UebModel::model('ExcelSchemeColumn')->getJoinDataBySchemeId($schemeId, $conditions,$className,$queryColumn, false);
	    	$total = count($datas);
	    	
			foreach ($datas as $key=>$val){
				$val = array_values($val);
				if($group_num>1){
					$isMultiArr = true;
					$data['total'][$val[1]] += $val[2];
					$data[$val[0]][$val[1]] += $val[2];
				}else{
					$isMultiArr = false;
					$xAxis[] = $val[0];
// 					$m =0;
// 					foreach ($val as $k=>$v){
// 						if ($m > 0){
// 							$data[0][$k] += $val[$k];
// 						}else{
// 							$data[0][$k] = 'total';
// 						}
// 						$m++;
// 					}
				}
			}
// 			if($group_num<2){
// 				$datas = array_merge($data,$datas);
// 			}
		}

    	$params['model'] = $model;
    	$params['modelName'] = $this->getId();
		$params['subTitle'] = $subTitle;
    	$params['scheme_id'] = $schemeId;
    	$params['className'] = $className;
    	$params['list'] = $list;
    	$params['total'] = $total;
    	
    	if($group_num>1){
    	//if($isMultiArr){	
	  		$params['data'] = $data;
	    	$this->render('application.components.views.reportlist',$params);
    	}else{
    		//双y轴
    		$params['xAxis'] = $xAxis;
    		$params['data'] = $datas;
    		$this->render('application.components.views.reportlist_1',$params);
    	}
    }
    
    /**
     * export data
     */
    public function actionExport() {
        $schemeName = Yii::app()->request->getParam('schemeName');
        $className = Yii::app()->request->getParam('className');
        $ids = Yii::app()->request->getParam('id');
        $field = Yii::app()->request->getParam('field', 'id');
        
        try {
            //$schemeId = UebModel::model('ExcelSchemeColumn')->getIdBySchemeName($schemeName);
            $schemeId = $schemeName;//直接调方案名称时用，否则用上面的根据名称取
            $titles = UebModel::model('ExcelSchemeColumn')->getColumnTitlePairsBySchemeId($schemeId,'is_value='.ExcelSchemeColumn::IS_VALUE);
            $schemeInfo = UebModel::model('ExcelCustomScheme')->getAttributesById($schemeId);
            
            $columnNameArr = UebModel::model('ExcelSchemeColumn')->getSchemeColumnDataBySchemeId($schemeId);
            $list = UebModel::model('ExcelSchemeColumn')->reportColumnGroup($columnNameArr);
            if($list){
            	if($list['is_condition']){
            		foreach ($list['is_condition'] as $key=>$val){
            			if ($val['default_value'] != '') {
            				$defaultValue = explode(',', $val['default_value']);
            				if (count($defaultValue) > 1) {
            					$conditionArr[] = "{$val['table_name']}.{$val['column_field']} IN (".implode(',', $defaultValue).")";
            				}else {
            					$conditionArr[] = "{$val['table_name']}.{$val['column_field']}='{$defaultValue[0]}'";
            				}
            			}
            		}
            	}
            }

            if (! empty($ids) ) {
                $conditionArr[] = " $field IN($ids)";
            } else {
                //$conditionArr[] = Yii::app()->session->get($className .'_condition');	//有session条件得不到结果，这种情况后续确定后处理
            }
            count($conditionArr) && $conditions = implode(' AND ', $conditionArr);
            
            $data = UebModel::model($className)->getDataBySchemeId($schemeId, $conditions, '', true);
            foreach ($data as $key=>$val){
            	$data[$key] = array_values($val);
            	//if ($key>30) unset($data[$key]);
            }

            $excelObj = ObjectFactory::getObject('MyExcel');
            $fileName = time() .'-'. rand(1, 1000).'.xls';
            $filePath = ObjectFactory::getObject('HashFilePath')
                    ->setBaseFilePath()
                    ->setDirectoryLevel(3)
                    ->getFilePath($fileName);
            $excelObj->export_excel($titles, $data, $filePath, 100, 0);
            if( file_exists($filePath) ) {
                $host = Yii::app()->request->getHostInfo();
                $filePath = str_replace(Yii::getPathOfAlias('webroot'), $host, $filePath);
                $downloadFileModel = UebModel::model('DownloadFile');
                $downloadFileModel->add($schemeName.'_'.$schemeInfo['scheme_name'], $filePath);
                $navTabId = 'page'.$downloadFileModel->getIndexNavTabId();
                $link = CHtml::link(Yii::t('system', 'Download File'), 'javascript:void(0);', array(                
                    'rel' => $navTabId,
                    'forward' => '/systems/downloadfile/list',                                     
                ));
                $jsonData = array(
                    'message' => Yii::t('system', 'Create Excel Success'),     
                    'link'    => $link,
                );
                echo $this->successJson($jsonData);
            } else {
               throw new Exception('create excel failure');
            } 
        } catch (Exception $e) {echo $e->getMessage();
             $jsonData = array(
                'message' => Yii::t('system', 'Create Excel Failure'),
             );
            echo $this->failureJson($jsonData);
        }      
              
        Yii::app()->end();
    }

    /**
     * unique check
     */
    public function actionUnique() {      
      $val = Yii::app()->request->getParam('value');
      $className = Yii::app()->request->getParam('className');
      $attributeName = Yii::app()->request->getParam('attributeName');
      $exist = UebModel::model($className)->exists(" $attributeName = :attributeName ", array( ':attributeName' => $val));
      echo $exist;
      Yii::app()->end();
    }
    
    /**
     * exist check
     */
    public function actionExist() {       
      $val = Yii::app()->request->getParam('value');
      $className = Yii::app()->request->getParam('className');
      $attributeName = Yii::app()->request->getParam('attributeName');
      $exist = UebModel::model($className)->exists(" $attributeName = :attributeName ", array( ':attributeName' => $val));
      echo !$exist;
      Yii::app()->end();
    }
    
    /**
     * custom validate
     */
    public function actionValidate() {
       $model = Yii::app()->request->getParam('model');
       $fieldName = Yii::app()->request->getParam('fieldName');             
       $msg = UebModel::model($model)->customValidate($fieldName);   
       die($msg);
    }
    
    /**
     * $data : view data
     * $dataColumn:params
     * return table html
     * 
    */
    public function renderGridcell($data,$row,$dataColumn){
    	if (! isset($dataColumn->htmlOptions['type']) ) {
    		$dataColumn->htmlOptions['type'] = 'text';
    	}
    	$type = $dataColumn->htmlOptions['type'];
    	if (! isset($dataColumn->htmlOptions['name']) ) {
    		$dataColumn->htmlOptions['name'] = $dataColumn->name;
    	}
    	$column = $dataColumn->name ;
    	$name = $dataColumn->htmlOptions['name'];
    	$str = '';
    	if(isset($data->detail) && !empty($data->detail)){
    		$num = count($data->detail)-1;
    		$str .= '<table cellpadding="0" cellspacing="0" width="100%" border=0 class="innerTable">';
    		foreach ($data->detail as $k=>$v){
    			$style = '';
    			if($num > $k) $style = 'border-bottom:1px dashed #70b3fa;overflow:hidden;white-space:nowrap;';
    			$str .= '<tr>';
    			$str .= '<td style="border:0;height:24px;'.$style.'">';
    			switch($type){
    				case 'checkbox':
    					$str .= "<input type='checkbox' id='".$name."_".$v[$column]."' name='".$name."[]' value=$v[$column]>";
    					break;
    				case 'text':
    					$str .= $v[$column];
    					break;
    				default:
    					break;
    			}
    			$str .= '</td>';
    			$str .= '</tr>';
    		}
    		$str .= '</table>';
    	}
    	return $str;
		/**取消用render()获取
    	//Yii::app()->clientscript->scriptMap['jquery.js'] = false;
    	$this->render('application.components.views._gridcell',array(
    		'data' => $data->detail,
    		'column' => $dataColumn->name,
    		'type'=> $dataColumn->htmlOptions['type'],
    		'name'=> $dataColumn->htmlOptions['name']
    		)
    	);
    	**/
    }
    /**
     * generate barcode
     * @param
     * <img src="<?php echo Yii::app()->request->hostInfo;?>
     * /modules/modelName/barcode/barcode/code128/text/aaaaa/o/1/t/30/r/1/f1/-1/f2/8/a1//a2/C/a3/" align="absmiddle">
     */
    public function actionBarcode(){
    	$code = Yii::app()->request->getParam('code');
    	$code  = $code=='' ? 'code128' : $code;
    	$text = Yii::app()->request->getParam('text');
    	$o = Yii::app()->request->getParam('o');
    	$t = Yii::app()->request->getParam('t');
    	$r = Yii::app()->request->getParam('r');
    	$f1 = Yii::app()->request->getParam('f1');
    	$f2 = Yii::app()->request->getParam('f2');
    	$a1 = Yii::app()->request->getParam('a1');
    	$a2 = Yii::app()->request->getParam('a2');
    	$a3 = Yii::app()->request->getParam('a3');
    	$barCode = ObjectFactory::getObject('BarCode');
    	$barCode->createBarCode($text,$code,$o,$t,$r,$f1,$f2,$a1,$a2,$a3);
    }
    /**
     * Get the Model class
     * @return string
     */
    public function getModelClass(){
    	return str_replace('Controller', "", get_class($this));
    }
    
    
    /**
     * 订单剩余自动发货
     */
    
    
    
    
    
	/**
	 * 测试paypal查找,不能实际用于系统任何地方
	 */
    public function actionRefunds(){
    	$refundArr = array(
    		'TransactionID'		   => '88X58157D08531732T',
    		'REFUNDTYPE'		   => 'Partial',
    		'AMT'		   => 0.01,
    		'NOTE'		   => 'dfdfdffdfd',
    	);
    	

    	ini_set('display_errors', 1);
    	$model = new RefundTransaction();
    	$email = 'penglongyun-business@live.cn';
    	$response = $model->refundTransactions($email,$refundArr);

    	print_r($response);
    }
    /**
	 * 测试paypal查找,不能实际用于系统任何地方
	 */
    public function actionSearchTransaction(){
    	$transactionId = '7XF46907BF01186327';//7XF46907BF0186327
    	$email = 'penglongyun-bu1siness@live.cn';
    	
    	$obj = new GetTransactionDetails;
    	ini_set('display_errors', 1);
		$respone = $obj->getDetailByTransactionId($transactionId,$email);
		//print_r($respone);die('===');
		print_r($respone);
    	
    }
    
    /**
     * 弹出普通提示
     */
    public function alertMsg($title, $msg, $url , $type='1') {
    	echo "<script>alert('{$msg}');location.href='{$url}';</script>";
    }
    
    
}