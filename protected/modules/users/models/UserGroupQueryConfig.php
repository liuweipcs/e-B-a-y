  <?php
/**
 * @package Ueb.modules.products.models
 *
 * @author Leal
 */
class UserGroupQueryConfig extends UsersModel
{
    /**
     * @inheritdoc
     * @param string $className
     * @return CActiveRecord
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className); // TODO: Change the autogenerated stub
    }

    /**
     * @inheritdoc
     * @return array
     */
    public function rules()
    {
    	return array(

    	);
    }

    /**
     * @inheritdoc
     * @return string
     */
    public function tableName()
    {
        return 'ueb_user_group_query_config'; // TODO: Change the autogenerated stub
    }
    /**
     * @param null $model
     * @param array $sort
     * @param array $with
     * @param null $CDbCriteria
     * @return CActiveDataProvider
     */
    public function search($model = null, $sort = array(), $with = array(), $CDbCriteria = null)
    {
        $sort = new CSort();
        $sort->attributes = array(
            'defaultOrder' => 'department_name',
        );

        $dataProvider = parent::search(get_class($this), $sort, array(), null);

        return $dataProvider;
    }

    /**
     * @return array
     */
    public function filterOptions()
    {

        return array(
             array(
                'name'   => 'department_name',
                'type'   => 'text',
                'search' => 'LIKE',
            ),
        );
    }

    /**
     * @inheritdoc
     * @return array
     */
    public function attributeLabels()
    {
        return array(
            'id'=>'ID',
            'department_name' => '部门名称',
            'departmen_id' => '部门分组ID',
        );
    }

    /**
     * @inheritdoc
     * @return array
     */
    public function relations()
    {
        return array(
/*            'account' => array(self::BELONGS_TO, 'AmazonAccount', array('account_id'=> 'id')),
            'user' => array(self::BELONGS_TO, 'User', array('uid'=> 'id')),*/
        );
    }


    public static function getIndexNavTabId() {
        return Menu::model()->getIdByUrl('/users/userqueryconf/index');
    }

    /**
     * 替换成英文的逗号分割字符串
     * @param $ids
     * @return mixed
     */
    public static function ReplaceStr($ids){
        $ids = preg_replace("/(\n)|(\s)|(\t)|(\')|(')|(，)|(\.)/",',',$ids);
        $topicids = explode("," ,$ids);
        foreach ($topicids as $v){
            if(!empty($v)){
                $tids[]=$v;
            }
        }
        $tids=implode(',',$tids);
        return $tids;
    }

    /**
     * 获取账号
     * @return array
     */
    static function getAccount(){

        $criteria = new CDbCriteria();
        $criteria->order = 'account_name ASC';

        $amazon = UebModel::model('AmazonAccount')->findAll($criteria);
        $data=[];
        foreach($amazon as $key => $val){
            $data[$val['id']] = $val['account_name'];
        }

        return $data;
    }

}