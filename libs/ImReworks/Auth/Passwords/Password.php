<?php

namespace ImReworks\Auth\Passwords;

class Password implements PasswordInterface
{
    protected $_provider;

    public function __construct($provider)
    {
        $this->_provider = $provider;    
    }

    public function expried()
    {
    
    }

    public function changePassword($credentials)
    {
        list($uid, $password, $confirm) = array(
            $credentials['uid'],
            $credentials['password'],
            $credentials['confirm']
        );
        if ($this->validatePassword($uid, $password)) 
            RST('', API_FAILED_PASSWORD_SAME, '修改密码不能与原密码相同');

        $this->validateNewPassword($credentials);

        if ($this->_provider->updatePassword($uid, $password)){ 
            RST('', API_SUCCESS_EXEC, '密码修改成功');
        }

        RST($rst, API_FAILED_CHANGE_PASSWORD, '密码修改失败');
    }

    public function resetPassword($credentials)
    {

    }
    
    public function validateNewPassword($credentials)
    {
        list($password, $confirm) = array($credentials['password'], $credentials['confirm']);

        $pattern = "/^(?=.*[a-zA-Z])(?=.*\d)(?=.*[~!@#$%^&*()_+`\-={}\[\]:\";'<>?,.\/]).{8,18}$/";
        preg_match($pattern, $password, $matches);
        
        if (empty($matches)) RST('', API_FAILED_PASSWORD_INVALIED, '密码格式不正确');
        if ($password !== $confirm) RST('', API_FAILED_PASSWORD_DIFF, '俩次输入密码不相同');

        return true;
    }

    public function validatePassword($uid, $password)
    {
        $md5_password = $this->_provider->retrivePassword($uid);
        
        return $md5_password && md5($password) === $md5_password;
    }

}






