<?php
Yii::app()->clientscript->scriptMap['jquery.js'] = false;
$this->widget('UGridView', array(
    'id' => 'downloadfile-grid',
    'template' => THelper::getListTpl(),
    'dataProvider' => $model->search(null),
    'filter' => $model,
	'toolBar' => array(
				    array(
						'text'          => Yii::t('system', 'Batch delete messages'),
						'url'           => '/systems/downloadFile/delete',
						'htmlOptions'   => array(
								'class'     => 'delete',
								'title'     => Yii::t('system', 'Really want to delete these records?'),
								'target'    => 'selectedTodo',
								'rel'       => 'downloadfile-grid',
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
            'name'=> 'id',
            'value'=>'$row+1',     
        ),       
    	array(
    		'name'	=> 'create_user_id',
    		'value' => 'UebModel::model("DownloadFile")->getFileType($data->file_type)',
    	),
    	'file_name',
    	array(
	   		'name'	=> 'create_user_id',
	   		'value' =>'MHelper::getUsername($data->create_user_id)',
        ),
    	'create_time',
    	array(
    			'header' => Yii::t('system', 'Operation'),
    			'headerHtmlOptions' => array('width' => '200', 'align' => 'center'),
    			'type'=>'raw',
    			'value' => 'CHtml::link(Yii::t("system", "Download"), "/systems/downloadFile/download/id/".$data->id."/filePath/".rawurlencode(base64_encode($data->file_path)), array("title"=> Yii::t("system", "Confirm Download"), "class" => "btnDownload", "target" => "ajaxTodo", "callback" => "downloadAjaxDone"))',
    	),
    ),		
    'tableOptions' => array(
        'layoutH' => 140,
    ),
    'pager' => array(),
		
));

?>
<script>
    var downloadAjaxDone = function(json) {        
        if ( json.statusCode == 200 ) {
            location.href = json.filePath;
        } else {
            alertMsg.error($.regional.system.msg.downloadFailed);
        }        
    };
</script>
