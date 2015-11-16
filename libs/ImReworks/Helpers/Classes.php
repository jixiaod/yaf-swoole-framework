<?php
// {{ 便捷方法

/**
* @brief  session
*/
class Session 
{
	
	static function start()
	{
        Yaf_Session::getInstance()->start();
	}
	
	static function set($key, $value)
    {
        return Yaf_Session::getInstance()->set($key, $value);
	}

	static function get($key)
	{
        return Yaf_Session::getInstance()->get($key);
	}

	static function del($key)
	{
        return Yaf_Session::getInstance()->del($key);
	}
    
    static function has($key)
	{
        return Yaf_Session::getInstance()->has($key);
	}

}// class session


/**
 * @brief  使用Zend通用日志组建
 */
class Logger
{
    static $logger;

    static function start()
    {
        $log_file = LOGGER_PATH . LOGGER_FILENAME;
        
        //检测日志文件大小，超过配置大小则备份日志文件重新生成
        if ( is_file( $log_file) && floor(LOGGER_FILE_SIZE) <= filesize($log_file) )

            rename($log_file,$log_file . '.' . date('YmdHms',SYSTEM_TIME));          
        try {

            $writer = new ImReworks\Log\Writer\Stream($log_file);
            self::$logger = new ImReworks\Log\Log($writer);

        } catch( Exception $e ) {
            echo $e->__toString();exit;
        }
    }

    /* EMERG   = 0;  // Emergency: 系统不可用 */
    /* ALERT   = 1;  // Alert: 报警 */
    /* CRIT    = 2;  // Critical: 紧要 */
    /* ERR     = 3;  // Error: 错误 */
    /* WARN    = 4;  // Warning: 警告 */
    /* NOTICE  = 5;  // Notice: 通知 */
    /* INFO    = 6;  // Informational: 一般信息 */
    /* DEBUG   = 7;  // Debug: 小时消息 */
    static function write($message, $priority = 6)
    {
        self::$logger->log($message, $priority);
    }
}
// }}
