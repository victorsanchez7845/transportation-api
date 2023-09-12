<?php

namespace App\Repositories\Api\Webhook;
use App\Models\Sales;
use App\Models\ReservationsFollowUp;
use Illuminate\Support\Facades\DB;
use App\Models\Payments;

class PaymentRepository{

    private $request = [];

    public function checkReservation($id){
                    $rez = DB::select('SELECT rez.id, rez.client_email, rez.language, it.code, rez.currency
                                            FROM reservations as rez
                                        INNER JOIN reservations_items as it ON it.reservation_id = rez.id
                                         WHERE rez.id = :code
                                        LIMIT 1', [
                                     'code' => $id
                                    ]);

        if(isset( $rez[0] )){
            return $rez[0];
        }else{
            return false;
        }        
    }

    public function savePayment($data){
        $sales_db = new Payments;
        $sales_db->description = $data['description'];
        $sales_db->total = $data['total'];
        $sales_db->exchange_rate = $data['exchange_rate'];
        $sales_db->operation = $data['operation'];
        $sales_db->payment_method = $data['method'];
        $sales_db->currency = $data['currency'];
        $sales_db->object = $data['object'];
        $sales_db->reservation_id = $data['id'];
        $sales_db->reference = $data['reference'];

        if($sales_db->save()){
            $follow_up_db = new ReservationsFollowUp;
            $follow_up_db->name = 'System';
            $follow_up_db->text = 'Pago realizado ('.$data['description'].')';
            $follow_up_db->type = 'INTERN';
            $follow_up_db->reservation_id = $data['id'];
            $follow_up_db->save();
            return true;
        }else{
            return false;
        }
    }

    public function getExchange($origin, $destination = "MXN"){        
        $items = DB::select('SELECT operation, exchange_rate
                                FROM payments_exchange_rate
                            WHERE origin = :origin AND destination = :destination
                            LIMIT 1', 
                        [
                            'origin' => $origin,
                            'destination' => $destination
                        ]);

        return $items[0];
    }    

}