<?php

class HttpController extends \ImReworks\Controller
{
    public function getAction()
    {
        //$m = new RequestModel;
        $url = 'http://swoole.lo';
        $http = new \Swoole\Client\Http($url);
        echo $http->get('/');
        return false;
    }
}


