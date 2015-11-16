<?php
/**
 * Description AuthServiceProvider
 * 
 * PHP version 5
 * 
 * @category PHP
 * @package ImReworks\Auth
 * @author Gang Ji <gang.ji@moji.com>
 * @copyright 2014-2016 Moji Fengyun Software Technology Development Co., Ltd.
 * @license license from Moji Fengyun Software Technology Development Co., Ltd.
 * @link http://www.moji.com
 */

namespace ImReworks\Auth;

use ImReworks\DI\Container;
use ImReworks\DI\ServiceProviderInterface;

class AuthServiceProvider implements ServiceProviderInterface
{

    public function register(Container $c)
    {
        $c['auth'] = function($c) {
            return new AuthManager($c);
        };

        $c['auth.driver'] = function($c) {
            return $c['auth']->createDatabaseDriver();
        };
    }
}



