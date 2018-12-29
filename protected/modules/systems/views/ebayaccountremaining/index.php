<style type="text/css">
    .grid .gridTbody td div{
        height:auto;
        overflow: hidden;
    }
</style>
<script type="text/javascript">
    $(function() {
        $("img").lazyload({
            effect : "fadeIn"
        });
    });
</script>
<?php
Yii::app()->clientscript->scriptMap['jquery.js'] = false;
$this->widget('UGridView', array(
    'id' => 'EbayAccountRemaining-grid',
    'dataProvider' => $model->search(null),
    'filter' => $model,
    'columns' => array(
        array(
            'class' => 'CCheckBoxColumn',
            'selectableRows' => 2,
            'value' => $model->id,
            'headerHtmlOptions' => array('style' => 'width:27px'),
        ),
        array(
            'name' => 'count',
            'value' => '$row+1',
            'htmlOptions' => array('style' => 'width:25px;text-align:center;'),
        ),
        array(
            'name'	=>	'account_id',
            'value'	=>	'(new Ebay())->findByPk($data->account_id)->user_name',
            'htmlOptions'  => array('style' => 'width:110px;')
        ),
		array(
			'name' => 'store_level',
			'value'=>'$data->store_level',
			'htmlOptions'=>array('style'=>'width:100px;'),
		),
		array(
			'name' => 'store_site',
			'value'=>'$data->store_site',
			'htmlOptions'=>array('style'=>'width:100px;'),
		),
        array(
            'name'	=>	'current_balance',
            'value'	=>	'$data->current_balance',
            'htmlOptions'  => array('style' => 'width:100px;')
        ),
        array(
            'name'	=>	'current_balance_currency',
            'value'	=>	'$data->current_balance_currency',
            'htmlOptions'  => array('style' => 'width:50px;')
        ),
        array(
            'name'	=>	'amount_limit_remaining',
            'value'	=>	'$data->amount_limit_remaining',
            'htmlOptions'  => array('style' => 'width:100px;')
        ),
        array(
            'name'	=>	'amount_limit_remaining_currency',
            'value'	=>	'$data->amount_limit_remaining_currency',
            'htmlOptions'  => array('style' => 'width:80px;')
        ),
        array(
            'name'	=>	'quantity_limit_remaining',
            'value'	=>	'$data->quantity_limit_remaining',
            'htmlOptions'  => array('style' => 'width:80px;')
        ),
        array(
            'name'	=>	'active_auction_count',
            'value'	=>	'$data->active_auction_count',
            'htmlOptions'  => array('style' => 'width:80px;')
        ),
        array(
            'name'	=>	'auction_bid_count',
            'value'	=>	'$data->auction_bid_count',
            'htmlOptions'  => array('style' => 'width:80px;')
        ),
        array(
            'name'	=>	'auction_selling_count',
            'value'	=>	'$data->auction_selling_count',
            'htmlOptions'  => array('style' => 'width:80px;')
        ),
        array(
            'name'	=>	'total_auction_selling_value',
            'value'	=>	'$data->total_auction_selling_value',
            'htmlOptions'  => array('style' => 'width:80px;')
        ),
        array(
            'name'	=>	'total_auction_selling_value_currency',
            'value'	=>	'$data->total_auction_selling_value_currency',
            'htmlOptions'  => array('style' => 'width:80px;')
        ),
        array(
            'name'	=>	'total_sold_count',
            'value'	=>	'$data->total_sold_count',
            'htmlOptions'  => array('style' => 'width:80px;')
        ),
        array(
            'name'	=>	'total_sold_value',
            'value'	=>	'$data->total_sold_value',
            'htmlOptions'  => array('style' => 'width:80px;')
        ),
        array(
            'name'	=>	'total_sold_value_currency',
            'value'	=>	'$data->total_sold_value_currency',
            'htmlOptions'  => array('style' => 'width:80px;')
        ),
        array(
            'name'	=>	'unsold_num',
            'value'	=>	'$data->unsold_num',
            'htmlOptions'  => array('style' => 'width:80px;')
        ),
        array(
            'name'	=>	'update_summary_time',
            'value'	=>	'date("Y-m-d H:i:s",$data->update_summary_time)',
            'htmlOptions'  => array('style' => 'width:80px;')
        ),
    ),
    'toolBar'       => array(
        array(
            'text'          => '更新',
            'url'           => '/systems/ebayaccountremaining/update',
            'htmlOptions'   => array(
                'class'     => 'add',
                'title'     => '确实要更新这些数据吗？',
                'target'    => 'selectedTodo',
                'rel'       => 'EbayAccountRemaining-grid',
                'postType'  => 'string',
                'warn'      => Yii::t('system', 'Please Select'),
                'callback'  => 'navTabAjaxDone',
            )
        ),
        array(
            'text'          => '导出EXCEL',
            'url'           => '/systems/ebayaccountremaining/export',
            'htmlOptions'   => array(
                'class'     => 'add',
                'title'     => '确实要导出这些记录吗?',
                'target'    => 'dwzExport',
                'targetType'=> 'navTab',
//                'rel'       => 'EbayAccountRemaining-grid',
//                'postType'  => 'string',
                'warn'      => Yii::t('system', 'Please Select'),
//                'callback'  => 'navTabAjaxDone',
            )
        ),
        array(
            'text'          => '更新Unsold',
            'url'           => '/systems/ebayaccountremaining/updatesold',
            'htmlOptions'   => array(
                'class'     => 'delete',
                'title'     => '确实要更新这些数据吗？',
                'target'    => 'selectedTodo',
                'rel'       => 'EbayAccountRemaining-grid',
                'postType'  => 'string',
                'warn'      => Yii::t('system', 'Please Select'),
                'callback'  => 'navTabAjaxDone',
            )
        ),
    ),
    'tableOptions' 	=> array(
        'layoutH' 	=> 120,
    ),
    'pager' 		=> array()
));

?>
