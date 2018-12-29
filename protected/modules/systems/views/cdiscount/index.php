<?php
Yii::app()->clientscript->scriptMap['jquery.js'] = false;
$this->widget('UGridView', array(
		'id' => 'cdiscountform-grid',
		'dataProvider' => $model->search(null),
		'filter' => $model,
		'toolBar' => array(
				array(
						'text'          => Yii::t('system', 'Add'),
						'url'           => '/systems/cdiscount/create',
						'htmlOptions'   => array(
								'class'     => 'add',
								'target'    => 'dialog',
								'rel'       => 'cdiscountform-grid',
								'postType'  => '',
								'callback'  => '',
								'width'		=>800,
								'height'	=>600,
						)
				),
				array(
						'text'          => Yii::t('system', 'Delete'),
						'url'           => '/systems/cdiscount/delete',
						'htmlOptions'   => array(
								'class'     => 'delete',
								'title' => Yii::t('system', 'Really want to delete these records?'),
								'target' => 'selectedTodo',
								'rel'=>'cdiscountform-grid',
								'postType' => 'string',
								'callback'=>"navTabAjaxDone",
						)
				)
		),
		'columns' => array(
				array(
						'class' => 'CCheckBoxColumn',
						'selectableRows' => 2,
						'value' =>'$data->id',
						'htmlOptions' 	=> array('style' => 'width:30px'),
						'headerHtmlOptions' 	=> array('style' => 'width:30px'),
				),
				array(
						'name'=> '序号',
						'value'=>'$row+1',
						'htmlOptions' 	=> array('style' => 'width:50px;height:32px;',),
				),
				array(
						'name'=> '账号',
						'value'=>'$data->email',
						'htmlOptions' 	=> array('style' => 'width:100px',),
				),
				array(
						'name'=> '账号名',
						'value'=>'$data->seller_name',
						'htmlOptions' 	=> array('style' => 'width:100px',),
				),

    		    array(
    		        'name'=> 'short_name',
    		        'value'=>'$data->short_name',
    		        'htmlOptions' 	=> array('style' => 'width:100px',),
    		    ),
                array(
                    'name'=> 'group_id',
                    'value'=>'UebModel::model("CdiscountStoreGroup")->getQueryOne($data->group_id)',
                    'htmlOptions' => array('style' => 'width:100px'),
                ),
// 				array(
// 						'name'=> 'Api密码',
// 						'value'=>'$data->token',
// 						'htmlOptions' 	=> array('style' => 'width:280px',),
// 				),
				array(
						'header' => Yii::t('system', 'Operation'),
						'class' => 'CButtonColumn',
						'headerHtmlOptions' => array('width' => '80', 'align' => 'center'),
						'htmlOptions' => array(
								'align' => 'center',
						),
						'template' => '{changCode}',
						'buttons' => array(
								'changCode' => array(
										'label' => Yii::t('system', 'Edit'),
										'url' => 'Yii::app()->createUrl("/systems/cdiscount/update", array("id" => $data->id))',
										'title' => Yii::t('system', 'Edit'),
										'options' => array('target' => 'dialog','class'=>'btnEdit','width'=>800,'height'=>450),
								),
						),
				)
		),
		'tableOptions' => array(
				'layoutH' => 75,
				'style'=>'width:90%',
		),
		'pager' => array(),
));