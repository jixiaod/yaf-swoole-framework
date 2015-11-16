<?php

namespace ImReworks\Auth\Passwords;

interface PasswordInterface
{
    public function expried();

    public function resetPassword($credentials);

    public function validatePassword($uid, $password);
}




