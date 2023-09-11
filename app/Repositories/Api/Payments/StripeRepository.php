<?php

namespace App\Repositories\Api\Payments;
use Illuminate\Support\Facades\DB;

class StripeRepository{
    private $data = [];

    public function check($request){
        $response = [
            "status" => false,            
        ];

        $this->data = $request->all();        
   
        $rez = DB::select("SELECT rez.id, rez.currency, site.payment_domain,
                            ROUND( COALESCE(SUM( s.total_sales ), 0), 2) as total_sales,
                            ROUND( COALESCE(SUM( p.total_payments ), 0), 2) as total_payments
                            FROM reservations AS rez
                            LEFT JOIN (
                                    SELECT reservation_id,  ROUND( COALESCE(SUM(total), 0), 2) as total_sales
                                    FROM sales
                                    GROUP BY reservation_id
                            ) as s ON s.reservation_id = rez.id
                            LEFT JOIN (
                                    SELECT reservation_id, ROUND( COALESCE(SUM(total * exchange_rate), 0), 2) as total_payments,
                                    GROUP_CONCAT(DISTINCT payment_method ORDER BY payment_method ASC SEPARATOR ',') AS payment_type_name
                                    FROM payments
                                    GROUP BY reservation_id
                            ) as p ON p.reservation_id = rez.id
                            INNER JOIN sites as site ON site.id = rez.site_id
                            WHERE rez.id = :code
                            GROUP BY rez.id, site.payment_domain",
                        [
                            'code' => $this->data['id']
                        ]);
        
        if(sizeof($rez) <= 0):
            $response['code'] = "not_found";
            $response['message'] = "Reservation not found";
            return $response;
        endif;

        $total = $rez[0]->total_sales - $rez[0]->total_payments;
        if($total <= 0):
            $response['code'] = "payments";
            $response['message'] = "No payments to be made";
            return $response;
        endif;
        
        $data = [
            "total" => $this->getExchange($rez[0]->currency, "MXN", $total),
            "currency" => "MXN",
            "payment_domain" => $rez[0]->payment_domain
        ];

        try{
            $stripe = new \Stripe\StripeClient(config('services.stripe.key'));
            $product = $stripe->products->create([
                'name' => (($request->language == "en")?'Transportation service':'Servicio de transportación'),
            ]);

            $price = $stripe->prices->create([
                'unit_amount' => ($data['total'] * 100),
                'currency' => 'mxn',                
                'product' => $product->id
            ]);

            $checkout_session = $stripe->checkout->sessions->create([
                'line_items' => [[
                    'price' => $price->id,
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => $data['payment_domain'] . $request->success_url,
                'cancel_url' => $data['payment_domain'] . $request->cancel_url,
                'payment_intent_data' => [
                    "metadata" => [ "reservation_id" => $request->id ]
                ]
            ]);

            $response['status'] = true;
            $response['data'] = ['url' => $checkout_session->url];
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