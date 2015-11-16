<?php

namespace ImReworks\Log\Writer;

use ImReworks\Log\Filter\Priority;
use ImReworks\Log\FactoryInterface;
use ImReworks\Log\LogException;

abstract class Abstracti implements FactoryInterface
{
    /**
     * @var array of ImReworks\Log\Filter\Interface
     */
    protected $_filters = array();

    /**
     * Formats the log message before writing.
     *
     * @var ImReworks\Log\Filter\Interface
     */
    protected $_formatter;

    /**
     * Add a filter specific to this writer.
     *
     * @param  ImReworks\Log\Filter\Interface $filter
     * @return ImReworks\Log\Writer\Abstracti
     */
    public function addFilter($filter)
    {
        if (is_int($filter)) {
            $filter = new ImReworks\Log\Filter\Priority($filter);
        }

        if (!$filter instanceof ImReworks\Log\Filter\Interfacei) {
            throw new LogException('Invalid filter provided');
        }

        $this->_filters[] = $filter;
        return $this;
    }

    /**
     * Log a message to this writer.
     *
     * @param  array $event log data event
     * @return void
     */
    public function write($event)
    {
        foreach ($this->_filters as $filter) {
            if (! $filter->accept($event)) {
                return;
            }
        }

        // exception occurs on error
        $this->_write($event);
    }

    /**
     * Set a new formatter for this writer
     *
     * @param  ImReworks\Log\Formatter\Interfacei $formatter
     * @return ImReworks\Log\Writer\Abstracti
     */
    public function setFormatter(ImReworks\Log\Formatter\Interfacei $formatter)
    {
        $this->_formatter = $formatter;
        return $this;
    }

    /**
     * Perform shutdown activites such as closing open resources
     *
     * @return void
     */
    public function shutdown()
    {}

    /**
     * Write a message to the log.
     *
     * @param  array  $event  log data event
     * @return void
     */
    abstract protected function _write($event);

    /**
     * Validate and optionally convert the config to array
     *
     * @param  array|Config $config Config or Array
     * @return array
     * @throws LogException
     */
    static protected function _parseConfig($config)
    {
        if ($config instanceof ImReworks\Config\Config) {
            $config = $config->toArray();
        }

        if (!is_array($config)) {
            throw new LogException(
                'Configuration must be an array or instance of Config'
            );
        }

        return $config;
    }
}
