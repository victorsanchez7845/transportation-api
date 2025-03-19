<?php

namespace App\Repositories\Api\Payments;
use Illuminate\Support\Facades\DB;

class StripeElementsRepository{
    private $data = [];

    public function check($request){
        $response = [
            "status" => false,            
        ];

        $this->data = $request->all();
        
        $data = [
            "total" => $this->getExchange($this->data['currency'], "MXN", $this->data['total']),
            "currency" => "MXN"
        ];       

        try{
            $key = config('services.stripe.key');
            $stripe = new \Stripe\StripeClient( $key );
    
            $items = $stripe->paymentIntents->create([
                'amount' => ($data['total'] * 100),
                'currency' => 'mxn',
                'automatic_payment_methods' => ['enabled' => true],
            ]);

            $response['status'] = true;
            $response['data'] = ['id' => $items['client_secret']];
            return $response;            

        }catch(\Exception $e){
            $response['code'] = "stripe";
            $response['message'] = $e->getMessage();
            return $response;
        }

    }

    public function getExchange($origin, $destination = "MXN", $total = 0){
        $items = DB::select('SELECT operation, exchange_rate
                                FROM payments_exchange_rate
                            WHERE origin = :origin AND destination = :destination
                            LIMIT 1', 
                        [
                            'origin' => $origin,
                            'destination' => $destination
                        ]);

        if($items[0]->operation == "multiplication"):
            return number_format( ( $items[0]->exchange_rate * $total ) , 2, '.', '');            
        else:
            return number_format( ( $total / $items[0]->exchange_rate ) , 2, '.', '');       
        endif;
    }

}