<?php

namespace App\Traits;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use UnexpectedValueException;

trait TokenTrait
{
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

        return JWT::encode($payload, config('services.jwt.key'), 'HS256');
    }

    public static function get($jwt = []){
        try{
            $decoded = JWT::decode($jwt, new Key(config('services.jwt.key'), 'HS256'));
            return json_decode(json_encode($decoded), true);            
        }catch(\Exception $e){            
            return false;
        }
    }
}