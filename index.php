<?php
//ini_set("session.save_handler", "memcache");
//ini_set("session.save_path", "tcp://127.0.0.1:11211");
error_reporting(E_ERROR);
//echo '系统暂时关闭';exit;
function findClass($class,$type = 'class',$isexit = 1){
    echo '<pre>';
    if($type == 'class'){
        var_dump(ReflectionClass::export($class));
    }else{
        var_dump($class);
    }
    if($isexit == 1)
    exit;
}

require_once(dirname(__FILE__) . '/protected/config/env.php');

//1> DEVELOPMENT 2> TEST  3> PRODUCTION
$envParm = Env::DEVELOPMENT;

$env = new Env($envParm);
$yii = dirname(__FILE__) . '/framework/yii.php';
defined('YII_DEBUG') or define('YII_DEBUG', $env->getDebug());
defined('YII_TRACE_LEVEL') or define('YII_TRACE_LEVEL', $env->getTraceLevel());
define('YII_ENV', $envParm);
define('DS', DIRECTORY_SEPARATOR);
define('CONF_PATH', dirname(__FILE__) . DS . 'protected' .DS .'config'.DS);
require_once($yii);
Yii::createWebApplication($env->getConfig())->run();
