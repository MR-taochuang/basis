<?php

namespace rely\encry;

use rely\init\Config;

class Aes extends Config{

    /**
     * @param $key
     * @param $iv
     * @return $this
     * 配置 key iv
     */
    public function instance($key,$iv){
        self::set('key',$key);
        self::set('iv',$iv);
        return $this;
    }

    /**
     * @param $input
     * @return string
     * 加密
     */
    public function encrypt($input)
    {
        $data = openssl_encrypt($input, 'AES-256-CBC', self::get('key'), OPENSSL_RAW_DATA, $this->hexToStr(self::get('iv')));
        $data = base64_encode($data);
        return $data;
    }

    /**
     * @param $input
     * @return string
     * 解密
     */
    public function decrypt($input)
    {
        $decrypted = openssl_decrypt(base64_decode($input), 'AES-256-CBC', self::get('key'), OPENSSL_RAW_DATA, $this->hexToStr(self::get('iv')));
        return $decrypted;
    }

    /**
     * @param $hex
     * @return string
     * hex转换
     */
    public function hexToStr($hex)
    {
        $string='';
        for ($i=0; $i < strlen($hex)-1; $i+=2)
        {
            $string .= chr(hexdec($hex[$i].$hex[$i+1]));
        }
        return $string;
    }

}