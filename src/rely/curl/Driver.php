<?php

namespace rely\curl;

use rely\Facade;
use rely\Init;


/**
 * Class Driver
 * @package rely\curl
 * @author Mr.taochuang <mr_taochuang@163.com>
 * @date 2019/7/3 11:14
 * curl驱动类
 */
class Driver
{

    /**
     * @var object
     * curl对象
     */
    private static $curl = null;

    /**
     * @var \rely\init\Config;
     * setopt 配置参数
     */
    private static $config;
    private static $init = [
        'timeout' => 60,
        'verifyhost' => 0,
        'sslcerttype' => 'PEM',
        'sslkeytype' => 'PEM',
        'verifypeer' => false
    ];
    /**
     * @var array
     * 设置的setopt
     */
    public static $setopt = [];
    /**
     * @var null
     * 请求url
     */
    public static $url = null;

    /**
     * @var int
     * 错误码
     * 1 成功
     * 2 无请求url
     */
    public $code = 1;

    /**
     * @var string
     * 错误信息
     */
    public $message = '';

    /**
     * @var
     * 响应状态
     */
    private $status;

    /**
     * @var
     * 响应数据
     */
    private $response;

    /**
     * @var
     * 处理后数据
     */
    private $data;

    /**
     * @var /网络缓存
     */
    public static $cache_curl;

    /**
     * Driver constructor.
     * @param array $config
     * 初始化curl
     */
    public function __construct($config = [])
    {
        self::$curl = curl_init();
        self::$config = (new \ReflectionClass(Facade::bind('config')))->newInstanceArgs([array_merge(self::$init, $config)]);
    }

    /**
     * @param null $url url地址
     * 注册url地址
     */
    public function instance($url = null)
    {
        if (is_null($url)) {
            $this->code = 2;
            $this->message = '请填写请求地址http://xxxxxxx 或 https://xxxxxx';
        } else {
            self::$url = $url;
        }
        return $this;
    }

    /**
     * @param array $param
     * @return int
     * get 请求
     */
    public function get($param = [])
    {
        if (!empty($param)) self::$url .= (stripos(self::$url, '?') !== false ? '&' : '?') . http_build_query(Init::dataswitch()->toArray($param));
        return self::request();
    }

    /**
     * @param $param /请求参数
     * @param $type /请求类型 array json xml
     * @return int
     * post请求
     */
    public function post($param, $type = 'array')
    {
        $dataswitch = Init::dataswitch();
        if ($type == 'array') $param=http_build_query($dataswitch->toArray($param));
        if ($type == 'json') $param=$dataswitch->toJson($param);
        if ($type == 'xml') $param=$dataswitch->toXml($param);
        self::setopt([CURLOPT_POST, true], [CURLOPT_POSTFIELDS, $param]);
        return self::request();
    }

    /**
     * @param $param /请求参数
     * @return int
     * 文件请求
     */
    public function file($param)
    {
        if (!is_array($param)) $param = Init::dataswitch()->toArray($param);
        $build = true;
        foreach ($param as $key => $value) if (is_object($value) && $value instanceof \CURLFile) {
            $build = false;
        } elseif (is_object($value) && isset($value->datatype) && $value->datatype === 'MY_CURL_FILE') {
            $build = false;
            $mycurl = new File((array)$value);
            $param[$key] = $mycurl->get();
            array_push(self::$cache_curl, $mycurl->tempname);
        } elseif (is_string($value) && class_exists('CURLFile', false) && stripos($value, '@') === 0) {
            if (($filename = realpath(trim($value, '@'))) && file_exists($filename)) {
                $build = false;
                $param[$key] = File::createCurlFile($filename);
            }
        }
        self::setopt([CURLOPT_POST, true], [CURLOPT_POSTFIELDS, $build ? http_build_query($param) : $param]);
        return self::request();
    }

    /**
     * @return int
     * 发送请求
     */
    private function request()
    {
        self::setopt([CURLOPT_URL, !empty(self::$config->get('url')) ? self::$config->get('url') : self::$url],
            [CURLOPT_TIMEOUT, self::$config->get('timeout')],
            [CURLOPT_HEADER, false],
            [CURLOPT_RETURNTRANSFER, true],
            [CURLOPT_SSL_VERIFYPEER, self::$config->get('verifypeer')],
            [CURLOPT_SSL_VERIFYHOST, self::$config->get('verifyhost')]);
        foreach (self::$setopt as $maps) {
            curl_setopt(self::$curl, $maps[0], $maps[1]);
        }
        list($this->response, $this->status) = [curl_exec(self::$curl), curl_getinfo(self::$curl)];
        $this->data = (intval($this->status["http_code"]) === 200) ? $this->response : curl_errno(self::$curl);
        curl_close(self::$curl);
        return $this->data;
    }

    /**
     * 设置代理
     * @param $Host /ip
     * @param $Port /端口
     */
    public function proxy($Host, $Port)
    {
        self::setopt([CURLOPT_PROXY, $Host], [CURLOPT_PROXYPORT, $Port]);
        return $this;
    }

    /**
     * 请求ssl配置
     * @param $ssl_cer
     * @param $ssl_key
     */
    public function ssl($ssl_cer, $ssl_key)
    {
        self::setopt([CURLOPT_SSLCERTTYPE, self::$config->get('sslcerttype')], [CURLOPT_SSLCERT, $ssl_cer], [CURLOPT_SSLKEYTYPE, self::$config->get('sslkeytype')], [CURLOPT_SSLKEY, $ssl_key]);
        return $this;
    }

    /**
     * 设置HTTP用户代理标头
     * @param $agent
     * @return $this
     */
    public function useragent($agent)
    {
        self::setopt([CURLOPT_USERAGENT, $agent]);
        return $this;
    }

    /**
     * 设置setpot
     */
    public function setopt()
    {
        foreach (func_get_args() as $maps) {
            self::$setopt[$maps[0]] = $maps;
        }
    }

}