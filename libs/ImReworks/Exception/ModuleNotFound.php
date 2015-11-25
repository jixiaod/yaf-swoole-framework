<?php
namespace ImReworks\Exception;

define('YAF_ERR_MODULE_NOT_FOUND', 911);

class ModuleNotFound extends \Yaf_Exception 
{
    protected $code = YAF_ERR_MODULE_NOT_FOUND;
}


