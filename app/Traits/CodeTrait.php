<?php

namespace App\Traits;

trait CodeTrait
{

    public static function generateCode(){
        // Genera un ID único basado en la marca de tiempo actual
        $uniqueId = uniqid();

        // Define el alfabeto personalizado para la representación más corta
        $alphabet = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';

        // Convierte el ID único a una representación más corta en base al alfabeto
        $reservationCode = self::baseConvert($uniqueId, 16, $alphabet);

        // $reservationCode ahora contiene el código corto único
        // que puedes usar como referencia de reserva
        return $reservationCode;
    }

    // Función para convertir números de una base a otra utilizando un alfabeto personalizado
    private static function baseConvert($number, $fromBase, $alphabet){
            $base10 = 0;
            $length = strlen($number);

            for ($i = 0; $i < $length; $i++) {
                $base10 += strpos($alphabet, $number[$i]) * pow($fromBase, $length - $i - 1);
            }

            $toBase = strlen($alphabet);
            $convertedNumber = '';

            while ($base10 > 0) {
                $convertedNumber = $alphabet[$base10 % $toBase] . $convertedNumber;
                $base10 = (int)($base10 / $toBase);
            }

            return $convertedNumber;
    }
}