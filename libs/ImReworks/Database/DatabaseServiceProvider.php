<?php

namespace ImReworks\Database;

use ImReworks\DI\Container;
use ImReworks\DI\ServiceProviderInterface;

class DatabaseServiceProvider implements ServiceProviderInterface
{

    public function register(Container $c)
    {
        $c['db'] = function($c) {
            return new Database($c['config.db'], 'db_cache_tag');
        };

    }

}
