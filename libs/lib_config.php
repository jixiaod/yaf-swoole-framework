<?php

define("LIBPATH", __DIR__);

global $yaf;
$yaf = ImReworks\Yaf::getInstance();

class session 
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
session::start();

function logger($msg, $level = 1)
{
    $yaf->log->put($msg, $level);
}
