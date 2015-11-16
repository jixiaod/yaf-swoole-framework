<?php

namespace ImReworks\Log\Filter;

use ImReworks\Log\Filter\Interfacei;
use ImReworks\Log\FactoryInterface;
use ImReworks\Log\LogException;

abstract class Abstracti
    implements Interfacei, FactoryInterface
{
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
            throw new LogException('Configuration must be an array or instance of Config');
        }

        return $config;
    }
}
