<?php

/**
 * Gibberish AES, a Codeigniter Implementation
 *
 * See Gibberish AES javascript encryption library, @link https://github.com/mdp/gibberish-aes
 *
 * This implementation is based on php Gibberish AES version
 * @link https://github.com/ivantcholakov/gibberish-aes-php
 *
 * Requirements:
 *
 * OpenSSL functions installed and PHP version >= 5.3.3 (preferred case)
 * or
 * Mcrypt functions installed. 
 *
 * If none of these functions exist, the class will try to use openssl
 * from the command line (avoid this case).
 * 
 * Usage:
 *
 * // This is a secret key, keep it in a safe place and don't loose it.
 * $key = 'my secret key';
 *
 * // The string to be encrypted.
 * $string = 'my secret message';
 *
 * // This is the result after encryption of the given string.
 * $encrypted_string = $this->gibberishaes->enc($string, $key);
 *
 * // This is the result after decryption of the previously encrypted string.
 * // $decrypted_string == $string (should be).
 * $decrypted_string = $this->gibberishaes->dec($encrypted_string, $key);
 * echo $decrypted_string;
 *
 * // The default key-size is 256 bits. 128 and 192 bits are also allowed.
 * // Example:
 * $old_key_size = $this->gibberishaes->size();
 * $this->gibberishaes->size(192);
 * // The short way: $old_key_size = GibberishAES::size(192);
 * $encrypted_string = $this->gibberishaes->enc($string, $key);
 * $decrypted_string = $this->gibberishaes->dec($encrypted_string, $key);
 * $this->gibberishaes->size($old_key_size);
 * echo $decrypted_string;
 * 
 * @author of php version Ivan Tcholakov <ivantcholakov@gmail.com>, 2012-2014.
 * @author of CI version Vibin TV <vibin17@gmail.com>, June 2014.
 * Code repository: @link https://github.com/vibintv/gibberish-aes-codeigniter
 *
 * @version 1
 *
 */

class GibberishAES {

    protected static $key_size = 256;            // The default key size in bits
    protected static $valid_key_sizes = array(128, 192, 256);   // Sizes in bits

    protected static $openssl_random_pseudo_bytes_exists = null;
    protected static $openssl_encrypt_exists = null;
    protected static $openssl_decrypt_exists = null;
    protected static $mcrypt_exists = null;
    protected static $openssl_cli_exists = null;

    // This is a static class, instances are disabled.
    final function __construct() {}
    final function __clone() {}

    /**
     * Crypt AES (256, 192, 128)
     *
     * @param   string  $string     The input message to be encrypted.
     * @param   string  $pass       The key (string representation).
     * @return  mixed               base64 encrypted string, FALSE on failure.
     */
    public static function enc($string, $pass) {

        $key_size = self::$key_size;

        // Set a random salt.
        $salt = self::random_pseudo_bytes(8);

        $salted = '';
        $dx = '';

        // Lengths in bytes:
        $key_length = (int) ($key_size / 8);
        $block_length = 16; // 128 bits, iv has the same length.
        // $salted_length = $key_length (32, 24, 16) + $block_length (16) = (48, 40, 32)
        $salted_length = $key_length + $block_length;

        while (strlen($salted) < $salted_length) {

            $dx = md5($dx.$pass.$salt, true);
            $salted .= $dx;
        }

        $key = substr($salted, 0, $key_length);
        $iv = substr($salted, $key_length, $block_length);

        $encrypted = self::aes_cbc_encrypt($string, $key, $iv);

        return $encrypted !== false ? base64_encode('Salted__'.$salt.$encrypted) : false;
    }

    /**
     * Decrypt AES (256, 192, 128)
     *
     * @param   string  $string     The input message to be decrypted.
     * @param   string  $pass       The key (string representation).
     * @return  mixed               base64 decrypted string, FALSE on failure.
     */
    public static function dec($string, $pass) {

        $key_size = self::$key_size;

        // Lengths in bytes:
        $key_length = (int) ($key_size / 8);
        $block_length = 16;

        $data = base64_decode($string);
        $salt = substr($data, 8, 8);
        $encrypted = substr($data, 16);

        /**
         * From https://github.com/mdp/gibberish-aes
         *
         * Number of rounds depends on the size of the AES in use
         * 3 rounds for 256
         *     2 rounds for the key, 1 for the IV
         * 2 rounds for 128
         *     1 round for the key, 1 round for the IV
         * 3 rounds for 192 since it's not evenly divided by 128 bits
         */
        $rounds = 3;
        if ($key_size == 128) {
            $rounds = 2;
        }

        $data00 = $pass.$salt;
        $md5_hash = array();
        $md5_hash[0] = md5($data00, true);
        $result = $md5_hash[0];

        for ($i = 1; $i < $rounds; $i++) {

            $md5_hash[$i] = md5($md5_hash[$i - 1].$data00, true);
            $result .= $md5_hash[$i];
        }

        $key = substr($result, 0, $key_length);
        $iv = substr($result, $key_length, $block_length);

        return self::aes_cbc_decrypt($encrypted, $key, $iv);
    }

    /**
     * Sets the key-size for encryption/decryption in number of bits
     * @param   mixed       $newsize    The new key size. The valid integer values are: 128, 192, 256 (default)
     *                                  $newsize may be NULL or may be omited - in this case
     *                                  this method is just a getter of the current key size value.
     * @return  integer                 Returns the old key size value.
     */
    public static function size($newsize = null) {

        $result = self::$key_size;

        if (is_null($newsize)) {
            return $result;
        }

        $newsize = (string) $newsize;

        if ($newsize == '') {
            return $result;
        }

        $valid_integer = ctype_digit($newsize);

        $newsize = (int) $newsize;

        if (!$valid_integer || !in_array($newsize, self::$valid_key_sizes)) {

            trigger_error(
                'GibberishAES: Invalid key size value was to be set. It should be integer value (number of bits) amongst: 128, 192, 256.',
                E_USER_WARNING
            );

        } else {

            self::$key_size = $newsize;
        }

        return $result;
    }

    // Non-public methods ------------------------------------------------------

    protected static function random_pseudo_bytes($length) {

        if (!isset(self::$openssl_random_pseudo_bytes_exists)) {
            self::$openssl_random_pseudo_bytes_exists = function_exists('openssl_random_pseudo_bytes');
        }

        if (self::$openssl_random_pseudo_bytes_exists) {
            return openssl_random_pseudo_bytes($length);
        }

        // Borrowed from http://phpseclib.com/

        $rnd = '';

        for ($i = 0; $i < $length; $i++) {

            $sha = hash('sha256', mt_rand());
            $char = mt_rand(0, 30);
            $rnd .= chr(hexdec($sha[$char].$sha[$char + 1]));
        }

        return $rnd;
    }

    protected static function aes_cbc_encrypt($string, $key, $iv) {

        $key_size = self::$key_size;

        if (!isset(self::$openssl_encrypt_exists)) {
            self::$openssl_encrypt_exists = function_exists('openssl_encrypt')
                && version_compare(PHP_VERSION, '5.3.3', '>='); // We need $iv parameter.
        }

        if (self::$openssl_encrypt_exists) {
            return openssl_encrypt($string, "aes-$key_size-cbc", $key, true, $iv);
        }

        if (!isset(self::$mcrypt_exists)) {
            self::$mcrypt_exists = function_exists('mcrypt_encrypt');
        }

        if (self::$mcrypt_exists) {

            // Info: http://www.chilkatsoft.com/p/php_aes.asp
            // http://en.wikipedia.org/wiki/Block_cipher_modes_of_operation

            $cipher = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '');

            if (mcrypt_generic_init($cipher, $key, $iv) != -1) {

                $encrypted = mcrypt_generic($cipher, self::pkcs7_pad($string));
                mcrypt_generic_deinit($cipher);
                mcrypt_module_close($cipher);

                return $encrypted;
            }

            return false;
        }

        if (!isset(self::$openssl_cli_exists)) {
            self::$openssl_cli_exists = self::openssl_cli_exists();
        }

        if (self::$openssl_cli_exists) {

            $cmd = 'echo '.self::escapeshellarg($string).' | openssl enc -e -a -A -aes-'.$key_size.'-cbc -K '.self::strtohex($key).' -iv '.self::strtohex($iv);

            exec($cmd, $output, $return);

            if ($return == 0 && isset($output[0])) {
                return base64_decode($output[0]);
            }

            return false;
        }

        trigger_error(
            'GibberishAES: System requirements failure, please, check them.',
            E_USER_WARNING
        );

        return false;
    }

    protected static function aes_cbc_decrypt($crypted, $key, $iv) {

        $key_size = self::$key_size;

        if (!isset(self::$openssl_decrypt_exists)) {
            self::$openssl_decrypt_exists = function_exists('openssl_decrypt')
                && version_compare(PHP_VERSION, '5.3.3', '>='); // We need $iv parameter.
        }

        if (self::$openssl_decrypt_exists) {
            return openssl_decrypt($crypted, "aes-$key_size-cbc", $key, true, $iv);
        }

        if (!isset(self::$mcrypt_exists)) {
            self::$mcrypt_exists = function_exists('mcrypt_encrypt');
        }

        if (self::$mcrypt_exists) {

            $cipher = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '');

            if (mcrypt_generic_init($cipher, $key, $iv) != -1) {

                $decrypted = mdecrypt_generic($cipher, $crypted);
                mcrypt_generic_deinit($cipher);
                mcrypt_module_close($cipher);

                return self::remove_pkcs7_pad($decrypted);
            }

            return false;
        }

        if (!isset(self::$openssl_cli_exists)) {
            self::$openssl_cli_exists = self::openssl_cli_exists();
        }

        if (self::$openssl_cli_exists) {

            $string = base64_encode($crypted);

            $cmd = 'echo '.self::escapeshellarg($string).' | openssl enc -d -a -A -aes-'.$key_size.'-cbc -K '.self::strtohex($key).' -iv '.self::strtohex($iv);

            exec($cmd, $output, $return);

            if ($return == 0 && isset($output[0])) {
                return $output[0];
            }

            return false;
        }

        trigger_error(
            'GibberishAES: System requirements failure, please, check them.',
            E_USER_WARNING
        );

        return false;
    }

    // See http://www.php.net/manual/en/function.mcrypt-decrypt.php#105985

    protected static function pkcs7_pad($string) {

        $block_length = 16;    // 128 bits: $block_length = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
        $pad = $block_length - (strlen($string) % $block_length);

        return $string.str_repeat(chr($pad), $pad);
    }

    protected static function remove_pkcs7_pad($string) {

        $block_length = 16;    // 128 bits: $block_length = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
        $len = strlen($string);
        $pad = ord($string[$len - 1]);

        if ($pad > 0 && $pad <= $block_length) {

            $valid_pad = true;

            for ($i = 1; $i <= $pad; $i++) {

                if (ord($string[$len - $i]) != $pad) {
                    $valid_pad = false;
                    break;
                }
            }

            if ($valid_pad) {
                $string = substr($string, 0, $len - $pad);
            }
        }

        return $string;
    }

    protected static function openssl_cli_exists() {

        exec('openssl version', $output, $return);

        return $return == 0;
    }

    protected static function strtohex($string) {

         $result = '';

         foreach (str_split($string) as $c) {
             $result .= sprintf("%02X", ord($c));
         }

         return $result;
    }

    protected static function escapeshellarg($arg) {

        if (strtolower(substr(php_uname('s'), 0, 3 )) == 'win') {

            // See http://stackoverflow.com/questions/6427732/how-can-i-escape-an-arbitrary-string-for-use-as-a-command-line-argument-in-windo

            // Sequence of backslashes followed by a double quote:
            // double up all the backslashes and escape the double quote
            $arg = preg_replace('/(\\*)"/g', '$1$1\\"', $arg);

            // Sequence of backslashes followed by the end of the arg,
            // which will become a double quote later:
            // double up all the backslashes
            $arg = preg_replace('/(\\*)$/', '$1$1', $arg);

            // All other backslashes do not need modifying

            // Double-quote the whole thing
            $arg = '"'.$arg.'"';

            // Escape shell metacharacters.
            $arg = preg_replace('/([\(\)%!^"<>&|;, ])/g', '^$1', $arg);

            return $arg;
        }

        // See http://markushedlund.com/dev-tech/php-escapeshellarg-with-unicodeutf-8-support
        return "'" . str_replace("'", "'\\''", $arg) . "'";
    }

}