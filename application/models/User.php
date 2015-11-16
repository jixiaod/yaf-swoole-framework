<?php

class UserModel extends Swoole\Model
{
    public $table = 'user';

    public function __construct()
    {
    }

    public function selectSample()
    {
        return 'Hello World!';
    }

    public function insertSample($arrInfo)
    {
        return true;
    }
}
