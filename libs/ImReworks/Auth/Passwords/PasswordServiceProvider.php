<?php
namespace ImReworks\Auth\Passwords;

use ImReworks\DI\Container;
use ImReworks\DI\ServiceProviderInterface;

class PasswordServiceProvider implements ServiceProviderInterface
{

    public function register(Container $c)
    {
        $c['auth.passwords'] = function($c) {

            $provider = $c['auth']->getProvider();
            return new Password($provider);
        };
    }
}



