<?php
Yii::app()->clientscript->scriptMap['jquery.js'] = false;
$this->widget('UGridView', array(
		'id' => 'helpcategoryform-grid',
		'dataProvider' => $model->search(null),
		'filter' => $model,
		'toolBar' => array(
				array(
						'text'          => Yii::t('system', '添加'),  
						'url'           => '/systems/yibaihelper/create',
						'htmlOptions'   => array(
								'class'     => 'add',
								'target' => 'dialog',
								'rel'=>'helpcategoryform-grid',
								'width'=>1000,
								'height'=>800,
						)
				),
				array(
						'text'          => Yii::t('system', '删除'),
						'url'           => '/systems/yibaihelper/delete',
						'htmlOptions'   => array(
								'class'     => 'delete',
								'target' => 'selectedTodo',
								'rel'=>'helpcategoryform-grid',
						)
				)
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
						'name'=> 'title',
						'value'=>'$data->title',
						'htmlOptions' 	=> array('style' => 'width:450px',),
				),
				array(
						'name'=> 'created_at',
						'value'=>'$data->created_at',
						'htmlOptions' 	=> array('style' => 'width:150px',),
				),
				
				array(
						'name'=> '所属分组',
						'value'=>'UebModel::model("HelperCategory")->getCateName($data->category_id)',
						'htmlOptions' 	=> array('style' => 'width:150px',),
				),
				array(
						'name'=> '添加人',
						'value'=>'MHelper::getUsername($data->author_id)',
						'htmlOptions' 	=> array('style' => 'width:150px',),
				),
				array(
						'header' => Yii::t('system', 'Operation'),
						'class' => 'CButtonColumn',
						'headerHtmlOptions' => array('width' => '80', 'align' => 'center'),
						'htmlOptions' => array(
								'align' => 'center',
						),
						'template' => '{view}&nbsp;{update}',
						'buttons' => array(
								'view' => array(
										'label'=>'查看',
										'title'=>'查看新闻',
										'url'=>'Yii::app()->createUrl("/systems/yibaihelper/view",array("id"=>$data->id))',
										'options'=>array(
												'class'=>'btnInfo',
												'target'=>'dialog',
												'width'=>1000,
												'height'=>800,
												'max'=>true  
										)
								),
								'update' => array(
										'label' => '更新',
										'title' => '更新新闻',
										'url'=>'Yii::app()->createUrl("/systems/yibaihelper/newsupdate",array("id"=>$data->id))',
										'options'=>array(
												'class'=>'btnEdit',
												'target'=>'dialog',
												'rel' 		=> 'yibaihelper-new-grid',
												'width'=>1000,
												'height'=>800,
												'mask'=>true
										)
								),
						),
				),
		),
		'tableOptions' => array(
				'layoutH' => 75,
				'style'=>'width:90%',
		),
		'pager' => array(),
));
?>
<style> 
.gridTbody{
   
   padding-bottom: 100px !important; 
   
}

</style> 