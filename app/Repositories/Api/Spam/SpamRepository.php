<?php

namespace App\Repositories\Api\Spam;

use App\Models\ReservationsItems;
use Illuminate\Support\Facades\DB;

class SpamRepository{
    public function spamChangeStatus($request){
        $item = ReservationsItems::find($request->id);
        $item->spam = ( $request->status == 1 ? "ACCEPT" : ( $request->status == 2 ? "LATER" : "REJECTED" ) );
        $item->save();
    }

    public function spamCallCount($request){
        $item = ReservationsItems::find($request->id);
        $item->spam_count = ( $item->spam_count + 1 );
        $item->save();
    }
}