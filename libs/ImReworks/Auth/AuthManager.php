<?php

/**
 * Description AuthManager
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

use ImReworks\Auth\DatabaseUserProvider;
class AuthManager
{
    protected $_container;

    public function __construct($container)
    {
        $this->_container = $container;
    }

    public function createDatabaseDriver()
    {
        $provider = $this->createDatabaseProvider();
        return new User($provider);
    }

    public function createDatabaseProvider()
    {
        $conn = $this->_container['db'];
        
        return new DatabaseUserProvider($conn); 
    }

    public function __call($method, $parameters)
    {   
        return call_user_func_array([$this->createDatabaseDriver(), $method], $parameters);
    }  

}



