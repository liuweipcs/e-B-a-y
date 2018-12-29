    <div class="bg14 pdtb2 dot">
            <strong><?php echo Yii::t('products', 'Attribute');?></strong>           
    </div>
    <div class="dot7 pd5">    
      <table class="dataintable" id="dataintable" width="100%" cellspacing="1" cellpadding="3" border="0">
       <thead>
        <tr>
            <th><?php echo Yii::t('products', 'Attribute name')?></th>         
            <th><?php echo Yii::t('products', 'Attribute value name')?> <input style="display:none" type="button" id=addAttrBtn name="addAttrBtn" value="<?php echo Yii::t('system', 'Add');?>">
	            <select id="addattr" style="display: none">
	            	<option><?php echo Yii::t('system', 'Please Select');?></option>
	            	<?php 
	            	foreach ($isNopublicAttr as $val){
		            	echo '<option id="'.$val['id'].'" value="'.$val['id'].'">';
		            	echo $val['attribute_name'];
		            	echo '</option>';
	            	}?>
	            </select>	        
            	<?php 
	            	foreach ($isNopublicAttr as $val){
						echo CHtml::link('','',array('target'=>'dialog','mask'=>'1','width'=>'650','height'=>'450','id'=>'addAttrLink_'.$val['id']));
	            	}?>
            </th>
            <?php //if(!isset($do) || empty($do)):?>
            <!--<th style="width:80px;" align="center"></th>-->
            <?php// endif;?>
        </tr>
        </thead>
    <tbody>
      <?php
      $attribute['33']='所有需发往万邑通物流公司澳大利亚的货物（SKU上有国家代码标识AU）';
      $attribute['34']='所有需发往万邑通物流公司英国的货物（SKU上有国家代码标识GB）';
      $attribute['35']='所有需发往万邑通物流公司德国的货物（SKU上有国家代码标识DE）';
      $attribute['36']='所有需发往万邑通物流公司美国西仓的货物（SKU上有国家代码标识US）';
      $attribute['37']='所有需发往万邑通物流公司美国东仓的货物（SKU上有国家代码标识US）';
      $attribute['39']='所有需发往出口易物流公司英国的货物（SKU上有国家代码标识GB）';
      $attribute['40']='所有需发往出口易物流公司德国的货物（SKU上有国家代码标识DE）';
      $attribute['41']='所有需发往出口易物流公司澳大利亚的货物（SKU上有国家代码标识AU）';
      $attribute['42']='所有需发往出口易物流公司美国的货物（SKU上有国家代码标识US）';
      $attribute['11']='可流动性的物质：乳液，化妆水，指甲油等，压缩气体与喷雾剂、发泡剂，带喷头的清洁剂、药品、护肤品、美发品、杀虫剂、空气清新剂等';
      $attribute['12']='小颗粒状物质如眼影';
      $attribute['13']='纯粹的电池，独立一块电池。分为锂电池，干电池，纽扣电池';
      $attribute['43']='电池在产品里面，无法直观看到电池的（分为内置锂电池，内置干电池，内置纽扣电池）';
      $attribute['45']='电池镶嵌在产品上面，可以直观看到电池的';
      $attribute['46']='电池和产品是两个独立的个体';
      $attribute['47']='不带任何电池，不侵权，无磁性，无蓝牙功能的正常商品';
      $attribute['48']='活体，刀枪火等违禁物品，液体，膏体，粉末统称为敏感货物';
      $attribute['49']='商品或者包装上面有“蓝牙”标识的';
      $attribute['50']='LOGO，或者产品形状侵权的产品';
      $attribute['51']='1)路由器、音箱、外置播放器，电路板(含磁)；
2)有变压器的电子产品，如电视机顶盒，电动螺丝刀电源等；
3)带磁饰品，如带磁项裢、项圈等；
4)马达，如硬盘、电动棒、电动剃须刀、遥控玩具车、碎纸机。';
      $attribute['52']='体积重大于实际重的产品（体积重计泡方式长*宽*高/5000）';
      $attribute['53']='很容易破损，需轻拿轻放的产品';
      $attribute['54']='有形状但无固定形状，如汽车去污泥，橡皮泥';
      $attribute['55']='外形为“刀、枪”形状的产品，实际是““刀、枪”的产品，外包装带有火焰、辐射等危险品标示';
      $attribute['4546']='1)含锂电池的电子产品，如：手机、手提电脑、平板电脑、播放器、蓝牙耳机、充电器、移动电源、读卡器、接收器、数码相机、按摩棒、摄像头、行车记录仪、录音笔、美发器等；
2)带钮扣电池产品，如电子手表、计步器、发光器、汽车防盗器等；
3)其他带电池物品，如玩具、户外用品、登山用品、摩托车配件等';
      $attribute['4547']='1)发胶发蜡、车蜡、竹炭产品、乒乓球、氧器机、呼吸机；
2)油墨（含打印机墨盒）、油漆、燃油、酒精、酒水、粘合剂（含超能胶、玻璃胶等）、漂白剂；
3)香水、指甲油、洗甲水、剃须膏、鞋油、蜂蜜';
      $attribute['6004']='不带任何电池，不侵权，无磁性，无蓝牙功能的正常商品';
      $attribute['6005']='电池在产品里面，无法直观看到电池的（分为内置锂电池，内置干电池，内置纽扣电池），或者含电池的电子产品';
      $attribute['6006']='纯粹的电池，独立一块电池。分为锂电池，干电池，纽扣电池';
      $attribute['6007']='配套电池，活体，刀枪火等违禁物品，液体，膏体，粉末，仿牌，磁性商品，易燃物品，药品，与人体直接接触的母婴用品，生活用品，实木木制品（例如鹦鹉支架）';
      
      foreach ($categoryAttributeList as $key => $val) {
            switch ($val['attribute_showtype_value']) {
            	
                 case 'list_box':                    
                     echo '<tr class="row" id="attribute_'.$val['id'].'" >';                    
                     $htmlOptions = array();                   
                     if (! empty($val['attribute_is_required'])) {                       
                        $htmlOptions['class'] = 'required'; 
                        $attributeName = $val['attribute_name'] .'<span class="required">*</span>';
                     } else {
                         $attributeName = $val['attribute_name'];
                     }
                     echo '<td class="multi-attr-td" >';
                     echo CHtml::label($attributeName, $val['attribute_name'], $htmlOptions);   
                     echo '</td>';
                     echo '<td class="multi-attr-td" >';           
                     echo CHtml::dropDownList("attr[{$val['id']}]", isset($selectAttrPairs[$val['id']]) ? $selectAttrPairs[$val['id']] : '', $attributeListData[$val['id']], array( 'empty' => Yii::t('system', 'Please Select')));                  
                     echo '</td>';
                     echo '</tr>';
                     break;
                 case 'check_box':
                     echo '<tr class="row" id="attribute_'.$val['id'].'" >';
                     echo '<td class="multi-attr-td" >';
                     echo CHtml::label($val['attribute_name']=='Product features'?'产品属性':$val['attribute_name'], $val['attribute_name']);
                     echo '</td>';
                     echo '<td class="multi-attr-td" id=attr_'.$val['id'].'>';
                     foreach ($attributeListData[$val['id']] as $key2 => $val2) {
                     	//get cn name,add by ethan 2014.8.9  	
                     	$attribute_value_name_cn = UebModel::model('ProductAttributeValueLang')->getAttributeNameByCode($val2,CN);
                        if(!$attribute_value_name_cn){
							$attribute_value_name_cn = $val2;
						}
                       //echo $val2;
                         if ( isset($selectAttrPairs[$val['id']]) && in_array($key2, (array)$selectAttrPairs[$val['id']]) ) {
                             $flag = true;
                         } else {
                             $flag = false;
                         }  
                    echo CHtml::checkBox("attr[{$val['id']}][]", $flag, array( 'value' => $key2));
					if($flag==true){
						echo '<span style="color:red;">'.$attribute_value_name_cn.'</span>';
					}else{
						echo ("<span title='$attribute[$key2]'>$attribute_value_name_cn</span>");
					}
                     }
                     echo '</td>';
                     echo '</tr>';
                     break;
                 default:
                     break;
             }
      }?>
      </tbody>  
      </table> 
    </div>
<?php
exit();
?>