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

}