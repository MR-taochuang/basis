<?php

namespace rely;

/**
 * Class Init
 * @package rely
 * @author Mr.taochuang <mr_taochuang@163.com>
 * @date 2019/7/3 10:34
 *
 * 依赖包基础类
 *
 * @method \rely\init\Dataswitch dataswitch() static 数据处理类
 * @method \rely\init\Regex regex() static 正则管理类库
 * @method \rely\init\File file() static 文件处理
 * @method \rely\init\Config config($config=[]) static 配置类
 * @method \rely\curl\Driver curl($config=[]) static curl请求类
 * @method \rely\encry\Rsa rsa($config=[]) static Rsa加密
 * @method \rely\encry\Custom encry() static 自定义加密
 * @method \rely\encry\Aes aes($config=[]) static aes加密
 * @method \rely\cache\Redis redis($config=[]) static redis缓存机制
 * @method \rely\cache\File cache($config=[]) static 文件缓存机制
 * @method \rely\alg\Business business_alg() static 业务算法
 *
 */
class Init {

    public static function __callStatic($class, $arguments)
    {
        return (new \ReflectionClass(Facade::bind($class)))->newInstanceArgs($arguments);
    }

}