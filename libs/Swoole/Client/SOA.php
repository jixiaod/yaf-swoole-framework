<?php
namespace Swoole\Client;
use Swoole\Protocol\SOAServer;

class SOA
{
    /**
     * Server的实例列表
     * @var array
     */
    protected $servers = array();

    /**
     * 当前选择的Server
     * @var int
     */
    protected $currentServerId;

    protected $env = array();

    protected $wait_list = array();
    protected $timeout = 0.5;
    protected $packet_maxlen = 2097152;   //最大不超过2M的数据包

    /**
     * 启用长连接
     * @var bool
     */
    protected $keep_connection = false;

    const OK = 0;
    public $re_connect      = true;    //重新connect

    protected static $_instances = array();

    function __construct($id = null)
    {
        $key = empty($id) ? 'default' : $id;
        self::$_instances[$key] = $this;
    }

    /**
     * 获取SOA服务实例
     * @param $id
     * @return SOA
     */
    static function getInstance($id = null)
    {
        $key = empty($id) ? 'default' : $id;
        if (empty(self::$_instances[$key]))
        {
            $object = new static($id);
        }
        else
        {
            $object = self::$_instances[$key];
        }
        return $object;
    }

    protected function beforeRequest($retObj)
    {

    }

    protected function afterRequest($retObj)
    {

    }

    /**
     * 生成请求串号
     * @return int
     */
    static function getRequestId()
    {
        list($us) = explode(' ', microtime());
        return intval(strval($us * 1000 * 1000) . rand(100000, 999999));
    }

    /**
     * 发送请求
     * @param $type
     * @param $send
     * @param SOA_result $retObj
     * @return bool
     */
    protected function request($send, $retObj)
    {
        $retObj->send = $send;

        $this->beforeRequest($retObj);

        $ret = false;
        $socket = null;
        $svr = null;

        //循环连接
        while (count($this->servers) > 0)
        {
            $svr = $this->getServer();
            $socket = new TCP;
            $socket->try_reconnect = false;
            $ret = $socket->connect($svr['host'], $svr['port'], $this->timeout);
            //连接被拒绝，证明服务器已经挂了
            //TODO 如果连接失败，需要上报机器存活状态
            if ($ret === false and $socket->errCode == 111)
            {
                $this->onConnectServerFailed($svr);
            }
            else
            {
                break;
            }
        }

        $retObj->socket = $socket;
        $retObj->server_host = $svr['host'];
        $retObj->server_port = $svr['port'];

        //使用SOCKET的编号作为ID
        $retObj->id = (int)$socket->get_socket();
        if ($ret === false)
        {
            $retObj->code = SOA_Result::ERR_CONNECT;
            unset($retObj->socket);
            return false;
        }
        //请求串号
        $retObj->requestId = self::getRequestId();
        //发送失败了
        if ($retObj->socket->send(SOAServer::encode($retObj->send, SOAServer::DECODE_PHP, 0, $retObj->requestId)) === false)
        {
            $retObj->code = SOA_Result::ERR_SEND;
            unset($retObj->socket);
            return false;
        }
        //加入wait_list
        $this->wait_list[$retObj->id] = $retObj;
        return true;
    }

    /**
     * 设置环境变量
     * @return array
     */
    public function getEnv()
    {
        return $this->env;
    }

    /**
     * 获取环境变量
     * @param array $env
     */
    public function setEnv($env)
    {
        $this->env = $env;
    }

    /**
     * 设置一项环境变量
     * @param $k
     * @param $v
     */
    public function putEnv($k, $v)
    {
        $this->env[$k] = $v;
    }

    /**
     * 完成请求
     * @param $retData
     * @param $retObj
     */
    protected function finish($retData, $retObj)
    {
        //解包失败了
        if ($retData === false)
        {
            $retObj->code = SOA_Result::ERR_UNPACK;
        }
        //调用成功
        elseif ($retData['errno'] === self::OK)
        {
            $retObj->code = self::OK;
            $retObj->data = $retData['data'];
        }
        //服务器返回失败
        else
        {
            $retObj->code = $retData['errno'];
            $retObj->data = null;
        }
        unset($this->wait_list[$retObj->id]);
        //执行after钩子函数
        $this->afterRequest($retObj);
        //执行回调函数
        if ($retObj->callback)
        {
            call_user_func($retObj->callback, $retObj);
        }
    }

    /**
     * 添加服务器
     * @param array $servers
     */
    function addServers(array $servers)
    {
        if (isset($servers['host']))
        {
            $this->servers[] = $servers;
        }
        else
        {
            $this->servers = array_merge($this->servers, $servers);
        }
    }

    /**
     * 从配置中取出一个服务器配置
     * @return array
     * @throws \Exception
     */
    function getServer()
    {
        if (empty($this->servers))
        {
            throw new \Exception("servers config empty.");
        }
        //随机选择1个Server
        $this->currentServerId = array_rand($this->servers);
        $_svr = $this->servers[$this->currentServerId];
        $svr = array('host' => '', 'port' => 0);
        list($svr['host'], $svr['port']) = explode(':', $_svr, 2);
        return $svr;
    }

    /**
     * 连接服务器失败了
     * @param $svr
     */
    function onConnectServerFailed($svr)
    {
        //从Server列表中移除
        unset($this->servers[$this->currentServerId]);
    }

    /**
     * RPC调用
     *
     * @param $function
     * @param $params
     * @param $callback
     * @return SOA_Result
     */
    function task($function, $params = array(), $callback = null)
    {
        $retObj = new SOA_Result($this);
        $send = array('call' => $function, 'params' => $params);
        if (count($this->env) > 0)
        {
            //调用端环境变量
            $send['env'] = $this->env;
        }
        $this->request($send, $retObj);
        $retObj->callback = $callback;
        return $retObj;
    }

    /**
     * 并发请求
     * @param float $timeout
     * @return int
     */
    function wait($timeout = 0.5)
    {
        $st = microtime(true);
        $t_sec = (int)$timeout;
        $t_usec = (int)(($timeout - $t_sec) * 1000 * 1000);
        $buffer = $header = array();
        $success_num = 0;

        while (true)
        {
            $write = $error = $read = array();
            if(empty($this->wait_list))
            {
                break;
            }
            foreach($this->wait_list as $obj)
            {
                if($obj->socket !== null)
                {
                    $read[] = $obj->socket->get_socket();
                }
            }
            if (empty($read))
            {
                break;
            }
            $n = socket_select($read, $write, $error, $t_sec, $t_usec);
            if($n > 0)
            {
                //可读
                foreach($read as $sock)
                {
                    $id = (int)$sock;

                    /**
                     * @var $retObj SOA_Result
                     */
                    $retObj = $this->wait_list[$id];
                    $data = $retObj->socket->recv();
                    //socket被关闭了
                    if (empty($data))
                    {
                        $retObj->code = SOA_Result::ERR_CLOSED;
                        unset($this->wait_list[$id], $retObj->socket);
                        continue;
                    }
                    //一个新的请求，缓存区中没有数据
                    if (!isset($buffer[$id]))
                    {
                        //这里仅使用了length和type，uid,serid未使用
                        $header[$id] = unpack(SOAServer::HEADER_STRUCT, substr($data, 0, SOAServer::HEADER_SIZE));
                        //错误的包头
                        if ($header[$id] === false or $header[$id]['length'] <= 0)
                        {
                            $retObj->code = SOA_Result::ERR_HEADER;
                            unset($this->wait_list[$id]);
                            continue;
                        }
                        //错误的长度值
                        elseif ($header[$id]['length'] > $this->packet_maxlen)
                        {
                            $retObj->code = SOA_Result::ERR_TOOBIG;
                            unset($this->wait_list[$id]);
                            continue;
                        }
                        $buffer[$id] = substr($data, SOAServer::HEADER_SIZE);
                    }
                    else
                    {
                        $buffer[$id] .= $data;
                    }
                    //达到规定的长度
                    if (strlen($buffer[$id]) == $header[$id]['length'])
                    {
                        $retObj->responseId = $header[$id]['serid'];
                        //成功处理
                        $this->finish(SOAServer::decode($buffer[$id], $header[$id]['type']), $retObj);
                        $success_num++;
                    }
                    //继续等待数据
                }
            }
            //发生超时
            if ((microtime(true) - $st) > $timeout)
            {
                foreach($this->wait_list as $obj)
                {
                    //TODO 如果请求超时了，需要上报服务器负载
                    $obj->code = ($obj->socket->connected) ? SOA_Result::ERR_TIMEOUT : SOA_Result::ERR_CONNECT;
                    //执行after钩子函数
                    $this->afterRequest($obj);
                }
                //清空当前列表
                $this->wait_list = array();
                return $success_num;
            }
        }
        //未发生任何超时
        $this->wait_list = array();
        return $success_num;
    }

}

/**
 * SOA服务请求结果对象
 * Class SOA_Result
 * @package Swoole\Client
 */
class SOA_Result
{
    public $id;
    public $code = self::ERR_NO_READY;
    public $msg;
    public $data = null;
    public $send;  //要发送的数据
    public $type;

    /**
     * 请求串号
     */
    public $requestId;

    /**
     * 响应串号
     */
    public $responseId;

    /**
     * 回调函数
     * @var mixed
     */
    public $callback;

    /**
     * @var \Swoole\Client\TCP
     */
    public $socket = null;

    /**
     * SOA服务器的IP地址
     * @var string
     */
    public $server_host;

    /**
     * SOA服务器的端口
     * @var int
     */
    public $server_port;

    /**
     * @var SOA
     */
    protected $soa_client;

    const ERR_NO_READY   = 8001; //未就绪
    const ERR_CONNECT    = 8002; //连接服务器失败
    const ERR_TIMEOUT    = 8003; //服务器端超时
    const ERR_SEND       = 8004; //发送失败
    const ERR_SERVER     = 8005; //server返回了错误码
    const ERR_UNPACK     = 8006; //解包失败了

    const ERR_HEADER     = 8007; //错误的协议头
    const ERR_TOOBIG     = 8008; //超过最大允许的长度
    const ERR_CLOSED     = 8009; //连接被关闭

    function __construct($soa_client)
    {
        $this->soa_client = $soa_client;
    }

    function getResult($timeout = 0.5)
    {
        if ($this->code == self::ERR_NO_READY)
        {
            $this->soa_client->wait($timeout);
        }
        return $this->data;
    }
}
