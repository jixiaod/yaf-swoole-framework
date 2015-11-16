<?php

namespace ImReworks\Config\Writer;

use ImReworks\Config\Config as IR_Config;
use ImReworks\Config\Writer\FileAbstract;
use ImReworks\Config\ConfigException as IR_Config_Exception;


class Ini extends FileAbstract
{
    /**
     * String that separates nesting levels of configuration data identifiers
     *
     * @var string
     */
    protected $_nestSeparator = '.';

    /**
     * If true the ini string is rendered in the global namespace without sections.
     *
     * @var bool
     */
    protected $_renderWithoutSections = false;

    /**
     * Set the nest separator
     *
     * @param  string $filename
     * @return Ini
     */
    public function setNestSeparator($separator)
    {
        $this->_nestSeparator = $separator;

        return $this;
    }

    /**
     * Set if rendering should occour without sections or not.
     *
     * If set to true, the INI file is rendered without sections completely
     * into the global namespace of the INI file.
     *
     * @param  bool $withoutSections
     * @return Ini
     */
    public function setRenderWithoutSections($withoutSections=true)
    {
        $this->_renderWithoutSections = (bool)$withoutSections;
        return $this;
    }

    /**
     * Render a IR_Config into a INI config string.
     *
     * @since 1.10
     * @return string
     */
    public function render()
    {
        $iniString   = '';
        $extends     = $this->_config->getExtends();
        $sectionName = $this->_config->getSectionName();

        if($this->_renderWithoutSections == true) {
            $iniString .= $this->_addBranch($this->_config);
        } else if (is_string($sectionName)) {
            $iniString .= '[' . $sectionName . ']' . "\n"
                       .  $this->_addBranch($this->_config)
                       .  "\n";
        } else {
            $config = $this->_sortRootElements($this->_config);
            foreach ($config as $sectionName => $data) {
                if (!($data instanceof IR_Config)) {
                    $iniString .= $sectionName
                               .  ' = '
                               .  $this->_prepareValue($data)
                               .  "\n";
                } else {
                    if (isset($extends[$sectionName])) {
                        $sectionName .= ' : ' . $extends[$sectionName];
                    }

                    $iniString .= '[' . $sectionName . ']' . "\n"
                               .  $this->_addBranch($data)
                               .  "\n";
                }
            }
        }

        return $iniString;
    }

    /**
     * Add a branch to an INI string recursively
     *
     * @param  IR_Config $config
     * @return void
     */
    protected function _addBranch(IR_Config $config, $parents = array())
    {
        $iniString = '';

        foreach ($config as $key => $value) {
            $group = array_merge($parents, array($key));

            if ($value instanceof IR_Config) {
                $iniString .= $this->_addBranch($value, $group);
            } else {
                $iniString .= implode($this->_nestSeparator, $group)
                           .  ' = '
                           .  $this->_prepareValue($value)
                           .  "\n";
            }
        }

        return $iniString;
    }

    /**
     * Prepare a value for INI
     *
     * @param  mixed $value
     * @return string
     */
    protected function _prepareValue($value)
    {
        if (is_integer($value) || is_float($value)) {
            return $value;
        } elseif (is_bool($value)) {
            return ($value ? 'true' : 'false');
        } elseif (strpos($value, '"') === false) {
            return '"' . $value .  '"';
        } else {
            /** @see IR_Config_Exception */
            throw new IR_Config_Exception('Value can not contain double quotes "');
        }
    }

    /**
     * Root elements that are not assigned to any section needs to be
     * on the top of config.
     *
     * @param  IR_Config
     * @return IR_Config
     */
    protected function _sortRootElements(IR_Config $config)
    {
        $configArray = $config->toArray();
        $sections = array();

        // remove sections from config array
        foreach ($configArray as $key => $value) {
            if (is_array($value)) {
                $sections[$key] = $value;
                unset($configArray[$key]);
            }
        }

        // readd sections to the end
        foreach ($sections as $key => $value) {
            $configArray[$key] = $value;
        }

        return new IR_Config($configArray);
    }
}
