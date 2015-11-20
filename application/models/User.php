<?php

class UserModel extends \ImReworks\Model
{
    public $table = 'user';

    public function redis()
    {
        $redis = $this->yaf->redis('master');
        var_dump($redis->set('yaf_swoole_key:123', 123));
        var_dump($redis->get('yaf_swoole_key:123'));

    }

}
