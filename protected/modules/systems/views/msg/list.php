<?php
Yii::app()->clientscript->scriptMap['jquery.js'] = false;
$config = array(
    'id' => 'usermsg-grid',   
    'dataProvider' => $model->search(),
    'filter' => $model,
    'toolBar' => array(
		array(
            'text'          => Yii::t('system', 'Add Message'),
            'url'           => '/systems/msg/create',
            'htmlOptions'   => array(
                'class' 	=> 'add',
				'target' 	=> 'dialog',
				'mask'		=>true,
				'rel' 		=> 'product-grid',
				'width' 	=> '900',
				'height' 	=> '600',
            )
        ),
        array(
            'text'          => Yii::t('system', 'Batch delete messages'),
            'url'           => '/systems/msg/delete',
            'htmlOptions'   => array(
                'class'     => 'delete',
                'title'     => Yii::t('system', 'Really want to delete these records?'),
                'target'    => 'selectedTodo',
                'rel'       => 'usermsg-grid',
                'postType'  => 'string',
                'callback'  => 'navTabAjaxDone',
            )
        ),
        array(
            'text'          => Yii::t('system', 'Batch Mark as Read'),
            'url'           => '/systems/msg/flag',            
            'htmlOptions'   => array(
                'class'     => 'edit',              
                'target'    => 'selectedTodo',
                'rel'       => 'usermsg-grid',
                'postType'  => 'string',
                'warn'      => Yii::t('system', 'Please Select'),
                'callback'  => 'navTabAjaxDone',
            )
        ),
    ),
    'columns' => array(
       array(
            'class' => 'CCheckBoxColumn',          
            'selectableRows' => 2,
            'value' => $model->id,
        ), 
       array(
            'name' => 'id',
            'value' => '$row+1',     
        ),       
       array(
           'name'   => 'msg_type',
           'value'  => 'MHelper::getMsgType($data->msg->msg_type)',
       ), 
       array(
           'name'   => 'msg_title',
           'value'  => '$data->msg->msg_title',
       ),
       array(
           'name'   => 'msg_content',
           'value'  => '$data->msg->msg_content',
       ),   
       array(
           'name'   => 'status',
           'value'  => 'VHelper::getMsgStatusLable($data->status)',
       ),        
       'update_time',
    ),
    'pager' => array(),
    'tableOptions' => array(
        'layoutH' => 135,
    ),
);
if ( Yii::app()->params['isAdmin'] ) {
    $config['columns'][] = array( 
        'name'   => 'user_name',   
        'value'  => 'MHelper::getUsername($data->user_id)'
    );
}
$this->widget('UGridView', $config);
?>
