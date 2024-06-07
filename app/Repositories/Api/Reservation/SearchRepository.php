<?php

namespace App\Repositories\Api\Reservation;
use App\Traits\CodeTrait;
use App\Traits\TokenTrait;
use App\Models\Reservations;
use App\Models\ReservationsItems;
use App\Models\ReservationsServices;
use App\Models\Sales;
use App\Models\ReservationsFollowUp;
use App\Models\TypesCancellation;
use Illuminate\Support\Facades\DB;

class SearchRepository{
    private $request = [];

    public function setData($request = []){
        $this->request = $request->all();
    }

    public function search(){

        $rez = $this->check();
        if($rez == false){
            return false;
        }

        $sales = $this->getSales( $rez->reservation_id );        
        $payments = $this->getPayments( $rez->reservation_id );
       
        $status = "PENDING";
        if($payments['total'] >= $sales['total']):
            $status = "CONFIRMED";
        endif;
        
        if($rez->pay_at_arrival == 1):
            $status = "CONFIRMED";
        endif;        

        $data = [
            "status" => $status,
            "config" => [
                "id" => $rez->reservation_id,
                "currency" => $rez->currency,
                "language" => $rez->language,
                "rate_group" => $rez->rate_group,
                "is_cancelled" => $rez->is_cancelled,
                "creation_date" => $rez->created_at,
                "destination_id" => $rez->destination_id,
                "payment_exchange_rate" => [
                    "USD_MXN" => 18
                ]
            ],
            "site" => [
                "id" => $rez->site_id,
                "name" => $rez->site_name,
                "logo" => $rez->logo,
                "color" => $rez->color,
                "email" => $rez->email,
                "send_email" => $rez->send_email
            ],
            "client" => [
                'first_name' => $rez->client_first_name,
                'last_name' => $rez->client_last_name,
                'phone' => $rez->client_phone,
                'email' => $rez->client_email,
            ],
            "items" => $this->getItems( $rez->reservation_id ),
            "sales" => $sales,
            "payments" => $payments,
            "history" => $this->getHistory( $rez->reservation_id ),
        ];
        
        return $data;
    }

    public function check(){

        $rez = DB::select('SELECT res.id as reservation_id, res.pay_at_arrival, item.code, res.destination_id, res.created_at, res.client_first_name, res.client_last_name, res.client_email, res.client_phone, res.currency, res.language, res.rate_group, res.is_cancelled, site.id as site_id, site.name as site_name, site.logo, site.color, site.transactional_email as email, site.transactional_email_send as send_email, prov.name as provider_name, prov.transactional_phone as provider_transactional_phone, prov.transactional_emails as provider_transactional_email
                            FROM reservations_items as item 
                                INNER JOIN reservations as res ON res.id = item.reservation_id
                                INNER JOIN sites as site ON site.id = res.site_id
                                INNER JOIN destinations as dest ON dest.id = res.destination_id
                                LEFT JOIN providers as prov ON prov.id = dest.id
                            WHERE item.code = :code AND res.client_email = :email', 
                        [
                            'code' => $this->request['code'],
                            'email' => $this->request['email']
                        ]);
                        
        if(isset( $rez[0] )){
            return $rez[0];
        }else{
            return false;
        }
    }

    public function getItems($id){
        $items = DB::select('SELECT item.code, IFNULL(serv_type_translate.translation, serv_type.name) AS service_name, serv_type.image_url,
        item.from_name, item.from_lat, item.from_lng, item.from_zone, item.to_name, item.to_lat, item.to_lng, item.to_zone, item.distance_time, 
        item.distance_km, item.op_one_status, item.op_one_pickup, item.flight_number, item.passengers, item.op_two_status, item.op_two_pickup, item.is_round_trip,
        zoneInit.name as zone_name_init, zoneEnd.name as zone_name_end
        FROM reservations_items as item            
        INNER JOIN destination_services as serv_type ON serv_type.id = item.destination_service_id
        LEFT JOIN destination_services_translate as serv_type_translate ON serv_type_translate.destination_services_id = serv_type.id AND serv_type_translate.lang = :lang
        INNER JOIN zones as zoneInit ON zoneInit.id = item.from_zone
        INNER JOIN zones as zoneEnd ON zoneEnd.id = item.to_zone
        WHERE item.reservation_id = :id', 
                        [
                            'id' => $id,
                            'lang' => $this->request['language']
                        ]);
        
        if(sizeof($items) <= 0) return [];

        $data = [];
        foreach($items as $key => $value):

            $data[ $value->code ] = [
                "code" => $value->code,
                "service_type_name" => $value->service_name,
                "service_type_image" => $value->image_url,
                //"service_status" => $value->status,
                "passengers" => $value->passengers,
                "pickup" => $value->op_one_pickup,
                "flight_number" => $value->flight_number,
                "is_round_trip" => $value->is_round_trip,
                "departure_pickup" => ((!empty($value->op_two_pickup))? $value->op_two_pickup : NULL),
                "time" => [
                    "time" => $value->distance_time,
                    "distance" => $value->distance_km,
                ],
                "from" => [
                    "id" => $value->from_zone,
                    "destination" => $value->zone_name_init,
                    "name" => $value->from_name,
                    "lat" => $value->from_lat,
                    "lnt" => $value->from_lng,
                ],
                "to" => [
                    "id" => $value->to_zone,
                    "destination" => $value->zone_name_end,
                    "name" => $value->to_name,
                    "lat" => $value->to_lat,
                    "lnt" => $value->to_lng,
                ]
            ];
        endforeach;

        return $data;
    }

    public function getSales($id){
        $data = [
            "total" => 0,
            "items" => []
        ];
        
        $sales = DB::select('SELECT sale.created_at, sale.description, sale.quantity, sale.total, sale_t.name as sale_type_name
                    FROM sales as sale
                        INNER JOIN sales_types AS sale_t ON sale_t.id = sale.sale_type_id
                    WHERE reservation_id = :id', 
                        [
                            'id' => $id
                        ]);
        if(sizeof($sales) <= 0):
            return $data;
        endif;

        $sum = array_reduce($sales, function($carry, $item) {
            return $carry + $item->total;
        }, 0);

        $data['total'] = number_format($sum, 2, ".", "");
        $data['items'] = $sales;
        return $data;
    }

    public function getHistory($id){

        $history = DB::select("SELECT follow_up.text as comment, follow_up.type, follow_up.created_at
                FROM reservations_follow_up as follow_up
            WHERE follow_up.reservation_id = :id AND follow_up.type IN('CLIENT')
            ORDER BY follow_up.created_at ASC", 
            [
                'id' => $id
            ]);
        
        if(sizeof($history) <= 0):
            return [];
        endif;

        return $history;
    }

    public function getPayments($id){
        $data = [
            "total" => 0,
            "items" => []
        ];
        
        $payments = DB::select('SELECT description, total, exchange_rate, currency, operation, payment_method
                    FROM payments
                WHERE reservation_id = :id', 
                        [
                            'id' => $id
                        ]);
        if(sizeof($payments) <= 0):
            return $data;
        endif;

        $sum = array_reduce($payments, function($carry, $item) {
            if( $item->operation == "division"):
                return $carry + ($item->total / $item->exchange_rate);
            endif;
            if( $item->operation == "multiplication"):
                return $carry + ($item->total * $item->exchange_rate);
            endif;
        }, 0);

        $data['total'] = number_format($sum, 2, ".", "");
        $data['items'] = $payments;
        return $data;
    }

    public function getTypesCancellations($request){
        $types = DB::select("SELECT * FROM types_cancellations 
            WHERE status = :status  ORDER BY id ASC", 
            [
                'status' => 1
            ]);
        
        if(sizeof($types) <= 0):
            return [];
        endif;

        return $types;
    }
}