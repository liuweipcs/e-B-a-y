<?php
/**
 * role setting tree component
 * 
 * @author ethanhu
 */

if ( User::isAdmin() ) {
    $list = Role::getTreeList();
} else {
    $roles = User::getLoginUserRoles();
    $list = Role::getTreeList($roles);
}

$pairs = Role::getPairs();

echo '<ul class="'. $class .'" id="'. $id .'"><li>';
echo CHtml::link($root, 'javascript:void(0);', array( "id" => 'roleAccessId_all'));
echo '<ul>';
if ( isset( $checkedArr) ) {
    echo subMenu($list, $pairs, $checkedArr);
} else {   
    echo subMenu($list, $pairs);
}

echo '</ul></li></ul>';
function subMenu($data, $pairs = null, $checkedArr = null) {
    $str = '';
    foreach ($data as $key => $val) {
        $str .= "<li>";
        $htmlOptions = array( "id" => 'roleAccessId_'.$val['child']);
        if(!isset($val['children'])){
        	$htmlOptions['onclick'] = 'get_role("'.$val['child'].'","'.$pairs[$val['child']].'","");';
        	$htmlOptions['rel']     = 'userBox';
        	$htmlOptions['target']  = 'ajax';
        }else{
        	$htmlOptions['onclick'] = 'get_role("'.$val['child'].'","'.$pairs[$val['child']].'","yes");';
        }
        
        if ( $checkedArr && in_array($val['child'], $checkedArr)) {
            $htmlOptions['checked'] = true;
        }
        if(!isset($val['children'])){
        	$str .= CHtml::link($pairs[$val['child']], '/users/access/selectUser/role_name/'.$pairs[$val['child']].'/role_code/'.$val['child'], $htmlOptions);
        }else{
        	$str .= CHtml::link($pairs[$val['child']], 'javascript:void(0);', $htmlOptions);
        }
        if ( isset($val['children'])) {
            $str .= "<ul>";
            $str .= subMenu($val['children'], $pairs, $checkedArr);
            $str .= "</ul>";
        }       
        $str .= '</li>';
    }
    return $str;
}
?>
<script>
function get_role(code,role_name,children=''){
	if(children=='yes'){
		$('#Productrole_role_name').val('');
		$('#Productrole_role_code').val('');
		//$('#roleAccessId_' + parent_code).closest('div').removeClass('selected');
		$('#userinfo').hide();
	}else{
		$('#Productrole_role_name').val(role_name);
		$('#Productrole_role_code').val(code);
		$('#userinfo').show();
	}
}
</script>

