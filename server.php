<?php

define('DEBUG', 'on');
define("WEBPATH", realpath(__DIR__));

if (!include __DIR__.'/vendor/autoload.php') {

    $message = <<< EOF
<p>You must set up the project dependencies by running the following commands:</p>
<pre>
    curl -s http://getcomposer.org/installer | php
    php composer.phar install
</pre>
EOF;

if (PHP_SAPI === 'cli') {
    $message = strip_tags($message);
} 

die($message);
}


//require WEBPATH . '/libs/lib_config.php';

//Swoole\Config::$debug = true;

$AppSvr = new Swoole\Protocol\HttpServer();
$AppSvr->loadSetting(__DIR__.'/conf/swoole.ini'); //加载配置文件
//$AppSvr->setDocumentRoot(__DIR__.'/webroot');
$AppSvr->setLogger(new Swoole\Log\EchoLog(true)); //Logger

//Swoole\Error::$echo_html = false;

$server = Swoole\Network\Server::autoCreate('0.0.0.0', 8888);
$server->setProtocol($AppSvr);
//$server->daemonize(); //作为守护进程
$server->run(array('worker_num' => 0, 'max_request' => 5000, 'log_file' => '/tmp/swoole.log'));






