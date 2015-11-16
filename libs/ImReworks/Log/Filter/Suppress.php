<?php

namespace ImReworks\Log\Filter;

use ImReworks\Log\Filter\Abstracti;

class Suppress extends Abstracti
{
    /**
     * @var boolean
     */
    protected $_accept = true;

    /**
     * This is a simple boolean filter.
     *
     * Call suppress(true) to suppress all log events.
     * Call suppress(false) to accept all log events.
     *
     * @param  boolean  $suppress  Should all log events be suppressed?
     * @return  void
     */
    public function suppress($suppress)
    {
        $this->_accept = (! $suppress);
    }

    /**
     * Returns TRUE to accept the message, FALSE to block it.
     *
     * @param  array    $event    event data
     * @return boolean            accepted?
     */
    public function accept($event)
    {
        return $this->_accept;
    }

    /**
     * Create a new instance of ImReworks\Log\Filter\Suppress
     *
     * @param  array|Config $config
     * @return ImReworks\Log\Filter\Suppress
     * @throws LogException
     */
    static public function factory($config)
    {
        return new self();
    }
}
