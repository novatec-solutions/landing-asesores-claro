<?php

class CryptoFileUsers{

    public static $key = "0123456789abcdef0123456789abcdef";
    public static $iv = "abcdef9876543210abcdef9876543210";
    public static $cipher = "AES-128-CBC";

    public static function decrypt($encrypt_data){

        $cipher = self::$cipher;
        $key = hex2bin(self::$key);
        $iv =  hex2bin(self::$iv);

        $decrypted = openssl_decrypt($encrypt_data, $cipher, $key, 0, $iv);

        return $decrypted;
    }

    public static function encrypt($encrypt_data){

        $cipher = self::$cipher;
        $key = hex2bin(self::$key);
        $iv =  hex2bin(self::$iv);

        $encrypt = openssl_encrypt($encrypt_data, $cipher, $key, 0, $iv);

        return $encrypt;
    }
}