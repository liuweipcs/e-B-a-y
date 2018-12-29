<?php

Yii::app()->clientscript->scriptMap['jquery.js'] = false;
$this->widget('UGridView', array(
		'id' => 'lazadaform-grid',
		'dataProvider' => $model->search(null),
		'filter' => $model,
		'toolBar' => array(
				array(
						'text'          => Yii::t('system', 'Add'),
						'url'           => '/systems/shopeeaccount/create',
						'htmlOptions'   => array(
								'class'     => 'add',
								'target'    => 'dialog',
								'rel'       => 'lazadaform-grid',
								'postType'  => '',
								'callback'  => '',
								'width'		=>800,
								'height'	=>600,
						)
				),
				array(
						'text'          => Yii::t('system', 'Delete'),
						'url'           => '/systems/shopeeaccount/delete',
						'htmlOptions'   => array(
								'class'     => 'delete',
								'title' => Yii::t('system', 'Really want to delete these records?'),
								'target' => 'selectedTodo',
								'rel'=>'lazadaform-grid',
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
						'value'=>'$data->seller_name',
						'htmlOptions' 	=> array('style' => 'width:100px',),
				),
				array(
						'name'=> 'shop_id',
						'value'=>'$data->shop_id',
						'htmlOptions' 	=> array('style' => 'width:50px',),
				),
    		    array(
    		        'name'=> 'short_name',
    		        'value'=>'$data->short_name',
    		        'htmlOptions' 	=> array('style' => 'width:100px',),
    		    ),
				array(
						'name'=> 'partner_id',
						'value'=>'$data->partner_id',
						'htmlOptions' 	=> array('style' => 'width:50px',),
				),
				array(
						'name'=> 'secret_key',
						'value'=>'$data->secret_key',
						'htmlOptions' 	=> array('style' => 'width:50px',),
				),
				array(
						'name'=> 'country_code',
						'value'=>'UebModel::model("ShopeeAccount")->getCountryName($data->country_code)',
						'htmlOptions' 	=> array('style' => 'width:50px',),
				),
                array(
                        'name'=> 'group_id',
                        'value'=>'UebModel::model("ShopeeStoreGroup")->getQueryOne($data->group_id)',
                        'htmlOptions' => array('style' => 'width:100px'),
                ),
            array(
                'name'=> '品牌',
                'value'=>'$data->brand',
                'htmlOptions' => array('style' => 'width:100px'),
            ),
			 	array(
						'name'=>'绑定账号',
						'value'=>'$data->user_id>0?UebModel::model("User")->getUserNameAndFullNameById($data->user_id)[$data->user_id]:""',
						'htmlOptions'=>array(
								'style'=>'width:400px'
						)
				),
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
										'url' => 'Yii::app()->createUrl("/systems/shopeeaccount/update", array("id" => $data->id))',
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