<?php

namespace App\Repositories\Api\Flights;
use Illuminate\Support\Facades\DB;
use Location\Coordinate;
use Location\Polygon;


class SearchRepository{
    private $request = [];

    public function getFlight($request = []){
        $this->request = $request->all();
        $data = $this->send();

        if(isset($data['error'])){
            return false;
        }

        if( !isset($data['data'][0]) ){
            return false;
        }
        //echo $data['data'][0]['arrival']['scheduled']; die();

        return [
            'date' => $data['data'][0]['flight_date'],
            'flight_status' => $data['data'][0]['flight_status'],
            'airline' => $data['data'][0]['airline'],
            'departure' => [
                'iata' => $data['data'][0]['departure']['iata'],
                'airport' => $data['data'][0]['departure']['airport'],
                'date' => $data['data'][0]['departure']['scheduled'],
            ],
            'arrival' => [
                'iata' => $data['data'][0]['arrival']['iata'],
                'airport' => $data['data'][0]['arrival']['airport'],
                'terminal' => $data['data'][0]['arrival']['terminal'],
                'date' => $data['data'][0]['arrival']['scheduled'],
            ],
        ];
    }

    public function send(){
        $queryString = http_build_query([
            'access_key' => config('services.flights.key'),
            'limit' => 1,
            //'flight_date' => $this->request['date'], //¡IMPORTANTE! Habilitar cuando vayamos a salir a producción
            'flight_iata' => $this->request['flight_number'],
          ]);
          
        $ch = curl_init(sprintf('%s?%s', 'http://api.aviationstack.com/v1/flights', $queryString));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);          
        $json = curl_exec($ch);
        curl_close($ch);          
        return json_decode($json, true);

    }
}