<?php

namespace App\Traits;
use DateTime;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

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
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, 600); // Tiempo de espera muy corto (1 milisegundo)        
        curl_exec($ch);
        curl_close($ch);
        return true;
    }

    public static function QrCode($code){
        $qr = QrCode::create($code);
        $qr->setSize(200);
        $writer = new PngWriter();
        $result = $writer->write($qr);
        echo $result->getDataUri();
    }

    public static function slug($phrase) {
        $slug = iconv('UTF-8', 'ASCII//TRANSLIT', $phrase);
        
        $slug = str_replace(' - ', '-', $slug);        
        $slug = str_replace(' ', '-', $slug);        
        $slug = str_replace('&', 'and', $slug);

        $slug = preg_replace('/[^a-zA-Z0-9\-_]/', '', $slug);
        
        $slug = strtolower($slug);

        return $slug;
    }
}