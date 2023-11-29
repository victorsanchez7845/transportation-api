<?php

namespace App\Repositories\Api\Quotation;

class AutocompleteRepository{

    public function search($request){

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
