<?php
/**
 * @file Function.php
 * @brief  定义一些便捷方法函数
 * @author tiger, ji.xiaod@gmail.com
 * @version $Id$
 * @date 2014-07-10
 */

/**
 * @brief  加载应用config配置
 *
 * @params $name 配置文件前缀 $name.config.php
 * @params $key 需要的特定key配置
 *
 * @return
 */
function C($name, $key = null)
{
    $name = strtolower($name);
    if ( Yaf_Registry::has($name) ) {

        $cfg = Yaf_Registry::get($name);

        if (!empty($key)) {
            $key = strtoupper($key);
            return isset($cfg[$key]) ? $cfg[$key] : false;
        }
        return $cfg;
    }

    if ( is_file(CONF_PATH . $name . '.config.php') ) {
        $cfg = include CONF_PATH . $name . '.config.php';
        Yaf_Registry::set($name, $cfg);

        if ( !empty($key) ) {
            $key = strtoupper($key);
            return isset($cfg[$key]) ? $cfg[$key] : false;
        }
        return $cfg;
    }

    return false;
}

function CFG($key)
{
    $key = strtoupper($key);
    if ( Yaf_Registry::has($key) ) return Yaf_Registry::get($key);

    if ( is_file(APP_CONF_PATH . DS .'config.php') ) {
        $cfg = include APP_CONF_PATH . DS .'config.php';

        Yaf_Registry::set($key, $cfg[$key]);

        return $cfg[$key];
    }

    return false;
}

/**
* @brief  加载配置
*
* @return 
*/
function load_ext_cfg()
{
    //加载全局动态配置文件
    if( CFG('LOAD_EXT_CONF') ) {

        $configs = CFG('LOAD_EXT_CONF');
        if( is_string($configs) ) $configs = explode(',', $configs);

        foreach ( $configs as $key => $config ) {
            C($config);
        }
    }
}

/**
 * @brief
 *
 * @params $data  返回的数据信息
 * @params $code  API返回码，0表示成功，大于0表示不成功
 * @params $msg   提示信息
 *
 * @return
 */
function RST($data, $code = API_SUCCESS_EXEC, $msg = 'Api call success.')
{
    header('Content-type: application/json');
    echo json_encode(array('code' => $code * 1, 'msg' => $msg, 'time' => SYSTEM_TIME, 'data' => $data));
    exit;
}

function RST_PARAM_ERROR()
{
    header('Content-type: application/json');
    echo json_encode(array('code' => API_PARAM_ERROR, 'msg' => 'Param error.', 'time' => SYSTEM_TIME));
    exit;
}

/**
 * @brief  生成随机字符串
 *
 * @params $length
 * @params $numeric
 *
 * @return
 */
function random($length = 16, $numeric = 0)
{
    mt_srand((double) microtime() * 1000000);
    if ($numeric) {
        $hash = sprintf('%0' . $length . 'd', mt_rand(0, pow(10, $length) - 1));
    } else {
        $hash = '';
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
        $max = strlen($chars) - 1;
        for ($i = 0; $i < $length; $i++) {
            $hash .= $chars[mt_rand(0, $max)];
        }

    }

    return $hash;
}

/**
 * @brief  格式话时间戳to date
 *
 * @param $time
 *
 * @return
 */
function FT($time)
{
    return empty($time) ? '' : date('Y-m-d H:i:s', $time);
}

/**
 * @brief  只取时间戳的日期部分
 *
 * @param $timestamp
 *
 * @return 
 */
function GTD($timestamp)
{
    $date = date('Y-m-d', strtotime($timestamp));
    return is_date_str($date) ? $date : '';
}

function get_client_ip()
{

    if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), "unknown")) {
        $ip = getenv("HTTP_CLIENT_IP");
    } else if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown")) {
        $ip = getenv("HTTP_X_FORWARDED_FOR");
    } else if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown")) {
        $ip = getenv("REMOTE_ADDR");
    } else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown")) {
        $ip = $_SERVER['REMOTE_ADDR'];
    } else {

        $ip = "unknown";
    }

    return ($ip);
}

function array_sort_by_length($array)
{
    usort($array, function ($a, $b) {
        if ($a == $b) {
            return 0;
        }

        return (strlen($a) - strlen($b));
    });
    return $array;
}

/**
* @brief sort the keys of an array using an array of keynames, in order.
*
* @param $array
* @param $subkey
* @param $sort_ascending
*
* @return 
*/
function sksort(&$array, $subkey="id", $sort_ascending=false) 
{

    if (count($array))
        $temp_array[key($array)] = array_shift($array);

    foreach($array as $key => $val){
        $offset = 0;
        $found = false;
        foreach($temp_array as $tmp_key => $tmp_val)
        {
            if(!$found and strtolower($val[$subkey]) > strtolower($tmp_val[$subkey]))
            {
                $temp_array = array_merge(    
                    (array)array_slice($temp_array,0,$offset),
                    array($key => $val),
                    array_slice($temp_array,$offset)
                );
                $found = true;
            }
            $offset++;
        }
        if(!$found) $temp_array = array_merge($temp_array, array($key => $val));
    }

    if ($sort_ascending) $array = array_reverse($temp_array);

    else $array = $temp_array;
}

function word_number($w)
{
    return ord(strtolower($w)) - 97;
}
/*check word is not specify char*/
function is_word($word, $min = 4, $max = 10)
{
    return preg_match("/^[A_Za-z0-9_\x7f-\xff]{{$min},{$max}}$/i", $word);
}
/*check number is china of mobile number*/
function is_mobile($tel)
{
    return preg_match('/^1[358]{1}[0-9]{9}$/i', $tel);
}
/*check string is YYYY-mm-dd or YYY/mm/dd of farmat date*/
function is_date_str($date)
{
    return preg_match('/^[0-9]{4}-(((0[13578]|(10|12))-(0[1-9]|[1-2][0-9]|3[0-1]))|(02-(0[1-9]|[1-2][0-9]))|((0[469]|11)-(0[1-9]|[1-2][0-9]|30)))$/', $date);
}

/*check email address is vaild*/
function is_email($user_email)
{

    $chars = '/^([a-z0-9+_]|\\-|\\.)+@(([a-z0-9_]|\\-)+\\.)+[a-z]{2,6}$/i';
    if (strpos($user_email, '@') !== false && strpos($user_email, '.') !== false) {
        return preg_match($chars, $user_email) ? true : false;
    } else {
        return false;
    }
}

function curl_post($url, $data = array(), $timeout = 10, $header = "")
{
    return CurlPost($url, $data, $timeout, $header);
}

function curl_get($url, $data = array(), $timeout = 10, $header = "")
{
    return CurlGet($url, $data, $timeout, $header);
}

/**
 * @brief  Curl post
 *
 * @params $url
 * @params $data
 * @params $timeout
 * @params $header
 *
 * @return
 */
function CurlPost($url, $data = array(), $timeout = 10, $header = "")
{
    $ssl = substr($url, 0, 8) == 'https://' ? true : false;
    $post_string = http_build_query($data);

    $ch = curl_init();
    if ($ssl) {
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);// 对认证证书来源的检查
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);// 从证书中检查SSL加密算法是否存在
    }
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array($header));
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    $result = curl_exec($ch);
    curl_close($ch);

    return $result;
}

/**
 * @brief  Curl Get
 *
 * @params $url
 * @params $data
 * @params $timeout
 * @params $header
 *
 * @return
 */
function CurlGet($url, $data = array(), $timeout = 10, $header = "")
{
    $ssl = substr($url, 0, 8) == 'https://' ? true : false;

    $post_string = http_build_query($data);

    $ch = curl_init();
    if ($ssl) {
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);// 对认证证书来源的检查
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);// 从证书中检查SSL加密算法是否存在
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, array($header));
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_USERPWD, "thisuser:Gst43sB");
    curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}

function http_request($url, $post_string, $timeout = 10, $header = "")
{
    $ssl = substr($url, 0, 8) == 'https://' ? true : false;

    $ch = curl_init();
    if ($ssl) {
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);// 对认证证书来源的检查
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);// 从证书中检查SSL加密算法是否存在
    }
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array($header));
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}


function content_cut($string, $length, $etc = '')
{
    return contentCut($string, $length, $etc);
}
/**
 * @brief  截取字符串
 *
 * @params $string 待操作字符串
 * @params $length 截取长度
 * @params $etc 截取后，字符串后加，如 “...”
 *
 * @return
 */
function contentCut($string, $length, $etc = '')
{
    $result = '';
    $string = html_entity_decode(trim(strip_tags($string)), ENT_QUOTES, 'UTF-8');
    $strlen = strlen($string);
    for ($i = 0;(($i < $strlen) && ($length > 0)); $i++) {

        $number = strpos(str_pad(decbin(ord(substr($string, $i, 1))), 8, '0', STR_PAD_LEFT), '0');
        if ($number) {
            if ($length < 1.0) {
                break;
            }

            $result .= substr($string, $i, $number);
            $length -= 1.0;
            $i += $number - 1;
        } else {
            $result .= substr($string, $i, 1);
            $length -= 0.5;
        }
    }
    $result = htmlspecialchars($result, ENT_QUOTES, 'UTF-8');
    if ($i < $strlen) {
        $result .= $etc;
    }
    return $result;
}

/**
* @brief  浏览器友好的变量输出
*
* @params $var
* @params $echo
* @params $label
* @params $strict
*
* @return 
*/
function dump($var, $echo=true, $label=null, $strict=true) {
    $label = ($label === null) ? '' : rtrim($label) . ' ';
    if (!$strict) {
        if (ini_get('html_errors')) {
            $output = print_r($var, true);
            $output = '<pre>' . $label . htmlspecialchars($output, ENT_QUOTES) . '</pre>';
        } else {
            $output = $label . print_r($var, true);
        }
    } else {
        ob_start();
        var_dump($var);
        $output = ob_get_clean();
        if (!extension_loaded('xdebug')) {
            $output = preg_replace("/\]\=\>\n(\s+)/m", '] => ', $output);
            $output = '<pre>' . $label . htmlspecialchars($output, ENT_QUOTES) . '</pre>';
        }
    }
    if ($echo) {
        echo($output);
        return null;
    }else
        return $output;
}

/**
 * @brief  检查数组中是否存在某个值，不区分大小写
 *
 * @params $needle
 * @params $haystack
 *
 * @return
 */
function in_arrayi($needle, $haystack)
{
    if (empty($haystack)) return false;
    return in_array(strtolower($needle), array_map('strtolower', $haystack));
}

/**
 * java time to php time
 *
 * @param int | str $time ,length 13
 */
function phptime($time)
{
    return substr(strval($time), 0, -3);
}

/**
 * php time to java time
 *
 * @param int | str $time ,length 13
 */
function javatime($time)
{
    return $time * 1000;
}

function is_empty_string($str)
{
    return isEmptyString($str);
}

function isEmptyString ($str)
{
    settype($str, 'string');
    return (is_null($str) || ((is_string($str)&& (strlen($str) == 0))));
}

function run($command)
{
    return run_command(BIN_CMD ." ". $command); 
}

function run_command($command) {
    $descriptorspec = array(
            1 => array('pipe', 'w'),
            2 => array('pipe', 'w'),
            );  
    $pipes = array();
    $resource = proc_open($command, $descriptorspec, $pipes, BIN_PATH);

    $stdout = stream_get_contents($pipes[1]);
    $stderr = stream_get_contents($pipes[2]);
    foreach ($pipes as $pipe) {
        fclose($pipe);
    }   

    $status = trim(proc_close($resource));
    if ($status) throw new Exception($stderr);

    return $stdout;
}

function unique_multidim_array($array, $key){
    $temp_array = array();
    $i = 0;
    $key_array = array();

    foreach($array as $val){
        if(!in_array($val[$key],$key_array)){
            $key_array[$i] = $val[$key];
            $temp_array[$i] = $val;
        }
        $i++;
    }
    return $temp_array;
}
