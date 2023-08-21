<?php

namespace App\Repositories\Api\Quotation;
use Illuminate\Support\Facades\DB;

class PhoneRepository{
    private $request = [];

    public function get($request = []){
        return DB::select('SELECT code, name, phone FROM countries');        
    }
}