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

        //$payload = @file_get_contents('php://input');
        //$charge = json_decode($payload);
        //$paymentIntent = $charge->object;                

        // The library needs to be configured with your account's secret key.
        // Ensure the key is kept out of any version control system you might be using.
        //$stripe = new \Stripe\StripeClient(config('services.stripe.key'));

        // This is your Stripe CLI webhook secret for testing your endpoint locally.
        //$endpoint_secret = config('services.stripe.webhook_secret');

        //$sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
        
        $payload = @file_get_contents('php://input');
        $event = json_decode($payload);

        /*try {
            $event = \Stripe\Webhook::constructEvent(
                $payload, $sig_header, $endpoint_secret
            );
        } catch(\UnexpectedValueException $e) {
            http_response_code(400);
            exit();
        } catch(\Stripe\Exception\SignatureVerificationException $e) {
            http_response_code(400);
            exit();
        }*/

        // Handle the event
        switch ($event->type) {
            case 'charge.succeeded':
                $paymentIntent = $event->data->object;

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
                    $email['type'] = 'update';        
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
}