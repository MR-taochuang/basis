<?php

namespace rely\cache;

use rely\init\Config;

/**
 * Class Redis
 * @package rely\cache
 * @author Mr.taochuang <mr_taochuang@163.com>
 * @date 2019/7/4 14:07
 * redis缓存
 */
class Redis extends Config{

    /**
     * @var \Redis;
     * redis实例类
     */
    protected static $redis;
    private static $table = ''; //数据表名
    private static $where = []; //搜索指定id的数据
    /**
     * @param string $host
     * @param string $port
     * @param string $database
     * @param string $prefix
     * @return $this
     * redis连接
     */
    public function connection($host = '127.0.0.1', $port = '6379', $database= '1', $prefix = 'db'){
        self::set('host',$host);
        self::set('port',$port);
        self::set('database',$database);
        self::set('prefix',$prefix);
        self::$redis = new \Redis();
        self::$redis->connect($host, $port);
        self::$redis->select($database);
        return $this;
    }
    /**
     * @param $table
     * @return $this
     * 表名
     */
    public function name($table)
    {
        self::$table = $table;
        return $this;
    }

    /**
     * @param $param
     * @return $this
     * 获取指定id的数据
     */
    public function where($param)
    {
        if (is_array($param)) {
            self::$where = $param;
        } else {
            self::$where = explode(',', $param);
        }
        return $this;
    }

    /**
     * @param $data
     * @return bool|int|string
     * @throws \Exception
     * 添加数据
     */
    public function insert(Array $data)
    {
        $id = self::$redis->get(self::$table . '_id')??0;
        $id=$id+1;
        self::$redis->set(self::$table . '_id', $id);
        if (empty(self::$table)) throw new \Exception('请配置数据表');

        self::$redis->hMset(self::$table . '_' . $id, $data);
        self::$redis->zAdd(self::get('prefix') . '_' . self::$table, $id, $id);
        return $id;
    }

    /**
     * @return array
     * 查询所有数据
     */
    public function select()
    {
        $data = [];
        if (empty(self::$where)) {
            $count = self::count();
            $result = self::$redis->ZRANGE(self::get('prefix') . '_' . self::$table, 0, $count);
            foreach ($result as $id) {
                $data[$id] = self::$redis->hGetAll(self::$table . '_' . $id);
            }
        } else {
            foreach (self::$where as $id) {
                $data[$id] = self::$redis->hMGet(self::$table . '_' . $id, self::$where);
            }
        }

        return $data;
    }
    public function ids($position,$count){
        return self::$redis->ZRANGE(self::get('prefix') . '_' . self::$table, $position, $count);
    }
    /**
     * @param int $page_num
     * @param null $page
     * @return array
     * 分页取出redis里的值
     */
    public function paginate($page_num = 15, $page = null)
    {
        if (is_null($page)) $page =$_REQUEST['page'];
        empty($page) ? $page = 1 : true;
        $start = ($page - 1) * $page_num;
        $end = ($start + $page_num) - 1;
        $count = self::count();
        $result = self::$redis->ZRANGE(self::get('prefix') . '_' . self::$table, $start, $end);
        $last_page = ceil($count / $page_num);
        $data = array();
        foreach ($result as $id) {
            $data[$id] = self::$redis->hGetAll(self::$table . '_' . $id);
            if (count(self::$where) > 0) {
                $pageList[] = self::$redis->hMGet(self::$table . '_' . $id, self::$where);
            } else {
                $pageList[] = self::$redis->hGetAll(self::$table . '_' . $id);
            }
        }
        return ['total' => $count, 'per_page' => $page_num, 'current_page' => $page, 'last_page' => $last_page, 'data' => $data];
    }

    /**
     * @param $data
     * @param $id
     * @return mixed
     * 修改指定数据
     */
    public function update($data, $id)
    {
        self::$redis->del(self::$table . '_' . $id);
        self::$redis->hMset(self::$table . '_' . $id, $data);
        return $id;
    }

    /**
     * @param $id
     * @return bool
     * 删除redis缓存数据
     */
    public function delete($id)
    {
        if (!is_array($id)) $id = explode(',', $id);
        foreach ($id as $maps) {
            self::$redis->del(self::$table . '_' . $maps);
            self::$redis->zRem(self::get('prefix') . '_' . self::$table, $maps);
        }
        return true;
    }

    /**
     * @param string $field
     * @return array
     * 获取所有的redis key
     */
    public function keys($field = '*')
    {
        return self::$redis->keys($field);
    }

    /**
     * @return int
     * 获取数据总条数
     */
    public function count()
    {
        return self::$redis->zCard(self::get('prefix') . '_' . self::$table);
    }

    /**
     * @return \Redis
     * 初始redis;
     */
    public static function instance()
    {
        return self::$redis;
    }

    /*
     * 清空Db表
     * @return bool
     */
    public function clear()
    {
        self::$redis->flushDB();
        return true;
    }

    /**
     * 清空所有表数据
     */
    public function truncate()
    {
        self::$redis->flushAll();
        return true;
    }

}