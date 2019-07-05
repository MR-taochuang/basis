<?php

namespace rely\init;

use rely\Init;
use rely\init\Config;

/**
 * Class File
 * @package init
 * @author Mr.taochuang <mr_taochuang@163.com>
 * @date 2019/7/4 16:48
 * 文件处理
 */
class File extends Config{


    /**
     * @param $dir
     * @param array $filter
     * @return array
     * 获取目录下目录
     */
    public function getDir($dir, $filter = [])
    {
        $res = scandir($dir);
        foreach ($res as $k => $dir_path) {
            if (is_file($dir . DIRECTORY_SEPARATOR . $dir_path)) {
                unset($res[$k]);
            } else {
                if (in_array($dir_path, $filter)) {
                    unset($res[$k]);
                }
            }
        }
        rsort($res);
        return $res;
    }

    /**
     * @param $dir /目录
     * @param string $ext 指定文件
     * @return array
     * 获取目录下文件
     */
    public function getDirFile($dir, $ext = 'php',$del_ext=false)
    {
        $dir=self::getDir($dir);
        $res = scandir($dir);
        foreach ($res as $k => $dir_path) {
            if (!is_file($dir . DIRECTORY_SEPARATOR . $dir_path)) {
                unset($res[$k]);
            } else {
                if (!empty($ext) && pathinfo($dir_path, 4) !== $ext) {
                    unset($res[$k]);
                }
                if($del_ext){
                    $res[$k]=str_replace('.'.pathinfo($dir_path, 4),'',$dir_path);
                }
            }

        }
        rsort($res);
        return $res;
    }

    /**
     * 根据文件后缀获取文件类型
     * @param string|array $ext 文件后缀
     * @param array $mine 文件后缀MINE信息
     * @return string
     */
    public static function getExtMine($ext, $mine = [])
    {
        $mines = self::getMines();
        foreach (is_string($ext) ? explode(',', $ext) : $ext as $e) {
            $mine[] = isset($mines[strtolower($e)]) ? $mines[strtolower($e)] : 'application/octet-stream';
        }
        return join(',', array_unique($mine));
    }

    /**
     * 获取所有文件扩展的类型
     * @return array
     */
    public static function getMines()
    {

        $mines =Init::cache()->get ('all_ext_mine');
        if (empty($mines)) {
            $content = file_get_contents('http://svn.apache.org/repos/asf/httpd/httpd/trunk/docs/conf/mime.types');
            preg_match_all('#^([^\s]{2,}?)\s+(.+?)$#ism', $content, $matches, PREG_SET_ORDER);
            foreach ($matches as $match) foreach (explode(" ", $match[2]) as $ext) $mines[$ext] = $match[1];
            Init::cache()->set('all_ext_mine', $mines);
        }
        return $mines;
    }

    /**
     * @param $name
     * @param $content
     * @return string
     * @throws \Exception
     * 文件写入
     */
    public function pushFile($name, $content)
    {
        $file = Init::cache()->getCacheField($name);
        if (!file_put_contents($file, $content)) {
            throw new \Exception('local file write error.', '0');
        }
        return $file;
    }

}