<?php

namespace rely\init;
use rely\Init;


/**
 * Class Regex
 * @package rely\init
 * @author Mr.taochuang <mr_taochuang@163.com>
 * @date 2019/7/4 9:42
 * 正则管理类
 * @method mobile($data) 手机号验证
 * @method email($data) 邮箱验证
 * @method domain($data) 正则域名验证
 * @method date($data) 正则日期格式[yyyy-mm-dd]验证
 * @method date1($data) 正则日期格式[yyyy/mm/dd]验证
 * @method hanzi($data) 正则汉字验证
 * @method en_nu($data) 英文和数字验证
 * @method id_card($data) 正则国内身份证号验证
 * @method bank($data) 正则银行卡验证
 * @method id_tel_phone($data) 正则固定电话验证
 * @method url($data) 正则网址url验证
 * @method ip($data) 正则IP验证
 * @method postcode($data) 邮编验证
 * @method qq($data) 腾讯qq验证
 * @method weixin($data) weixin账号验证
 * @method html($data) html验证
 * @method en($data) 英文验证
 * @method zh_en_line($data) 中文英文下滑线验证
 * @method account($data) 账号合法验证
 * @method password($data) 密码验证 6-18之间
 * @method strong_password($data) 强密码验证 6-18验证
 * @method money($data) 金钱正则验证
 */
class Regex{

    /**
     * @var string
     * 正则驱动位置
     */
    private $cache_field='regex_data_class';

    const CHECK='check';
    /**
     * @var
     * 改动状态
     */
    private $state=false;
    /**
     * @var \rely\cache\File;
     * 缓存类
     */
    private $cache;
    public static $regex=[];

    /**
     * Regex constructor.
     * 初始化正则驱动
     */
    public function __construct(){
        $this->cache=Init::cache();
        self::$regex=$this->cache->has($this->cache_field)===false?$this->cache->set($this->cache_field,[])->get($this->cache_field):$this->cache->get($this->cache_field);

    }
    /**
     * @param $field
     * @param $rule
     * @param string $comments
     * @return $this
     * 正则追加
     */
    public function pull($field,$rule,$comments=''){
        self::$regex[$field]=['rule'=>$rule,'comments'=>!empty($comments)?$comments:'正则验证字段:'.$field];
        $this->field=$field;
        $this->comments=!empty($comments)?$comments:'正则验证字段:'.$field;
        $this->state=true;
        return $this;
    }
    /**
     * @param null $field
     * @return array|bool|mixed
     * 正则获取
     */
    public function get($field=null){
        if(is_null($field)) return self::$regex;
        return isset(self::$regex[$field])?self::$regex[$field]:false;
    }

    /**
     * @param $field
     * @param string $data
     * @return bool|int
     * 正则检验
     */
    public static function check($field,string $data){
        $rule=empty(self::$regex[$field]['rule'])?$field:self::$regex[$field]['rule'];
        return preg_match($rule,$data)?true:false;
    }
    /**
     * @param $field
     * @return bool
     * 删除正则
     */
    public function delete($field){
        if(isset(self::$regex[$field])){
            unset(self::$regex[$field]);
            $this->state=true;
            return true;
        }else{
            return false;
        }
    }
    /**
     * 清空正则
     */
    public function clear(){
        $this->state=true;
        self::$regex=[];
        return true;
    }
    /**
     * 正则数据追加
     */
    public function __destruct()
    {
        $this->cache->set($this->cache_field,self::$regex);
    }
    /**
     * @param $name
     * @param $arguments
     * @return mixed
     * 验证规则
     */
    public function __call($name, $arguments)
    {
        return call_user_func_array([$this,self::CHECK], [$name,$arguments[0]]);
    }
}