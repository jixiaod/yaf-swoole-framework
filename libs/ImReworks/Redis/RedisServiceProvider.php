<?php

namespace ImReworks\Redis;

use ImReworks\DI\Container;
use ImReworks\DI\ServiceProviderInterface;

class RedisServiceProvider implements ServiceProviderInterface
{

    public function register(Container $c)
    {
        $c['redis'] = function($c) {
            return new Redis($c['config.redis']);
        };

    }

}
