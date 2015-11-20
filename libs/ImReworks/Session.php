<?php
namespce ImReworks;

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

    static function get($key = null)
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

}
