<?php

namespace ImReworks\Mongo;

use ImReworks\DI\Container;
use ImReworks\DI\ServiceProviderInterface;

class MongoServiceProvider implements ServiceProviderInterface
{

    public function register(Container $c)
    {
        $c['mongo'] = function($c) {
            return new Mongo($c['config.mongo']);
        };

    }

}
