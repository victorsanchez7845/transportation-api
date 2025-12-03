<?php

namespace App\Http\Controllers\Api\Webhook;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Api\Payments\PaypalRepository;
use App\Repositories\Api\Webhook\PaymentRepository;
use Illuminate\Support\Facades\Validator;
use App\Traits\FunctionsTrait;
use App\Services\AESCrypto;
use App\Traits\LoggerTrait;

class VerifyController extends Controller
{
    use FunctionsTrait, LoggerTrait;
    
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
                    'method' => 'STRIPE',
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

    public function paypal(Request $request, PaymentRepository $paymentRepository, PaypalRepository $paypalRepository){
        try {
            $this->createLog([
                'type' => 'info',
                'category' => 'paypal_debug',
                'message' => 'API. ENTRA WEBHOOK!!',
            ]);
            
            $payload = @file_get_contents('php://input');
            $event = array();
            parse_str($payload, $event);                
    
            try {
                $this->createLog([
                    'type' => 'info',
                    'category' => 'paypal_debug',
                    'message' => 'API. webhook data: ' . json_encode($event),
                ]);
            } catch(\Exception $e) {
                $this->createLog([
                    'type' => 'error',
                    'category' => 'paypal_debug',
                    'message' => 'API. Error en webhook al capturar log',
                    'exception' => $e
                ]);
            }
            
            try {
                // Esto porque aún no se averigua cómo antes funcionaba que $event fuera un json correcto
                // Se optó por no borrar el código anterior por si acaso, pero se agrega la nueva capa que sí funciona
                $eventJson = json_decode($payload, true);
            } catch(\Exception $e) {}

            if(isset( $event['payment_status'] ) && $event['payment_status'] == "Completed") { // Código anterior (no se sabe a qué evento respondía realmente)
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
            }
            else if($eventJson['event_type'] === 'CHECKOUT.ORDER.APPROVED' && $eventJson['status'] === 'SUCCESS') {
                $order = $paypalRepository->getOrder($eventJson['resource']['id']);

                foreach($order['purchase_units'] as $unit) {
                    $check = $paymentRepository->checkReservation( $unit['reference_id'] );
                    if($check == false):
                        http_response_code(400);
                        exit();
                    endif;

                    foreach($unit['payments']['captures'] as $capture) {
                        $exchange = $paymentRepository->getExchange(strtoupper($capture['amount']['currency_code']), $check->currency);
                        $data = [
                            'id' => $unit['reference_id'],
                            'total' => $capture['amount']['value'],
                            'currency' => $capture['amount']['currency_code'],
                            'exchange_rate' => $exchange->exchange_rate,
                            'operation' => $exchange->operation,
                            'method' => 'PAYPAL',
                            'description' => 'PayPal',
                            'object' => json_encode($event),
                            'reference' => $capture['id'],
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
                    }
                }
            }
        } catch(\Exception $e) {
            $this->createLog([
                'type' => 'error',
                'category' => 'paypal_debug',
                'message' => 'API. Error general en webhook de paypal',
                'exception' => $e
            ]);
        http_response_code(400);
        }
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
        
        $result = json_decode($decrypt_data, true);
        if($result['type'] == "PAYMENT"):
            //$result['payload']['merchantTransactionId'] = "16318-12312312312312"; //ELIMINAR EN PRODUCCIÓN
            if( $result['payload']['paymentType'] != "DB" ){
                return response()->json([
                    'error' => [
                        'code' => 'payment_type',
                        'message' => 'Incorrect code type'
                    ]
                ], 400);
            }


            if ( $result['payload']['resultDetails']['acquirerResponse'] != 00 ) {
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

    public function mit(Request $request, PaymentRepository $paymentRepository){

        $strResponse = $request->input('strResponse');
        if( $strResponse == NULL ):
            return response()->json([
                'error' => [
                    'code' => 'strResponse',
                    'message' => 'strResponse is needed'
                ]
            ], 400);
        endif;        

        $xml = AESCrypto::decrypt( $strResponse, config('services.santander.seed') );
        //$xml = AESCrypto::decrypt( $event['strResponse'], '5DCC67393750523CD165F17E1EFADD21' ); //COMENTAR EN PRODUCCIÓN  

        $xmlObject = simplexml_load_string($xml);
        $xmlObject = json_decode(json_encode($xmlObject), true);

        if($xmlObject['response'] == "approved"):
            
            //$xmlObject['reference'] = "36949-".strtotime(date("Y-m-d H:i:s")); //COMENTAR EN PRODUCCIÓN
        
            $id = explode("-", $xmlObject['reference']);
            $id = $id[0];           

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
                'total' => $xmlObject['amount'],
                'currency' => "MXN",
                'exchange_rate' => $exchange->exchange_rate,
                'operation' => $exchange->operation,
                'method' => 'SANTANDER',
                'description' => 'Automated payment',
                'object' => json_encode($xmlObject),
                'reference' => $xmlObject['reference'],
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
                    'code' => 'not_approved',
                    'message' => 'The response is: '.$xmlObject['response']
                ]
            ], 400);
        endif;
    }
}