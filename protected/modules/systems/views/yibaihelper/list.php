<?php
Yii::app()->clientscript->scriptMap['jquery.js'] = false;
$this->widget('UGridView', array(
		'id' => 'helpcategoryform-grid',
		'dataProvider' => $model->listSearch(null),
		'filter' => $model,
		'columns' => array(
				array(
						'name'=> 'id',
						'value'=>'$data->id',
						'htmlOptions' 	=> array('style' => 'width:30px',),
				),
				array(
						'name'=> 'title',
						'type'=>'raw',
						'value'=>'Chtml::link($data->title,"/systems/yibaihelper/view/id/$data->id",array("target"=>"dialog","max"=>true))',
						'htmlOptions' 	=> array('style' => 'width:350px',),
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
						'template' => '{view}',
						'buttons' => array(
								'view' => array(
										'label'=>'查看',
										'title'=>'查看新闻',
										'url'=>'Yii::app()->createUrl("/systems/yibaihelper/view",array("id"=>$data->id))',
										'options'=>array(
												'class'=>'btnInfo',
												'target'=>'dialog',
												// 'width'=>1000,
												// 'height'=>800,
												'overflow'=>'auto',
												'max'=>true
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