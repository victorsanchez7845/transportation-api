<?php

namespace App\Repositories\Api\Spam;

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
}