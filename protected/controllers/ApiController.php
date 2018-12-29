<?php
/** 
 * Entrance Of API
 * @package API.controllers
 * @author Gordon
 * @since 2015-01-05
 */
class ApiController extends UebController {

	/**
	 * 显示文档信息
	 */
	public function actionDoc(){
		$this->layout = '//layouts/pdaLogin';
		echo 'API Document';exit;
	}
	
	/**
	 * 接收请求
	 */
	public function actionIndex(){
		set_time_limit(25);
		$this->layout = false;
		$apiParam = $_REQUEST['col'];
		$apiParam = json_decode($apiParam);
		$model = new ApiModel;
		$attribute = $apiParam;
		if( $apiParam ){
			$model->initApiParam($attribute);
			if( $model->authenticate() ){//通过验证
				$result = $model->run();				
				echo json_encode($result);
				MHelper::writeLog(json_encode($result), 'API_SUC');
			}else{
				echo json_encode($model->_buildReturnData());
				MHelper::writeLog(json_encode($model->_buildReturnData()), 'API_FAIL');
			}
		}
	}
}