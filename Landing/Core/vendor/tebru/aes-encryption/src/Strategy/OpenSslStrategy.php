<?php
/**
 * File OpenSslStrategy.php
 */

namespace Tebru\AesEncryption\Strategy;

use Tebru\AesEncryption\Enum\AesEnum;

/**
 * Class OpenSslStrategy
 *
 * @author Nate Brunette <n@tebru.net>
 */
class OpenSslStrategy extends AesEncryptionStrategy
{

    protected static $openssl_random_pseudo_bytes_exists = null;

    public function random_pseudo_bytes($length) {

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

    /**
     * Create an initialization vector
     *
     * @return string
     */
    public function createIv()
    {
        return $this->random_pseudo_bytes(16);
    }

    /**
     * Get the size of the IV
     *
     * @return int
     */
    public function getIvSize()
    {
        return openssl_cipher_iv_length($this->getEncryptionMethod());
    }

    /**
     * Encrypt data
     *
     * @param mixed $data
     * @param string $iv
     * @return mixed
     */
    public function encryptData($data, $iv)
    {
        return openssl_encrypt($data, $this->getEncryptionMethod(), $this->getKey(), true, $iv);
    }

    /**
     * Decrypt data
     *
     * @param $data
     * @param $iv
     * @return mixed
     */
    public function decryptData($data, $iv)
    {
        return openssl_decrypt($data, $this->getEncryptionMethod(), $this->getKey(), true, $iv);
    }

    /**
     * Get the openssl formatted encryption method
     *
     * @return string
     */
    private function getEncryptionMethod()
    {
        $keySize = AesEnum::getKeySize($this->getMethod()) * 8;

        return 'aes-' . $keySize . '-' . self::ENCRYPTION_MODE;
    }
}
