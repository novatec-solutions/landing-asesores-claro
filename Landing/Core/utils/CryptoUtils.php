<?php

class CryptoUtils{

    public static function decrypt($encrypt_data){

        $cipher = 'AES-128-CBC';
        $key = hex2bin("0123456789abcdef0123456789abcdef");
        $iv =  hex2bin("abcdef9876543210abcdef9876543210");

        $decrypted = openssl_decrypt($encrypt_data, $cipher, $key, OPENSSL_ZERO_PADDING, $iv);
        $decrypted = trim($decrypted);

        return $decrypted;
    }

    public static function encryptMD5($password){
        $secret = "Claro.*2019#123";

        $key = md5(mb_convert_encoding($secret, "UTF-8"), true);  
        $key = str_pad($key, 24, "\0");   

        $cipher = new \phpseclib3\Crypt\TripleDES('ecb');
        $cipher->setKey($key);

        $hoy = date("Y-m-d");

        $message = $password.'|'.$hoy;

        $cryptText = $cipher->encrypt($message);
        $cryptText = base64_encode($cryptText);
        
        return $cryptText;
    }

}