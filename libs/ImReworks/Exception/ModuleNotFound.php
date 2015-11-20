<?php
namespace ImReworks;

define('YAF_ERR_MODULE_NOT_FOUND', 911);

class ModuleNotFound extends Yaf_Exception 
{
    protected string $code = YAF_ERR_MODULE_NOT_FOUND;
}


