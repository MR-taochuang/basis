<?php

namespace rely\init;

/**
 * Class Datatype
 * @package rely\init
 * @author Mr.taochuang <mr_taochuang@163.com>
 * @date 2019/7/3 12:32
 * 数据操作类
 */
class Dataswitch
{
    /**
     * @var
     * 错误信息
     */
    public $message = '';

    /**
     * @param $data /要转换的数据
     * @param null $partition 字符串炸开数组分隔符
     * @return array|mixed|string
     * 转数组
     */
    public function toArray($data, $partition = null)
    {
        $data_type = self::getDataType($data);
        if ($data_type == 'array') return $data;
        if ($data_type == 'json') return self::json2array($data);
        if ($data_type == 'xml') return self::xml2array($data);
        if (!is_null($partition) && $data_type == 'string') return explode($partition, $data);
        if ($data_type = 'object') return self::object2array($data);
        $this->message = '数据格式不正确转换出错';
        return $data;
    }

    /**
     * @param $data /要转的数据
     * @return array|mixed|string
     * 转json
     */
    public function toJson($data)
    {
        $data_type = self::getDataType($data);
        if ($data_type == 'json') return $data;
        $data = self::toArray($data);
        if (is_array($data)) {
            return preg_replace_callback('/\\\\u([0-9a-f]{4})/i', function ($matches) {
                return mb_convert_encoding(pack("H*", $matches[1]), "UTF-8", "UCS-2BE");
            }, ($jsonData = json_encode($data)) == '[]' ? '{}' : $jsonData);
        } else {
            $this->message = '数据格式不正确转换出错';
            return $data;
        }
    }

    /**
     * @param $data /要转的数据
     * @return array|mixed|string
     * 转xml
     */
    public function toXml($data)
    {
        $data_type = self::getDataType($data);
        if ($data_type == 'xml') return $data;
        $data = self::toArray($data);
        if (is_array($data)) {
            return "<xml>" . self::arr2xml($data) . "</xml>";
        } else {
            $this->message = '数据格式不正确转换出错';
            return $data;
        }
    }

    /**
     * @param $data /要转的数据
     * @return array|mixed|object|string
     * 转对象
     */
    public function toObject($data)
    {
        $data_type = self::getDataType($data);
        if ($data_type == 'object') return $data;
        $data = self::toArray($data);
        return (object)$data;
    }

    /**
     * @param $data /要转的数据
     * @param null $partition 分隔符
     * @return string
     * 转字符串
     */
    public function toString($data, $partition = null)
    {
        $data_type = self::getDataType($data);
        if ($data_type == 'string') return $data;
        $data = self::toArray($data);
        return is_null($partition) ? implode('', $data) : implode($partition, $data);

    }

    /**
     * @param $data /要转换的数字
     * @param int $place /保留几位小数
     * @param int $type /1四舍五入 2向下取 3向上取
     * @return float|int
     * 数字转换
     */
    public function toNumber($data, int $place = 2, int $type = 1)
    {
        if (is_numeric($data) === false) return $data;
        if ($place == 0 && $type == 1) return (int)round($data);
        if ($place == 0 && $type == 2) return (int)floor($data);
        if ($place == 0 && $type == 3) return (int)ceil($data);
        if ($type == 1) return (float)sprintf("%.{$place}f", substr(sprintf("%." . ($place * 2) . "f", $data), 0, -2));
        if ($type == 2) return (float)sprintf("%.{$place}f", substr(sprintf("%." . ($place + 2) . "f", $data), 0, -2));
        if ($type == 3) return (float)sprintf("%.{$place}f", substr(sprintf("%." . ($place + 2) . "f", $data), 0, -2)) + (1 / (pow(10, $place)));
        return $data;
    }

    /**
     * @param $data /json数据
     * @return mixed
     * @throws \Exception
     * json转数组
     */
    public function json2array($data)
    {
        $result = json_decode($data, true);
        if (empty($result)) {
            throw new \Exception('invalid response.', '0');
        }
        return $result;
    }

    /**
     * @param $data /xml数据
     * @return mixed
     * xml转数组
     */
    public function xml2array($data)
    {
        $entity = libxml_disable_entity_loader(true);
        $data = (array)simplexml_load_string($data, 'SimpleXMLElement', LIBXML_NOCDATA);
        libxml_disable_entity_loader($entity);
        return json_decode(self::toJson($data), true);
    }

    /**
     * @param $data /object对象
     * @return array
     * 对象转为数组
     */
    public function object2array($data)
    {
        if (is_object($data)) {
            $data = (array)$data;
        }
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = self::object2array($value);
            }
        }
        return $data;
    }

    /**
     * XML内容生成
     * @param array $data 数据
     * @param string $content
     * @return string
     */
    private static function arr2xml($data, $content = '')
    {
        foreach ($data as $key => $val) {
            is_numeric($key) && $key = 'item';
            $content .= "<{$key}>";
            if (is_array($val) || is_object($val)) {
                $content .= self::arr2xml($val);
            } elseif (is_string($val)) {
                $content .= '<![CDATA[' . preg_replace("/[\\x00-\\x08\\x0b-\\x0c\\x0e-\\x1f]/", '', $val) . ']]>';
            } else {
                $content .= $val;
            }
            $content .= "</{$key}>";
        }
        return $content;
    }

    /**
     * @param $data
     * @param int $page_num
     * @param null $page
     * @return array
     * 数组分页
     */
    public function array_page(array $data, $page_num = 15, $page = null)
    {
        if (is_null($page)) $page = $_REQUEST['page'];
        empty($page) ? $page = 1 : true;
        $data = array_slice($data, ($page - 1) * $page_num, $page_num);
        $total = count($data);
        return ['total' => $total, 'per_page' => $page_num, 'current_page' => $page, 'last_page' => ceil($total / $page_num), 'data' => $data];
    }

    /**
     * @param $data
     * 获取数据类型
     */
    public function getDataType($data)
    {
        $type = gettype($data);
        if ($type == 'string') {
            if ($this->json_check($data)) $type = 'json';
            if ($this->xml_check($data)) $type = 'xml';
        }
        return $type;
    }

    /**
     * @param $data
     * @return bool
     * 检测是否为json数据
     */
    public function json_check($data)
    {
        if (!is_null(json_decode($data))) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param $data
     * @return bool|mixed
     * 检测是否为xml数据
     */
    public function xml_check($data)
    {
        $xml_parser = xml_parser_create();
        if (!xml_parse($xml_parser, $data, true)) {
            xml_parser_free($xml_parser);
            return false;
        } else {
            return (json_decode(json_encode(simplexml_load_string($data)), true));
        }
    }

    /**
     * @param $data /要打印的数据
     * 打印输出
     */
    public function toPrint($data)
    {
        echo '<pre>';
        var_dump($data);
        echo '<pre>';
    }

    /**
     * @param $data
     * @return mixed
     * 下滑线转驼峰
     */
    public static function convert2Underline($data)
    {
        $data = preg_replace_callback('/([-_]+([a-z]{1}))/i', function ($matches) {
            return strtoupper($matches[2]);
        }, $data);
        return $data;
    }

    /**
     * 驼峰转下划线
     * @param $data
     * @return mixed
     */
    public function hump2ToLine($data)
    {
        $data = preg_replace_callback('/([A-Z]{1})/', function ($matches) {
            return '_' . strtolower($matches[0]);
        }, $data);
        return $data;
    }

    /**
     * @param $data
     * @return string
     * 获取汉字/英文首字母
     */
    function Str2First($data)
    {
        $data = iconv("UTF-8", "gb2312", $data);//编码转换
        if (preg_match("/^[\x7f-\xff]/", $data)) {
            $fchar = ord($data{0});
            if ($fchar >= ord("A") and $fchar <= ord("z")) return strtoupper($data{0});
            $a = $data;
            $val = ord($a{0}) * 256 + ord($a{1}) - 65536;
            if ($val >= -20319 and $val <= -20284) return "A";
            if ($val >= -20283 and $val <= -19776) return "B";
            if ($val >= -19775 and $val <= -19219) return "C";
            if ($val >= -19218 and $val <= -18711) return "D";
            if ($val >= -18710 and $val <= -18527) return "E";
            if ($val >= -18526 and $val <= -18240) return "F";
            if ($val >= -18239 and $val <= -17923) return "G";
            if ($val >= -17922 and $val <= -17418) return "H";
            if ($val >= -17417 and $val <= -16475) return "J";
            if ($val >= -16474 and $val <= -16213) return "K";
            if ($val >= -16212 and $val <= -15641) return "L";
            if ($val >= -15640 and $val <= -15166) return "M";
            if ($val >= -15165 and $val <= -14923) return "N";
            if ($val >= -14922 and $val <= -14915) return "O";
            if ($val >= -14914 and $val <= -14631) return "P";
            if ($val >= -14630 and $val <= -14150) return "Q";
            if ($val >= -14149 and $val <= -14091) return "R";
            if ($val >= -14090 and $val <= -13319) return "S";
            if ($val >= -13318 and $val <= -12839) return "T";
            if ($val >= -12838 and $val <= -12557) return "W";
            if ($val >= -12556 and $val <= -11848) return "X";
            if ($val >= -11847 and $val <= -11056) return "Y";
            if ($val >= -11055 and $val <= -10247) return "Z";
        } else {
            return '#';
        }
    }

    /**
     * @param $data
     * @return bool|string
     * 时间转换剩余多少时间
     */
    function Sec2Time($data)
    {
        if (is_numeric($data)) {
            $res = [];
            $value = array(
                "years" => 0, "days" => 0, "hours" => 0,
                "minutes" => 0, "seconds" => 0,
            );
            if ($data >= 31556926) {
                $value["years"] = floor($data / 31556926);
                $data = ($data % 31556926);
            }
            if ($data >= 86400) {
                $value["days"] = floor($data / 86400);
                $data = ($data % 86400);
            }
            if ($data >= 3600) {
                $value["hours"] = floor($data / 3600);
                $data = ($data % 3600);
            }
            if ($data >= 60) {
                $value["minutes"] = floor($data / 60);
                $data = ($data % 60);
            }
            $value["seconds"] = floor($data);
            if ($value["years"] > 0) {
                $res['year'] = $value['years'];
            }
            if ($value["days"] > 0) {
                $res['day'] = $value['days'];
            }
            if ($value['hours'] > 0) {
                $res['hour'] = $value['hours'];
            }
            if ($value['minutes'] > 0) {
                $res['minute'] = $value['minutes'];
            }
            if ($value['seconds'] > 0) {
                $res['second'] = $value['seconds'];
            }
            Return $res;

        } else {
            return (bool)FALSE;
        }
    }

    /**
     * @param int $digits /单号位数
     * @param string $prefix /单号长度
     * @return string
     * 创建随机单号
     */
    public function create_order_number($digits = 24, $prefix = '')
    {
        $date = date('YmdHis');
        $digits = intval($digits);
        if ($digits < 14) return '随机单号大于14字符';
        $digits = $digits - strlen($date) - strlen($prefix);
        if ($digits < 0) return '随机单号生成失败';
        $rand = '';
        $num = floor($digits / 10);
        for ($i = 0; $i < $num; $i++) {
            $rand .= str_pad(mt_rand(1, (int)9999999999), 10, '0', STR_PAD_LEFT);
        }
        if ($digits % 10 != 0) {
            $rand .= str_pad(mt_rand(1, (int)substr(9999999999, 0, $digits - ($num * 10))), $digits - ($num * 10), '0', STR_PAD_LEFT);
        }
        $order_number = $prefix . $date . $rand;
        return $order_number;
    }

    /**
     * @param int $length 字符长度
     * @param $type 1 数字+字母 2数字 3 数字+字母+字符 4字母
     * @return string
     * 随机字符
     */
    public function create_number1($length = 6, int $type = 1)
    {
        $chars = [
            1 => '0123456789abcdefghigklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
            2 => '0123456789',
            3 => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()-_ []{}<>~`+=,.;:/?|',
            4 => 'abcdefghigklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'
        ];
        $str = '';
        for ($i = 0; $i < $length; $i++) {
            $str .= $chars[mt_rand(0, strlen($chars[$type]) - 1)];
        }
        return $str;
    }

    /**
     * @param $needle
     * @param $replace
     * @param $haystack
     * @return mixed
     * 只替换一次字符
     */
    public function str_replace_once($needle, $replace, $haystack)
    {
        $pos = strpos($haystack, $needle);
        if ($pos === false) {
            return $haystack;
        }
        return substr_replace($haystack, $replace, $pos, strlen($needle));
    }
}