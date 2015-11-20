<?php

namespace ImReworks;

class Controller extends \Yaf_Controller_Abstract
{
    public $view;
    public $yaf;

    public function init()
    {
        $this->yaf = \ImReworks\Yaf::getInstance();
        $this->view = $this->getView();
    }

    public function assign($key, $value)
    {
        $this->view->assign($key, $value);
    }

    public function getLegalParam($tag, $legalType, $legalList = array(), $default = null)
    {
        $param = $this->getRequest()->get($tag, $default);

        if ($param !== null) {
            switch ($legalType) {
            case 'eid': { //encrypted id
                if ($param) {
                    return aesDecrypt(hex2bin($param), WAYGER_AES_KEY);

                } else {
                    return null;
                }

                break;
            }

            case 'id': {
                if (preg_match ('/^\d{1,20}$/', strval($param) )) {
                    return strval($param);
                }

                break;
            }

            case 'time': {
                return intval($param);
                break;
            }

            case 'int': {
                $val = intval($param);

                if (count($legalList)==2) {
                    if ($val>=$legalList[0] && $val<=$legalList[1]) {
                        return $val;
                    }

                } else {
                    return $val;
                }

                break;
            }

            case 'str': {
                $val = strval($param);

                if (count($legalList)==2) {
                    if (strlen($val)>=$legalList[0] && strlen($val)<=$legalList[1]) {
                        return $val;
                    }

                } else {
                    return $val;
                }

                break;
            }

            case 'trim_spec_str': {
                $val = trim(strval($param));

                if (!preg_match("/['.,:;*?~`!@#$%^&+=)(<>{}]|\]|\[|\/|\\\|\"|\|/",$val)) {
                    if (count($legalList)==2) {
                        if (strlen($val)>=$legalList[0] && strlen($val)<=$legalList[1]) {
                            return $val;
                        }

                    } else {
                        return $val;
                    }
                }

                break;
            }

            case 'enum': {
                if (in_array($param,$legalList)) {
                    return $param;
                }

                break;
            }

            case 'array': {
                if (count($legalList)>0) {
                    return explode($legalList[0],strval($param));

                } else {
                    if (empty($param)) {
                        return array();
                    }

                    return explode(',',strval($param));
                }

                break;
            }

            case 'json': {
                return json_decode(strval($param),true);
                break;
            }

            case 'raw': {
                return $param;
                break;
            }

            case 'email': {
                return \Swoole\Validate::regx('email', $param);
                break;
            }

            case 'tel': {
                return \Swoole\Validate::regx('tel', $param);
                break;
            }

            case 'phone': {
                return \Swoole\Validate::regx('phone', $param);
                break;
            }

            case 'domain': {
                return \Swoole\Validate::regx('domain', $param);
                break;
            }

            case 'date': {
                return \Swoole\Validate::regx('date', $param);
                break;
            }

            case 'datetime': {
                return \Swoole\Validate::regx('datetime', $param);
                break;
            }

            case 'time': {
                return \Swoole\Validate::regx('time', $param);
                break;
            }

            default:
                break;
            }
        }

        return false;
    }

    /**
     * 输出JSON字串
     * @param string $data
     * @param int    $code
     * @param string $message
     *
     * @return string
     */
    function json($data = '', $code = 0, $message = '')
    {
        $json = array('code' => $code, 'message' => $message, 'data' => $data);

        if (!empty($_REQUEST['jsonp'])) {
            $this->http->header('Content-type', 'application/x-javascript');
            return $_REQUEST['jsonp'] . "(" . json_encode($json) . ");";

        } else {
            $this->http->header('Content-type', 'application/json');
            return json_encode($json);
        }
    }
}
