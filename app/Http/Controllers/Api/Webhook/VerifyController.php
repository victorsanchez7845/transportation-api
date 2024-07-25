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

    public function mifel(Request $request, PaymentRepository $paymentRepository){        
        
        $payload = @file_get_contents('php://input');
        $event = json_decode($payload, true);

        if(!isset( $event['encryptedBody'] )):
            return response()->json([
                'error' => [
                    'code' => 'params',
                    'message' => 'encryptedBody are needed'
                ]
            ], 400);            
        endif;
        
        $key_from_configuration = "DE11A77EB43C76E68A84631C6A716B5AC9C3499148CE27DF70F72CA511FEA8B0";  //KEY de desarrollo: A316D872053A63C8BEDE94971DA4CFEA8F7B7B0927741DA7033965C62471FD9D
        $iv_from_http_header = $request->header('x-initialization-vector');
        $auth_tag_from_http_header = $request->header('x-authentication-tag');
        $http_body = $event['encryptedBody'];

        $key = hex2bin($key_from_configuration);
        $iv = hex2bin($iv_from_http_header);
        $auth_tag = hex2bin($auth_tag_from_http_header);
        $cipher_text = hex2bin($http_body);
        
        $decrypt_data = openssl_decrypt($cipher_text, "aes-256-gcm", $key, OPENSSL_RAW_DATA, $iv, $auth_tag);
        if($decrypt_data == false):
            return response()->json([
                'error' => [
                    'code' => 'decrypt',
                    'message' => 'It was not possible to decrypt the data'
                ]
            ], 400);
        endif;

        return response()->json([
            'error' => [
                'code' => 'payment_type',
                'message' => 'Is not successful'
            ]
        ], 400);
        
        $result = json_decode($decrypt_data, true);
        if($result['type'] == "PAYMENT"):
            //$result['payload']['merchantTransactionId'] = "16318-12312312312312"; //ELIMINAR EN PRODUCCIÓN
            
            if (strpos($result['payload']['result']['code'], '000.100') === false) {
                return response()->json([
                    'error' => [
                        'code' => 'payment_type',
                        'message' => 'Is not successful'
                    ]
                ], 400);
            }
            
            /*if($result['payload']['result']['code'] != "000.100.110"){
                return response()->json([
                    'error' => [
                        'code' => 'payment_type',
                        'message' => 'The notification is not of type PAYMENT'
                    ]
                ], 400);
            }*/
            
            $transactionID = explode("-", $result['payload']['merchantTransactionId']);
            $id = $transactionID[0];

            $check = $paymentRepository->checkReservation( $id );
            if($check == false):
                return response()->json([
                    'error' => [
                        'code' => 'not_found',
                        'message' => 'Reservation not found'
                    ]
                ], 400);
            endif;

            $exchange = $paymentRepository->getExchange("MXN", $check->currency);
            
            $data = [
                'id' => $id,
                'total' => $result['payload']['amount'],
                'currency' => "MXN",
                'exchange_rate' => $exchange->exchange_rate,
                'operation' => $exchange->operation,
                'method' => 'MIFEL',
                'description' => 'Automated payment',
                'object' => $decrypt_data,
                'reference' => $result['payload']['id'],
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

                return response()->json(['OK'], 200);
            else:
                return response()->json([
                    'error' => [
                        'code' => 'error',
                        'message' => '¡Error saving payment!'
                    ]
                ], 400);
            endif;

        else:
            return response()->json([
                'error' => [
                    'code' => 'payment_type',
                    'message' => 'The notification is not of type PAYMENT'
                ]
            ], 400);
        endif;
    }
}