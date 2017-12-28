<?php
/**
 * 对称加密
 * User: myf
 * Date: 2017/11/12
 * Time: 20:46
 */

namespace Myf\Libs;


class Xcrypt
{

    //Rijndael加密法
    const METHOD = 'aes128';

    /**
     * 加密
     * @param object|Array|String $obj
     * @return string
     */
    public static function encrypt($obj){
        $data = Utils::enSerialize($obj);
        $iv = mcrypt_create_iv(32);
        $password = Utils::enSerialize($iv);
        $encrypted = openssl_encrypt($data,self::METHOD,$password,OPENSSL_RAW_DATA,$iv);
        $res = [
            Utils::enSerialize($encrypted),
            $password,
        ];
        $encrypted = Utils::enSerialize($res);
        return $encrypted;
    }

    /**
     * 解密
     * @param string $encrypted
     * @return Object
     */
    public static function decrypt($encrypted){
        $res = Utils::deSerialize($encrypted);
        $originEncrypted =Utils::deSerialize($res[0]);
        $password = $res[1];
        $iv = Utils::deSerialize($password);
        $origin = openssl_decrypt($originEncrypted,self::METHOD,$password,OPENSSL_RAW_DATA,$iv);
        return Utils::deSerialize($origin);
    }

}