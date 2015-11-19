<?php

namespace ImReworks;

use Swoole\Exception\NotFound;

class Yaf
{
    public $app_path;
    public $config;

    public static $yaf;

    static $modules = array(
                          'redis' => \ImReworks\Redis::class,
                          'mongo' => \ImReworks\Mongo::class,
                          'db' => \Swoole\Database::class,
                          'cache' => true, //缓存
                          'event' => \Swoole\Event::class, //异步事件
                          'log' => \Swoole\Log, //日志
                          'upload' => true, //上传组件
                          'user' => true,   //用户验证组件
                          'session' => true, //session
                          'http' => true, //http
                          'url' => true, //urllib
                          'limit' => true, //频率限制组件
                      );

    private function __construct()
    {
        if (empty($this->app_path)) {
            if (defined('WEBPATH')) {
                $this->app_path = WEBPATH . '/application';

            } else {
                Swoole\Error::info("core error", __CLASS__.": Swoole::\$app_path and WEBPATH empty.");
            }
        }

        define('APPSPATH', $this->app_path);

        $this->config = new \ImReworks\Config;
        $this->config->setPath(APPSPATH . '/configs');
    }

    public static function getInstance()
    {
        if (!self::$yaf) {
            self::$yaf = new Yaf;
        }

        return self::$yaf;
    }

    public function __get($name)
    {
        //如果不存在此对象，从工厂中创建一个
        if (false == \Yaf_Registry::has($name)) {
            //载入组件
            $config = $this->config['db'][YAF_ENVIRON];
            \Yaf_Registry::set($name, new self::$modules[$name]($config));
        }

        return \Yaf_Registry::get($name);
    }

    public function __call($func, $args)
    {
        //如果不存在此对象，从工厂中创建一个
        if (false == \Yaf_Registry::haf($name)) {
            //载入组件
            $env = YAF_ENVIRON;
            $config = \Yaf_Registry::get('config')->$name->$env;
            \Yaf_Registry::set($name, new self::$modules[$name]($config));
        }

        return \Yaf_Registry::get($name);
    }

}




