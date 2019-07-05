<?php

namespace rely\curl;
use rely\Init;

/**
 * Class File
 * @package rely\curl
 * @author Mr.taochuang <mr_taochuang@163.com>
 * @date 2019/7/3 11:15
 * curl 文件上传类
 */
class File extends \stdClass{

    /**
     * 当前数据类型
     * @var string
     */
    public $datatype = 'MY_CURL_FILE';

    /**
     * MyCurlFile constructor.
     * @param string|array $filename
     * @param string $mimetype
     * @param string $postname
     */
    public function __construct($filename, $mimetype = '', $postname = '')
    {
        if (is_array($filename)) {
            foreach ($filename as $k => $v) $this->{$k} = $v;
        } else {
            $this->mimetype = $mimetype;
            $this->postname = $postname;
            $this->extension = pathinfo($filename, PATHINFO_EXTENSION);
            if (empty($this->extension)) $this->extension = 'tmp';
            if (empty($this->mimetype)) $this->mimetype = Init::file()->getExtMine($this->extension);
            if (empty($this->postname)) $this->postname = pathinfo($filename, PATHINFO_BASENAME);
            $this->content = base64_encode(file_get_contents($filename));
            $this->tempname = md5($this->content) . ".{$this->extension}";
        }
    }

    /**
     * 获取文件信息
     * @return \CURLFile|string
     */
    public function get()
    {
        $this->filename = Init::file()->pushFile($this->tempname, base64_decode($this->content));
        if (class_exists('CURLFile')) {
            return new \CURLFile($this->filename, $this->mimetype, $this->postname);
        }
        return "@{$this->tempname};filename={$this->postname};type={$this->mimetype}";
    }
    /**
     * 创建CURL文件对象
     * @param $filename
     * @param string $mimetype
     * @param string $postname
     * @return \CURLFile|string
     */
    public static function createCurlFile($filename, $mimetype = null, $postname = null)
    {
        if (is_string($filename) && file_exists($filename)) {
            if (is_null($postname)) $postname = basename($filename);
            if (is_null($mimetype)) $mimetype =  Init::file()->getExtMine(pathinfo($filename, 4));
            if (function_exists('curl_file_create')) {
                return curl_file_create($filename, $mimetype, $postname);
            }
            return "@{$filename};filename={$postname};type={$mimetype}";
        }
        return $filename;
    }

}