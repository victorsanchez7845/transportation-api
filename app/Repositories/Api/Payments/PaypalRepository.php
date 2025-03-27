<?php

namespace App\Repositories\Api\Payments;
use Illuminate\Support\Facades\DB;

class PaypalRepository{
    private $data = [];
    private $PayPal = [
        "clientId" => "AZxVyIFCk6LofbbQ1t6Uk7mIoEkE2iZ0lADH4sSpu-znHTpBR1Ce2ia7mTtk9kA2nTzcd9GcCvK3Gp_P",
        "clientSecret" => "EG4cBO1xslWKbcpzLsnnI8f7TlvYl9syR4Yamrjd9E-oMxZS7nIl7hqTatQHJKDVXGSbmZ4fMt_nhUhH",
        "URL" => "https://api-m.sandbox.paypal.com"
    ];

    public function check($request, $type = 0){
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
                                    WHERE deleted_at IS NULL AND sales.sale_type_id <> 3
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
            "total" => $this->getExchange($rez[0]->currency, $rez[0]->currency, $total),
            "currency" => $rez[0]->currency,
            "payment_domain" => $rez[0]->payment_domain
        ];

      

        try{

            $merchantId = config('services.paypal.merchant'); // Merchant ID
            if($type == 1):
                // Nueva cuenta de paypal | cabrivieramaya@gmail.com
                // $merchantId = "4YFWSB3V6T8P2";
            endif;

            $currency = $data['currency'];
            $total = $data['total'];
            $description = (($request->language == "en")?'Transportation service':'Servicio de transportación');
            $successUrl = $data['payment_domain'] . $request->success_url; // Reemplaza con la URL de éxito de tu sitio
            $returnUrl = $data['payment_domain'] . $request->cancel_url; // Reemplaza con la URL de retorno de tu sitio

            // Construye la URL de PayPal
            $paypalURL = 'https://www.paypal.com/cgi-bin/webscr?cmd=_xclick';
            //$paypalURL = 'https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_xclick';
            $paypalURL .= '&business=' . urlencode($merchantId);
            $paypalURL .= '&currency_code=' . urlencode($currency);
            $paypalURL .= '&amount=' . urlencode($total);
            $paypalURL .= '&item_name=' . urlencode($description); 
            $paypalURL .= '&return=' . urlencode($successUrl);
            $paypalURL .= '&cancel_return=' . urlencode($returnUrl);
            $paypalURL .= '&notify_url=' . urlencode('https://api.caribbean-transfers.com/api/v1/ipn/paypal');
            $paypalURL .= '&invoice='.$rez[0]->id;

            $response['status'] = true;
            $response['data'] = ['url' => $paypalURL];
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

    public function orders($request, $type = 0){
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
                                    WHERE deleted_at IS NULL AND sales.sale_type_id <> 3
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
            "total" => $this->getExchange($rez[0]->currency, $rez[0]->currency, $total),
            "currency" => $rez[0]->currency,
            "payment_domain" => $rez[0]->payment_domain
        ];

        $description = (($request->language == "en")?'Transportation service':'Servicio de transportación');

        $lang = "en-US";
        if($request->language == "es"):
            $lang = "es-MX";
        endif;
        
        $itemData = [
            "intent" => "CAPTURE", 
            "purchase_units" => [
                  [
                    "reference_id" => $rez[0]->id,
                    "invoice_id" => $rez[0]->id,
                    "items" => [
                        [
                           "name" => $description, 
                           "description" => '', 
                           "quantity" => 1, 
                           "unit_amount" => [
                              "currency_code" => $data['currency'], 
                              "value" => $data['total']
                           ] 
                        ] 
                    ], 
                    "amount" => [
                        "currency_code" => $data['currency'], 
                        "value" => $data['total'], 
                        "breakdown" => [
                            "item_total" => [
                                "currency_code" => $data['currency'], 
                                "value" => $data['total']
                            ]
                        ] 
                    ]
                ]
            ], 
            "application_context" => [
                "user_action" => "PAY_NOW",                
                "return_url" => $data['payment_domain'] . $request->success_url, 
                "cancel_url" => $data['payment_domain'] . $request->cancel_url,
                "locale" => $lang
            ] 
        ];
        
        $token = $this->getToken();
        if($token == false){
            $response['code'] = "token";
            $response['message'] = "Error handling token";
            return $response;
        }

        $paypalURL = $this->createOrder($token, $itemData);
        if($paypalURL == false){
            $response['code'] = "order";
            $response['message'] = "Error making order";
            return $response;
        }
        
        $response['status'] = true;
        $response['data'] = ['url' => $paypalURL];
        return $response;
    }

    public function ordersCapture($request){        

        $token = $this->getToken();
        if($token == false){
            $response['code'] = "token";
            $response['message'] = "Error handling token";
            return $response;
        }

        $captureUrl = $this->PayPal['URL'] . "/v2/checkout/orders/{$request->id}/capture";        

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $captureUrl);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
            "Authorization: Bearer $token"
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $captureResponse = curl_exec($ch);
        curl_close($ch);

        $response['status'] = true;
        $response['data'] = json_decode($captureResponse, true);
        return $response;

    }

    public function getToken(){        
        $clientId = $this->PayPal['clientId'];
        $clientSecret = $this->PayPal['clientSecret'];

        // URL del endpoint de autenticación
        $url = $this->PayPal['URL'] . "/v1/oauth2/token";

        // Inicializar cURL
        $ch = curl_init($url);

        // Configurar las opciones de cURL
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_USERPWD, "$clientId:$clientSecret"); // Autenticación básica
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Accept: application/json",
            "Accept-Language: en_US"
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials");

        // Ejecutar la solicitud
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Manejar la respuesta
        if ($httpCode == 200) {
            $data = json_decode($response, true);
            return $data['access_token'];
        } else {
            return false;
        }
    }

    public function createOrder($token, $itemData){

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->PayPal['URL'] . "/v2/checkout/orders");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
            "Authorization: Bearer $token"
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($itemData));

        $response = curl_exec($ch);
        curl_close($ch);

        // Mostrar respuesta
        $orderResponse = json_decode($response, true);
        
        if (isset($orderResponse['id'])) {
            return $orderResponse['id'];
        } else {
            return false;
        }
    }

}