<?php
/**
 * Description Timer
 * 
 * PHP version 5
 * 
 * @category PHP
 * @package ImReworks 
 * @author Gang Ji <gang.ji@moji.com>
 * @copyright 2014-2016 Moji Fengyun Software Technology Development Co., Ltd.
 * @license license from Moji Fengyun Software Technology Development Co., Ltd.
 * @link http://www.moji.com
 */

namespace ImReworks\Cli;

class Timer
{
    protected $blocks = [];
    protected $avgs = [];
    protected $enabled;

    public function __construct($enabled = true)
    {
        $this->enabled = $enabled;
    }

    public function enable()
    {
        $this->enabled = true;
    }

    public function disable()
    {
        $this->enabled = false;
    }

    public function getEnabled()
    {
        return $this->enabled;
    }

    public function start($block)
    {
        if ($this->enabled) {
            if (!isset($this->blocks[$block])) {
                $this->blocks[$block] = [];
            }
        }

        $this->blocks[$block]['start'] = microtime(true);
        $this->blocks[$block]['start-line'] = $this->getLineNumber();
    }

    public function stop($block)
    {
        if ($this->enabled) {
            if (!isset($this->blocks[$block])) {
                throw new \Exception('Block'. $block . ' has not been started.');
            }
        }

        $this->blocks[$block]['stop'] = microtime(true);
        $this->blocks[$block]['stop-line'] = $this->getLineNumber();
    }

    public function getLineNumber()
    {
        $dbt = debug_backtrace();
        return $dbt[1]['line'];
    }

    public function startAvg($block)
    {
        if ($this->enabled) {
            if (!isset($this->avgs[$block])) {
                $this->avgs[$block] = [];
                $this->avgs[$block]['count'] = 0;
                $this->avgs[$block]['total'] = 0;
            }

            $this->avgs[$block]['start'] = microtime(true);
            if (!isset($this->avgs[$block]['start-line'])) {
                $this->avgs[$block]['start-line'] = $this->getLineNumber();
            }

            if (!isset($this->avgs[$block]['max-time'])) {
                $this->avgs[$block]['max-time'] = 0;
            }
            if (!isset($this->avgs[$block]['min-time'])) {
                $this->avgs[$block]['min-time'] = 9999;
            }
        }
    }

    public function stopAvg($block)
    {
        if ($this->enabled) {
            if (!isset($this->avgs[$block])) {
                throw new \Exception('Average block '.$block.' has not been started!');
            }
        }

        $this->avgs[$block]['stop'] = microtime(true);
        if (!isset($this->avgs[$block]['stop-line'])) {
            $this->avgs[$block]['stop-line'] = $this->getLineNumber();
        }

        $this->avgs[$block]['count']++;

        $time = $this->avgs[$block]['stop'] - $this->avgs[$block]['start'];

        if ($this->avgs[$block]['max-time'] < $time) {
            $this->avgs[$block]['max-time'] = $time;
        }

        if ($this->avgs[$block]['min-time'] > $time) {
            $this->avgs[$block]['min-time'] = $time;
        }

        $this->avgs[$block]['total'] = $this->avgs[$block]['total'] + $time;
    }

    public function report($block = null)
    {
        if ($this->enabled) {
            $output = '';
            $output .= 'Timing report :' . PHP_EOL;

            if ($block === null) {
                foreach ($this->blocks as $key => $block) {
                    $output .= $this->printBlock($key);
                }

                $output .= PHP_EOL;
                $output .= 'Averages : ' . PHP_EOL;
                foreach ($this->avgs as $key => $block) {
                    $output .= $this->printBlock($key);
                } 
            
            } else {
                try {
                    $output .= $this->printBlock($block);
                } catch (\Exception $e) {
                    try {
                        $output .= $this->printAvgBlock($block);
                    } catch (\Exception $e) {
                        throw new \Exception('Block does not exist in either average or normal blocks');
                    }
                }
            }
            $output .= PHP_EOL;
            return $output;
        }
    }

    public function get($block)
    {
        if ($this->enabled) {
            if (!array_key_exists($block, $this->blocks)) {
                throw new \Exception('Block ' . $block . ' not defined.');
            }

            $this->finishBlock($block);
            return $this->blocks[$block];
        }
    }

    public function getAvg($block)
    {
        if ($this->enabled) {
            if (!array_key_exists($block, $this->blocks)) {
                throw new \Exception('Block ' . $block . ' not defined.');
            }

            $this->finishBlock($block);

            return $this->blocks[$block];
        }   
    }

    private function printBlock($block)
    {
        if (!array_key_exists($block, $this->blocks)) {
            throw new \Exception('Block' . $block . ' not defined.');
        }

        $this->finishBlock($block);

        $output = '';
        $output .= "    $block";
        $output .= " (".$this->blocks[$block]['start-line']."-".$this->blocks[$block]['stop-line'].")";
        $output .= ": ";
        $output .= round($this->blocks[$block]['total'], 4);
        $output .= ' seconds';
        $output .= PHP_EOL;

        return $output;
    }

    private function printAvgBlock() 
    {
        if (!array_key_exists($block, $this->avgs)) {
            throw new \Exception('Average block ' . $block . ' not defined.'); 
        }

        $this->finishAvgBlock($block);
        $output = '';
        $output .= "    $block";
        $output .= " [".$this->avgs[$block]['count']."]";
        $output .= " (".$this->avgs[$block]['start-line']."-".$this->avgs[$block]['stop-line'].")";
        $output .= ": ";
        $output .= round($this->avgs[$block]['avg'], 4);
        $output .= ' seconds';
        $output .= PHP_EOL;

        $output .= "        max time: ".round($this->avgs[$block]['max-time'], 4).PHP_EOL;
        $output .= "        min time: ".round($this->avgs[$block]['min-time'], 4).PHP_EOL;

        return $output;
    }

    private function finishBlock($block)
    {
        if (!array_key_exists($block, $this->blocks)) {
            throw new \Exception('Block '. $block . ' not defined.');
        }
        $this->blocks[$block]['total'] = $this->blocks[$block]['stop'] - $this->blocks[$block]['start'];

        return $this->blocks[$block];
    }

    private function finishAvgBlock($block)
    {
        if (!array_key_exists($block, $this->args)) {
            throw new \Exception('Average block '. $block . ' not defined.');
        }

        $this->avgs[$block]['avg'] = $this->avgs[$block]['total'] / $this->avgs[$block]['count'];

        return $this->avgs[$block];
    }

}






