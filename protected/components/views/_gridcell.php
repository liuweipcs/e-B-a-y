<?php
if(isset($data)):?>
<table cellpadding="0" cellspacing="0" width="100%" border=0 class="innerTable">
<?php
$str = '';
foreach ($data as $k => $v):
$str .= '<tr>';
$str .= '<td style="border-bottom:1px dashed #70b3fa;border-right:0;height:24px;">';
switch($type){
	case 'checkbox':
		$str .= "<input type='checkbox' id='".$name."_".$v[$column]."' name='".$name."[]' value=$v[$column]>";
		break;
	case 'text':
		$str .= $v[$column];
		break;
	case 'image':
		$str .= $v[$column];
		break;
	default:
		break;
}
$str .= '</td>';
$str .= '</tr>';

endforeach;
echo $str;
?>
</table>
<?php endif;?>
