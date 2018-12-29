<?php
/**
 * Controller Action 语言包
 * 说明:该语言包格式为以控制器为单位的二维数组，键值为该模块对应的控制器名（去掉Controller），对应的底层数组为该控制器下面的action方法名,
 * 总格式:
 * array(
 * 		'common'=>array(),								//通用块
 * 		'product'=>array(),								//ProductController模块里的action，全部小写
 *		'productattrval'=>array(),                     //ProductattrvalController模块里的action，全部小写
 *		...
 *		...
 *		...
 * )
 * @author Gordon
 * @since 2014-06-07
 * @DESCRIPTION
 */
return array(
		'common'=>array(
			'create'									=> '新建',
			'update'									=> '更新',
			'delete'									=> '删除',
			'view'										=> '查看',
			'exist'										=>'是否存在',
			'list'										=>'列表',
			'index'										=>'列表',
			'export'									=>'导出',
			'unique'									=>'检查唯一性',
			'cancel'									=>'取消',
		),
		'module'=>array(
			'logistics'									=> '物流模块',
			'logs'										=> '日志模块',
			'products'									=> '产品模块',
			'purchases'									=> '采购模块',
			'systems'									=> '系统模块',
			'users'										=> '用户模块',
			'warehouses'								=> '仓库模块',
			'commons'									=> '公共模块',
			'auth'										=> '平台模块',
			'orders'									=> '订单模块',
			'pda'										=> '手持PDA模块',
			'report'									=> '报表模块',
			'report'									=> '报表模块',
			'services/aliexpress'						=> '速卖通模块',
			'services/amazon'							=> '亚马逊模块',
			'services/ebay'								=> '易贝模块',
			'services/warehouse'						=> '仓库模块',
			'services/wish'								=> 'WISH模块',
		),
		
		'products'=>array(
			'exception' => '产品异常处理',
			'productdevelopment' => '新品开发',
			'productjob' => '新品审核',
			'product' => '产品管理',
			'productattrsel' => '产品属性值管理',
			'productdescription' => '产品描述管理',
			'productattr' => '产品属性列表',
			'productattrval' => '产品属性值',
			'productcat' => '产品分类',
			'productpricecalculation' => '产品价格计算',
			'productsales' => '产品销售',
			'productdropshipping' => '分销产品',
			'productabroad' => '海外仓产品',
			'productimage' => '产品图片',
			'productstockprice' => '产品库存',
			'productbrand' => '品牌管理',
			'productbind' => '捆绑管理',
			'productadapter' => '产品转接头设置',
			'productrole' => '产品权限分配',
			'productstatus' => '产品状态设置',
			'producttowaypackage' => '产品包装方式',
			'productadinfo' => '产品广告信息',
			'productinfringe' => '产品安全级别',
			'productlogisticsinfo' => '产品物流设置',
			'productrecordquery' => '产品记录查询',
		),
		
		
		/**
		 * ***************************采购模块****************************
		 */

		//供应商管理
		'provider'=>array('getcode' =>'获取供应商名称'),
		//供应商等级
		'providerlevel'=>array(),
		'purchaseinfo'=>array(),
		//询价管理
		'purchaseinquire'=>array(
			'check'										=>'批量审核/取消审核',
			'addskuinquire'							=>'从询价管理添加询价',
			'add'											=>'从采购需求页添加询价',
			'getinquireinfo'							=>'获取询价信息',
		),
		//采购日志
		'purchaselog'=>array(),
		//采购请款记录
		'purchaseorderapplypayment'=>array(
			'check'										=>'批量审核/取消审核',
			'batchpayment'							=>'批量付款',
			'updateapply'							=>'批量修改请款',
			'printindex'								=>'打印请款',
			'delete'										=>'删除请款',
			
		),
		//采购请款退款记录
		'purchaseorderapplyrefund'=>array(
			'applyrefund'							=>'请求退款',
			'check'										=>'确认退款',
			'cancel'										=>'取消退款',
		),
		//采购单管理
		'purchaseorder'=>array(
			'createpurchaseorder'						=>'生成采购单',
			'batchapplypayment'							=>'批量请款',
			'applyrefund'									=>'申请退款',
			'batchupdatepurchaseorder'				=>'批量修改采购单',
			'checklsstatus'							=>'检测采购详情是否已审核',
			'check'										=>'批量审核/取消审核',
			'print'										=>'打印',
			'batchprint'								=>'批量打印',
			'batchprint2'								=>'批量打印2',
			'purchaseorderno'						=>'查看流水信息',
			'barcode'									=>'条形码',
			'updatefee'								=>'修改运费',
		),
		//未到货产品管理
		'purchaseorderdetail'=>array(
			'check'										=>'批量审核/取消审核',
			'batchupdate'								=>'批量修改',
		),
		//采购付款记录
		'purchaseorderpayment'=>array(
			'showpurchaseorderpayment'					=>'显示采购付款订单'
		),
		//特采管理
		'purchaserequire'=>array(
			'addrequire'								=>'添加采购',
			'check'										=>'批量审核/取消审核',
			'batchupdate'							=>'批量修改',
			'updateqty'								=>'修改采购数量',
			'create'										=>'添加特采',
			'delete'										=>'批量删除采购需求',
		),
		//采购附约管理
		'purchasetreaty'=>array('getpurchasetreatyid'	=>'获取采购附约'),
	
		
		/**
		 * ***********************产品模块************************
		 */
		//产品管理
		'product'=>array(
			'selectproduct'							   =>'选择产品',
			'batchupdate'							   =>'批量修改',
			'batchupdatequery'						   =>'按查询结果批量修改',
			'checkInventory'						   =>'查看库存',
			'basec'									   =>'基本资料',
			'updatestatus'							   =>'修改状态',
			'baseu'							   		   =>'修改产品',
			'getchild'							  	   =>'获取下一级',
			'getbindSku'							   =>'获取绑定的SKU',
			'checksku'							  	   =>'验证SKU',
			'getsku'							  	   =>'获取SKU',
			'getproductlistbysku'					   =>'获取SKU及子SKU信息',			
		),
		//产品管理
		'productdevelopment'=>array(
			'basec'									   =>'基本资料',
			'baseu'							   		   =>'修改产品',	
			'deleteimage'							   =>'删除图片',	
		),
		//转接头
		'productadapter'=>array(),
		//广告信息
		'productadinfo'=>array(),
		//产品属性值
		'productattrval'=>array(
			'getmore'									=>'多语言翻译'
		),
		//产品品牌
		'productbrand'=>array(),
		//产品任务管理
		'productobj'=>array(),
		//产品属性
		'productattr'=>array(
			'getmore'									=>'多语言翻译'
		),
		//产品属性值管理
		'productattrsel'=>array(
			'multi'										=>'多属性',
			'multirow'									=>'多行属性',
			'multidelete'								=>'多属性删除',
		),
		//产品绑定
		'productbind'=>array(
			'skuexists'									=>'SKU是否存在',
			'selectsku'									=>'SKU选择',			
		),
		//分类管理
		'productcat'=>array(
			'getattr'									=>'获取attr',
			'getisusedstatus'							=>'获取使用状态',
			'updatecategory'							=>'修改分类',
			'translatecategory'							=>'分类翻译',
			'getcattree'								=>'获取分类attr',
			
		),
		//产品描述
		'productdescription'=>array(
			'getdescbyskuandenglishcode'				=>'获取某SKU产品的英文描述,以进行其它语言的自动翻译',
			//'getdescbyskuandenglistcode'				=> 'getdescbyskuandenglistcode (无效)',
		),
		//产品图片
		'productimage'=>array(
			'moveimg'									=>'一键处理图片',
			'view1'										=>'缩略图展示',
		),
		//产品人员管理
		'productrole'=>array(
			'prolist'									=>'列表',
			'getuser'									=>'根据角色获取用户名',
			'getskubycondition'							=>'根据条件获取SKU',
			'skuexists'									=>'SKU是否存在',
		),
		//产品销量
		'productsales'=>array(
			'autogenerate'=>'自动生成',
		),
		/**
		 * **************************PDA管理模块*****************************
		 */
		//pda
		'pad'=>array(
			'pdacreate'                                => '移库信息显示',
			'pdainsert'                                => '创建移库单',
			'pdainsertto'                              => '创建移库单的出库操作',
			'pdareadyin'                               => '创建移库单的入库信息',
			'pdaupdate'                                => '创建移库单的入库操作',
			'insertout'                                => '库存转移的出库操作(ajax)',
			'insertto'                                 => '库存转移的入库操作',		 
			'readyin'                                  => '只出库未入库的入库操作',
		),
		//拣货任务
		'pdapick'=>array(
		//	'typelist'                                 => '类型列表(无效)',
			'missionlist'                              => '任务页面',
			'missiondetail'                            => '拣货任务详情',
			'missiondetailcontent'                     => '拣货任务详情',
			'missionlistcontent'                       => '根据状态得到任务列表',
		),
		//库位管理
		'pdastockdetail'=>array(
			'getlocationlistbywarehouseid'			   =>'根据仓库号获取库位号',
			'getlocationinfobyid'					   =>'根据Id获取库位信息',
		),
		//入库操作
		'pdastockin'=>array('pdaindex'                 => '出入库操作'),
		//出库操作
		'pdastockout'=>array('pdaindex'                => '出入库操作'),
		//库存轮盘
		'pdastockturncheck'=>array(
			'detail'                                   => '(PDA端)SKU清单',
			'form'                                     => '轮盘SKU详情',
			'updateqty'                                => '盘点数量更新',
			'stockupdate'							   => '非手持端盘点数量',
			'detailinfo'							   => 'PC端SKU清单',
		),

		/**
		 * *******************订单模块********************
		 *
		 **/
		
		//订单
		'order'=>array(
			'changewarehouse'	 						=> '修改返货仓库',
			'payment'									=> '付款',
			'resend' 									=> '订单重寄',
			'detail' 									=> '订单详情',
			'createpackage'								=> '创建包裹',
			'getlogistics' 								=> '获取物流方式(ajax)',
			'prepareorder' 								=> '订单检测',
			'saveservice'								=> '客服留言',
			'update'									=> '修改订单',
			'Exportfile'								=> '海外仓订单导出',
		),
		//异常订单
		'orderexceptioncheck'=>array(
			'markfinished' 								=> '手动标记处理完成',
			'release' 									=> '放行订单',
			'batchrelase' 								=> '批量放行',
			'setship' 									=> '设置异常订单可发',
			'cancelrelease'								=> '取消放行',
		),
		//海外仓订单
		'orderimport'=>array(
			'loadorderdata'								=> '订单导入',
			'uploadfile'								=> '获取海外仓',
		//	'hiddeniframe'								=> 'hiddeniframe(无效)',
			'orderimport'								=> '手动导入订单',
		),
		//订单日志
		'orderlog'=>array(),
		//NOTE
		'ordernote'=>array('sign'=>'标记处理',),
		//订单包裹
		'orderpackage'=>array(
			'orderprintcreatenum' 						=> '批次拣货',
			'merge' 									=> '合并包裹',
			'split' 									=> '包裹拆分',
			'cancelship' 								=> '取消发货(已注释)',
			'createtracknum' 							=> '获取track NO.',
			'signallship' 								=> '批量标记发货(没用到)',
			'create4pxsg' 								=> '创建第四方sg(无调用)',
			'shipinfo'                         		 	=> '包裹发货详情',
			'ship'										=> '包裹出货',
			'shipnew'									=> '包裹出货(新)',
			'shiplist'									=> '包裹列表',
			'cancelallship'								=> '取消包裹',
			'checkgzfile'								=> '广州txt文件导入',
			'Importpackagedataghxbcn'					=> '深圳挂号导入',
			'exportfile'								=> '文件导出',
			'import'									=> '文件导入',
		),
		
		
		'orderpackagekf' => array(
			'cancelpackage'								=> '批量取消包裹',
			'PackageKf'									=> '客服处理',
			'check'										=> '扫描客服包裹',
		),
		
		
		
		
		//订单包裹日志
		'orderpackagelog'=>array(),
		//待处理订单
		'orderpendingall'=>array(
			'add' 										=>'添加',
			'createpackage'								=>'手动创建包裹',
		),
		//检验待处理订单是否可发货
		'orderpengding'=>array(
			'checkpendingordership'						=>'检测欠货的待处理订单是否可以发货(是否库存已补齐)',
		),
		//订单打印
		'orderprint'=>array(
			'printPDF'									=>'打印PDF',
		),
		//订单规则
		'orderrule'=>array(
			'getiflist' 								=>'获取if规则列表',
			'getifformlable' 							=>'获取if规则表单lable(ajax)',
			'updatesort' 								=>'更新排序',
		),
		//订单退款记录
		'ordertransaction'=>array(
			'detail' 									=>'订单详情',
			'refundlog'									=>'订单退款记录',
			'refund' 									=>'退款',
			
		),
	//	'ordershipview'=>array(
			//'aa'=>'aa(无效)',
	//	),
		
		'ordergiftlog'	=> array(
			'giftorderdone' 							=> '批量处理'
		),
 
		
		/**
		 *  ************************仓库模块*********************
		 * */
		
		'productweight'=>array(
			'weight'							=> '重量',
		),
		'purchaseorderreceipt'=>array(
			'createqc'                          => '创建QC人员',
			'updateqc'                          => '更新QC人员',
			'skuprint'                          => 'SKU打印',
			'confirmgood'                       => '确认良品',
			'confirmgoodpack'					=> '确认良品号',
			'getpurchaseorderdetail'       		=> '获取采购订单详情',
			'pending'                           => '设置为待处理',
		),
		'purchaseorderstockin'=>array(
			'creategrn'                         => '创建入库单',
			'batchprint'						=> '批量打印',
			'stockin' 							=> '入库',
			'warehousesku'                      => 'SKU入库数量列表', 
			'addlocation'                       => '检测能否入库',
			'updateqty'                         => '盘点数量更新',
		),
		'purchasestockinqcrecord'=>array(
			'createqc'							=> '创建QC人员',
		),
		'stockallot'=>array(
			'batchdelete'						=> '批量删除',
			'stockin' 							=> '入库',
		),
		'stocktaking'=>array(
			'assigntask' 						=> '分配轮盘任务',
			'getleftjod' 						=> 'getleftjod(ajax)',
			'inventorydetails'					=> '库存详情',
			'result'							=> '结果'
		),
		'warehousearea'=>array('arealist' 		=> '仓库区域列表'),
		'warehouse'=>array(
			'batchstart'                        => '批量开启',
			'batchstop'                         => '批量停止',
			'getcode' 							=>'获取供应商名称',
			'list' 								=>'仓库列表'
		),
		'warehouselocation'=>array(
			'change'							=> '修改库位',
			'getlocationinfobyid'               => '获取库位信息',
			'getlocationlistbywarehouseid'      => '根据仓库ID获取库位列表',
		),
		'warehouselocationskumap'=>array(
			'refreshorder' 						=> '刷新库位排序号',
			'selectproduct' 					=> '选择产品',			
		),
		'warehouseshelf'=>array(
			'shelflist' 						=> '区域货架列表',			
		),
		'warehouseshelfrules'=>array(
			'shelfrules' 						=> '货架生成规则',
		),
		'warehousesstockmanage'=>array(
			'page'                              => '产品入库单查询',
		),

        
        /**
         * **********************系统管理********************************
         */
		'autocode'=>array('get'  				=> '获取'),
		'dashboardrole'=>array('person'         => '个性化设置'),
		'downloadfile'=>array('download'		=> '下载'),
		'eventcontrol'=>array('stop'            => '手动终止事件'),
		'exceldefaultcolumn'=>array(
			'gettablenames'						=> '获取表',
			'getcolumnfields'					=> '获取字段',
			'getothertabletr'					=> '获取其他表',
			'getothercolunmfield'				=> '获取其他字段',
					
		),
		//'execlschemecolum'=>array(
			//'reportlist'						=> 'reportlist(无效)',
		//),
		'excelscheme'=>array(
			'getexceltemplateattr'				=> '获取模板属性',
			'saveschemename'					=> '保存execl方案名字',
			'getexcelschemename'				=> '获取execl方案名字',
			'getallschemename'					=> '获取所有方案名字',
			'getselectdatabyvalue'				=> '根据值获取下拉列表数据',
			'getfixedcolumnattr'				=> '获取固定的字段',
			'deleterelateddata'					=> '删除有关数据',
			'getcustomschemename'				=> '获取自定义方案名字',			
		),
		'excelschemetype'=>array(
			'getdatabasetables'					=> '获取数据的表',
			'getdatabasetr'						=> '获取数据库',
		),
		'language'=>array('trans'				=> '跨'),
		'menu'=>array(
			'assign'                            => '分配资源',
			'ulist'                             => '用户角色资源',
			'refreshhistoryurl'                 => '更新历史路径',
			'tasktree'                          => '权限任务列表',
			'operationlist'                     => '显示权限操作列表',
			'updatemenuitem'                    => '权限失效修复程序',
			'distributeresourceauto'            => '自动分配操作资源到订单',
		),
		'msg'=>array(
			'flag'								=> '标志',
			'get'								=> '获取',		
		),
		//'platform'=>array(
			//'getoptions'						=> '获取选项(无效)',
		//),
		'region'=>array(
			'area'                              => '获取地区信息',
			'city'		                        => '城市列表',
			'select'							=> '选择',
			'getinfobyparentid'                 => '区域详情',
		),
		'sysconfig'=>array(
			'person'         					=> '个性化设置',
			'product'                           => '产品设置',
			'euboffline'                        => '线下E邮宝',
			'eubonline'                         => '线上E邮宝',
			'global'                            => '全局设置',
			'refresh'                           => '刷新缓存',
		),

	 
        
        /**
         * ********************user**************************
         * 
         */
        'access'=>array(
	        'selectown'                         => '根据角色获取用户名',
	        'selectuser'                        => '选择角色 和用户名',
		),
		'dep'=>array(
			'ulist'								=> '用户列表',
		),
		'roles'=>array(
			'ulist'								=> '角色资源',
			'userlist'							=> '用户列表',
			'rdelete'							=> '角色删除',
		),
		'userlistshow'=>array(
			'refresh'							=> '刷新',
		),
		'user'=>array(
			'change'                            => '修改用户密码',
			'copyauth'                          => '复制权限',
			'ulist'								=> '角色资源',
			'getuserid'                         => '获取用户id(josn)',
		),
        /**
         **********************services****************************
         */
		'aliexpress'=>array(
			'getorder'							=> '同步抓取速卖通订单',
		),
		'aliexpresstoken'=>array(
			'gettoken'							=> '将获取到的token信息保存至数据库中',
		),
		'ebaycategory'=>array(
			'getcategory'						=> 'Ebay获取分类',
		),
		'ebay'=>array(
			'getorders'							=> '获取订单',
			'a'									=> 'a',
			'test'								=> '检验',
			'getbalance'						=> '获取差额',
		),
		'ebayorder'=>array(
			'getorders'							=> 'Ebay拉取订单',
		),

		
		
		/**
		 ************************ 物流模块*************************
		 */	
		
		'abroadship' 		=> array(
				//'abolish'						=> 'abolish(无效)',
				//'receivin'						=> 'receivin(无效)',
				'ship'							=> '确认发货',
				'receiving'						=> '确认到货',
				'recover'						=> '恢复货单',
				'cancelship'					=> '取消发货',
				'checkskunum'					=> 'checkskunum(返回的是布尔值)',
				'checkskucount'					=> 'checkskucount(返回的是布尔值)',
				'print'							=> '打印货单',
				'loadorderdata'								=> '订单导入',
		),
		'calculateshipcost' => array(
				'calresult'						=> '运费计算工具',
		),
		'cargocompany' 		=> array(
				'batchchangestatus'				=> '批量开启，批量停用',
		),
		'logistics' 		=> array(
		
		),
		'logisticstype' 	=> array(
				'dashboardtest'					=> '调用物流控制台',
		),
		'logisticsuserset' 	=> array(
				'updatesn'						=> '更新排序',
				'carrierlist'					=> '查看物流方式列表',
				'copyadd'						=> '新增物流方式',
				'updatecarrier'					=> '修改平台carrier设置',
				'updateshipment'				=> '交运日期设置',
				'vlist'						    => '列表',
				'vupdate'						=> '更新',
		),
);