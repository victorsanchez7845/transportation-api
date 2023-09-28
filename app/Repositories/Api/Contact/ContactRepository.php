<?php

namespace App\Repositories\Api\Contact;
use App\Traits\MailjetTrait;

class ContactRepository{
    use MailjetTrait;
    private $request = [];
    private $email = "bookings@caribbean-transfers.com";

    public function send($request){
        if(isset( $request['company_email'] )):
            $this->email = strtolower( trim( $request['company_email'] ) );
        endif;

        $message = 'Nombre del cliente: '.$request['client_full_name']." \n";
        $message .= 'Asunto: '.$request['client_subject']." \n";
        $message .= 'E-mail: '.$request['client_email']." \n";
        $message .= 'Teléfono: '.$request['client_phone']." \n";
        $message .= 'Mensaje: '.$request['client_message']." \n";        

        $email_data = array(
            "Messages" => array(
                array(
                    "From" => array(
                        "Email" => $this->email,
                        "Name" => "Bookings"
                    ),
                    "To" => array(
                        array(
                            "Email" => $this->email,
                            "Name" => "Bookings"
                        )
                    ),
                    "Subject" => 'Correo de contacto | ' . $this->email,
                    "TextPart" => $message
                )
            )
        );

        $email_response = $this->sendMailjet($email_data);
        if(isset($email_response['Messages'][0]['Status']) && $email_response['Messages'][0]['Status'] == "success"):
            return true;
        else:
            return false;
        endif;
    }
}