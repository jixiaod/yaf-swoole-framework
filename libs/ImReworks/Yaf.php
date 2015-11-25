<?php

namespace ImReworks;

class Yaf
{
    public $app_path;
    public $config;

    public static $yaf;

    static $modules = array(
                          'redis'   => ['cls' => \ImReworks\Redis::class, 'cfg' => true],
                          'mongo'   => ['cls' => \ImReworks\Mongo::class, 'cfg' => true],
                          'db'      => ['cls' => \Swoole\Database::class, 'cfg' => true],
                          'cache'   => ['cls' => \Swoole\Cache::class, 'cfg' => true],
                          'event'   => ['cls' => \Swoole\Event::class, 'cfg' => true],
                          'log'     => ['cls' => \Swoole\Log\FileLog::class, 'cfg' => true],
                          'upload'  => ['cls' => \Swoole\Upload::class, 'cfg' => true],
                          'session' => ['cls' => \ImReworks\Session::class, 'cfg' => true]
                      );

    public static function getInstance()
    {
        if (!self::$yaf) {
            self::$yaf = new Yaf;
        }

        return self::$yaf;
    }

    private function __construct()
    {
        if (empty($this->app_path)) {
            if (defined('WEBPATH')) {
                $this->app_path = WEBPATH . '/application';

            } else {
                \Swoole\Error::info("core error", __CLASS__.": Swoole::\$app_path and WEBPATH empty.");
            }
        }

        define('APPSPATH', $this->app_path);
        $this->config = new \ImReworks\Config;
        $this->config->setPath(APPSPATH . '/configs');
    }

    public function __get($name)
    {
        if (false == \Yaf_Registry::has($name)) {
            return $this->loadModule($name);
        }

        return \Yaf_Registry::get($name);
    }

    public function loadModule($name, $key = 'master')
    {
        // module 不存在
        if (!array_key_exists($name, self::$modules)) {
            throw new \ImReworks\Exception\ModuleNotFound("module [$module] not found.");
        }

        // module 需要有配置
        if (self::$modules[$name]['cfg']) {
            // 配置存在
            if (isset($this->config[$name][YAF_ENVIRON][$key])) {
                
                $config = $this->config[$name][YAF_ENVIRON][$key];
                \Yaf_Registry::set($name, new self::$modules[$name]['cls']($config));

            } else {
                throw new \ImReworks\Exception\ConfigNotFound("{$name}->". YAF_ENVIRON ." is not found.");
            }

        } else {
            \Yaf_Registry::set($name, new self::$modules[$name]);
        }

        return \Yaf_Registry::get($name);
    }

    public function __call($func, $args)
    {
        if (false == \Yaf_Registry::has($func)) {
            return $this->loadModule($func, $args[0]);
        }

        return \Yaf_Registry::get($func);
    }
}

