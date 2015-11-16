<?php

namespace ImReworks\Log\Writer;

use ImReworks\Log\Writer\Abstracti;
use ImReworks\Log\LogException;
use ImReworks\Log\Formatter\Interfacei;

class Db extends Abstracti
{
    /**
     * Database adapter instance
     *
     * @var ImReworks\Db\Adapter
     */
    protected $_db;

    /**
     * Name of the log table in the database
     *
     * @var string
     */
    protected $_table;

    /**
     * Relates database columns names to log data field keys.
     *
     * @var null|array
     */
    protected $_columnMap;

    /**
     * Class constructor
     *
     * @param ImReworks\Db\Adapter $db   Database adapter instance
     * @param string $table         Log table in database
     * @param array $columnMap
     * @return void
     */
    public function __construct($db, $table, $columnMap = null)
    {
        $this->_db    = $db;
        $this->_table = $table;
        $this->_columnMap = $columnMap;
    }

    /**
     * Create a new instance of ImReworks\Log\Writer\Db
     *
     * @param  array|Config $config
     * @return ImReworks\Log\Writer\Db
     */
    static public function factory($config)
    {
        $config = self::_parseConfig($config);
        $config = array_merge(array(
            'db'        => null,
            'table'     => null,
            'columnMap' => null,
        ), $config);

        if (isset($config['columnmap'])) {
            $config['columnMap'] = $config['columnmap'];
        }

        return new self(
            $config['db'],
            $config['table'],
            $config['columnMap']
        );
    }

    /**
     * Formatting is not possible on this writer
     *
     * @return void
     * @throws LogException
     */
    public function setFormatter(Interfacei $formatter)
    {
        throw new LogException(get_class($this) . ' does not support formatting');
    }

    /**
     * Remove reference to database adapter
     *
     * @return void
     */
    public function shutdown()
    {
        $this->_db = null;
    }

    /**
     * Write a message to the log.
     *
     * @param  array  $event  event data
     * @return void
     * @throws LogException
     */
    protected function _write($event)
    {
        if ($this->_db === null) {
            throw new LogException('Database adapter is null');
        }

        if ($this->_columnMap === null) {
            $dataToInsert = $event;
        } else {
            $dataToInsert = array();
            foreach ($this->_columnMap as $columnName => $fieldKey) {
                if (isset($event[$fieldKey])) {
                    $dataToInsert[$columnName] = $event[$fieldKey];
                }
            }
        }

        $this->_db->insert($this->_table, $dataToInsert);
    }
}
