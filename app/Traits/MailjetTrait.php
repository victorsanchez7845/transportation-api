<?php
namespace App\Traits;

trait MailjetTrait
{
    /**
    * Para usar esta función, se necesita enviar la data que es un arreglo como este ejemplo:
    *   $data = array(
    *    "Messages" => array(
    *        array(
    *            "From" => array(
    *                "Email" => "pilot@mailjet.com",
    *                "Name" => "Mailjet Pilot"
    *            ),
    *            "To" => array(
    *                array(
    *                    "Email" => "passenger1@mailjet.com",
    *                    "Name" => "passenger 1"
    *                )
    *            ),
    *            "Subject" => "Your email flight plan!",
    *            "TextPart" => "Dear passenger 1, welcome to Mailjet! May the delivery force be with you!",
    *            "HTMLPart" => "<h3>Dear passenger 1, welcome to <a href=\"https://www.mailjet.com/\">Mailjet</a>!</h3><br />May the delivery force be with you!"
    *        )
    *    )
    *  );
    
     */
    public static function send( $data = [] ){
        
        $curl = curl_init();
    
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.mailjet.com/v3.1/send',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Authorization: Basic '.base64_encode(config('services.mailjet.key').":".config('services.mailjet.secret'))
            ),
        ));
        
        $response = curl_exec($curl);
        curl_close($curl);
        echo "<pre>";
        print_r($response);
        die();

        $response = json_decode($response, true);
        echo "<pre>";
        print_r($response);
        die();        
        //API KEY; 510f04d80f39f4f03886e3b1bef79c98
        //SECRET KEY: 01ff138880081fa0e637875a54410fae
    }
}