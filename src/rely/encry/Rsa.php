<?php

namespace rely\encry;

use rely\init\Config;

/**
 * Class Rsa
 * @package rely\encry
 * @author Mr.taochuang <mr_taochuang@163.com>
 * @date 2019/7/4 9:52
 * rsa加密处理
 */
class Rsa extends Config{

    /**
     * @param $private_key /私钥
     * @param $public_key /公钥
     * @return $this
     * 初始rsa公钥私钥
     */
    public function instance($private_key,$public_key){
        if(file_exists($private_key)) $private_key=file_get_contents($private_key);
        if(file_exists($public_key)) $public_key=file_get_contents($public_key);
        self::set('private_key',$private_key);
        self::set('public_key',$public_key);
        return $this;
    }
    /**
     *返回对应的私钥
     */
    private function getPrivateKey()
    {
        $privateKey = self::get('private_key');
        return openssl_pkey_get_private($privateKey);
    }

    /**
     *返回对应的公钥
     */
    private function getPublicKey()
    {
        $publicKey = self::get('public_key');
        return openssl_pkey_get_public($publicKey);
    }
    /**
     * @param $decrypted
     * @return null
     * 公钥加密
     */
    private function publicEncrypt($decrypted)
    {
        if (!is_string($decrypted)) {
            return null;
        }
        return (openssl_public_encrypt($decrypted, $encrypted, self::getPublicKey())) ? $encrypted : null;


    }
    /**
     * 私钥解密
     * @param $encrypted //解密字符
     * @return null
     */
    private function privDecryptNB64($encrypted)
    {
        if (!is_string($encrypted)) {
            return null;
        }
        return (openssl_private_decrypt($encrypted, $decrypted, self::getPrivateKey())) ? $decrypted : null;
    }

    /**
     * @param string $decrypted
     * @return $this
     * 分段公钥加密
     */
    public function partPubEncrypt($decrypted='')
    {
        if(empty($decrypted)) $decrypted=$this->data;
        $dataArray = str_split($decrypted, 117);
        $bContent = '';
        foreach ($dataArray as $key => $subData) {
            $bContent .= self::publicEncrypt($subData);
        }
        return base64_encode($bContent);
    }
    /**
     * 分段私钥解密
     * @param string $encrypted
     * @return //Ambigous <string, NULL, Ambigous>
     */
    public function partPrivDecrypt($encrypted='')
    {
        if(empty($encrypted)) $encrypted=$this->data;
        $encrypted = base64_decode($encrypted);
        $dataArray = str_split($encrypted, 128);
        $bContent = '';
        foreach ($dataArray as $key => $subData) {
            $bContent .= self::privDecryptNB64($subData);
        }
        return  $bContent;
    }


}