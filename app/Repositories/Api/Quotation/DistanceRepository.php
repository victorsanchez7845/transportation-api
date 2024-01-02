<?php

namespace App\Repositories\Api\Quotation;
use Illuminate\Support\Facades\DB;
use Location\Coordinate;
use Location\Polygon;

class DistanceRepository{
    private $request = [];

    public function get($request = [], $get = true){
        $this->request = $request->all();
        if( $get == true ):
            $geospacial = $this->send();
        endif;

        return [
            "distance" => ((isset($geospacial['rows'][0]['elements'][0]['distance']['text']))? $geospacial['rows'][0]['elements'][0]['distance']['text'] : 'N/A' ),
            "time" => ((isset($geospacial['rows'][0]['elements'][0]['duration']['text']))? $geospacial['rows'][0]['elements'][0]['duration']['text'] : 'N/A' ),
            "time_seconds" => ((isset($geospacial['rows'][0]['elements'][0]['duration']['value']))? $geospacial['rows'][0]['elements'][0]['duration']['value'] : 0 ),
        ];
    }

    public function send(){      
        
        $url = "https://maps.googleapis.com/maps/api/distancematrix/json";
        $params = array(
            'origins' => $this->request['start']['lat'].",".$this->request['start']['lng'],
            'destinations' => $this->request['end']['lat'].",".$this->request['end']['lng'],
            'units' => 'metric',
            'mode' => 'driving',
            'key' => config('services.maps.key'),
        );

        $url .= '?' . http_build_query($params);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);
        curl_close($ch);
        return json_decode($response, true);        
    }
}