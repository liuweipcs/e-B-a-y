<?php
set_time_limit(0);
try {
    //connect database
    $pdoMysql = new PDO("mysql:dbname=ueb_system;host=192.168.3.201", 'root', '49BA59ABBE56E057');
    $pdoMysql->exec("SET NAMES 'utf8'");
    
    $return  = array();
    $accountList = array('ebay', 'amazon', 'aliexpress', 'walmart','lazada');
    $platform = isset($_REQUEST['platform']) ? $_REQUEST['platform'] : '';
    if (empty($platform) || !in_array($platform, $accountList)) {
        $return['success'] = false;
        $return['error'] = 'platform is invalid';
        echo json_encode($return);
        exit;
    }
    
    $sql = '';
    $tableName = '';
    switch ($platform) { 
        case 'ebay':
            $tableName = 'ueb_ebay_account';
            break;
        case 'amazon':
            $tableName = 'ueb_amazon_account';
            break;
        case 'aliexpress':
            $tableName = 'ueb_aliexpress_account';
            break;
        case 'walmart':
            $tableName = 'ueb_walmart_account';
            break;   
        case 'lazada':
        	$tableName = 'ueb_lazada_account';
        	break;
    }
    $sql = "SELECT * FROM `" . $tableName . "` WHERE `status` = 1";
    $pdoStatement = $pdoMysql->query($sql);
    $accounts = $pdoStatement->fetchAll(PDO::FETCH_ASSOC);
    if (empty($accounts))
        $accounts = array();
    $return['success'] = true;
    $return['account_list'] = $accounts;
    echo json_encode($return);
    exit;
} catch (Exception $e) {
    echo $e->getMessage();
    $return['success'] = false;
    $return['error'] = 'Server Internal Error';
    echo json_encode($return);
    exit;    
}