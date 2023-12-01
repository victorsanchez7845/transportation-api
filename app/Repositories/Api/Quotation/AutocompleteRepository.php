<?php

namespace App\Repositories\Api\Quotation;
use Illuminate\Support\Facades\DB;

class AutocompleteRepository{

    public function searcDB($request){
        
        $term = preg_replace('/[^a-zA-Z0-9_ ]/', '', $request->keyword);

        $data = DB::table('autocomplete')
            ->select('name', 'address', 'latitude', 'longitude')
            ->where('name', 'LIKE', '%' . $term . '%')
            ->get();

        if( sizeof($data) <= 0 ){
            return false;
        }        

        $items = [];
        foreach($data as $key => $value):
            $items[] = [
                "name" => $value->name,
                "address" => $value->address,
                "geo" => [
                    "lat" => $value->latitude,
                    "lng" => $value->longitude
                ]
            ];
        endforeach;

        return $items;

    }

    public function search($request){

        $searchDB = $this->searcDB($request);
        if($searchDB != false):
            return $searchDB;
        endif;

        $data = $this->send($request->keyword);
        if($data == false){
            return false;
        }

        $items = [];
        foreach($data as $key => $value):
            $items[] = [
                "name" => $value['name'],
                "address" => $value['formatted_address'],
                "geo" => [
                    "lat" => $value['geometry']['location']['lat'],
                    "lng" => $value['geometry']['location']['lng'],
                ]
            ];
        endforeach;

        return $items;
    }
    
    public function send($keyword = ''){
        
        $api_url = "https://maps.googleapis.com/maps/api/place/textsearch/json?query=". urlencode($keyword) . "&location=21.0442704,-86.8747223&radius=400&key=".config('services.maps.key');
    
        $curl = curl_init();        
        curl_setopt($curl, CURLOPT_URL, $api_url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);        
        $response = curl_exec($curl);        
        if ($response === false) {
            curl_close($curl);
            return false;
        }

        $data = json_decode($response, true);
        if($data['status'] != "OK"){
            return false;
        }

        return $data['results'];
    }
}
