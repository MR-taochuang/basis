<?php

namespace rely\init;

/**
 * Class Config
 * @package rely\curl
 * @author Mr.taochuang <mr_taochuang@163.com>
 * @date 2019/7/3 11:25
 * 配置类
 */
class Config implements \ArrayAccess
{

    /**
     * @var array
     * 配置信息
     */
    private static $config = [];

    /**
     * @param $config
     * 配置初始化
     */
    public function __construct($config=[])
    {
        self::$config = array_merge(self::$config, $config);
    }

    /**
     * @param string|null $field
     * @return array
     * 获取config配置参数
     */
    public function get(string $field = null)
    {
        if (is_null($field)) return self::$config;
        return isset(self::$config[$field]) ? self::$config[$field] : null;
    }

    /**
     * @param string $field
     * @param $value
     * @return $this
     * 设置config参数
     */
    public function set(string $field, $value)
    {
        self::$config[$field] = $value;
        return $this;
    }

    /**
     * @param string $field
     * @return bool
     * 判断config配置是否存在
     */
    public function has(string $field):bool
    {
        return !is_null($this->get($field));
    }
    /**
     * 设置配置变量
     * @access public
     * @param string $field  参数名
     * @param mixed  $value 值
     */
    public function __set(string $field, $value): void
    {
        $this->set($field, $value);
    }

    /**
     * 获取配置变量
     * @access public
     * @param string $field 参数名
     * @return mixed
     */
    public function __get(string $field)
    {
        return $this->get($field);
    }
    /**
     * @param mixed $field
     * @param mixed $value
     * ArrayAccess
     */
    public function offsetSet($field, $value): void
    {
        $this->set($field, $value);
    }

    public function offsetExists($field): bool
    {
        return $this->has($field);
    }

    public function offsetUnset($field)
    {
        throw new \Exception('not support: unset');
    }

    public function offsetGet($field)
    {
        return $this->get($field);
    }
    /**
     * ArrayAccess
     */

}