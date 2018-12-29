<?php 
/**
 * @package Ueb.modules.EbayApiTask.models
 * 
 * @author Tom 2014-01-02
 */
class EbayApiTask extends SystemsModel{
	const TASK_STATUS_ACTIVE = 1; // the task is pendding 
	const TASK_STATUS_ERROR = 2; //  the task is fail
	const TASK_STATUS_SUCCESS = 3; // the task is successful
	
    /**
     * @return string the associated database table name
     */
    public function tableName() {
    	return 'ueb_ebay_api_task';
    }
    
    public function getTaskStatusActive(){
    	return self::TASK_STATUS_ACTIVE;
    }
    
    public function getTaskStatusError(){
    	return self::TASK_STATUS_ERROR;
    }
    
    public function getTaskStatusSuccess(){
    	return self::TASK_STATUS_SUCCESS;
    }
    
	protected function _getTaskCondition($accountId,$taskName,$taskStatus){
		return "account_id = $accountId AND task_name='{$taskName}' AND task_status=$taskStatus";
	}

	public function updateTaskStatusByPk($taskId,$taskStatus,$totalNum){
		$data = array(
			'complete_time' => MHelper::getNowTime(),
			'task_status' => $taskStatus,
			'total_num' => $totalNum
		);
		return $this->updateByPk($taskId,$data);
	}
	
  	public function getTaskInfoByAccount($id){
  		
  	}

  	public function taskExists($task,$account)
    {
        $model = $this->find('account_id=:account_id and task_name=:task_name and task_status=1',array(':task_name'=>$task,':account_id'=>$account));
        if(empty($model))
        {
            return false;
        }
        else
        {
            if(time() - strtotime($model->execute_time) > 600)
            {
                return false;
            }
            else
            {
                return true;
            }
        }
    }
}
?>