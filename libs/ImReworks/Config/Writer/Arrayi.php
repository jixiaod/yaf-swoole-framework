<?php

namespace ImReworks\Config\Writer;

use ImReworks\Config\Writer\FileAbstract;

class Arrayi extends FileAbstract
{
    public function render()
    {
        $data        = $this->_config->toArray();
        $sectionName = $this->_config->getSectionName();

        if (is_string($sectionName)) {
            $data = array($sectionName => $data);
        }

        $arrayString = "<?php\n"
                     . "return " . var_export($data, true) . ";\n";

        return $arrayString;
    }
}
