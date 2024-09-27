<?php
namespace App\Services;

class AESCrypto {

    public static function encrypt($plaintext, $key128) {
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-128-cbc'));
        $cipherText = openssl_encrypt($plaintext, 'AES-128-CBC', hex2bin($key128), 1, $iv);
        return base64_encode($iv . $cipherText);
    }

    public static function decrypt($encodedInitialData, $key128) {
        $encodedInitialData = base64_decode($encodedInitialData);
        $iv = substr($encodedInitialData, 0, 16);
        $encodedInitialData = substr($encodedInitialData, 16);
        $decrypted = openssl_decrypt($encodedInitialData, 'AES-128-CBC', hex2bin($key128), 1, $iv);
        return $decrypted;
    }
}