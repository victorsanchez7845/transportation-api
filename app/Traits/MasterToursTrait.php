<?php

namespace App\Traits;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

trait MasterToursTrait
{
    public static $user = "CANCUN";
    public static $password = "52KFeN69=F";

    public static function getListing(){
        $headers = [];
        return self::sendRequest('/Reservas/ListaReservas', 'GET', [], $headers);
    }

    public static function acceptReservation($id){
        $headers = [];
        $data = [
            "IdReserva" => $id,
            "Estado" => "ACEPTADO"
        ];

        return self::sendRequest('/Reservas/AceptarReserva?'.http_build_query($data), 'POST', $data, $headers);
    }

    public static function sendRequest($URI, $method = 'GET', $data = null, $headers_merge = []) {        

        $headers = array(
            'Content-Type: application/json',
        );

        $auth = base64_encode(self::$user.":".self::$password);
        $headers[] = "Authorization: Basic $auth";

        $headers = array_merge($headers, $headers_merge);

        $url = 'https://mastertaxipoint.somee.com/EndPoint/api'.$URI;

        $ch = curl_init($url);

        if ($method == 'POST') {
            curl_setopt($ch, CURLOPT_POST, 1);
            if ($data) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
        }

        if ($method == 'GET') {
            if ($data) {
                $url .= '?' . http_build_query($data);
            }
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);        

        if (curl_errno($ch)) {
            echo 'Error: ' . curl_error($ch);
        }

        curl_close($ch);

        return json_decode($response, true);
    }
}