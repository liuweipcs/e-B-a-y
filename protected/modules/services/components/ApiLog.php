<?php
/**
 * @desc API日志类
 * @author Fun
 *
 */
class ApiLog
{
    /**
     * @desc 日志基础路径
     * @var unknown
     */
    public $logPath = '';
    
    /**
     * @desc 日志信息
     * @var unknown
     */
    public $logs = array();
    
    const MODE_APPEND = 'append';       //追加到文件
    const MODE_REPLACE = 'replace';     //替换到文件
    
    /**
     * @desc construnct
     */
    public function __construct()
    {
        $this->logPath = Yii::getPathOfAlias('webroot') . DIRECTORY_SEPARATOR . 'log';
    }
    
    /**
     * @desc 设置日志路径
     * @param string $path
     */
    public function setLogPath($path = '')
    {
        $this->logPath = $path;
    }
    
    /**
     * @desc 设置日志信息
     * @param string $log
     */
    public function setLogs($log = '')
    {
        $this->logs[] = strval($log);
    }
    
    /**
     * @desc 写入日志信息
     * @param unknown $logFilename
     * @param unknown $mode
     * @return boolean
     */
    public function writeLog($logFilename, $mode = self::MODE_APPEND)
    {
        $filePath = $this->logPath . DIRECTORY_SEPARATOR . trim($logFilename, '/');
        $path = dirname($filePath);
        if (!file_exists($path))
            mkdir($path, 0777, true);
        if ($mode == self::MODE_APPEND)
            $fp = fopen($filePath, 'a');
        else
            $fp = fopen($filePath, 'w');
        if (!$fp)
            return false;
        foreach ($this->logs as $log)
            fwrite($fp, $log . "\r\n");
        fclose($fp);
        return true;
    }
}