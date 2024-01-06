<?php

namespace App\Repositories\Api\Hotels;
use Illuminate\Support\Facades\DB;
use App\Models\Autocomplete;

class HotelsRepository{
    private $data = [];

    public function save($request, $data){

        $request['name'] = str_replace("'", "", $request['name']);
        $request['name'] = str_replace("(", "", $request['name']);
        $request['name'] = str_replace(")", "", $request['name']);
        $request['name'] = str_replace("`", "", $request['name']);
        $request['name'] = str_replace("-", "", $request['name']);
        
        $autocomplete = new Autocomplete();        
        $autocomplete->name = $request['name'];
        $autocomplete->address = $request['address'];
        $autocomplete->latitude = $request['start']['lat'];
        $autocomplete->longitude = $request['start']['lng'];
        $autocomplete->zone_id = $data['start']['data']['zone']['id'];        
        if( $autocomplete->save() ){
            return true;
        }else{
            return false;
        }
    }
}