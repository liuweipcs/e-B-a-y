<?php

class Orderaccesscontrol extends UsersModel
{
	/**
	 * Returns the static model of the specified AR class.
	 * @return CActiveRecord the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'ueb_orderaccesscontrol';
	}


	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		return array(
//                    array('new_password', 'required', 'on' => 'change'),
//                    array('user_name,en_name,user_full_name,user_password,department_id','required'),
//                    array('user_status,user_email,user_tel,is_intranet','safe'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
			//'posts' => array(self::HAS_MANY, 'Post', 'author_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
		);
	}

    /**
     * get search info
     */

    public function search()
    {
        $sort = new CSort();
        $sort->attributes = array(
            'defaultOrder'  => 'id',
            'user_name',
        );

        return parent::search(get_class($this), $sort);
    }


     public function filterOptions() {
        return array(
        );
    }

    /**
     * order field options
     * @return $array
     */
    public function orderFieldOptions() {
    	return array(
    	);
    }

	public function insertData($useId,$platformCode,$accountIds){
		return $result =Yii::app()->db->createCommand()->insert('ueb_system.ueb_orderaccesscontrol',
				array( 'user_id'=>$useId,
					   'platform_code'=>$platformCode,
					   'account_ids'=>$accountIds,
				));
	}

	/*
	  *更新数据库记录
	 */
	public function updateData($id,$accountIds){
		return $this->updateByPk($id,array('account_ids'=>$accountIds));
	}

	/*
     * 查询数据库是否存在该条记录
    */
	public function checkData($useId,$platformCode){
		return $this->getDbConnection()->createCommand()
				->select('*')
				->from(self::tableName())
				->where('user_id=:user_id', array(':user_id'=>$useId))
				->andwhere('platform_code=:platform_code', array(':platform_code'=>$platformCode))
				->queryRow();
	}

	/*
     * 查询数据库是否存在该条记录
    */
	public function checkDatas($useId){
		$arr = array();
		$result = $this->getDbConnection()->createCommand()
				->select('*')
				->from(self::tableName())
				->where('user_id=:user_id', array(':user_id'=>$useId))
				->queryAll();
		foreach($result as $k => $v){
			$arr[$v['platform_code']] = explode(',',$v['account_ids']);
		}
		return $arr;
	}

	/*
     * 查询数据库中所有的用户use_id
    */
	public function getAllUserId(){
		$arr = array();
		$sql = "SELECT DISTINCT user_id FROM `ueb_orderaccesscontrol`";
		$result = Yii::app()->db->createCommand($sql)->queryAll();
		foreach($result as $k => $v){
			$arr[$k] = $v['user_id'];
		}
		return $arr;
	}

}