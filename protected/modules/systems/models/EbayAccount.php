<?php 
	/**
     *  ebay account
     * 
	 * @package Ueb.modules.systems.models
	 * @author Bob <Foxzeng>
	 */
	class EbayAccount extends AccountModel{
		
	    /**
	     * Returns the static model of the specified AR class.
	     * @return CActiveRecord the static model class
	     */
	    public static function model($className = __CLASS__) {
	        return parent::model($className);
	    }
	
	    
	    /**
	     * @return string the associated database table name
	     */
	    public function tableName() {
	    	return 'ueb_ebay_account';
	    }
        
        /**
         *  get by short name 
         * 
         * @param string $shortName
         * @return array
         */
        public static function getByShortName($shortName) {           
            return Yii::app()->db->createCommand() 
			->select('*')
			->from(self::tableName())
            ->where(" short_name = '{$shortName}'")
            ->queryRow();
        }
 
        public static function getOneEnableAccount(){
        	return Yii::app()->db->createCommand() 
				        	->select('*')
				        	->from(self::tableName())
				        	->where("status = 1")
				        	->queryRow();
        }
        
        public function getAccountInfoById($id){
        	return $this->dbConnection->createCommand()
        				->select('*')
        				->from(self::tableName())
        				->where('id = '.$id)
        				->queryRow();
        }
        /**
         * @desc 根据account获取账号信息
         * @see AccountModel::getAccountInfoByAccount()
         */
        public function getAccountInfoByAccount($account){

            return $this->dbConnection->createCommand()->select('*')->from(self::tableName())->where('id = "'.$account.'"')->queryRow();
        }
        /**
         * @desc 根据账号ID获取账号名称
         * @param string $accountId
         */
        public function getAccountNameById($accountId) {
        	return self::model()->getDbConnection()
        	->createCommand()
        	->select("short_name")
        	->from(self::tableName())
        	->where("id = :id", array(':id' => $accountId))
        	->queryScalar();
        }

        /**
         * @desc 获取可用账号列表
         * @author Gordon
         */
        public static function getAbleAccountList(){
        	return AmazonAccount::model()->dbConnection->createCommand()
        	->select('*')
        	->from(self::model()->tableName())
        	->where('status=1')
        	->queryAll();
        } 
        /**
         * ebay  id 对应short_name 列表
         * @return multitype:unknown
         */
        public function getEbayAccountList(){
        	$data= $this->dbConnection->createCommand()
		        	->select('*')
		        	->from(self::tableName())
		        	->where("status = 1")
		        	->order('id')
		        	->queryAll();
        	static $list=array();
        	foreach ($data as $val){
				if($val['user_name']){
					$list[$val['id']]=$val['user_name'];
				}else{
					$list[$val['id']]=$val['short_name'];
				}
        		
        	}
        	return $list;
       }

       public function getEbayAccountByPlatform($field,$condition)
       {
           return $this->getDbConnection()->createCommand()
                                          ->select($field)
                                          ->from($this->tableName())
                                          ->where($condition)
                                          ->queryAll();
       }

        public static function getByAccountName($accountName) {
            $info = Yii::app()->db->createCommand()
                ->select('*')
                ->from(self::tableName())
                ->where(" user_name = '{$accountName}'")
                ->queryRow();
            if(!empty($info))
            {
                $info['siteId'] = UebModel::model('EbaySiteMapAccount')->getSitesByAccount($info['id']);
            }
            return $info;
        }
        
        public function getIdNamePairs()
        {
            $list = [];
            $info = Yii::app()->db->createCommand()
                ->select('id, user_name')
                ->from(self::tableName())
                ->queryAll();
            if(!empty($info))
            {
                foreach ($info as $row)
                    $list[$row['id']] = $row['user_name'];
            }
            return $list;            
        }

		/**
		 * @desc 根据账号ID获取账号简称名称
		 *
		 */
		public function getAccountShortNameById($accountId) {
			return self::model()->getDbConnection()
					->createCommand()
					->select("short_name")
					->from(self::tableName())
					->where("id = :id", array(':id' => $accountId))
					->queryScalar();
		}

		public function getAccountNamesByIds($ids){
			$arr =array();
			if(!empty($ids)){
				$sql = "SELECT id,short_name FROM `ueb_ebay_account` WHERE id in ({$ids})";
				$rows = Yii::app()->db->createCommand($sql)->queryAll();
				foreach($rows as $key => $val){
					$arr[$val['id']] = $val['short_name'];
				}
			}else{
				$arr =array();
			}
			return VHelper::arrayMultisort($arr);
		}

        public function getByuserName($Name) {
            return Yii::app()->db->createCommand()
                ->select('id')
                ->from(self::tableName())
                ->where(" user_name = '{$Name}'")
                ->queryRow();
        }

	}
?>