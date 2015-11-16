<?php

namespace ImReworks\Log;

use ImReworks\Config\Config as IR_Config;
use ImReworks\Log\Writer\Abstracti;
use ImReworks\Log\LogException;
use ImReworks\Log\Filter\Interfacei as LogFilterInterface;

class Log
{
    const EMERG   = 0;  // Emergency: system is unusable
    const ALERT   = 1;  // Alert: action must be taken immediately
    const CRIT    = 2;  // Critical: critical conditions
    const ERR     = 3;  // Error: error conditions
    const WARN    = 4;  // Warning: warning conditions
    const NOTICE  = 5;  // Notice: normal but significant condition
    const INFO    = 6;  // Informational: informational messages
    const DEBUG   = 7;  // Debug: debug messages

    /**
     * @var array of priorities where the keys are the
     * priority numbers and the values are the priority names
     */
    protected $_priorities = array();

    /**
     * @var array of ImReworks\Log\Writer\Abstracti
     */
    protected $_writers = array();

    /**
     * @var array of LogFilterInterface
     */
    protected $_filters = array();

    /**
     * @var array of extra log event
     */
    protected $_extras = array();

    /**
     *
     * @var string
     */
    protected $_defaultWriterNamespace = 'ImReworks\Log\Writer';

    /**
     *
     * @var string
     */
    protected $_defaultFilterNamespace = 'ImReworks\Log\Filter';

    /**
     *
     * @var string
     */
    protected $_defaultFormatterNamespace = 'ImReworks\Log\Formatter';

    /**
     *
     * @var callback
     */
    protected $_origErrorHandler       = null;

    /**
     *
     * @var boolean
     */
    protected $_registeredErrorHandler = false;

    /**
     *
     * @var array|boolean
     */
    protected $_errorHandlerMap        = false;

    /**
     *
     * @var string
     */
    protected $_timestampFormat        = 'c';

    /**
     * Class constructor.  Create a new logger
     *
     * @param ImReworks\Log\Writer\Abstracti|null  $writer  default writer
     * @return void
     */
    public function __construct(Abstracti $writer = null)
    {
        $r = new \ReflectionClass($this);
        $this->_priorities = array_flip($r->getConstants());

        if ($writer !== null) {
            $this->addWriter($writer);
        }
    }

    /**
     * Factory to construct the logger and one or more writers
     * based on the configuration array
     *
     * @param  array|IR_Config Array or instance of IR_Config
     * @return Log
     * @throws LogException
     */
    static public function factory($config = array())
    {
        if ($config instanceof IR_Config) {
            $config = $config->toArray();
        }

        if (!is_array($config) || empty($config)) {
            /** @see LogException */
            throw new LogException('Configuration must be an array or instance of IR_Config');
        }

        if (array_key_exists('className', $config)) {
            $class = $config['className'];
            unset($config['className']);
        } else {
            $class = __CLASS__;
        }

        $log = new $class;

        if (!$log instanceof ImReworks\Log\Log) {
            /** @see LogException */
            throw new LogException('Passed className does not belong to a descendant of Log');
        }

        if (array_key_exists('timestampFormat', $config)) {
            if (null != $config['timestampFormat'] && '' != $config['timestampFormat']) {
                $log->setTimestampFormat($config['timestampFormat']);
            }
            unset($config['timestampFormat']);
        }

        if (!is_array(current($config))) {
            $log->addWriter(current($config));
        } else {
            foreach($config as $writer) {
                $log->addWriter($writer);
            }
        }

        return $log;
    }


    /**
     * Construct a writer object based on a configuration array
     *
     * @param  array $spec config array with writer spec
     * @return ImReworks\Log\Writer\Abstracti
     * @throws LogException
     */
    protected function _constructWriterFromConfig($config)
    {
        $writer = $this->_constructFromConfig('writer', $config, $this->_defaultWriterNamespace);

        if (!$writer instanceof WriterAbstract) {
            $writerName = is_object($writer)
                        ? get_class($writer)
                        : 'The specified writer';
            /** @see LogException */
            throw new LogException("{$writerName} does not extend ImReworks\Log\Writer\Abstracti!");
        }

        if (isset($config['filterName'])) {
            $filter = $this->_constructFilterFromConfig($config);
            $writer->addFilter($filter);
        }

        if (isset($config['formatterName'])) {
            $formatter = $this->_constructFormatterFromConfig($config);
            $writer->setFormatter($formatter);
        }

        return $writer;
    }

    /**
     * Construct filter object from configuration array or IR_Config object
     *
     * @param  array|IR_Config $config IR_Config or Array
     * @return LogFilterInterface
     * @throws LogException
     */
    protected function _constructFilterFromConfig($config)
    {
        $filter = $this->_constructFromConfig('filter', $config, $this->_defaultFilterNamespace);

        if (!$filter instanceof LogFilterInterface) {
             $filterName = is_object($filter)
                         ? get_class($filter)
                         : 'The specified filter';
            throw new LogException("{$filterName} does not implement LogFilterInterface");
        }

        return $filter;
    }

   /**
    * Construct formatter object from configuration array or IR_Config object
    *
    * @param  array|IR_Config $config IR_Config or Array
    * @return ImReworks\Log\Formatter\Interfacei
    * @throws LogException
    */
    protected function _constructFormatterFromConfig($config)
    {
        $formatter = $this->_constructFromConfig('formatter', $config, $this->_defaultFormatterNamespace);

        if (!$formatter instanceof ImReworks\Log\Formatter\Interfacei) {
             $formatterName = is_object($formatter)
                         ? get_class($formatter)
                         : 'The specified formatter';
            /** @see LogException */
            throw new LogException($formatterName . ' does not implement ImReworks\Log\Formatter\Interfacei');
        }

        return $formatter;
    }

    /**
     * Construct a filter or writer from config
     *
     * @param string $type 'writer' of 'filter'
     * @param mixed $config IR_Config or Array
     * @param string $namespace
     * @return object
     * @throws LogException
     */
    protected function _constructFromConfig($type, $config, $namespace)
    {
        if ($config instanceof IR_Config) {
            $config = $config->toArray();
        }

        if (!is_array($config) || empty($config)) {
            throw new LogException(
                'Configuration must be an array or instance of IR_Config'
            );
        }

        $params    = isset($config[ $type .'Params' ]) ? $config[ $type .'Params' ] : array();
        $className = $this->getClassName($config, $type, $namespace);

        $reflection = new ReflectionClass($className);
        if (!$reflection->implementsInterface('ImReworks\Log\FactoryInterface')) {
            throw new LogException(
                $className . ' does not implement ImReworks\Log\FactoryInterface and can not be constructed from config.'
            );
        }

        return call_user_func(array($className, 'factory'), $params);
    }

    /**
     * Get the writer or filter full classname
     *
     * @param array $config
     * @param string $type filter|writer
     * @param string $defaultNamespace
     * @return string full classname
     * @throws LogException
     */
    protected function getClassName($config, $type, $defaultNamespace)
    {
        if (!isset($config[$type . 'Name'])) {
            throw new LogException("Specify {$type}Name in the configuration array");
        }

        $className = $config[$type . 'Name'];
        $namespace = $defaultNamespace;

        if (isset($config[$type . 'Namespace'])) {
            $namespace = $config[$type . 'Namespace'];
        }

        // PHP >= 5.3.0 namespace given?
        if (substr($namespace, -1) == '\\') {
            return $namespace . $className;
        }

        // empty namespace given?
        if (strlen($namespace) === 0) {
            return $className;
        }

        return $namespace . '\\' . $className;
    }

    /**
     * Packs message and priority into Event array
     *
     * @param  string   $message   Message to log
     * @param  integer  $priority  Priority of message
     * @return array Event array
     */
    protected function _packEvent($message, $priority)
    {
        return array_merge(array(
            'timestamp'    => date($this->_timestampFormat),
            'message'      => $message,
            'priority'     => $priority,
            'priorityName' => $this->_priorities[$priority]
            ),
            $this->_extras
        );
    }

    /**
     * Class destructor.  Shutdown log writers
     *
     * @return void
     */
    public function __destruct()
    {
        foreach($this->_writers as $writer) {
            $writer->shutdown();
        }
    }

    /**
     * Undefined method handler allows a shortcut:
     *   $log->priorityName('message')
     *     instead of
     *   $log->log('message', Log::PRIORITY_NAME)
     *
     * @param  string  $method  priority name
     * @param  string  $params  message to log
     * @return void
     * @throws LogException
     */
    public function __call($method, $params)
    {
        $priority = strtoupper($method);
        if (($priority = array_search($priority, $this->_priorities)) !== false) {
            switch (count($params)) {
                case 0:
                    /** @see LogException */
                    throw new LogException('Missing log message');
                case 1:
                    $message = array_shift($params);
                    $extras = null;
                    break;
                default:
                    $message = array_shift($params);
                    $extras  = array_shift($params);
                    break;
            }
            $this->log($message, $priority, $extras);
        } else {
            /** @see LogException */
            throw new LogException('Bad log priority');
        }
    }

    /**
     * Log a message at a priority
     *
     * @param  string   $message   Message to log
     * @param  integer  $priority  Priority of message
     * @param  mixed    $extras    Extra information to log in event
     * @return void
     * @throws LogException
     */
    public function log($message, $priority, $extras = null)
    {
        // sanity checks
        if (empty($this->_writers)) {
            /** @see LogException */
            throw new LogException('No writers were added');
        }

        if (! isset($this->_priorities[$priority])) {
            /** @see LogException */
            throw new LogException('Bad log priority');
        }

        // pack into event required by filters and writers
        $event = $this->_packEvent($message, $priority);

        // Check to see if any extra information was passed
        if (!empty($extras)) {
            $info = array();
            if (is_array($extras)) {
                foreach ($extras as $key => $value) {
                    if (is_string($key)) {
                        $event[$key] = $value;
                    } else {
                        $info[] = $value;
                    }
                }
            } else {
                $info = $extras;
            }
            if (!empty($info)) {
                $event['info'] = $info;
            }
        }

        // abort if rejected by the global filters
        foreach ($this->_filters as $filter) {
            if (! $filter->accept($event)) {
                return;
            }
        }

        // send to each writer
        foreach ($this->_writers as $writer) {
            $writer->write($event);
        }
    }

    /**
     * Add a custom priority
     *
     * @param  string   $name      Name of priority
     * @param  integer  $priority  Numeric priority
     * @throws LogException
     */
    public function addPriority($name, $priority)
    {
        // Priority names must be uppercase for predictability.
        $name = strtoupper($name);

        if (isset($this->_priorities[$priority])
            || false !== array_search($name, $this->_priorities)) {
            /** @see LogException */
            throw new LogException('Existing priorities cannot be overwritten');
        }

        $this->_priorities[$priority] = $name;
        return $this;
    }

    /**
     * Add a filter that will be applied before all log writers.
     * Before a message will be received by any of the writers, it
     * must be accepted by all filters added with this method.
     *
     * @param  int|IR_Config|array|LogFilterInterface $filter
     * @return Log
     * @throws LogException
     */
    public function addFilter($filter)
    {
        if (is_int($filter)) {
            $filter = new ImReworks\Log\Filter\Priority($filter);

        } elseif ($filter instanceof IR_Config || is_array($filter)) {
            $filter = $this->_constructFilterFromConfig($filter);

        } elseif(! $filter instanceof LogFilterInterface) {
            /** @see LogException */
            throw new LogException('Invalid filter provided');
        }

        $this->_filters[] = $filter;
        return $this;
    }

    /**
     * Add a writer.  A writer is responsible for taking a log
     * message and writing it out to storage.
     *
     * @param  mixed $writer ImReworks\Log\Writer\Abstracti or Config array
     * @return Log
     */
    public function addWriter($writer)
    {
        if (is_array($writer) || $writer instanceof  IR_Config) {
            $writer = $this->_constructWriterFromConfig($writer);
        }

        if (!$writer instanceof Abstracti) {
            throw new LogException(
                'Writer must be an instance of ImReworks\Log\Writer\Abstracti'
                . ' or you should pass a configuration array'
            );
        }

        $this->_writers[] = $writer;
        return $this;
    }

    /**
     * Set an extra item to pass to the log writers.
     *
     * @param  string $name    Name of the field
     * @param  string $value   Value of the field
     * @return Log
     */
    public function setEventItem($name, $value)
    {
        $this->_extras = array_merge($this->_extras, array($name => $value));
        return $this;
    }

    /**
     * Register Logging system as an error handler to log php errors
     * Note: it still calls the original error handler if set_error_handler is able to return it.
     *
     * Errors will be mapped as:
     *   E_NOTICE, E_USER_NOTICE => NOTICE
     *   E_WARNING, E_CORE_WARNING, E_USER_WARNING => WARN
     *   E_ERROR, E_USER_ERROR, E_CORE_ERROR, E_RECOVERABLE_ERROR => ERR
     *   E_DEPRECATED, E_STRICT, E_USER_DEPRECATED => DEBUG
     *   (unknown/other) => INFO
     *
     * @link http://www.php.net/manual/en/function.set-error-handler.php Custom error handler
     *
     * @return Log
     */
    public function registerErrorHandler()
    {
        // Only register once.  Avoids loop issues if it gets registered twice.
        if ($this->_registeredErrorHandler) {
            return $this;
        }

        $this->_origErrorHandler = set_error_handler(array($this, 'errorHandler'));

        // Contruct a default map of phpErrors to Log priorities.
        // Some of the errors are uncatchable, but are included for completeness
        $this->_errorHandlerMap = array(
            E_NOTICE            => Log::NOTICE,
            E_USER_NOTICE       => Log::NOTICE,
            E_WARNING           => Log::WARN,
            E_CORE_WARNING      => Log::WARN,
            E_USER_WARNING      => Log::WARN,
            E_ERROR             => Log::ERR,
            E_USER_ERROR        => Log::ERR,
            E_CORE_ERROR        => Log::ERR,
            E_RECOVERABLE_ERROR => Log::ERR,
            E_STRICT            => Log::DEBUG,
        );
        // PHP 5.3.0+
        if (defined('E_DEPRECATED')) {
            $this->_errorHandlerMap['E_DEPRECATED'] = Log::DEBUG;
        }
        if (defined('E_USER_DEPRECATED')) {
            $this->_errorHandlerMap['E_USER_DEPRECATED'] = Log::DEBUG;
        }

        $this->_registeredErrorHandler = true;
        return $this;
    }

    /**
     * Error Handler will convert error into log message, and then call the original error handler
     *
     * @link http://www.php.net/manual/en/function.set-error-handler.php Custom error handler
     * @param int $errno
     * @param string $errstr
     * @param string $errfile
     * @param int $errline
     * @param array $errcontext
     * @return boolean
     */
    public function errorHandler($errno, $errstr, $errfile, $errline, $errcontext)
    {
        $errorLevel = error_reporting();

        if ($errorLevel & $errno) {
            if (isset($this->_errorHandlerMap[$errno])) {
                $priority = $this->_errorHandlerMap[$errno];
            } else {
                $priority = Log::INFO;
            }
            $this->log($errstr, $priority, array('errno'=>$errno, 'file'=>$errfile, 'line'=>$errline, 'context'=>$errcontext));
        }

        if ($this->_origErrorHandler !== null) {
            return call_user_func($this->_origErrorHandler, $errno, $errstr, $errfile, $errline, $errcontext);
        }
        return false;
    }

    /**
     * Set timestamp format for log entries.
     *
     * @param string $format
     * @return Log
     */
    public function setTimestampFormat($format)
    {
        $this->_timestampFormat = $format;
        return $this;
    }

    /**
     * Get timestamp format used for log entries.
     *
     * @return string
     */
    public function getTimestampFormat()
    {
        return $this->_timestampFormat;
    }
}
