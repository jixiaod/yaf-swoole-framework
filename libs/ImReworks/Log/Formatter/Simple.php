<?php

namespace ImReworks\Log\Formatter;

use ImReworks\Config\Config as IR_Config;
use ImReworks\Log\Formatter\Abstracti;
use ImReworks\Log\LogException;

class Simple extends Abstracti
{
    /**
     * @var string
     */
    protected $_format;

    const DEFAULT_FORMAT = '%timestamp% %priorityName% (%priority%): %message%';

    /**
     * Class constructor
     *
     * @param  null|string  $format  Format specifier for log messages
     * @return void
     * @throws LogException
     */
    public function __construct($format = null)
    {
        if ($format === null) {
            $format = self::DEFAULT_FORMAT . PHP_EOL;
        }

        if (!is_string($format)) {
            throw new LogException('Format must be a string');
        }

        $this->_format = $format;
    }

    /**
	 * Factory for ImReworks\Log\Formatter\Simple classe
	 *
	 * @param array|Config $options
	 * @return ImReworks\Log\Formatter\Simple
     */
    public static function factory($options)
    {
        $format = null;
        if (null !== $options) {
            if ($options instanceof IR_Config) {
                $options = $options->toArray();
            }

            if (array_key_exists('format', $options)) {
                $format = $options['format'];
            }
        }

        return new self($format);
    }

    /**
     * Formats data into a single line to be written by the writer.
     *
     * @param  array    $event    event data
     * @return string             formatted line to write to the log
     */
    public function format($event)
    {
        $output = $this->_format;

        foreach ($event as $name => $value) {
            if ((is_object($value) && !method_exists($value,'__toString'))
                || is_array($value)
            ) {
                $value = gettype($value);
            }

            $output = str_replace("%$name%", $value, $output);
        }

        return $output;
    }
}
