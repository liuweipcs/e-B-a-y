<style>
.row label {  padding:4px;  }
.selectType{  margin-left:8px;  font-size:14px;  color:red; }
.product_information{  float:left;  width:760px;  }
/*.photo{  float:left;  width:80px;  margin-left:10px;  margin-top:10px;  }*/
.clear:after{  content:',';  visibility:hidden;  display:block;  height:0px;  clear:both;  }
ul,li{padding:0; margin:0; list-style-type:none;}
.zxx_test{width:640px; margin:10px auto;}
.left_thumb{width:100px; float:left; padding:0 6px;}
.left_thumb li a{display:block; height:100px; position:relative; outline:none;}
.left_thumb li img{width:100px; height:75px; position:absolute; left:0; top:0; padding:3px; background:#eeeeee; border:1px solid #cccccc;}
.left_thumb li img.on{background:#beceeb; border:1px solid #828da1;}
.left_thumb li img.hover{padding:10px; no-repeat -1px -1px; border:none;}
#photo{float:left;width:100%;background-repeat:none;padding:0 0 10px;background:white;}
.photo1{float:left;border-bottom:1px solid #D2D2D2; padding-top:2px;}
.photo1 p,.photo2 p{float:left;}
.photo2{float:left; padding-top:2px;}
.phot{float: right;padding: 2px;}
.phot a{ padding: 0 2px 0 2px;}
.phot img{border:1px solid #ccc;padding: 2px;}
.phot a img{
    -webkit-transition: all ease .3s;
    transition: all ease .3s}
.phot a img:hover{
    position: relative;
    top: 20px;
    left: -30px;
    z-index: 1000;
    -webkit-transform: scale(3);
    transform: scale(3); }

</style>
    <div class="bg14 pdtb2 dot">
            <strong><?php echo Yii::t('products', 'Basic information');?></strong>
    </div>
    <div class="dot7 pd20 clear" style="padding-bottom: 40px;padding-left: 10px;padding-right: 10px;padding-top:0px;">
    	<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td style="width:780px;" valign="top"><div class="product_information" style="width:760px;">
        	<table class="dataintable" width="760" border="0" cellspacing="1" cellpadding="3">
		     <tr>
		         <td width="100"><?php  echo Yii::t('products', 'Sku');?></td>
		         <td width="120" style="background-color:#fff;"><span style='color:green;font-size:13px;'><?php echo $model->sku;?></span></td>
		         <td width="80"><?php  echo '自定义码';?></td>
		         <td width="120" style="background-color:#fff;"><span style='color:green;font-size:13px;'><?php echo MHelper::getAmazonEncryptSku($model->sku);?></span></td>
		     </tr>
				<tr>
					<td><?php  echo Yii::t('products', 'SKU别名');?></td>
					<td colspan='3' style="background-color:#fff;" ><?php echo UebModel::model("Product")->getAliasesName($model->sku);?></td>
				</tr>
			 <tr>
				<td><?php  echo Yii::t('products', '中文名称');?></td>
				<td colspan='3' style="background-color:#fff;" ><?php echo UebModel::model("Productdesc")->getProductCnNameBySku($model->sku);?></td>
			 </tr>

		     <tr>
		     	 <td width="100"><?php  echo Yii::t('products', 'Whether the new product');?></td>
		         <td width="120" style="background-color:#fff;"><?php echo $model->product_is_new == 1 ? Yii::t('system','Yes') : Yii::t('system','No');?></td>
		         <td><?php  echo Yii::t('products', 'Product Cost');?></td>
		         <td style="background-color:#fff;"><span style='color:green;font-size:13px;'><?php echo $model->product_cost; ?></span></td>
		     </tr>
		     <tr>
		     	<td width = "100"><?php  echo Yii::t('products', 'Product Category');?></td>
		     	<td width = "120" style="background-color:#fff;"><?php echo $model->getCategoryNameBySku($model->sku);?></td>
		     </tr>
			 <tr>
		         <td><?php  echo Yii::t('products', '平台分类');?></td>
		         <td colspan='3' style="background-color:#fff;" ><?php echo UebModel::model("ProductPlatformCategory")->getCategoryFullNameById('ALIEXPRESS',$model->sku);?></td>
		     </tr>
			<tr>
				<td><?php  echo Yii::t('products', '产品线');?></td>
				<td colspan='3' style="background-color:#fff;" >
					<?php
					if($model->product_linelist_id){
						$Productlinelist = UebModel::model("Productlinelist")->getListParentByIds(array($model->product_linelist_id));
						if(!empty($Productlinelist)){
							foreach ($Productlinelist as $value){
								echo $value;
							}
						}
					}
					?>
				</td>
			</tr>
			<?php 
			if ($model->product_type==2){
				$combine=UebModel::model('ProductCombine')->getCombinesize($model->id);
				if(!empty($combine)){
				$size=array_sum($combine['product_length']).'*'.array_sum($combine['product_width']).'*'.array_sum($combine['product_height']);
				$pack_size=array_sum($combine['pack_product_length']).'*'.array_sum($combine['pack_product_width']).'*'.array_sum($combine['pack_product_height']);
				$product_weight=array_sum($combine['product_weight']);
				$product_gross_weight=array_sum($combine['gross_product_weight']);
				}
			}
			?>
		     <tr>
		         <td><?php  echo  '产品尺寸(长*宽*高)cm';?></td>
		         <td style="background-color:#fff;"><?php if (isset($size))echo $size ;else  echo $model->product_length.'*'.$model->product_width.'*'.$model->product_height; ?></td>
				 <td><?php  echo Yii::t('products', '开发类型');?></td>
                 <td style="background-color:#fff;" ><span style='color:green;font-size:13px;'><?php echo UebModel::model("Product")->getProductStateTypeConfig($model->state_type);?></span></td>

		     </tr>
		     <tr>
		         <td><?php  echo  '包装尺寸(长*宽*高)cm';?></td>
		         <td colspan='3' style="background-color:#fff;"><?php if (isset($pack_size))echo $pack_size ;else echo $model->pack_product_length.'*'.$model->pack_product_width.'*'.$model->pack_product_height; ?></td>
<!--		         <td>--><?php // echo Yii::t('products', '毛重(g)');?><!--</td>-->
<!--		         <td style="background-color:#fff;"><span style='color:green;font-size:13px;'>--><?php //echo $model->gross_product_weight;?><!--</span></td>-->
		     </tr>
                <tr>
                    <td><?php  echo Yii::t('products', '毛重(g)');?></td>
                    <td style="background-color:#fff;"><span style='color:green;font-size:13px;'><?php if (isset($product_gross_weight))echo $product_gross_weight ;else echo $model->gross_product_weight;?></span></td>
                    <td><?php  echo Yii::t('products', '产品净重(g)');?></td>
                    <td style="background-color:#fff;"><span style='color:green;font-size:13px;'><?php if (isset($product_weight))echo $product_weight ;else echo $model->product_weight;?></span></td>
                </tr>
		      <tr>
		         <td><?php  echo Yii::t('products', 'Product Status');?></td>
		         <?php if($model->product_status=='7'){?>
		         <td style="background-color:#fff;"><span style='color:red;font-size:13px;'><?php echo UebModel::model('Product')->getProductStatusConfig($model->product_status);?></span></td>
		         <?php }else{?>
		         <td style="background-color:#fff;"><span style='color:green;font-size:13px;'><?php echo UebModel::model('Product')->getProductStatusConfig($model->product_status);?></span></td>
		         <?php }?>
		         <td><?php  echo Yii::t('system', 'Type');?></td>
		         <td><?php echo VHelper::getProductTypeConfig($model->product_type);?></td>
		     </tr>


		      <tr>
		         <td><?php  echo Yii::t('system', 'Create Time');?></td>
		         <td style="background-color:#fff;"><?php echo $model->create_time?></td>
		         <td><?php  echo Yii::t('system', 'Modify Time');?></td>
		         <td style="background-color:#fff;"><?php echo $model->modify_time;?></td>
		     </tr>
		     <tr>
		         <td><?php  echo Yii::t('products', 'Security Level');?></td>
		         <td style="background-color:#fff;"><?php echo $model->security_level;?></td>
		         <td><?php  echo Yii::t('products', 'Infringement Species');?></td>
		         <td style="background-color:#fff;"><?php  echo $model->infringement;?></td>
		     </tr>
		     <tr>
		     	<td><?php  echo Yii::t('products', 'Infringement Reason');?></td>
		     	<td style="background-color:#fff;"><?php echo isset($mops) ? $mops->infringement_reason : '';?></td>
		     	<td><?php  echo Yii::t('products', '侵权平台');?></td>
		     	<td style="background-color:#fff;font-weight:bold;"><?php echo isset($mops) ? $mops->infringe_platform : '';?></td>
		     </tr>
		      <tr>
		         <td><?php  echo Yii::t('products', 'Customs Code');?></td>
		         <td style="background-color:#fff;"><?php //echo $model->ship_cost.'<span style="color:red;">'.$model->currency.'</span>';?></td>
		         <td ><?php  echo Yii::t('purchases', 'provider_type')?></td>
		         <td style="background-color:#fff;"><?php echo VHelper::getproviderTypeConfig($model->provider_type);?></td>
		     </tr>
		     <tr>
		         <td><?php  echo Yii::t('products', 'Is a multiple attribute');?></td>
		         <td style="background-color:#fff;"><?php  echo VHelper::getProductMultiConfig($model->product_is_multi);?></td>
		         <td><?php  echo Yii::t('products', 'Original material type');?></td>
		         <td style="background-color:#fff;"><?php echo $model->original_material_type_id == 0 ? '' : VHelper::getProductOriginalMaterialTypeConfig($model->original_material_type_id);?></td>
		     </tr>
		     <tr>
		         <td><?php  echo Yii::t('products', 'Whether to bring the original packaging');?></td>
		         <td style="background-color:#fff;"><?php echo $model->product_original_package == 1 ? Yii::t('system','Yes') : Yii::t('system','No');?></td>
		         <td ><?php  echo Yii::t('products', 'Whether for location');?></td>
		         <td style="background-color:#fff;"><?php echo $model->product_is_storage == 1 ? Yii::t('system','Yes') : Yii::t('system','No');?></td>
		     </tr>
		      <tr>
		         <td><?php  echo Yii::t('products', '发货包装');?></td>
		         <td style="background-color:#fff;"><?php echo $model->product_package_code ? UebModel::model('ProductToWayPackage')->getProductPackageById($model->product_package_code) : '-';?></td>
		         <td ><?php  echo Yii::t('products', '发货包材');?></td>
		         <td style="background-color:#fff;"><?php echo $model->product_package_material_code ? UebModel::model('ProductToWayPackage')->getProductPackageById($model->product_package_material_code) : '-';?></td>
		     </tr>
		     <tr>
		         <td style="color:red;"><?php  echo Yii::t('products', '采购来料包装');?></td>
		         <td style="background-color:#fff;"><?php echo $model->product_to_way_package ? UebModel::model('ProductToWayPackage')->getProductPackageById($model->product_to_way_package) : '-';?></td>
		     	 <td style="color:red;"><?php  echo Yii::t('products', '贴标加工包装');?></td>
		         <td style="background-color:#fff;"><?php echo $model->product_label_proces ? UebModel::model('ProductToWayPackage')->getProductPackageById($model->product_label_proces) : '-';?></td>
		     </tr>
		     <tr>
		         <td><?php  echo Yii::t('products', 'Binding Provider');?></td>
		         <td style="background-color:#fff;" >
				 <?php if(Menu::model()->exists("menu_url = '/purchases/provider/link'")): ?>
				 <?php echo $bindProvider;?>
				 <?php endif; ?>
				 </td>
		         <td><?php  echo Yii::t('products', '备货平台');?></td>
		         <td style="background-color:#fff;" ><?php echo $model->product_bak_type?$model->getProductBakType($model->product_bak_type):'-';?></td>
		     </tr>
                <tr>
                    <td><?php  echo Yii::t('products', '热销度');?></td>
                    <td style="background-color:#fff;" >
                        <?php if($model->hot_rank=='0'):?><?php echo '低'?>
                        <?php elseif($model->hot_rank=='1'): ?><?php echo '中'; ?>
                        <?php elseif($model->hot_rank=='2'):?><?php echo '高';?><?php endif?>
					<td><?php  echo Yii::t('products', '是否带说明书');?></td>
					<td style="background-color:#fff;" >
						<?php if($model->instructions=='0'): ?><?php echo '否'; ?>
						<?php elseif($model->instructions=='1'): ?><?php echo '是'; ?><?php endif?>
					</td>
                </tr>
             <tr style="display: none;">
					<td><?php  echo Yii::t('products', '标签');?></td>
					<td colspan='3' style="background-color:#fff;" ><?php echo rtrim($model->label,',');?></td>
			 </tr>
             <tr>
                    <td><?php  echo Yii::t('products', '参考价');?></td>
                    <td style="background-color:#fff;" ><?php echo '￥'. $model->reference_price;?></td>
				 	<td><?php  echo Yii::t('products', '样品类型');?></td>
				 	<td style="background-color:#fff;" >
					 <?php if($model->buy_sample_type=='3'):?><?php echo '购买'?>
					 <?php elseif($model->buy_sample_type=='1'): ?><?php echo '免费'; ?>
					 <?php elseif($model->buy_sample_type=='2'):?><?php echo '借用';?><?php endif?>
             </tr>
		     <tr>
		         <td><?php  echo Yii::t('products', '多个一卖');?></td>
		         <td colspan='3' style="background-color:#fff;" ><?php echo $model->product_combine_code;?></td>
		     </tr>

		     <tr>
		         <td><?php  echo Yii::t('products', 'Product EN Link');?></td>
		         <td colspan='3'style="background-color:#fff;" >
                     <?php $productenlink=explode(",",$model->product_en_link);
                     if(strlen($productenlink[0])>100) {
                        if(strlen($productenlink[1])>100){
                         }
                     }?>
                     <?php if($productenlink[0]):?>
                     <a href="<?php echo $productenlink[0]; ?>" target="_blank" ><?php  echo substr($productenlink[0], 0, 60).'...';?></br></a>
                     <?php endif?>
                     <?php if($productenlink[1]): ?>
                     <a href="<?php echo $productenlink[1]; ?>" target="_blank" ><?php  echo substr($productenlink[1], 0, 60).'...';?></a>
                     <?php endif?>
                 </td>
		     </tr>
				<tr>
					<td style="color:red;"><?php  echo Yii::t('products', '买样备注');?></td>
					<td colspan='3'style="background-color:#fff;" ><?php echo $model->buycomp_note; ?></td>
				</tr>
				<tr>
					<td style="color:red;"><?php  echo Yii::t('products', '质检备注');?></td>
					<td colspan='3'style="background-color:#fff;" ><?php echo $model->quality_note; ?></td>
				</tr>
                <tr>
                    <td><?php  echo Yii::t('products', '图片备注');?></td>
                    <td colspan='3'style="background-color:#fff;" ><?php echo $model->image_remark; ?></td>
                </tr>
				 <tr>
                    <td style="color:red;"><?php  echo Yii::t('products', '平台分配备注');?></td>
                    <td colspan='3'style="background-color:#fff;" ><?php echo $model->onlie_remark; ?></td>
                </tr>
                <tr>
                    <td><?php  echo Yii::t('products', '审核备注信息');?></td>
                    <td colspan='3'style="background-color:#fff;" ><?php echo UebModel::model('Product')->getInforemark($model->sku); ?></td>
                </tr>
				<tr>
                    <td><?php  echo Yii::t('products', '文案或摄影备注');?></td>
                    <td colspan='3'style="background-color:#fff;" ><?php echo UebModel::model('Productjob')->getPhotoRemarkbysku($model->sku); ?></td>
                </tr>

                <tr>
                	<td style="color:red;"><?php
//               	VHelper::dump($productBrand[$model->product_brand_id]);die;
                	if($model->product_brand_id>93) echo
                	 Yii::t('products', '自定义品牌名');
                	else echo Yii::t('products', 'Product brand') ;?></td>
		         	<td style="background-color:#fff;"><?php echo $productBrand[$model->product_brand_id];?></td>
		         	<td >工作原理</td>
		         	<td style="background-color:#fff;"><?php echo $model->product_principle;?></td>
                </tr>
                <tr>
                	<td >进口产品型号</td>
		         	<td style="background-color:#fff;"><?php echo $model->product_model;?></td>
	         		<td > 规格</td>
		         	<td style="background-color:#fff;"><?php echo $model->specifications;?></td>
                </tr>
				<tr>
                    <td><?php  echo Yii::t('products', '产品材质');?></td>
                    <td colspan='3'style="background-color:#fff;" >[中文]<?php echo UebModel::model('Product')->getInfoMaterial($model->sku,'cn'); ?><br />[英文]<?php echo UebModel::model('Product')->getInfoMaterial($model->sku,'en'); ?>
					</td>
                </tr>
				<tr>
                    <td><?php  echo Yii::t('products', '产品用途');?></td>
                    <td colspan='3'style="background-color:#fff;" >[中文]<?php echo UebModel::model('Product')->getInfoUse($model->sku,'cn'); ?><br />[英文]<?php echo UebModel::model('Product')->getInfoUse($model->sku,'en'); ?>
					</td>
                </tr>
                <td><?php  echo Yii::t('products', '配货中文名称(简称)');?></td>
                <td colspan='3' style="background-color:#fff;"><?php echo $model->picking_name ;?></td>
                <tr>
                    <td style="color:red;"><?php  echo Yii::t('products', '海关编码(HS Code)');?></td>
                    <td style="background-color:#fff;" ><?php echo $model->customs_code ;?></td>
                    <td><?php  echo Yii::t('products', '申报货值');?></td>
                    <td style="background-color:#fff;"><?php echo $model->declare_price ;?></td>
                </tr>

                <tr>
                    <td><?php  echo Yii::t('products', '进口申报中文名');?></td>
                    <td style="background-color:#fff;"><?php echo $model->declare_cname ;?></td>
                    <td><?php  echo Yii::t('products', '进口申报英文名');?></td>
                    <td style="background-color:#fff;"><?php echo $model->declare_ename ;?></td>
                </tr>

                <tr>
                    <td><?php  echo Yii::t('products', '出口申报英文名');?></td>
                    <td style="background-color:#fff;"><?php echo $model->export_ename ;?></td>
                    <td style="color:red;"><?php  echo Yii::t('products', '出口申报中文名(开票品名)');?></td>
                    <td style="background-color:#fff;"><?php echo $model->export_cname ;?></td>
                </tr>
                <tr>
                    <td style="color:red;"><?php  echo Yii::t('products', '申报单位');?></td>
                    <td style="background-color:#fff;"><?php echo $model->declare_unit ;?></td>
                    <td style="color:red;"><?php  echo '出口申报型号';?></td>
                    <td style="background-color:#fff;"><?php echo $model->product_model_out ;?></td>
                </tr>
                <tr>
                    <td><?php  echo Yii::t('products', '物流备注');?></td>
                    <td style="background-color:#fff;" ><?php echo UebModel::model('Product')->getLogisticsNotebysku($model->sku); ?></td>
                    <td style="color:red;"><?php  echo Yii::t('products', '是否商检');?></td>
                    <td style="background-color:#fff;"><?php echo $model->is_inspection ? ($model->is_inspection==1?不商检:商检):'' ;?></td>
                </tr>
                <tr>
                    <td><?php  echo Yii::t('products', '产品关税税率');?></td>
                    <td style="background-color:#fff;">
                        <?php if($model->tariff):?>
                        <?php echo $model->tariff .'%';?>
                        <?php endif?>
                    </td>
                    <td style="color:red;"><?php  echo Yii::t('products', '产品出口退税税率');?></td>
                    <td style="background-color:#fff;">
                        <?php if($model->tax_rate):?>
                        <?php echo $model->tax_rate .'%';?>
                        <?php endif?>
                    </td>
                </tr>
		</table>
         </div></td>
  </tr>
</table>
    </div>
<?php 
exit();
?>