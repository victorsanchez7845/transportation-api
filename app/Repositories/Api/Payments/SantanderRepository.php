<?php

namespace App\Repositories\Api\Payments;
use Illuminate\Support\Facades\DB;
use App\Services\AESCrypto;

class SantanderRepository{
    private $data = [];
    
    private $env = "live";
    private $credentials = [
        "dev" => [
            "URL" => "https://sandboxpo.mit.com.mx/gen",
            "id_company" => "SNBX",
            "id_branch" => "01SNBXBRNCH",
            "user" => "SNBXUSR0123",
            "password" => "SECRETO",
            "seed" => "5DCC67393750523CD165F17E1EFADD21",
            "data0" => "SNDBX123",
        ],
        "live" => [
            "URL" => "https://bc.mitec.com.mx/p/gen",
            "id_company" => "8J8C",
            "id_branch" => "0002",
            "user" => "",
            "password" => "",
            "seed" => "",
            "data0" => "9265657419",
        ]
    ];

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
            "total" => $this->getExchange($rez[0]->currency, "MXN", $total),
            "currency" => "MXN",
            "payment_domain" => $rez[0]->payment_domain
        ];

        $this->init();

        $xml = $this->makeXML( $data );        

        $encrypted = $this->buildXML( $xml );        

        $xml = $this->makeRequest( $encrypted );
        // dd($data, $encrypted, $xml);
        
        if($xml == false):
            $response['code'] = "mit_request_error";
            $response['message'] = "Error when generating the link";
            return $response;
        endif;

        $xmlObject = simplexml_load_string($xml);
        $xmlObject = json_decode(json_encode($xmlObject), true);

        if($xmlObject['cd_response'] != "success"):
            $response['code'] = "mit_link_error";
            // $response['message'] = "Error when generating the link";
            $response['message'] = $xmlObject['nb_response'];
            return $response;
        endif;

        $response['status'] = true;
        $response['data'] = [
            'url' => $xmlObject['nb_url']
        ];        
        return $response;
    }

    public function init(){
        if( $this->env == "live" ):
            $this->credentials[ $this->env ]['user'] = config('services.santander.user');
            $this->credentials[ $this->env ]['password'] = config('services.santander.password');
            $this->credentials[ $this->env ]['seed'] = config('services.santander.seed');
        endif;
    }

    public function makeXML($data = []){

        $id_company = $this->credentials[ $this->env ]['id_company'];
        $id_branch = $this->credentials[ $this->env ]['id_branch'];
        $user = $this->credentials[ $this->env ]['user'];
        $pwd = $this->credentials[ $this->env ]['password'];

        $reference = $this->data['id']."-".strtotime(date("Y-m-d H:i:s"));
        $total = $data['total'];
        $currency = $data['currency'];

        $label = (( $this->data['language'] == "en" )?'Service type':'Tipo de servicio');
        $value = (( $this->data['language'] == "en" )?'Transportation':'Transportación');

        $xml = <<<EOT
                    <?xml version="1.0" encoding="UTF-8"?>
                    <P>
                        <business>
                            <id_company>$id_company</id_company>
                            <id_branch>$id_branch</id_branch>
                            <user>$user</user>
                            <pwd>$pwd</pwd>
                        </business>
                        <nb_fpago>COD</nb_fpago>
                        <url>
                            <reference>$reference</reference>
                            <amount>$total</amount>
                            <moneda>$currency</moneda>
                            <canal>W</canal>
                            <omitir_notif_default>0</omitir_notif_default>                            
                            <st_correo>1</st_correo>                            
                            <datos_adicionales>
                                <data id="1" display="true">
                                    <label>$label</label>
                                    <value>$value</value>
                                </data>
                            </datos_adicionales>
                            <nb_fpago>TCD,APY</nb_fpago>                            
                            <version>IntegraWPP</version>
                        </url>
                    </P>
                EOT;

        return AESCrypto::encrypt( $xml, $this->credentials[ $this->env ]['seed'] );
    }

    public function buildXML($xml_encrypted = ''){
        $data0 = $this->credentials[ $this->env ]['data0'];

        $xml = '<pgs><data0>'.$data0.'</data0><data>'.$xml_encrypted.'</data></pgs>';
        
        return $xml;
    }

    public function makeRequest( $xml ){
        
        $postFields = [
            'xml' => $xml
        ];

        $ch = curl_init();

        // Configuramos la URL y las opciones del POST
        curl_setopt($ch, CURLOPT_URL, $this->credentials[$this->env]['URL'] );
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query( $postFields ) );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Definimos los headers
        $headers = [
            'Cache-Control: no-cache',
            'Content-Type: application/x-www-form-urlencoded'
        ];
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        // Ejecutamos la solicitud
        $response = curl_exec($ch);
        curl_close($ch);
        
        return AESCrypto::decrypt( $response, $this->credentials[ $this->env ]['seed']);
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