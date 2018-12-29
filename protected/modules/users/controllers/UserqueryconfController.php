<?php

/**
 * @author Leal
 * @data 2017-03-16
 */
class UserqueryconfController extends UebController
{
    /**
     * [actionIndex description]
     *
     * @return [type] [description]
     */
    public function actionIndex()
    {
        $model = new UserGroupQueryConfig();
        return $this->render('index', array('model' => $model));
    }


    /**
     * [actionAdd description]
     * @return [type] [description]
     */
    public function actionAdd()
    {
        $attributes = Yii::app()->request->getPost('userconf');
        $model=new UserGroupQueryConfig();

        if (!empty($attributes)) {
            $did=$attributes['department_id'];
            $did=UserGroupQueryConfig::ReplaceStr($did);
            $dname=trim($attributes['department_name']);

            $res = $model->findByAttributes(['department_name'=>$dname]);

            if ($res) {
                echo $this->failureJson(array(
                    'message' => '部门名称 '.$res->department_name.' 已经存在！',
                ));
                exit;
            }

            $model->department_name=$dname;
            $model->department_id=$did;

            $result=$model->save($model);
            if ($result) {
                echo $this->successJson(array(
                    'statusCode' => '200',
                    'message' => '添加成功！',
                    'navTabId' => 'page'.UserGroupQueryConfig::getIndexNavTabId(),
                    'callbackType' => 'closeCurrent'
                ));
                exit;
            } else {
                echo $this->failureJson(array(
                    'message' => '添加失败！' ,
                ));
                exit;
            }

        }

        return $this->render('add');
    }

    /**
     * [actionEdit description]
     * @return [type] [description]
     */
    public function actionEdit()
    {
        $id = (int)Yii::app()->request->getQuery('id');

        $model = new UserGroupQueryConfig();

        $postmodel = $model->findByPk($id);

        $attributes = Yii::app()->request->getPost('userconf');

        if (!empty($attributes)) {
            $did=$attributes['department_id'];
            $did=UserGroupQueryConfig::ReplaceStr($did);
            $dname=trim($attributes['department_name']);
            $postid = $attributes['id'];
            $resid = $model->findByPk($postid);

            if($dname !== $resid->department_name){
                $res = $model->find(array(
                    'select' =>array('department_name'),
                    'condition' => 'department_name=:name',
                    'params' => array(':name'=>$dname),
                ));

                if (!empty($res)) {
                    echo $this->failureJson(array(
                        'message' => '部门名称 '.$res->department_name.' 已经存在！',
                    ));
                    exit;
                }
            }

            //更新数据
            //$model = $model->findByPk($postid);
            $resid->setAttribute('department_id', $did);
            $resid->setAttribute('department_name', $dname);

            $re_save = $resid->save();
            if ($re_save) {
                echo $this->successJson(array(
                    'statusCode' => '200',
                    'message' => '编辑成功！',
                    'navTabId' => 'page'.UserGroupQueryConfig::getIndexNavTabId(),
                    'callbackType' => 'closeCurrent'
                ));
                exit;
            } else {
                echo $this->failureJson(array(
                    'message' => '编辑失败！' ,
                ));
                exit;
            }

        }

        return $this->render('edit',[
            'model' => $postmodel,
        ]);
    }

    /**
     * 删除
     * @return bool|string
     */
    public function actionDelete()
    {
        if(Yii::app()->request->isAjaxRequest)
        {
            $model = new UserGroupQueryConfig();
            $get=Yii::app()->request->getQuery('id');
            if(!empty($get)){
                $model->deleteByPk(intval($get));
                echo $this->successJson(array('message' => '删除成功!'));
                Yii::app()->end();
            }else{//批量删除
                $arr=explode(',', $_REQUEST['ids']);
                if(!empty(is_array($arr)))
                {
                    $model->deleteByPk($arr);
                    echo $this->successJson(array(
                        'message' => Yii::t('system', 'Delete successful'),
                    ));
                }else{
                    echo $this->failureJson(array(
                        'message' => Yii::t('system', 'Delete failure')
                    ));
                }
                Yii::app()->end();
            }
        }
        return false;
    }

}