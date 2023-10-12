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

    public function getFlightsByDate($request){
        
        $this->request = $request->all();
        $items = [            
            "arr_iata" => $this->request['iata_code'],
            'flight_date' => $this->request['date'],
        ];

        $data = $this->send($items);
        if( !isset(  $data['pagination']['total'] ) ||  $data['pagination']['total'] <= 0):
            return [];
        endif;
        
        //Con este código buscamos los demás datos de las aerolíneas que están paginadas...
        if( $data['pagination']['total'] > 100 ):
            $new_data = $this->fetchNextData($data['pagination']);
            $data['data'] = array_merge($data['data'], $new_data);
        endif;        

        return $this->filterData($data['data']);        
    }

    public function filterData($data){
        $items = [];

        foreach( $data as $key => $value ):
            if( !isset(  $items[ $value['departure']['airport'] ][ $value['airline']['name'] ] ) ):
                $items[ $value['departure']['airport'] ][ $value['airline']['name'] ] = [];
            endif;

            $arrival_original_date = new \DateTime( $value['arrival']['scheduled'] );
            $departure_original_date = new \DateTime( $value['departure']['scheduled'] );

            $items[ $value['departure']['airport'] ][ $value['airline']['name'] ][] = [
                "flightNumber" => $value['flight']['iata'],
                "terminal" => $value['arrival']['terminal'],
                "airlineCode" => $value['airline']['iata'],
                "flightArrivalTime" => $arrival_original_date->format('H:i'),
                "originIata" => $value['departure']['iata'],
                "arrivalAirport" => $value['arrival']['airport'],
                "airlineName" => $value['airline']['name'],
                "flightDepartureDateTime" => $value['departure']['scheduled'],
                "flightArrivalDateTime" => $value['arrival']['scheduled'],
                "airports" => [
                    "arrival" => [
                        "name" => $value['arrival']['airport'],
                        "iata" => $value['arrival']['iata'],
                        "time" => $arrival_original_date->format('H:i'),
                        "timeZoneRegionName" => $value['arrival']['timezone']
                    ],
                    "departure" => [
                        "name" => $value['departure']['airport'],
                        "iata" => $value['departure']['iata'],
                        "time" => $departure_original_date->format('H:i'),
                        "timeZoneRegionName" => $value['departure']['timezone']
                    ]
                ]
            ];

        endforeach;

        return $items;
    }

    function fetchNextData($pagination) {
        $data = array();

        $items = [            
            "arr_iata" => $this->request['iata_code'],
            'flight_date' => $this->request['date'],          
        ];
        
        while ($pagination['offset'] + $pagination['limit'] < $pagination['total']) {            
            // Calcular los valores para la siguiente solicitud.
            $pagination['offset'] += $pagination['limit'];
            $remaining = $pagination['total'] - $pagination['offset'];
            $nextLimit = ($remaining > $pagination['limit']) ? $pagination['limit'] : $remaining;
            
            $item_new = $items;
            $item_new['limit'] = $nextLimit;
            $item_new['offset'] = $pagination['offset'];
            
            $new_data = $this->send( $item_new );
            if(isset( $new_data['data'] )):
                $data = array_merge($data, $new_data['data']);            
            endif;
        }

        return $data;
    }

    
    public function send( $items = []  ){
        $initial = [
            'access_key' => config('services.flights.key'),
        ];
        $data = array_merge($initial, $items);
        $queryString = http_build_query( $data );
          
        $ch = curl_init(sprintf('%s?%s', 'http://api.aviationstack.com/v1/flights', $queryString));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);          
        $json = curl_exec($ch);
        curl_close($ch);          
        return json_decode($json, true);

    }
}