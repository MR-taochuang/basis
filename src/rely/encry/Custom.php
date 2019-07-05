<?php

namespace rely\encry;

/**
 * Class Custom
 * @package rely\encry
 * @author Mr.taochuang <mr_taochuang@163.com>
 * @date 2019/7/4 10:43
 * 自定义加密
 */
class Custom {

    public $data;

    public $message;

    /**
     * @param int $num
     * @return $this
     * 数字加密
     */
    function encode_num($num = 0)
    {
        $num_len = strlen($num);
        if (!is_numeric($num) || $num_len > 15) {
            $this->data = false;
            $this->message = '请输入数字';
            return $this;
        }
        list($start_rand, $end_rand, $nums, $pies) = [rand(100, 999), rand(10, 99), [], self::unique_rand(1, 9, 9)];
        for ($i = 0; $i < ($num_len / 2); $i++) {
            $nums[$pies[$i]] = ['num' => substr($num, $i * 2, 2), 'rand' => rand(1000, 7000), 'pie' => $pies[$i], 'i' => $i + 1];
        }
        ksort($nums);
        $str = $start_rand;
        foreach ($nums as $maps) {
            list($rand, $d_rand) = [rand(10, 70), rand(1, 9)];
            $i = $rand + $maps['i'] + $d_rand;
            $len=strlen($maps['num']);
            $vs = ($maps['num'] * $maps['pie']) + $maps['rand'] + $start_rand - ($end_rand * $maps['i']) + $i;
            $str .= $maps['rand'] . $maps['pie'] . $rand . $i . $d_rand .$len .$vs;
        }
        return $str . $end_rand;
    }

    /**
     * @param int $num
     * @return $this
     * 数字解密
     */
    function decode_num($num = 0)
    {
        if (!is_numeric($num)) {
            $this->data = false;
            $this->message = '请输入数字';
            return $this;
        }
        if (empty($num)) $num = $this->data;
        list($start_rand, $end_rand, $num_len, $encode, $nums) = [ceil(substr($num, 0, 3)), ceil(substr($num, -2)), ceil(strlen($num) - 5), substr(substr($num, 3), 0, -2), []];
        for ($i = 0; $i < ceil($num_len / 15); $i++) {
            $nums[] = substr($encode, $i * 15, 15);
        }
        $n = [];
        foreach ($nums as $maps) {
            list($maps_rand, $pie, $rand, $i, $d_rand, $vs,$len) = [ceil(substr($maps, 0, 4)), ceil(substr($maps, 4, 1)), substr($maps, 5, 2), intval(substr($maps, 7, 2)), substr($maps, 9, 1), ceil(substr($maps, 11)),ceil(substr($maps,10,1))];
            $rule_i = intval($i - $rand - $d_rand);
            $v = ceil(($vs - $i + ($end_rand * $rule_i) - $start_rand - $maps_rand) / $pie);
            if($len==2 && strlen($v)==1) $v='0'.$v;
            $n[$rule_i] = $v;
        }
        ksort($n);
        return implode($n, '');
    }

    /**
     * array unique_rand( int $min, int $max, int $num )
     * 生成一定数量的不重复随机数
     * $min 和 $max: 指定随机数的范围
     * $num: 指定生成数量
     */
    function unique_rand($min, $max, $num)
    {
        $count = 0;
        $return = array();
        while ($count < $num) {
            $return[] = mt_rand($min, $max);
            $return = array_flip(array_flip($return));
            $count = count($return);
        }
        shuffle($return);
        return $return;
    }

    /**
     * @param string $string
     * @param string $key
     * @return $this
     * 字符串加密
     */
    function encode($string = '',$key='')
    {
        if (empty($string)) $string = $this->data;
        $encode = '';
        $key = substr(md5($key??$_SERVER['HTTP_USER_AGENT']), 8, 18);
        $keyLen = strlen($key);
        $strLen = strlen($string);
        for ($i = 0; $i < $strLen; $i++) {
            $k = $i % $keyLen;
            $encode .= $string[$i] ^ $key[$k];
        }
        return base64_encode($encode);
    }

    /**
     * @param string $string
     * @param string $key
     * @return $this
     * 字符串解密
     */
    function decode($string = '',$key='')
    {
        if (empty($string)) $string = $this->data;
        $string = base64_decode($string);

        $decode = '';
        $key = substr(md5($key??$_SERVER['HTTP_USER_AGENT']), 8, 18);
        $keyLen = strlen($key);
        $strLen = strlen($string);
        for ($i = 0; $i < $strLen; $i++) {
            $k = $i % $keyLen;
            $decode .= $string[$i] ^ $key[$k];
        }
        return $decode;
    }

}