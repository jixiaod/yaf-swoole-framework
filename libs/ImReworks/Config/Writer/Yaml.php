<?php

namespace ImReworks\Config\Writer;

use ImReworks\Config\Config as IR_Config;
use ImReworks\Config\Yaml as IR_Yaml;
use ImReworks\Config\Writer\FileAbstract;
use ImReworks\Config\ConfigException as IR_Config_Exception;


class Yaml extends FileAbstract
{
    /**
     * What to call when we need to decode some YAML?
     *
     * @var callable
     */
    protected $_yamlEncoder = array('ImReworks\Config\Writer\Yaml', 'encode');

    /**
     * Get callback for decoding YAML
     *
     * @return callable
     */
    public function getYamlEncoder()
    {
        return $this->_yamlEncoder;
    }

    /**
     * Set callback for decoding YAML
     *
     * @param  callable $yamlEncoder the decoder to set
     * @return IR_Yaml
     */
    public function setYamlEncoder($yamlEncoder)
    {
        if (!is_callable($yamlEncoder)) {
            throw new IR_Config_Exception('Invalid parameter to setYamlEncoder - must be callable');
        }

        $this->_yamlEncoder = $yamlEncoder;
        return $this;
    }

    /**
     * Render a Config into a YAML config string.
     *
     * @since 1.10
     * @return string
     */
    public function render()
    {
        $data        = $this->_config->toArray();
        $sectionName = $this->_config->getSectionName();
        $extends     = $this->_config->getExtends();

        if (is_string($sectionName)) {
            $data = array($sectionName => $data);
        }

        foreach ($extends as $section => $parentSection) {
            $data[$section][IR_Yaml::EXTENDS_NAME] = $parentSection;
        }

        // Ensure that each "extends" section actually exists
        foreach ($data as $section => $sectionData) {
            if (is_array($sectionData) && isset($sectionData[IR_Yaml::EXTENDS_NAME])) {
                $sectionExtends = $sectionData[IR_Yaml::EXTENDS_NAME];
                if (!isset($data[$sectionExtends])) {
                    // Remove "extends" declaration if section does not exist
                    unset($data[$section][IR_Yaml::EXTENDS_NAME]);
                }
            }
        }

        return call_user_func($this->getYamlEncoder(), $data);
    }

    /**
     * Very dumb YAML encoder
     *
     *
     * @param array $data YAML data
     * @return string
     */
    public static function encode($data)
    {
        return self::_encodeYaml(0, $data);
    }

    /**
     * Service function for encoding YAML
     *
     * @param int $indent Current indent level
     * @param array $data Data to encode
     * @return string
     */
    protected static function _encodeYaml($indent, $data)
    {
        reset($data);
        $result = "";
        $numeric = is_numeric(key($data));

        foreach($data as $key => $value) {
            if(is_array($value)) {
                $encoded = "\n".self::_encodeYaml($indent+1, $value);
            } else {
                $encoded = (string)$value."\n";
            }
            $result .= str_repeat("  ", $indent).($numeric?"- ":"$key: ").$encoded;
        }
        return $result;
    }
}
