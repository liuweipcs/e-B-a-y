<?php
/**
 *  Process conflict, the event control component
 *
 * @author Bob <Foxzeng> 
 * @package application.components
 */
class UEventControl {
    
    private static $_instance;
    
    /**
     * @var array $config
     */
    public $config = array();
       
    /**
     * @var int Running interval period (seconds) 
     */
    public $runningIntervalTime = 600;
    
    /**
     * @var int max runtime
     */
    public $maxRunTime = 3600;
    
    /**
     * @var object model obj
     */
    protected $_modelObj = null;
    
    public function __construct() {               
        $this->config = ConfigFactory::getConfig('eventControllConfig');
        $this->_modelObj = UebModel::model('eventControl');
    }
    
    /**
	 * @get instance of self
     */
	public static function getInstance()
	{
		if(! self::$_instance instanceof self){
			self::$_instance = new self();
		}
		
		return self::$_instance; 
	}
    
    /**
     *  set max run time
     * 
     * @param int $maxTime
     */
    public function setMaxRunTime($maxTime) {
		$this->maxRunTime = $maxTime;
	}
    
    /**
     *  get max run time
     * 
     * @param string $eventName
     */
    public function getMaxRunTime($eventName) {
        if ( isset($this->config[$eventName]) ) {
            $this->maxRunTime = $this->config[$eventName];
        }
		
        return $this->maxRunTime;
	}
    
    
    
    
    /**
     * thie step of event control 
     * 
     * @param string $eventName
     * @param string $step
     * @param string $note
     * @return type
     */
    public function event($eventName, $step = 'start', $note = '', $relatedKey = '') {
    
        $name = 'event'.ucfirst(strtolower($step));
        if ( $step == 'start') {
        	$flag=$this->$name($eventName, $note, $relatedKey);
            return $flag;
        } else {
        	$flag=$this->$name($eventName, $note);
            return $flag;
        }       
    }

    /**
     *  event start
     * 
     * @param string $eventName
     * @param string $note event note
     * @param string $relatedKey event related key
     * 
     * @return boolean
     */
    public function eventStart($eventName, $note = '', $relatedKey = '') {
		if( $this->checkEventRunStatus($eventName)) {
            if ( empty($relatedKey) ) { 
                $relatedKey = $eventName;              
            }
            $this->_modelObj->setAttribute('event_name', $eventName);
            $this->_modelObj->setAttribute('event_related_key', $relatedKey);
            $this->_modelObj->setAttribute('event_status', EventControl::STATUS_RUNNING);
            $this->_modelObj->setAttribute('start_time', date('Y-m-d H:i:s'));
            $this->_modelObj->setAttribute('respond_time', date('Y-m-d H:i:s'));//Gordon 开始时添加响应时间
            $this->_modelObj->setAttribute('note', $note); 
            $this->_modelObj->setIsNewRecord(true);
            return $this->_modelObj->save();			
		}
        
		return false;
	}
    
    /**
     * event respond
     * @param string $eventName
     * @param string $note
     */
    public function eventRespond($eventName, $note = '') {       
        $model = $this->_modelObj->findByEventName($eventName);
        //Artificial markers for failure
        if ( $model['event_status'] == EventControl::STATUS_FAILURE ) {
            return false;
        }
        //More than the maximum execution time
        if( (time() - strtotime($model['start_time'])) >= $this->getMaxRunTime($eventName)) {
            $this->event($eventName, 'failure');          
            return false;
        }
        $model->setAttribute('respond_time', date('Y-m-d H:i:s'));
        $model->setAttribute('event_status', EventControl::STATUS_RUNNING);
        $model->setAttribute('note', $note);
		return $model->save();		
	}
    
    /**
     * event end
     * @param string $eventName
     * @param string $note
     */
    public function eventEnd($eventName, $note = '') {
        $model = $this->_modelObj->findByEventName($eventName);
        $model->setAttribute('respond_time', date('Y-m-d H:i:s'));
        $model->setAttribute('event_status', EventControl::STATUS_SUCCESS);
        $model->setAttribute('note', $note);
		$model->save();	
    } 
    
    /**
     * event failure
     * @param string $eventName
     * @param string $note
     */
    public function eventFailure($eventName, $note = '') {
        $model = $this->_modelObj->findByEventName($eventName);
        $model->setAttribute('respond_time', date('Y-m-d H:i:s'));
        $model->setAttribute('event_status', EventControl::STATUS_FAILURE);
        $model->setAttribute('note', $note);
		$model->save();	
    } 
    
    /**
     * check event run status
     * 
     * @param string $eventName
     */
    public function checkEventRunStatus($eventName) {       			
       $model = $this->_modelObj->findByEventName($eventName); 
       
       if ( empty($model) ) return true;
       //Determine whether related events, if any, to exit      
       if ( $eventName != $model['event_related_key']) {      
           if (! $this->checkRelatedEventsRunStatus($eventName, $model['event_related_key'])) {
               return false;
           }
       } 
       //echo($model->id.'####'.$this->runningIntervalTime.'####'.$model['event_status'].'####'.EventControl::STATUS_FAILURE.'####'.EventControl::STATUS_SUCCESS); 
       //Event completed
       if ( $model['event_status'] == EventControl::STATUS_FAILURE || 
                $model['event_status'] == EventControl::STATUS_SUCCESS) {
            return true;
       }
       
       //More than the maximum interval time
       if ( (time() - strtotime($model['respond_time'])) >= $this->runningIntervalTime) {
       	
            $this->event($eventName, 'failure');          
            return true;
        }      
        
        return false;
    }
    
    /**
     * check related events run status
     * 
     * @param string $eventName
     * @param string $relatedKey
     * @return boolean
     */
    public function checkRelatedEventsRunStatus($eventName, $relatedKey) {
        $eventName = (array) $eventName;  
        $model = $this->_modelObj->findByRelatedKeyNotINEventName($relatedKey, $eventName);       
        while (! empty($model) ) {
            $flag = $this->_checkRelatedEventRunStatus($model, $relatedKey);           
            if (! $flag) { 
                return false;                
            } 
            array_push($eventName, $model['event_name']);
            $model = $this->_modelObj->findByRelatedKeyNotINEventName($relatedKey, $eventName);          
        }
        
        return true;
    }

    /**
     * check related event run status (one)
     * 
     * @param type $eventName
     * @param type $relatedKey
     * @return boolean
     */
    protected function _checkRelatedEventRunStatus($model, $relatedKey) {     
         //Event completed
       if ( $model['event_status'] == EventControl::STATUS_FAILURE || 
                $model['event_status'] == EventControl::STATUS_SUCCESS) {
            return true;
       }
        
       //More than the maximum interval time
       if ( (time() - strtotime($model['respond_time'])) >= $this->runningIntervalTime) {
            $this->event($model['event_name'], 'failure');          
            return true;
        }
        
        return false;
    }
}
?>
