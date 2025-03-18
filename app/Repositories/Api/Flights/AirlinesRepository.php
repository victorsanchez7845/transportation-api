<?php

namespace App\Repositories\Api\Flights;
use Illuminate\Support\Facades\DB;
use Location\Coordinate;
use Location\Polygon;


class AirlinesRepository{
    private $request = [];

    public function getAirlines($request = []){        
        $data = DB::select("SELECT fk_terminals_airline.id as id, IFNULL(air_trans.name, air.name) as airline_name, air.code as airline_code, IFNULL(air_ter_trans.name, air_ter.name) AS terminal_name
                    FROM airports_terminals_airline as fk_terminals_airline
                        INNER JOIN airlines as air ON air.id = fk_terminals_airline.airline_id
                        INNER JOIN airports_terminals as air_ter ON air_ter.id = fk_terminals_airline.airport_terminal_id
												INNER JOIN airports as airp ON airp.id = air_ter.airport_id
                        LEFT JOIN airports_terminals_translate as air_ter_trans ON air_ter_trans.id = air_ter.id AND air_ter_trans.lang = ?
                        LEFT JOIN airlines_translate as air_trans ON air_trans.airline_id = air.id AND air_trans.lang = ?
							WHERE airp.iata_code = ?
                        ORDER BY air_ter.order ASC, air.name ASC", [$request['language'], $request['language'], $request['iata']]);
        if( sizeof( $data) > 0 ){
            return $data;
        }else{
            return false;
        }
    }
}