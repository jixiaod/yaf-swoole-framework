<?php

define("LIBPATH", __DIR__);

global $yaf;
$yaf = ImReworks\Yaf::getInstance();


function logger($msg, $level = 1)
{
    $yaf->log->put($msg, $level);
}
