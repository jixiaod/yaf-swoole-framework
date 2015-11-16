<?php

namespace ImReworks\Log\Formatter;

use ImReworks\Log\Formatter\Abstracti;

class Xml extends Abstracti
{
    /**
     * @var string Name of root element
     */
    protected $_rootElement;

    /**
     * @var array Relates XML elements to log data field keys.
     */
    protected $_elementMap;

    /**
     * @var string Encoding to use in XML
     */
    protected $_encoding;

    /**
     * Class constructor
     * (the default encoding is UTF-8)
     *
     * @param array|Config $options
     * @return void
     */
    public function __construct($options = array())
    {
        if ($options instanceof ImReworks\Config\Config) {
            $options = $options->toArray();
        } elseif (!is_array($options)) {
            $args = func_get_args();

            $options = array(
            	'rootElement' => array_shift($args)
            );

            if (count($args)) {
                $options['elementMap'] = array_shift($args);
            }

            if (count($args)) {
                $options['encoding'] = array_shift($args);
            }
        }

        if (!array_key_exists('rootElement', $options)) {
            $options['rootElement'] = 'logEntry';
        }

        if (!array_key_exists('encoding', $options)) {
            $options['encoding'] = 'UTF-8';
        }

        $this->_rootElement = $options['rootElement'];
        $this->setEncoding($options['encoding']);

        if (array_key_exists('elementMap', $options)) {
            $this->_elementMap  = $options['elementMap'];
        }
    }

    /**
	 * Factory for ImReworks\Log\Formatter\Xml class
	 *
	 * @param array|Config $options
	 * @return ImReworks\Log\Formatter\Xml 
     */
    public static function factory($options)
    {
        return new self($options);
    }

    /**
     * Get encoding
     *
     * @return string
     */
    public function getEncoding()
    {
        return $this->_encoding;
    }

    /**
     * Set encoding
     *
     * @param  string $value
     * @return ImReworks\Log\Formatter\Xml
     */
    public function setEncoding($value)
    {
        $this->_encoding = (string) $value;
        return $this;
    }

    /**
     * Formats data into a single line to be written by the writer.
     *
     * @param  array    $event    event data
     * @return string             formatted line to write to the log
     */
    public function format($event)
    {
        if ($this->_elementMap === null) {
            $dataToInsert = $event;
        } else {
            $dataToInsert = array();
            foreach ($this->_elementMap as $elementName => $fieldKey) {
                $dataToInsert[$elementName] = $event[$fieldKey];
            }
        }

        $enc = $this->getEncoding();
        $dom = new DOMDocument('1.0', $enc);
        $elt = $dom->appendChild(new DOMElement($this->_rootElement));

        foreach ($dataToInsert as $key => $value) {
            if (empty($value) 
                || is_scalar($value) 
                || (is_object($value) && method_exists($value,'__toString'))
            ) {
                if($key == "message") {
                    $value = htmlspecialchars($value, ENT_COMPAT, $enc);
                }
                $elt->appendChild(new DOMElement($key, (string)$value));
            }
        }

        $xml = $dom->saveXML();
        $xml = preg_replace('/<\?xml version="1.0"( encoding="[^\"]*")?\?>\n/u', '', $xml);

        return $xml . PHP_EOL;
    }
}
