<?php

namespace rely\alg;

use rely\Init;
use rely\init\Config;

/**
 * Class Business
 * @package rely\alg
 * @author Mr.taochuang <mr_taochuang@163.com>
 * @date 2019/7/5 10:41
 * 业务算法
 */
class Business extends Config
{

    /**
     * @param $start /起始随机
     * @param $end /结束随机
     * @param $luck /运气值 可设置运气值 0-9 超出未无运气
     * @return string
     * 计算幸运儿
     */
    public function luck($start, $end, $luck = 1)
    {
        list($rand, $month, $week, $day, $hour, $min, $second) = [mt_rand($start, $end), date('m'), date('w'), date('d'), date('h'), date('i'), date('s')];
        $key = ceil(ceil($rand * ($week + $day + $month) * ($second + $rand) / ($month + $week)) * ceil($day + $hour + $month) / ceil($min * $second + $month));
        $luck_num = substr(ceil($key / rand(2, 9)), -1);
        return $luck_num == $luck ? 'luck' : 'unluck';
    }

    /**
     * @param int $type 1 取出不放回 2取出放回
     * @param int $num 取出后放回的次数
     * @return bool|int
     * 执行抽奖
     */
    public function query_draw($type = 1, $num = 0)
    {
        if (self::get('probability') == 0) return false;
        $cache = Init::cache();
        //拉取奖池数据
        $draw_data = $cache->get(self::get('draw_name'));
        //获取奖池总奖数
        $total = 1 / self::get('probability');
        //随机抽取
        $key = rand(1, $total);
        if (!in_array($key, self::get('prize'))) return false;
        if ($type == 1 && !in_array($key, $draw_data)) return false;
        if ($type == 2 && $num > 0 && array_count_values($draw_data)[$key] >= $num) return false;
        array_push($draw_data, $key);
        $cache->set(self::get('draw_name'), $draw_data);
        return $key;
    }

    public function get_draw_data($name = 'draw')
    {
        return Init::cache()->get($name);
    }

    /**
     * @param $name /奖池名称
     * @param $probability /概率
     * @param $prize /奖品库
     * @return $this
     * 创建奖池/重置已经存在的奖池
     */
    public function create_draw($probability = 1 / 1000, $name = 'draw', $prize = 1)
    {
        self::set('draw_name', $name);
        self::set('probability', $probability);
        self::set('prize', is_array($prize) ? $prize : [$prize]);
        Init::cache()->has($name) ?: Init::cache()->set($name, []);
        return $this;
    }

    /**
     * @param string $draw_name
     * @return bool
     * 清除抽奖缓存
     */
    public function undraw($draw_name = 'draw')
    {
        return Init::cache()->delete($draw_name);
    }

    /**
     * 创建名片信息
     */
    public function create_card(array $options){
        $name=isset($options['name'])?$options['name']:'';
        $surname=isset($options['surname'])?$options['surname']:'';
        $work=isset($options['work'])?$options['work']:'';
        $nickname=isset($options['nickname'])?$options['nickname']:'';
        $mobile=isset($options['mobile'])?$options['mobile']:'';
        $work_mobile=isset($options['work_mobile'])?$options['work_mobile']:'';
        $company=isset($options['company'])?$options['company']:'';
        $email=isset($options['email'])?$options['email']:'';
        $country=isset($options['country'])?$options['country']:'';
        $city=isset($options['city'])?$options['city']:'';
        $area=isset($options['area'])?$options['area']:'';
        $address=isset($options['address'])?$options['address']:'';
        $work_country=isset($options['work_country'])?$options['work_country']:'';
        $work_city=isset($options['work_city'])?$options['work_city']:'';
        $work_area=isset($options['work_area'])?$options['work_area']:'';
        $work_address=isset($options['work_address'])?$options['work_address']:'';
        $postage=isset($options['postage'])?$options['postage']:'';
        $work_postage=isset($options['work_postage'])?$options['work_postage']:'';
        $info="BEGIN:VCARD\r\nVERSION:3.0\r\nN:{$name}\r\nFN:{$surname}\r\nTITLE:{$work}\r\nNICKNAME:{$nickname}\r\nTEL;CELL;VOICE:{$mobile}\r\nTEL;WORK;VOICE:{$work_mobile}\r\nORG:{$company}\r\nEMAIL;PREF;INTERNET:{$email}\r\nADR;TYPE=WORK:;;{$work_address};{$work_area};{$work_city};{$work_postage};{$work_country}\r\nADR;TYPE=HOME:;;{$address};{$area};{$city};{$postage};{$country}\r\nNOTE;ENCODING=QUOTED-PRINTABLE:备注来自名片通讯录\r\nEND:VCARD";
        return $info;
    }

}