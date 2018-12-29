<?php
Yii::app()->clientscript->scriptMap['jquery.js'] = false; 
$config = array(
    'id' => 'user-grid',  
    'dataProvider' => $model->search(null),
    'filter' => $model,
    
    'columns' => array(
       array(
            'class'              => 'CCheckBoxColumn',  
            'name'               => 'orgId',
            'selectableRows'     => 2,
            'value'              => '$data->id',             
        ), 
       array(
            'name' => 'id',
            'value' => '$row+1',     
        ),       
        'user_name',
    	'en_name',
        'user_full_name',
//        'user_email',
//        'user_tel',
    	array(
            'name'  => 'department_id',
            'value' => '$data->department_id>0 ? UebModel::model("Department")->getDepartment($data->department_id):"--"'
        ),
        array(
            'name'  => 'user_status',
            'value' => 'VHelper::getStatusLable($data->user_status)'
        ),
    		array(
    				'name'  => 'is_intranet',
    				'value' => 'empty($data->is_intranet) ? "不允许":"允许"'
    		),
    ),
    'tableOptions' => array(
        'layoutH' => 135,
    ),
    'pager' => array(),
);
if ( Yii::app()->request->getParam('target',null) == 'dialog' ) {
    $config['toolBar'] = array(
        array(
            'text' => Yii::t('system', 'Please Select'),
            'type' => 'button',
            'htmlOptions' => array(
                'class' => 'edit',
                'multLookup' => 'user-grid_c0[]',
                'warn' => Yii::t('users', 'Please select a user'),
                'rel' => Yii::app()->request->getParam('on')=='userId' ? '{target:"to_user_id", url:"users/users/getuserid"}' : "{target:'roleUserPanel1', url: 'users/users/ulist'}",
				'urlname'  => '/users/users/ulist',
            )
        ),
    );
    $config['tableOptions'] = array( 'layoutH' => 126 );
} else {
    $config['columns'][] = array(
        'header' => Yii::t('system', 'Operation'),
        'class' => 'CButtonColumn',
        'headerHtmlOptions' => array('width' => '200', 'align' => 'center'),
        'template' => '{changType}',
        'buttons' => array(
                    'changType' => array(
                                    'label' => Yii::t('system', 'Edit Users'),
                                    'url' => 'Yii::app()->createUrl("/users/users/update", array("id" => $data->id))',
                                    'title' => Yii::t('system', 'Edit Users'),
                                    'options' => array('target' => 'dialog','class'=>'btnEdit'),
                    ),
		),
);
    $config['columns'][] = array(
        'header' => Yii::t('system', ''),
        'class' => 'CButtonColumn',
        'headerHtmlOptions' => array('width' => '120', 'align' => 'center'),
        'htmlOptions' => array(
            'align' => 'center',
        ),
        'template' => '{changPassword}',
        'buttons' => array(
            'changPassword' => array(
                'label' => Yii::t('users', 'Change Password'),
                'url' => 'Yii::app()->createUrl("/users/users/reset", array("id" => $data->id))',
                'title' => Yii::t('users', 'Change Password'),
                'options' => array('target' => 'dialog'),
            ),
        ),
    );
	$config['toolBar'] = array(
        array(
            'text'          => Yii::t('system', 'Batch delete messages'),
            'url'           => '/users/users/delete',
            'htmlOptions'   => array(
                'class'     => 'delete',
                'title'     => Yii::t('system', 'Really want to delete these records?'),
                'target'    => 'selectedTodo',
                'rel'       => 'user-grid',
                'postType'  => 'string',
            	'warn'      => Yii::t('system', 'Please Select'),
                'callback'  => 'navTabAjaxDone',
            )
        ),
    	array(
            'text'          => Yii::t('users', 'Add User'),
            'url'           => '/users/users/create/department_id/'.$departmentId,
            'htmlOptions'   => array(
                'class'     => 'add',
                'target'    => 'dialog',
                'rel'       => 'msgtype-grid',
                'postType'  => '',
                'callback'  => '',
            	'width'  => 700,
            	'height'  => 550,
         )
       ),
		array(
            'text'          => Yii::t('system', 'Batch Disable'),
            'url'           => '/users/users/BatchChangeStatus/type/0',
            'htmlOptions'   => array(
                'class'     => 'edit',
                'title'     => Yii::t('system', 'Really want to delete these records?'),
                'target'    => 'selectedTodo',
                'rel'       => 'user-grid',
                'postType'  => 'string',
            	'warn'      => Yii::t('system', 'Please Select'),
                'callback'  => 'navTabAjaxDone',
            )
        ),
	 	array(
            'text'          => Yii::t('system', 'Batch Enable'),
            'url'           => '/users/users/BatchChangeStatus/type/1',
            'htmlOptions'   => array(
                'class'     => 'edit',
                'title'     => Yii::t('system', 'Really want to delete these records?'),
                'target'    => 'selectedTodo',
                'rel'       => 'user-grid',
                'postType'  => 'string',
            	'warn'      => Yii::t('system', 'Please Select'),
                'callback'  => 'navTabAjaxDone',
            )
        ),
			array(
					'text'          => Yii::t('system', '允许外网访问'),
					'url'           => '/users/users/intranetpass',
					'htmlOptions'   => array(
							'class'     => 'edit',
							'title'     => Yii::t('system', '确定设置允许外网访问?'),
							'target'    => 'selectedTodo',
							'rel'       => 'user-grid',
							'postType'  => 'string',
							'warn'      => Yii::t('system', 'Please Select'),
							'callback'  => 'navTabAjaxDone',
					)
			),
			array(
					'text'          => Yii::t('system', '不允许外网访问'),
					'url'           => '/users/users/intranetnotgo',
					'htmlOptions'   => array(
							'class'     => 'edit',
							'title'     => Yii::t('system', '确定设置取消外网访问?'),
							'target'    => 'selectedTodo',
							'rel'       => 'user-grid',
							'postType'  => 'string',
							'warn'      => Yii::t('system', 'Please Select'),
							'callback'  => 'navTabAjaxDone',
					)
			),
    );
}
$this->widget('UGridView', $config);
?>
<script type="text/javascript">
// function navTabAjaxDone(json){
// 	var obj = eval('('+json+')');
// 	if(obj.message=='设置成功'){
// // 		 navTabPageBreak();
// // 		location.reload();
// 		}	
// }
</script>
