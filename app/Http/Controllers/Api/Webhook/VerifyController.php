<?php

namespace App\Http\Controllers\Api\Webhook;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Api\Webhook\PaymentRepository;
use Illuminate\Support\Facades\Validator;
use App\Traits\FunctionsTrait;

class VerifyController extends Controller
{
    use FunctionsTrait;
    
    public function stripe(Request $request, PaymentRepository $paymentRepository){

        $payload = @file_get_contents('php://input');
        $event = json_decode($payload);

        // Handle the event
        switch ($event->type) {
            case 'charge.succeeded':
                $paymentIntent = $event->data->object;

                if(!isset( $paymentIntent->metadata->reservation_id )):
                    http_response_code(400);
                    exit();
                endif;

                //Verificar que exista la reservación
                $check = $paymentRepository->checkReservation($paymentIntent->metadata->reservation_id);
                if($check == false):
                    http_response_code(400);
                    exit();
                endif;
                
                $exchange = $paymentRepository->getExchange("MXN", $check->currency);       
                $data = [
                    'id' => $paymentIntent->metadata->reservation_id,
                    'total' => ($paymentIntent->amount / 100),
                    'currency' => "MXN",
                    'exchange_rate' => $exchange->exchange_rate,
                    'operation' => $exchange->operation,
                    'method' => 'CARD',
                    'description' => 'Stripe',
                    'object' => json_encode($paymentIntent),
                    'reference' => $paymentIntent->id
                ];
            
                //Guardamos el pago en la base de datos
                $response = $paymentRepository->savePayment($data);
                if( $response ):
                    //Envío de correo al cliente...
                    $email = [];
                    $email['code'] = $check->code;
                    $email['email'] = $check->client_email;
                    $email['language'] = $check->language;
                    $email['type'] = 'confirmed';        
                    $this->sendEmail(config('app.url')."/api/v1/reservation/send", $email);  

                    http_response_code(200);
                    //$stripe->index($request);
                    exit();
                else:
                    http_response_code(400);
                    exit();
                endif;
            default:
                echo 'Received unknown event type ' . $event->type;
        }

        http_response_code(200);
        //$stripe->index($request);
    }

    public function paypal(Request $request, PaymentRepository $paymentRepository){
        
        $payload = @file_get_contents('php://input');
        $event = array();
        parse_str($payload, $event);                

        if(isset( $event['payment_status'] ) && $event['payment_status'] == "Completed"):
            $check = $paymentRepository->checkReservation( $event['invoice'] );
            if($check == false):
                http_response_code(400);
                exit();
            endif;

            $exchange = $paymentRepository->getExchange(strtoupper($event['mc_currency']), $check->currency);       
            $data = [
                'id' => $event['invoice'],
                'total' => $event['mc_gross'],
                'currency' => $event['mc_currency'],
                'exchange_rate' => $exchange->exchange_rate,
                'operation' => $exchange->operation,
                'method' => 'PAYPAL',
                'description' => 'PayPal',
                'object' => json_encode($event),
                'reference' => $event['txn_id'],
            ];

            //Guardamos el pago en la base de datos
            $response = $paymentRepository->savePayment($data);
            if( $response ):
                //Envío de correo al cliente...
                $email = [];
                $email['code'] = $check->code;
                $email['email'] = $check->client_email;
                $email['language'] = $check->language;
                $email['type'] = 'update';        
                $this->sendEmail(config('app.url')."/api/v1/reservation/send", $email);  

                http_response_code(200);
                exit();
            else:
                http_response_code(400);
                exit();
            endif;

        endif;
        http_response_code(200);
    }
}