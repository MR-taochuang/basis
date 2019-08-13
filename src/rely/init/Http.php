<?php

namespace rely\init;
use rely\Init;

/**
 * Class Browser
 * @package rely\init
 * @author Mr.taochuang <mr_taochuang@163.com>
 * @date 2019/8/11 9:22
 * http操作
 */
class Http extends Config{

    /**
     * @return array
     * 获取请求浏览器
     */
    public function browser()
    {
        $sys = $_SERVER['HTTP_USER_AGENT'];
        if (stripos($sys, "Firefox/") > 0) {
            $exp[0] = "Firefox";
            $exp[1] =Init::dataswitch()->get_string_between($sys,'Firefox/');

        } elseif (stripos($sys, "Maxthon") > 0) {
            $exp[0] = "maxthon";
            $exp[1] =Init::dataswitch()->get_string_between($sys,'Maxthon/','/');

        } elseif (stripos($sys, "Baiduspider") > 0) {
            $exp[0] = "baidu";
            $exp[1] = 'spider';
        }elseif (stripos($sys, "YisouSpider") > 0) {
            $exp[0] = "Yiso";
            $exp[1] = 'spider';
        }elseif (stripos($sys, "Googlebot") > 0) {

            $exp[0] = "google";
            $exp[1] = 'spider';

        }elseif (stripos($sys, "Android") > 0) {

            $exp[0] = "android";
            $exp[1] = Init::dataswitch()->get_string_between($sys,'Android ');

        }
        elseif (stripos($sys, "MSIE") > 0) {
            $exp[0] = "IE";
            $exp[1] = Init::dataswitch()->get_string_between($sys,'MSIEs');

        } elseif (stripos($sys, "OPR") > 0) {
            $exp[0] = "Opera";
            $exp[1] =Init::dataswitch()->get_string_between($sys,'OPR/','/');

        } elseif(stripos($sys, "Edge") > 0) {
            $exp[0] = "Edge";
            $exp[1] = Init::dataswitch()->get_string_between($sys,'Edge/','/');

        } elseif (stripos($sys, "Chrome") > 0) {
            $exp[0] = "Chrome";
            $exp[1] =Init::dataswitch()->get_string_between($sys,'Chrome/','/');

        } elseif(stripos($sys,'rv:')>0 && stripos($sys,'Gecko')>0){
            $exp[0] = "IE";
            $exp[1] = Init::dataswitch()->get_string_between($sys,'rv:','/');

        }else if(stripos($sys,'AhrefsBot')>0){
            $exp[0] = "AhrefsBot";
            $exp[1] = 'spider';
        }else if(stripos($sys,'Safari')>0){
            preg_match("/([d.]+)/", $sys, $safari);
            $exp[0] = "Safari";
            $exp[1] = $safari[1];

        }else if(stripos($sys,'bingbot')>0){
            $exp[0] = "bingbot";
            $exp[1] = 'spider';

        }else if(stripos($sys,'WinHttp')>0){
            $exp[0] = "windows";
            $exp[1] = 'WinHttp tool';

        }else if(stripos($sys,'iPhone OS 10')>0){

            $exp[0] = "iPhone";
            $exp[1] = 'OS 10';

        }else if(stripos($sys,'Sogou')>0){

            $exp[0] = "soguo";
            $exp[1] = 'spider';

        }else if(stripos($sys,'HUAWEIM')>0){

            $exp[0] = "huawei";
            $exp[1] = 'phone';

        }else if(stripos($sys,'Dalvik')>0){

            $exp[0] = "android";
            $exp[1] = 'Dalvik virtual machine';

        }else if(stripos($sys,'Mac OS X 10')>0){
            $exp[0] = "MAC";
            $exp[1] = 'OS X10';

        }else if(stripos($sys,'Opera/9.8')>0){
            $exp[0] = "Opera";
            $exp[1] = '9.8';
        }else if(stripos($sys,'JikeSpider')>0){

            $exp[0] = "immediate";
            $exp[1] = 'spider';

        }else if(stripos($sys,'Baiduspider')>0){
            $exp[0] = 'baidu';
            $exp[1] = 'spider';
        }
        else {
            $exp[0] = $sys;
            $exp[1] = "";
        }
        return ['browser'=>$exp[0],'version'=>$exp[1]];
    }
    public function host(){
        return $_SERVER['SERVER_PORT']==80?'http://'.$_SERVER['HTTP_HOST'].'/':'https://'.$_SERVER['HTTP_HOST'].'/';
    }
}