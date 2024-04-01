<?php

namespace App\Repositories\Api\Quotation;
use Illuminate\Support\Facades\DB;
use App\Models\Autocomplete;
use App\Models\AutocompleteTranslate;

class AutocompleteRepository{

    public function searcDB($request){
   
        $term = preg_replace('/[^a-zA-Z0-9_ ]/', '', $request->keyword);

        $data = DB::select("
                    SELECT
                        aut.id, aut.latitude, aut.longitude,
                    CASE
                        WHEN aut.name LIKE '%{$term}%' THEN aut.name
                        ELSE aut_trans.name
                    END AS name,
                        aut.address
                    FROM autocomplete as aut
                    LEFT JOIN autocomplete_translate as aut_trans ON aut_trans.autocomplete_id = aut.id
                    WHERE aut.name LIKE '%{$term}%' OR aut_trans.name LIKE '%{$term}%' limit 25");        

        if( sizeof($data) <= 0 ){
            return false;
        }        

        $items = [];
        foreach($data as $key => $value):
            $items[] = [
                "name" => $value->name,
                "address" => $value->address,
                "type" => "DEFAULT",
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

        return false;
        
        $data = $this->send($request->keyword);
        if($data == false){
            return false;
        }

        $items = [];
        foreach($data as $key => $value):
            $items[] = [
                "name" => $value['name'],
                "address" => $value['formatted_address'],
                "type" => "GCP",
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
