<?php

namespace App\Repositories\Api\Payments;
use Illuminate\Support\Facades\DB;

class MifelRepository{
    private $data = [];
    
    private $env = "live";
    private $credentials = [
        "dev" => [
            "URL" => "https://eu-test.oppwa.com/v1/checkouts",
            "entityID" => "8ac7a4ca8fcb8b3e018fd001fb460379",
            "token" => "OGFjN2E0Y2E4ZmNiOGIzZTAxOGZkMDAxNmY2YTAzNzV8ZWZTU2p6N1dGc1BkZVljUg==",
            "descriptor" => "9372793",
        ],
        "live" => [
            "URL" => "https://eu-prod.oppwa.com/v1/checkouts",
            "entityID" => "8acda4ce904f6efe0190522c41fd0f26",
            "token" => "OGFjZGE0Y2U5MDRmNmVmZTAxOTA1MjJiM2NhZTBmMjF8WVNDNXc3eGdaQ3BDUGZhQw==",
            "descriptor" => "9372793",
        ]
    ];

    public function check($request){
        $response = [
            "status" => false,            
        ];

        $this->data = $request->all();        
   
        $rez = DB::select("SELECT rez.id, rez.currency, rez.client_email, rez.client_phone, site.payment_domain,
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
                            WHERE rez.id = :code AND rez.is_cancelled = 0
                            GROUP BY rez.id, site.payment_domain",
                        [
                            'code' => $this->data['id']
                        ]);
        
        if(sizeof($rez) <= 0):
            $response['code'] = "cancelled";
            $response['message'] = "Your reservation has been cancelled, if you want to reactivate it contact us.";
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
            "merchantTransactionId" => $rez[0]->id ."-". strtotime(date("Y-m-d H:i:s")),            
            "customer.email" => $rez[0]->client_email,
            "customer.phone" => $rez[0]->client_phone,
        ];
        
        // echo "<pre>";
        // print_r($data);

        $available_months = [
            'months' => [],
            'table' => [],
            'default' => [
                "price" => number_format($data['amount'],2),
                "currency" => 'MXN'
            ]
        ];
        
        if(false):
            if($rez[0]->currency == "MXN"):
                    $additional = 0;
                    $total = $data['amount'] + $additional;

                    $available_months['months'][] = 1;
                    $available_months['table'][] = [
                        'months' => 1,
                        'monthly' => number_format( $total , 2 ),
                        'financing' => 0,
                        'total' => number_format( $total, 2 ),
                    ];

                if($data['amount'] >= 300):
                    $additional = ($data['amount'] * 0.035);
                    $total = $data['amount'] + $additional;

                    $available_months['months'][] = 3;
                    $available_months['table'][] = [
                        'months' => 3,
                        'monthly' => number_format( $total / 3, 2 ),
                        'financing' => number_format( $additional, 2 ),
                        'total' => number_format( $total, 2 )
                    ];
                endif;
                if($data['amount'] >= 600):
                    $additional = ($data['amount'] * 0.055);
                    $total = $data['amount'] + $additional;

                    $available_months['months'][] = 6;
                    $available_months['table'][] = [
                        'months' => 6,
                        'monthly' => number_format( $total / 6, 2 ),
                        'financing' => number_format( $additional, 2 ),
                        'total' => number_format( $total, 2 )
                    ];

                endif;
                if($data['amount'] >= 900):
                    $additional = ($data['amount'] * 0.085);
                    $total = $data['amount'] + $additional;

                    $available_months['months'][] = 9;
                    $available_months['table'][] = [
                        'months' => 9,
                        'monthly' => number_format( $total / 9, 2 ),
                        'financing' => number_format( $additional, 2 ),
                        'total' => number_format( $total, 2 )
                    ];
                endif;
                if($data['amount'] >= 1200):
                    $additional = ($data['amount'] * 0.115);
                    $total = $data['amount'] + $additional;

                    $available_months['months'][] = 12;
                    $available_months['table'][] = [
                        'months' => 12,
                        'monthly' => number_format( $total / 12, 2 ),
                        'financing' => number_format( $additional, 2 ),
                        'total' => number_format( $total, 2 )
                    ];
                endif;
            else:
                $additional = 0;
                $total = $data['amount'] + $additional;

                $available_months['months'][] = 1;
                $available_months['table'][] = [
                    'months' => 1,
                    'monthly' => number_format( $total , 2 ),
                    'financing' => 0,
                    'total' => number_format( $total, 2 ),
                ];
            endif;
        endif;

        //Eliminar para el plan de pagos fijos
        $additional = 0;
        $total = $data['amount'] + $additional;

        $available_months['months'][] = 1;
        $available_months['table'][] = [
            'months' => 1,
            'monthly' => number_format( $total , 2 ),
            'financing' => 0,
            'total' => number_format( $total, 2 ),
        ];
        //Eliminar para el plan de pagos fijos

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

    public function validate($request){

        $url = $this->credentials[ $this->env ]['URL']."/".$request->id."/payment";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true); // Esto debería estar en true en producción
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);        

        $responseData = curl_exec($ch);

        if (curl_errno($ch)) {
            return curl_error($ch);
        }
        
        $data = json_decode($responseData, true);

        if( !isset( $data['resultDetails']['acquirerResponse'] ) ){
            return false;
        }

        if($data['resultDetails']['acquirerResponse'] == "00"){
            return true;
        }else{
            return false;
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

    public function makeRequest($data = []) {
        $items = [
            'entityId' => $this->credentials[ $this->env ]['entityID'],
            'descriptor' => $this->credentials[ $this->env ]['descriptor'],            
        ];

        if($this->env == "dev"):
            $items['testMode'] = "EXTERNAL";
        endif;
                
        $data = array_merge($items, $data);
        $url = $this->credentials[ $this->env ]['URL'];
    
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                       'Authorization:Bearer '. $this->credentials[ $this->env ]['token'] ));
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