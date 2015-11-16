<?php

namespace ImReworks\Db\Adapter;

use ImReworks\Db\DbException;

class AdapterException extends DbException
{
    protected $_chainedException = null;

    public function __construct($message = '', $code = 0, Exception $e = null)
    {
        if ($e && (0 === $code)) {
            $code = $e->getCode();
        }
        parent::__construct($message, $code, $e);
    }

    public function hasChainedException()
    {
        return ($this->getPrevious() !== null);
    }

    public function getChainedException()
    {
        return $this->getPrevious();
    }

}
