<?php
namespace ImReworks\Config\Writer;

use ImReworks\Config\Config as IR_Config;
use ImReworks\Config\Writer as IR_Writer;
use ImReworks\Config\ConfigException as IR_Config_Exception;

class FileAbstract extends IR_Writer
{
    /**
     * Filename to write to
     *
     * @var string
     */
    protected $_filename = null;

    /**
     * Wether to exclusively lock the file or not
     *
     * @var boolean
     */
    protected $_exclusiveLock = false;

    /**
     * Set the target filename
     *
     * @param  string $filename
     * @return IR_Writer_Array
     */
    public function setFilename($filename)
    {
        $this->_filename = $filename;

        return $this;
    }

    /**
     * Set wether to exclusively lock the file or not
     *
     * @param  boolean     $exclusiveLock
     * @return IR_Writer_Array
     */
    public function setExclusiveLock($exclusiveLock)
    {
        $this->_exclusiveLock = $exclusiveLock;

        return $this;
    }

    /**
     * Write configuration to file.
     *
     * @param string $filename
     * @param IR_Config $config
     * @param bool $exclusiveLock
     * @return void
     */
    public function write($filename = null, IR_Config $config = null, $exclusiveLock = null)
    {
        if ($filename !== null) {
            $this->setFilename($filename);
        }

        if ($config !== null) {
            $this->setConfig($config);
        }

        if ($exclusiveLock !== null) {
            $this->setExclusiveLock($exclusiveLock);
        }

        if ($this->_filename === null) {
            throw new IR_Config_Exception('No filename was set');
        }

        if ($this->_config === null) {
            throw new IR_Config_Exception('No config was set');
        }

        $configString = $this->render();

        $flags = 0;

        if ($this->_exclusiveLock) {
            $flags |= LOCK_EX;
        }

        $result = @file_put_contents($this->_filename, $configString, $flags);

        if ($result === false) {
            throw new IR_Config_Exception('Could not write to file "' . $this->_filename . '"');
        }
    }

    /**
     * Render a IR_Config into a config file string.
     *
     * @since 1.10
     * @todo For 2.0 this should be redone into an abstract method.
     * @return string
     */
    public function render()
    {
        return "";
    }
}
