<?php
/**
 * Description GuardInterface
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

interface UserInterface
{

    public function user();

    public function reg($account);
    public function login($email, $pwd);

    public function logout();

}
