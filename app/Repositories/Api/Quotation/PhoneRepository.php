<?php

namespace App\Repositories\Api\Quotation;
use Illuminate\Support\Facades\DB;

class PhoneRepository{
    private $request = [];

    public function get($request = []){
        return DB::select('SELECT phonecode as code, nicename as name FROM countries ORDER BY nicename ASC');        
    }
}