<?php

namespace App\Repositories\Api\Payments;
use Illuminate\Support\Facades\DB;

class MifelRepository{
    private $data = [];
    private $entityID = "8a82941852cad0530152cbb0b9030333";
    private $token = "OGE4Mjk0MTg1MmNhZDA1MzAxNTJjYmIwYmM1ZjAzN2R8UnFRbTlOYWpSaw==";

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
                                    WHERE deleted_at IS NULL
                                    GROUP BY reservation_id
                            ) as s ON s.reservation_id = rez.id
                            LEFT JOIN (
                                SELECT reservation_id,
                                ROUND(SUM(CASE WHEN operation = 'multiplication' THEN total * exchange_rate
                                                            WHEN operation = 'division' THEN total / exchange_rate
                                                    ELSE total END), 2) AS total_payments,
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
            "amount" => $this->getExchange($rez[0]->currency, "MXN", $total),
            "currency" => "MXN",
            "paymentType" => 'DB',
            "transactionCategory" => 'EC',
            "merchantTransactionId" => $rez[0]->id . strtotime(date("Y-m-d H:i:s")),
            "merchantInvoiceId" => $rez[0]->id . strtotime(date("Y-m-d H:i:s"))
        ];

        $available_months = [];
        
        if($rez[0]->currency == "MXN"):
            if($data['amount'] >= 300):
                $available_months[] = 3;
            endif;
            if($data['amount'] >= 600):
                $available_months[] = 6;
            endif;
            if($data['amount'] >= 900):
                $available_months[] = 9;
            endif;
            if($data['amount'] >= 1200):
                $available_months[] = 12;
            endif;
        endif;        

        $items = $this->makeRequest( $data );
        
        if ( preg_match('/^0{3}\.200/', $items['result']['code'] )):
            $response['status'] = true;
            $response['data'] = [
                'url' => $items['id'],
                "promotions" => $available_months
            ];
            return $response;
        else:
            $response['code'] = "mifel";
            $response['message'] = $items['result']['description'];
            return $response;
        endif;        
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

    public function makeRequest($data = []) {
        $items = [
            'entityId' => $this->entityID,
        ];

        $data = array_merge($items, $data);
        $url = "https://eu-test.oppwa.com/v1/checkouts";
    
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                       'Authorization:Bearer '. $this->token ));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data) );
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);// this should be set to true in production
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $responseData = curl_exec($ch);
        if(curl_errno($ch)) {
            return curl_error($ch);
        }
        curl_close($ch);
        return json_decode($responseData, true);
    }
}