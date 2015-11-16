<?php

namespace ImReworks\Data;

class Mysql
{    
    const SUPPLIER_SERVER     = 'master';
    const CONSUMER_SERVER     = 'slave';
    const ACTIVE_CONNECTION   = '%s_datasource_active_connection_%s';
    const FAILED_CONNECTIONS  = '%s_datasource_failed_connections';
    

    private $__dbname = '';
    private $config = array();
    private $cacheTag = '';
    private $connections = array();
    
    public function __construct($config, $cacheTag)
    {
        $this->setConfig($config);
        $this->setCacheTag($cacheTag);
    }


    public function setDbname($dbname) 
    {
        $this->__dbname = $dbname;
    }

    public function getDbname()
    {
        return $this->__dbname;
    }
    
    /**
     * Set configuration array.
     *
     * @param array $config
     * @return void
     */
    public function setConfig($config)
    {
        $this->config = $config;
    }
    
    /**
     * Return configuration array.
     *
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }
        
    /**
     * Set cache tag name.
     *
     * @param string
     * @return void
     */
    public function setCacheTag($name)
    {
        $this->cacheTag = $name;
    }
    
    /**
     * Return cache tag name.
     *
     * @return string
     */
    public function getCacheTag()
    {
        return $this->cacheTag;
    }
    
    /**
     * Set an instance of Zend_Db_Adapter_Abstract.
     * 
     * @param Zend_Db_Adapter_Abstract $conn
     * @param string $server Options: master, slave
     * @return void
     */
    public function setConnection(\ImReworks\Db\Adapter\AdapterAbstract $conn, $server)
    {
        $namespace = sprintf(self::ACTIVE_CONNECTION, $this->getCacheTag(), strtolower($server));
        $this->connections[$namespace] = $conn;
    }
    
    /**
     * Return an instance of Zend_Db_Adapter_Abstract.
     * 
     * @param string $server master (supplier) or slave (consumer)
     * @return Zend_Db_Adapter_Abstract
     * @throws Exception 
     */
    public function getConnection($server)
    {
        $server = strtolower($server);
        $namespace = sprintf(self::ACTIVE_CONNECTION, $this->getCacheTag(), $server);
        if ($this->hasConnection($namespace)) {
            return $this->connections[$namespace];
        }
        
        $servers = $this->getListOfServers($server);
        $keys = (array) array_rand($servers, count($servers));
        foreach ($keys as $i => $key) {
            $connection = $this->createConnection($servers[$key]);
            if ($connection instanceof \ImReworks\Db\Adapter\AdapterAbstract) {
                $this->setConnection($connection, $server);
                return $connection;
            }
        }
        throw new Exception(sprintf('Unable to connect to "%s" server', $server));
    }
    
    /**
     * Verify that a given connection name exists.
     * 
     * @param string $name
     * @return boolean
     */
    public function hasConnection($name)
    {
        return array_key_exists($name, $this->connections);
    }
    
    /**
     * Create an instance of Zend_Db_Adapter_Abstract.
     *
     * @param array $server master (supplier) or slave (consumer)
     * @return Zend_Db_Adapter_Abstract|false
     * @see Zend_Db
     */
    public function createConnection($server)
    {
        $config = $this->getConfig();
        $server['dbname'] = $this->getDbname();
        foreach ($config[$server['dbname']] as $key => $value) {
            if ('servers' !== $key && !array_key_exists($key, $server)) {
                $server[$key] = $value;
            }
        }
        $db = \ImReworks\Db\Db::factory($config['adapter'], $server);

        if ($this->isConnected($db)) {
            return $db;
        }
        return false;
    }
    
    public function isConnected(\ImReworks\Db\Adapter\AdapterAbstract $adapter)
    {
        try {
            return ($adapter->getConnection()) ? true : false;
        } catch (\ImReworks\Db\DbException $e) {
            throw new Exception($e->getMessage());
        }
    }
    
    /**
     * Return list of database servers that will be used to create a 
     * connection.
     * 
     * @param string $server master (supplier) or slave (consumer)
     * @return array
     */
    public function getListOfServers($server)
    {
        $cfg = $this->getConfig();
        $dbname = $this->getDbname();
        $config = $cfg[$dbname];

        $servers = (isset($config['servers'])) ? $config['servers'] : array();
        $masterServers = (isset($config['master_servers'])) ? $config['master_servers'] : 1;
        if (self::SUPPLIER_SERVER === $server) {
            $servers = array_slice($servers, 0, $masterServers);
        } elseif (self::CONSUMER_SERVER === $server) {
            $masterRead = (isset($config['master_read'])) ? $config['master_read'] : false;
            if (false === $masterRead) {
                $servers = array_slice($servers, $masterServers, count($servers), true);
            }
        }
        return $servers;
    }
}







