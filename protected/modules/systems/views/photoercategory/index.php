<?php
Yii::app()->clientscript->scriptMap['jquery.js'] = false;
$this->widget('UGridView', array(
		'id' => 'photoercategoryform-grid',
		'dataProvider' => $model->search(null),
		'filter' => $model,
		'toolBar' => array(
				array(
						'text'          => Yii::t('system', 'Add'),
						'url'           => '/systems/photoercategory/create',
						'htmlOptions'   => array(
								'class'     => 'add',
								'target'    => 'dialog',
								'rel'       => 'photoercategoryform-grid',
								'postType'  => '',
								'callback'  => '',
								'width'		=>800,
								'height'	=>600,
						)
				),
				array(
						'text'          => Yii::t('system', 'Delete'),
						'url'           => '/systems/photoercategory/delete',
						'htmlOptions'   => array(
								'class'     => 'delete',
								'title' => Yii::t('system', 'Really want to delete these records?'),
								'target' => 'selectedTodo',
								'rel'=>'photoercategoryform-grid',
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
						'name'=> 'user_id',
						'value'=>'MHelper::getUsername($data->user_id)',
						'htmlOptions' 	=> array('style' => 'width:100px',),
				),
				array(
						'name'=>'店铺商品种类',
						'value'=>'UebModel::model("ProductCategory")->getCategoryCnNames($data->category_id)',
						'htmlOptions'=>array(
								'style'=>'width:300px'
						)
				),
            array(
                'name'=> '角色',
                'value'=> 'UebModel::model("PhotoerCategory")->getPhotoType($data->photo_type)',
                'htmlOptions' => array('style' => 'width:100px'),
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
										'url' => 'Yii::app()->createUrl("/systems/photoercategory/update", array("id" => $data->id))',
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