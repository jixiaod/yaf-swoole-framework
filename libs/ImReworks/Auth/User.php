<?php
/**
 * Description Guard
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
use ImReworks\Auth\Passwords\PasswordInterface as Password;

class User implements UserInterface
{
    protected $_provider;

    public function __construct($provider)
    {
        $this->_provider = $provider;    
    }

    public function getProvider()
    {
        return $this->_provider;
    }

    public function user()
    {

    }

    public function reg($account)
    {
        if (!$this->validateAccount($account)) return false;
        if ($this->_provider->createAccount($account)) {
            RST($user, API_SUCCESS_EXEC, '账户注册成功');
        }
        RST('', API_FAILED_REGISTER_ACCOUNT, '账户注册失败，请重新尝试');
    }

    public function login($email, $pwd, $space_id = 1)
    {
        if ($user = $this->_provider->retriveUserByPassword($email, $pwd)) {
            $this->_provider->updateLoginInfo($user['id']);   

            if ($user['adminer']) {
                $user['all_auth_mod'] = $this->_provider->retriveAuthesBySpaceId($space_id);
                $user['all_spaces'] = $this->_provider->retriveSpaces();
            } else {
                $user['all_auth_mod'] = $this->_provider->retriveAuthesByGid($user['gid'], $space_id);
                $user['all_spaces'] = $this->_provider->retriveSpacesByGid($user['gid']);
            }
            
            return $user;
        }

        return false;
    }

    public function logout()
    {
    
    }

    public function validateAccount($data)
    {
        list($username, $nickname, $truename, $tel) 
            = array(
                $data['username'], 
                $data['nickname'], 
                $data['truename'],
                $data['tel']
            );

        if (!$this->validateUsername($username))
            RST('', API_FAILED_USERNAME_INVALID, '账号名称格式不正确');
            
        if (empty($nickname)) 
            RST('', API_FAILED_NICKNAME_ENPTY, '昵称不能为空');

        if (empty($truename)) 
            RST('', API_FAILED_TURENAME_EMPTY, '真是姓名不能为空');

        if (!$this->validateTel($tel)) 
            RST('', API_FAILED_TEL_INVALID, '手机号码格式不正确');
        
        return true;
    }

    public function validateUsername($username) 
    {
        $pattern = '/@moji.com$/'; 
        preg_match($pattern, $username, $matches);

        if (empty($matches)) return false;
        if ($this->_provider->duplicateUsername($username))
            RST('', API_FAILED_ACCOUNT_DUPLICATE, '账号名称重复');

        return true;
    }

    public function validateTel($tel)
    {
        $pattern = '/^(0|86|17951)?(13[0-9]|15[012356789]|17[678]|18[0-9]|14[57])[0-9]{8}$/'; 
        preg_match($pattern, $tel, $matches);

        if (empty($matches)) return false;

        return true;
    }
}




