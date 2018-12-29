
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
           'htmlOptions'=> array('style' => 'width:10px;height:32px;',),
        ), 
       array(
            'name' => 'id',
            'value' => '$row+1',
           'htmlOptions'=> array('style' => 'width:30px;height:32px;',),
        ),
        array(
            'name' => 'user_name',
            'value' => '$data->user_name',
            'htmlOptions'=> array('style' => 'width:80px;height:32px;',),
        ),
        array(
            'name' => 'en_name',
            'value' => '$data->en_name',
            'htmlOptions'=> array('style' => 'width:80px;height:32px;',),
        ),
        array(
            'name' => 'user_full_name',
            'value' => '$data->user_full_name',
            'htmlOptions'=> array('style' => 'width:80px;height:32px;',),
        ),
    	array(
            'name'  => 'department_id',
            'value' => '$data->department_id>0 ? UebModel::model("Department")->getDepartment($data->department_id):"--"',
            'htmlOptions'=> array('style' => 'width:80px;height:32px;',),
        ),
        array(
            'name'  => 'user_status',
            'value' => 'VHelper::getStatusLable($data->user_status)',
            'htmlOptions'=> array('style' => 'width:80px;height:32px;',),
        ),
    		array(
    				'name'  => 'is_intranet',
    				'value' => 'empty($data->is_intranet) ? "不允许":"允许"',
                'htmlOptions'=> array('style' => 'width:80px;height:32px;',),
    		),
    		array(
    				'name'  => '采购',
    				'type'=>'raw',
    				'value' => 'UebModel::model("UserExtraPlatform")->getAccessplatforms($data->id,UserExtraPlatform::SITE_PURCHASE)',
    				'htmlOptions'=> array('style' => 'width:40px;'),
    		),
    		array(
    				'name'  => 'wms',
    				'type'=>'raw',
    				'value' => 'UebModel::model("UserExtraPlatform")->getAccessplatforms($data->id,UserExtraPlatform::SITE_WMS)',
    				'htmlOptions'=> array('style' => 'width:40px;'),
    		),
    		array(
    				'name'  => '客服',
    				'type'=>'raw',
    				'value' => 'UebModel::model("UserExtraPlatform")->getAccessplatforms($data->id,UserExtraPlatform::SITE_CUSTOMER_SERVICE)',
    				'htmlOptions'=> array('style' => 'width:40px;'),
    		),
    		array(
    				'name'  => '物流',
    				'type'=>'raw',
    				'value' => 'UebModel::model("UserExtraPlatform")->getAccessplatforms($data->id,UserExtraPlatform::SITE_LOGISTICS)',
    				'htmlOptions'=> array('style' => 'width:40px;'),
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
                'rel' => Yii::app()->request->getParam('on')=='userId' ? '{target:"to_user_id", url:"users/users/getuserid"}' : "{target:'roleUserPanel', url: 'users/users/ulist'}",
				'urlname'  => '/users/users/ulist',
            )
        ),
    );
    $config['tableOptions'] = array( 'layoutH' => 126 );
} else {
    $config['columns'][] = array(
        'header' => Yii::t('system', 'Operation'),
        'class' => 'CButtonColumn',
        'headerHtmlOptions' => array('width' => '50px', 'align' => 'center'),
        'template' => '{changType}<br/>{checkAccess}',
//     		<a class="btnSelect" title="确认授权">确认授权</a>
        'buttons' => array(
                    'changType' => array(
                                    'label' => '编辑资料',
                                    'url' => 'Yii::app()->createUrl("/users/users/update", array("id" => $data->id))',
                                    'title' => Yii::t('system', 'Edit Users'),
                                    'options' => array('target' => 'dialog','class'=>'btnEdit'),
                    ),
        		'checkAccess' => array(
        				'label' => '授权',
        				'url' => 'Yii::app()->createUrl("/users/users/access", array("id" => $data->id))',
        				'title' => '授权',
        				'options' => array(
        						'onclick'=>'checkAccess(this);return false;',
        						'class'=>'checkaccess'
        				),
        		),
		),
);
    $config['columns'][] = array(
        'header' => Yii::t('system', ''),
        'class' => 'CButtonColumn',
        'headerHtmlOptions' => array('width' => '50px', 'align' => 'center'),
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
function checkAccess(obj){
	var parent = $(obj).parent('td').parent('tr').find('input[type=checkbox]');
	var params = [];
	for(var i =0;i<parent.length;i++){
		if(!$(parent[i]).attr('checked')){
			continue;
		}
		params.push('"' + $(parent[i]).attr('name') + '":"' +$(parent[i]).val() + '"');
	}
	$.post($(obj).attr("href"),$.parseJSON('{'+params.join(',')+'}'),function(data){
		data = $.parseJSON(data);
		if(data.statusCode==300){
			alertMsg.warn(data.message);
		}else{
			alertMsg.correct(data.message);
		}
	});
	return false;
}
</script>
