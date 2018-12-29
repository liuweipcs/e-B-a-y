<?php
Yii::app()->clientscript->scriptMap['jquery.js'] = false;

$this->widget('UGridView', array(
    'id' => 'amazonform-grid', 
	'dataProvider' => $model->search($creiteria),
    'filter' => $model,
	'toolBar' => array(
		array(
			'text'          => Yii::t('system', 'Add'),
			'url'           => '/systems/amazon/create',
			'htmlOptions'   => array(
				'class'     => 'add',
				'target'    => 'dialog',
				'rel'       => 'amazonform-grid',
				'postType'  => '',
				'callback'  => '',
				'width'		=>600,
				'height'	=>450,
			)
		),

        array(
                'text'        =>  '批量添加',
                'url'         => '/systems/amazon/batchcreate',
                'htmlOptions' => array(
                'class'       => 'add',
                'target'      => 'dialog',
                'rel'         => 'amazonform-grid',
                'postType'    => '',
                'callback'    => '',
                'width'       => 600,
                'height'      => 450
            ),
        ),

        array(
                'text'        => '保存修改',
                'url'         => 'javascript:;',
                'urlname'     => '/systems/amazon/batchsort',
                'htmlOptions' => array(
                'class'       => 'edit',
                'onclick'     => 'batchMode(this)'
            )
        ),
	),
    'columns' => array(
       array(
            'class' => 'CCheckBoxColumn',          
            'selectableRows' => 2,
            'value' => $model->id,
       		'htmlOptions' 	=> array('style' => 'width:30px'),
       		'headerHtmlOptions' 	=> array('style' => 'width:30px'),
        ), 
    	array(
            'name'=> 'id',
            'value'=>'$row+1',
    		'htmlOptions' 	=> array('style' => 'width:50px',),
        ),

        array(
            'name' => '排序',
            'type' => 'raw',
            'value' => 'sprintf("<input name=\"sort[]\" dataid=\"%s\" value=\"%s\" style=\"width:30px;\">", $data->id, $data->sort)',
            'htmlOptions' => array('style' => 'width:80px;')
        ),
    	array(
    		'name'=> 'account_name',
            'type'=>  'raw',
    		'value'=>'sprintf("<div style=\"background:%s\">%s</div>", $data->color, $data->account_name)',
    		'htmlOptions' 	=> array('style' => 'width:100px;;',),
    	),
    	array(
    		'name'=> 'short_name',
    		'value'=>'$data->short_name',
    		'htmlOptions' 	=> array('style' => 'width:100px',),
    	),
		array(
			'name'=> 'merchant_id',
			'value'=>'UebModel::model("Amazon")->getAmazonMd5Val($data->merchant_id)',
			'htmlOptions' 	=> array('style' => 'width:130px',),
		),	
		array(
			'name'=> 'market_place_id',
			'value'=>'$data->market_place_id',
			'htmlOptions' 	=> array('style' => 'width:130px',),
		),		
		array(
			'name'=> 'secret_key',
			'value'=>'UebModel::model("Amazon")->getAmazonMd5Val($data->secret_key)',
			'htmlOptions' 	=> array('style' => 'width:130px',),
		),			
				
		array(
			'name'=> 'aws_access_key_id',
			'value'=>'UebModel::model("Amazon")->getAmazonAwsaccesskeyid($data->aws_access_key_id)',
			'htmlOptions' 	=> array('style' => 'width:130px',),
		),	
		array(
			'name'=> 'service_url',
			'value'=>'$data->service_url',
			'htmlOptions' 	=> array('style' => 'width:200px',),
		),
        array(
            'name'=> 'amzsite_email',
            'value'=>'$data->amzsite_email',
            'htmlOptions'   => array('style' => 'width:200px',),
        ),
		array(
    		'name'=> 'group_id',
    		'value'=>'UebModel::model("Amazon")->getAmazonAccountGroup($data->group_id)',
    		'htmlOptions' 	=> array('style' => 'width:60px',),
    	),
    	array(
    		'name'=> 'status',
    		'value'=>'UebModel::model("Amazon")->getAmazonAccountStatus($data->status)',
    		'htmlOptions' 	=> array('style' => 'width:50px',),
    	),	
    		
    	array(
    		'header' => Yii::t('system', 'Operation'),
    		'class' => 'CButtonColumn',
    		'headerHtmlOptions' => array('width' => '60', 'align' => 'center'),
    		'htmlOptions' => array(
    			'align' => 'center',
    		),
    		'template' => '{changCode}{view}',
    		'buttons' => array(
    			'changCode' => array(
    				'label' => Yii::t('system', 'Edit'),
    				'url' => 'Yii::app()->createUrl("/systems/amazon/batchupdate", array("id" => $data->id))',
    				'title' => Yii::t('system', 'Edit'),
    				'options' => array('target' => 'dialog','class'=>'btnEdit','width'=>600,'height'=>450),
    			),
                'view' => array(
                    'label' => Yii::t('system', 'View'),
                    'url' => 'Yii::app()->createUrl("/systems/amazon/update", array("id" => $data->id))',
                    'title' => Yii::t('system', 'View'),
                    'options' => array('target' => 'dialog', 'class' => 'btnView', 'width' => 600, 'height' => 450),
                ),
    		),
    	)
    ),
    'tableOptions' => array(
        'layoutH' => 75,
    ),
    'pager' => array(),
));

?>
<script type="text/javascript">
    function batchMode(obj) {
        var $tr = $(obj).closest('#amazonform-grid').find('tbody tr');
        var arr = [];

        $tr.each(function (i, e) {
            var $input = $(e).find('input[name="sort[]"]');
            arr.push({id:$input.attr('dataid'), val:$input.val()});
        });

        $.ajax({
            url : '/systems/amazon/batchsort',
            type: 'post',
            data: {batch:arr},
            success: function (da) {
                alertMsg.correct('保存成功');
            }
        });

        console.log(arr);
    }
</script>
