<?php

namespace App\Traits;
use DateTime;

trait FunctionsTrait
{
    public static function getPrettyDate( $date = '', $language = 'en'){
        $week = [
            'en' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
            'es' => ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo']
        ];
        
        $months = [
            'en' => ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
            'es' => ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre']
        ];

        $dateObject = DateTime::createFromFormat('Y-m-d H:i:s', $date);

        $day_name = $week[$language][$dateObject->format('N') - 1];
        $day = $dateObject->format('j');
        $month_name = $months[$language][$dateObject->format('n') - 1];
        $year = $dateObject->format('Y');
        if($language == "en"):
            return "$day_name, $month_name $day, $year";
        else:
            return "$day_name $day de $month_name de $year";
        endif;
    }

    public static function sendEmail($baseUrl = '', $data = []){        
        $url = $baseUrl . '?' . http_build_query($data);        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // Realizar la solicitud de manera asincrónica (no esperar la respuesta)
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, 1); // Tiempo de espera muy corto (1 milisegundo)        
        curl_exec($ch);
        curl_close($ch);
        return true;
    }
}