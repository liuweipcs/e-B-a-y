<div class="pageHeader" style="border:1px #B8D0D6 solid">
    <form id="pagerForm" 
    <?php if( $_REQUEST['target'] == 'dialog'):?>
    onsubmit="return dwzSearch(this, 'dialog');"
    <?php else:?>
    onsubmit="return divSearch(this, '<?php echo $_REQUEST['target'];?>');"
    <?php endif;?>
    action="<?php echo Yii::app()->createUrl($this->route);?>" 
    method="get">
        <?php echo CHtml::hiddenField('target', @$_REQUEST['target'])?>
        <?php echo CHtml::hiddenField('scheme_id', @$_REQUEST['scheme_id'])?>
        <?php echo CHtml::hiddenField('className', @$_REQUEST['className'])?>
        <?php //$this->renderPartial('application.components.views._searchHidden', array( 'pages' => $pages)); ?>
        
        <?php echo CHtml::hiddenField('pageNum', @$_REQUEST['pageNum'])?>
		<?php echo CHtml::hiddenField('numPerPage', @$_REQUEST['numPerPage'])?>
		<?php echo CHtml::hiddenField('orderField', @$_REQUEST['orderField'])?>
		<?php echo CHtml::hiddenField('orderDirection', @$_REQUEST['orderDirection'])?>
		<?php echo CHtml::hiddenField('pagesChecked', @$_REQUEST['pagesChecked'])?>
		<?php echo CHtml::hiddenField('subTitle', @$_REQUEST['subTitle'])?>
        <?php echo CHtml::hiddenField('ac', true)?>
        <div class="searchBar">
        	<div class="searchContent">
        	<?php
			if(isset($list['is_condition']) && !empty($list['is_condition'])):
				foreach ($list['is_condition'] as $key=>$val):
					echo '<div class="left h25 ml10">'.$val['show_html'].'</div>';
				endforeach;
			endif;

			if(isset($list['is_value']) && !empty($list['is_value'])):
			echo '<div class="clear"></div>';
			echo '<div class="left h25 ml10">';
			$i=0;
			echo CHtml::label(Yii::t('system', 'Report column').': ','',array('style'=>'width:auto;text-align:right;font-weight:bold;'));
			foreach ($list['is_value'] as $key=>$val):
			if( !isset($_REQUEST['ac']) ){
				$flag = true;//query all
			}else{
				if( isset($_REQUEST['is_value'][$val['id']]) ){
					$flag = true;
				}else{
					$flag = false;
				}
			}
			echo '<div style="float:left;margin-right:5px;">';
			echo CHtml::checkBox( "is_value[".$val['id']."]",$flag,
					array('value' =>$val['column_title'],'id' =>'is_value_'.$key) );
			echo $val['column_title'];
			echo '&nbsp;&nbsp;';
			echo '</div>';
			$i++;
			endforeach;
			echo '<div style="float:left;padding-top:5px;color:blue;">['.Yii::t('system','Choose you to statistical fields').']</div>';
			echo '</div>';
			endif;
			
			
			if(isset($list['is_group']) && !empty($list['is_group'])):
			echo '<div class="clear"></div>';
			echo '<div class="left h25 ml10">';
			$i=0;
			echo CHtml::label(Yii::t('system', 'Is group for report').': ','',array('style'=>'width:auto;text-align:right;font-weight:bold;'));
			foreach ($list['is_group'] as $key=>$val):
			if($key<3)
				$checked = true;
			else
				$checked = false;
			
			$options = array('value' =>$val['column_field'],'id' =>$val['column_field'].$key );
			
			if(isset($val['time'])){
				$options = array_merge(array('onclick'=>'showTimeType(this);'),$options);
				$flag = false;
				if ( isset($_REQUEST['is_group'][$val['db_name'].'.'.$val['table_name']][$val['column_field']][0]) 
					&& !empty($_REQUEST['is_group'][$val['db_name'].'.'.$val['table_name']][$val['column_field']][0]) ){
					$flag = true;
				}
				
				echo CHtml::checkBox( "is_group[".$val['db_name'].".".$val['table_name']."][".$val['column_field']."][0]",$flag ? $flag:$checked, $options );
			}else{
				$flag = isset($_REQUEST['is_group'][$val['db_name'].'.'.$val['table_name']][$val['column_field']]) 
					&& !empty($_REQUEST['is_group'][$val['db_name'].'.'.$val['table_name']][$val['column_field']]) 
					? true :false;
				echo CHtml::checkBox( "is_group[".$val['db_name'].".".$val['table_name']."][".$val['column_field']."]",$flag ? $flag:$checked, $options );
			}
			
			
			echo $val['column_title'];
			if(isset($val['time'])){
				$isNone = !$flag ? 'none' : '';
				echo '<span id="orderByTime" style="display:'.$isNone.';">';
				$this->renderPartial('application.components.views.timeTypeList', 
						array('db_name'=>$val['db_name'],'table_name'=>$val['table_name'],'column_field'=>$val['column_field'],'default_date'=>$val['default_date']));
				echo '</span>';
			}
			echo '&nbsp;&nbsp;';
			endforeach;
			echo '</div>';
			endif;
			
			if(isset($list['is_having']) && !empty($list['is_having'])):
			//echo '<div class="clear"></div>';
			echo '<div class="left h25 ml10">';
			echo CHtml::label(Yii::t('system', 'Is have for report').': ','',array('style'=>'width:auto;text-align:right;font-weight:bold;'));
			foreach ($list['is_having'] as $key=>$val):
			if (empty($val['column_title'])):	//table_name
				$flag = ( isset($_REQUEST['is_having'][$val['table_name']][$val['column_field']][name_key]) )
						? true :false;
				echo '<div style="float:left;margin-right:5px;">';
				echo CHtml::checkBox( "is_having[".$val['table_name']."][".$val['column_field']."][name_key]",$flag, 
						array('value' =>$val['column_field'],'onclick'=>'getSymbol(this,'.$key.');') );
				echo $val['column_title'];
				echo '</div>';
				$display = !$flag ? 'display:none;' : 'display:block;';
				echo '<div style="float:left;margin-right:5px;'.$display.'" id="is_having_'.$key.'">';
				echo CHtml::dropDownList("is_having[".$val['table_name']."][".$val['column_field']."][symbol]", 
						@$_REQUEST['is_having'][$val['table_name']][$val['column_field']][symbol],
				array('>'=>'>','<'=>'<','>='=>'>=','<='=>'<='),array('empty' => Yii::t('system','Please Select')));
				echo CHtml::textField("is_having[".$val['table_name']."][".$val['column_field']."][symbol_value]", 
						@$_REQUEST['is_having'][$val['table_name']][$val['column_field']][symbol_value],
						array('onblur'=>'acheckNum(this);','class'=>'textInput','size'=>8));
				echo '</div>';
			else:
				$flag = isset($_REQUEST['is_having'][$key][$key][name_key]) ? true :false;
				echo '<div style="float:left;margin-right:5px;">';
				echo CHtml::checkBox( "is_having[".$key."][".$key."][name_key]",$flag,
						array('value' =>$val['column_title'],'onclick'=>'getSymbol(this,'.$key.');') );
				echo $val['column_title'];
				echo '</div>';
				$display = !$flag ? 'display:none;' : 'display:block;';
				echo '<div style="float:left;margin-right:5px;'.$display.'" id="is_having_'.$key.'">';
				echo CHtml::dropDownList("is_having[".$key."][".$key."][symbol]",
						@$_REQUEST['is_having'][$key][$key][symbol],
						array('>'=>'>','<'=>'<','>='=>'>=','<='=>'<='),array('empty' => Yii::t('system','Please Select')));
				echo CHtml::textField("is_having[".$key."][".$key."][symbol_value]",
						@$_REQUEST['is_having'][$key][$key][symbol_value],
						array('onblur'=>'acheckNum(this);','class'=>'textInput','size'=>8));
				echo '</div>';
			endif;

			echo '&nbsp;&nbsp;';
			endforeach;
			echo '</div>';
			endif;
			/*
			if(isset($list['is_count']) && !empty($list['is_count'])):
			echo '<div class="left h25 ml10">';
			echo CHtml::label(Yii::t('system', 'Is count for report').': ','',array('style'=>'width:auto;text-align:right;'));
			foreach ($list['is_count'] as $key=>$val):
			//echo CHtml::textField("is_count[".$val['table_name']."][".$val['column_field']."]",$val['column_field']);
			$flag = isset($_REQUEST['is_count'][$val['table_name']][$val['column_field']]) && !empty($_REQUEST['is_count'][$val['table_name']][$val['column_field']])
			? true :false;
			echo CHtml::checkBox( "is_count[".$val['table_name']."][".$val['column_field']."]",$flag,
					array('value' =>$val['column_field'],'id' =>$val['column_field'].$key) );
			echo $val['column_title'];
			echo '&nbsp;&nbsp;';
			endforeach;
			echo '</div>';
			endif;
			
			if(isset($list['is_sum']) && !empty($list['is_sum'])):
			echo '<div class="left h25 ml10">';
			echo CHtml::label(Yii::t('system', 'Is sum for report').': ','',array('style'=>'width:auto;text-align:right;'));
			foreach ($list['is_sum'] as $key=>$val):
			//echo CHtml::textField("is_sum[".$val['table_name']."][".$val['column_field']."]",$val['column_field']);
			$flag = isset($_REQUEST['is_sum'][$val['table_name']][$val['column_field']]) && !empty($_REQUEST['is_sum'][$val['table_name']][$val['column_field']])
			? true :false;
			echo CHtml::checkBox( "is_sum[".$val['table_name']."][".$val['column_field']."]",$flag,
					array('value' =>$val['column_field'],'id' =>$val['column_field'].$key) );
			echo $val['column_title'];
			echo '&nbsp;&nbsp;';
			endforeach;
			echo '</div>';
			endif;
			
			if(isset($list['is_avg']) && !empty($list['is_avg'])):
			echo '<div class="left h25 ml10">';
			echo CHtml::label(Yii::t('system', 'Is avg for report').': ','',array('style'=>'width:auto;text-align:right;'));
			foreach ($list['is_avg'] as $key=>$val):
			//echo CHtml::textField("is_avg[".$val['table_name']."][".$val['column_field']."]",$val['column_field']);
			$flag = isset($_REQUEST['is_avg'][$val['table_name']][$val['column_field']]) && !empty($_REQUEST['is_avg'][$val['table_name']][$val['column_field']])
			? true :false;
			echo CHtml::checkBox( "is_avg[".$val['table_name']."][".$val['column_field']."]",$flag,
					array('value' =>$val['column_field'],'id' =>$val['column_field'].$key) );
			echo $val['column_title'];
			echo '&nbsp;&nbsp;';
			endforeach;
			echo '</div>';
			endif;
			*/
			?>
			</div>
            <div class="subBar">
            	<ul>
            	<li>
                  <div class="buttonActive" style="padding-right:20px;">
                      <div class="buttonContent">
                          <button type="submit"><?php echo Yii::t('system', 'Search')?></button>
                      </div>                         
                  </div>
                </li>
                </ul>                       
            </div>
        </div>
    </form>
</div>
<script language="javascript">
$(document).ready(function(){
	$("#orderByTime select").change(function(){
		var myDate=new Date();
		var adt;
		var bdt;
		var y;
		var m;	
		var month=new Array(12);
		month[-1]="12";
		month[0]="01";
		month[1]="02";
		month[2]="03";
		month[3]="04";
		month[4]="05";
		month[5]="06";
		month[6]="07";
		month[7]="08";
		month[8]="09";
		month[9]="10";
		month[10]="11";
		month[11]="12";
		if(this.value == '<?php echo ExcelSchemeColumn::_MONTH;?>'){
			adt = myDate.getFullYear()-1 + "-01-01 00:00:00";
			bdt = myDate.getFullYear() + "-" + month[myDate.getMonth()] + "-" + myDate.getDate() + " " + myDate.getHours() + ":" + myDate.getMinutes() + ":" + myDate.getSeconds();
			$(".date:first").attr('value',adt);
			$(".date:last").attr('value',bdt);
		}
		else if(this.value == '<?php echo ExcelSchemeColumn::_DAY;?>'){
			if(myDate.getMonth() == 0){
				y = myDate.getFullYear()-1;
			}else{
				y = myDate.getFullYear();
			}
			adt = y + "-" + month[myDate.getMonth()-1] + "-01 00:00:00";
			bdt = y + "-" + month[myDate.getMonth()] + "-" + myDate.getDate() + " " + myDate.getHours() + ":" + myDate.getMinutes() + ":" + myDate.getSeconds();
			$(".date:first").attr('value',adt);
			$(".date:last").attr('value',bdt);
		}else if(this.value == '<?php echo ExcelSchemeColumn::_HOUR;?>'){
			adt =  myDate.getFullYear() + "-" + month[myDate.getMonth()] + "-" + myDate.getDate() + " 00:00:00";
			bdt =  myDate.getFullYear() + "-" + month[myDate.getMonth()] + "-" + myDate.getDate() + " " + myDate.getHours() + ":" + myDate.getMinutes() + ":" + myDate.getSeconds();
			$(".date:first").attr('value',adt);
			$(".date:last").attr('value',bdt);
		}
		else{
			if(myDate.getMonth() == 0){
				y = myDate.getFullYear()-1;
			}else{
				y = myDate.getFullYear();
			}
			adt = y + "-" + month[myDate.getMonth()-1] + "-01 00:00:00";
			bdt = y + "-" + month[myDate.getMonth()] + "-" + myDate.getDate() + " " + myDate.getHours() + ":" + myDate.getMinutes() + ":" + myDate.getSeconds();
			$(".date:first").attr('value',adt);
			$(".date:last").attr('value',bdt);
		}
		console.log(this.value,adt,bdt);
		$(".date:last").focus();
	});
})

function getSymbol(obj,key){
		var arrChk= $("input[name='"+obj.name+"']:checked");
		if(arrChk.length>0){
			$('#is_having_'+key).show();
		}else{
			$('#is_having_'+key).hide();
		}
	}
	function acheckNum(obj){
		if(obj.value ==''){
			obj.value = '';
		}
		if(!isNaN(obj.value)){
			
		}else{
			obj.value ='';
		}
	}
	var showTimeType = function(obj){
		var arrChk= $("input[name='"+obj.name+"']:checked");
		if(arrChk.length>0){
			$('#orderByTime').show();
		}else{
			$('#orderByTime').hide();
		}
	}
	
</script>