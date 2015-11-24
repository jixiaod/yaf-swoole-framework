<?php

class RequestModel
{
    public function http()
    {
        $url = 'http://www.moji.com';
        $http = \Swoole\Client\Http($url);

        echo $http->get();
    }
}


