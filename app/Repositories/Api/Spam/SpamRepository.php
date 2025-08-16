<?php

namespace App\Repositories\Api\Spam;

use App\Models\Reservations;
use App\Models\ReservationsItems;
use App\Models\ReservationsFollowUp;
use Illuminate\Support\Facades\DB;

class SpamRepository{
    public function spamChangeStatus($request){
        $item = ReservationsItems::find($request->id);
        $item->spam = ( $request->status == 1 ? "ACCEPT" : ( $request->status == 2 ? "LATER" : "REJECTED" ) );
        $item->save();

        $follow_up_db = new ReservationsFollowUp;
        $follow_up_db->name = 'System';
        $follow_up_db->text = "Automatización de llamada actualizado a (".( $request->status == 1 ? "ACCEPT" : ( $request->status == 2 ? "LATER" : "REJECTED" ) ).") ";
        $follow_up_db->type = 'HISTORY';
        $follow_up_db->reservation_id = $item->reservation_id;
        $follow_up_db->save();
    }

    public function spamCallCount($request){
        $item = ReservationsItems::find($request->id);
        $item->spam_count = ( $item->spam_count + 1 );
        $item->save();
    }

    public function bookings(){
        $sql = "SELECT res.uuid, res.id as reservation_id, res.is_advanced, res.pay_at_arrival, item.code, res.destination_id, res.created_at, res.client_first_name, res.client_last_name, res.client_email, res.client_phone, res.currency, res.language, res.rate_group, res.is_cancelled
                            FROM reservations_items as item 
                            INNER JOIN reservations as res ON res.id = item.reservation_id";

        $data = DB::select($sql);
        return $data;
    }    
}