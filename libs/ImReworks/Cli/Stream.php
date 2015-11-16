<?php

namespace ImReworks\Cli;

class Stream
{
    protected static $out = STDOUT;
    protected static $in = STDIN;
    protected static $err = STDERR;

    public static function _call($func, $args)
    {
        $method = __CLASS__ . '::' . $func;
        return call_user_func_array($method, $args);
    }

    public static function line($msg = '')
    {
        $args = array_merge(func_get_args(), array(''));
        $args[0] .= "\n";

        self::_call('out', $args);
    }

    public static function err($msg = '')
    {
        $args = array_merge(func_get_args(), array(''));
        $args[0] .= "\n";

        fwrite(static::$err, self::_call('render', $args));
    }

    public static function out($msg)
    {
        fwrite(static::$out, self::_call('render', func_get_args()));
    }

    public static function render($msg)
    {
        $args = func_get_args();

        if (count($args) == 1) return self::__formart($msg);
        
        if (!is_array($args[1])) {
            $args[0] = preg_replace('/(%([^\w]|$))/', "%$1", $args[0]);

            $msg = call_user_func_array('sprintf', $args);
            return self::__formart($msg);
        }
        
        foreach ($args[1] as $key => $val) {

            $msg = str_replace('{:' . $key . '}', $val, $msg);
        }

        return self::__formart($msg);
    }

    public static function __formart($msg)
    {
        return '[' . date('Y-m-d H:i:s') . '] >> ' . $msg;
    }

}
