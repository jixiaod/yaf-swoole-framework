<?php

namespace ImReworks\Log\Filter;

use ImReworks\Log\Filter\Abstracti;
use ImReworks\Log\LogException;

class Message extends Abstracti
{
    /**
     * @var string
     */
    protected $_regexp;

    /**
     * Filter out any log messages not matching $regexp.
     *
     * @param  string  $regexp     Regular expression to test the log message
     * @return void
     * @throws LogException
     */
    public function __construct($regexp)
    {
        if (@preg_match($regexp, '') === false) {
            throw new LogException("Invalid regular expression '$regexp'");
        }
        $this->_regexp = $regexp;
    }

    /**
     * Create a new instance of ImReworks\Log\Filter\Message
     *
     * @param  array|Config $config
     * @return ImReworks\Log\Filter\Message
     */
    static public function factory($config)
    {
        $config = self::_parseConfig($config);
        $config = array_merge(array(
            'regexp' => null
        ), $config);

        return new self(
            $config['regexp']
        );
    }

    /**
     * Returns TRUE to accept the message, FALSE to block it.
     *
     * @param  array    $event    event data
     * @return boolean            accepted?
     */
    public function accept($event)
    {
        return preg_match($this->_regexp, $event['message']) > 0;
    }
}
