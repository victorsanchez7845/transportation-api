<?php

namespace App\Repositories\Api\Reservation;
use App\Traits\CodeTrait;
use App\Traits\TokenTrait;
use App\Models\Reservations;
use App\Models\ReservationsItems;
use App\Models\ReservationsServices;
use App\Models\Sales;
use App\Models\ReservationsFollowUp;
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

        $data = [
            "status" => $status,
            "config" => [
                "currency" => $rez->currency,
                "language" => $rez->language,
                "rate_group" => $rez->rate_group,
                "is_cancelled" => $rez->is_cancelled,
                "creation_date" => $rez->created_at,
                "site" => $rez->site_name,
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

        $rez = DB::select('SELECT res.id as reservation_id, item.code, res.created_at, res.client_first_name, res.client_last_name, res.client_email, res.client_phone, res.currency, res.language, res.rate_group, res.is_cancelled, site.name as site_name
                            FROM reservations_items as item 
                                INNER JOIN reservations as res ON res.id = item.reservation_id
                                INNER JOIN sites as site ON site.id = res.site_id
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
            serv.from_name, serv.from_lat, serv.from_lng, serv.from_zone, serv.to_name, serv.to_lat, serv.to_lng, serv.to_zone, serv.distance_time, serv.distance_km, serv.status, serv.pickup, serv.flight_number, serv.passengers,
            zoneInit.name as zone_name_init, zoneEnd.name as zone_name_end
            FROM reservations_items as item
            INNER JOIN reservations_services as serv ON serv.reservation_item_id = item.id
            INNER JOIN destination_services as serv_type ON serv_type.id = serv.destination_service_id
            LEFT JOIN destination_services_translate as serv_type_translate ON serv_type_translate.destination_services_id = serv_type.id AND serv_type_translate.lang = :lang
            INNER JOIN zones as zoneInit ON zoneInit.id = serv.from_zone
            INNER JOIN zones as zoneEnd ON zoneEnd.id = serv.to_zone
            WHERE item.reservation_id = :id
            ORDER BY item.id ASC, serv.pickup ASC', 
                        [
                            'id' => $id,
                            'lang' => $this->request['language']
                        ]);
        if(sizeof($items) <= 0) return [];
        
        $data = [];
        foreach($items as $key => $value):
            if( !isset($data[ $value->code ]) ):
                $data[ $value->code ] = [];
            endif;

            $data[ $value->code ][] = [
                "code" => $value->code,
                "service_type_name" => $value->service_name,
                "service_type_image" => $value->image_url,
                "service_status" => $value->status,
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

        $data['total'] = $sum;
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
        
        $payments = DB::select('SELECT description, total, exchange_rate, payment_method, request_payment
                    FROM payments
                WHERE reservation_id = :id', 
                        [
                            'id' => $id
                        ]);
        if(sizeof($payments) <= 0):
            return $data;
        endif;

        $sum = array_reduce($payments, function($carry, $item) {
            return $carry + $item->total;
        }, 0);

        $data['total'] = $sum;
        $data['items'] = $payments;
        return $data;
    }
}