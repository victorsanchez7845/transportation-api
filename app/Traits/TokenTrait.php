<?php

namespace App\Traits;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use UnexpectedValueException;

trait TokenTrait
{
    private static $key = '1234567890';
    /**
     * $data = []
     * $time = quantity in Hours
     */
    public static function set($data = [], $time = 1){

        $current_time = time();
        $payload = array(
            'iat' => $current_time,
            'exp' => $current_time + (3600 * $time),  // 3600 = 1 Hora
            'data' => $data
        );

        return JWT::encode($payload, self::$key, 'HS256');
    }

    public static function get($jwt = []){
        try{
            return JWT::decode($jwt, new Key(self::$key, 'HS256'));            
        }catch(\Exception $e){            
            return false;
        }
    }
}