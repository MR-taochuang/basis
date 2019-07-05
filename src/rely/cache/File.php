<?php

namespace rely\cache;

use rely\Init;
use rely\init\Config;

/**
 * Class File
 * @package rely\cache
 * @author Mr.taochuang <mr_taochuang@163.com>
 * @date 2019/7/4 15:17
 * 文件缓存
 */
class File
{
    public $options = [
        "dir" => "",
        "expire" => "",
        'data_compress' => false,
        'serialize' => ['easydev【{$date}】:']
    ];

    public function __construct($dir = 'runtime', int $expire = null, $data_compress = false)
    {
        $this->options['dir'] = __DIR__ . DIRECTORY_SEPARATOR . $dir . DIRECTORY_SEPARATOR;
        $this->options['expire'] = is_null($expire) ? 0 : $expire;
        $this->options['data_compress'] = $data_compress;

    }

    /**
     * @param $field /缓存名称
     * @param $data /缓存数据
     * @param null $expire 缓存日期 0永久有效
     * @return $this
     * 设置缓存
     */
    public function set($field, $data, $expire = null)
    {
        $filename = $this->options['dir'] . self::getCacheField($field) . '.php';
        $expire = $expire??$this->options['expire'];
        $dir = dirname($filename);
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        $data = serialize($data);
        if ($this->options['data_compress'] && function_exists('gzcompress')) {
            //数据压缩
            $data = gzcompress($data, 3);
        }
        $data = "<?php\n//" . sprintf('%012d', $expire) . "\n exit();?>\n" . $data;
        $result = file_put_contents($filename, $data);
        if ($result) {
            clearstatcache();
        }
        return $this;
    }

    public function get($field)
    {
        $filename = $this->options['dir'] . self::getCacheField($field) . '.php';
        if(!file_exists($filename)) return false;
        $content = file_get_contents($filename);
        if (false !== $content) {
            $expire = (int)substr($content, 8, 12);
            if (0 != $expire && time() > filemtime($filename) + $expire) {
                unlink($filename);
                return false;
            }
            $content = substr($content, 32);
            if ($this->options['data_compress'] && function_exists('gzcompress')) {
                $content = gzuncompress($content);
            }
            return unserialize($content);
        } else {
            return false;
        }
    }

    /**
     * @param $field
     * @return bool
     * 删除缓存
     */
    public function delete($field){
        $filename = $this->options['dir'] . self::getCacheField($field) . '.php';
        if(!file_exists($filename)) return false;
        unlink($filename);return true;
    }
    /**
     * @param $field
     * @return bool|mixed
     * 是否存在缓存
     */
    public function has($field){
        return self::get($field);
    }
    /**
     * @return bool
     * 清空缓存
     */
    public function clear()
    {
        $files = (array)glob($this->options['dir'] . DIRECTORY_SEPARATOR . '*');
        foreach ($files as $path) {
            if (is_dir($path)) {
                $matches = glob($path . DIRECTORY_SEPARATOR . '*.php');
                if (is_array($matches)) {
                    array_map('unlink', $matches);
                }
                rmdir($path);
            } else {
                unlink($path);
            }
        }

        return true;
    }

    /**
     * @param $name
     * @return string
     * 获取缓存存储文件名
     */
    public function getCacheField($name)
    {
        $name = hash('md5', $name);
        $name = substr($name, 0, 2) . DIRECTORY_SEPARATOR . substr($name, 2);
        return $name;
    }
}