<?php

namespace rely;

use rely\alg\Email;
use rely\init\File;
use rely\cache\File as Cache;
use rely\cache\Redis;
use rely\curl\Driver as Curl;
use rely\encry\Aes;
use rely\encry\Rsa;
use rely\encry\Custom;
use rely\init\Http;
use rely\init\Regex;
use rely\init\Config;
use rely\init\Dataswitch;
use rely\alg\Business;

class Facade
{
    /**
     * 容器绑定标识
     */
    private static $bind = [
        'dataswitch' => Dataswitch::class,
        'config' => Config::class,
        'curl'=>Curl::class,
        'rsa'=>Rsa::class,
        'encry'=>Custom::class,
        'aes'=>Aes::class,
        'regex'=>Regex::class,
        'redis'=>Redis::class,
        'cache'=>Cache::class,
        'file'=>File::class,
        'business_alg'=>Business::class,
        'email'=>Email::class,
        'http'=>Http::class
    ];

    /**
     * @param $class
     * @return mixed
     * @throws \Exception
     * 类库绑定
     */
    public static function bind($class)
    {
        if (empty(self::$bind[$class]) || !class_exists(self::$bind[$class])) throw new \Exception('Class does not exist');
        return self::$bind[$class];
    }
}