<?php

namespace App\Repositories\Api\Flights;
use Illuminate\Support\Facades\DB;
use Location\Coordinate;
use Location\Polygon;


class AirlinesRepository{
    private $request = [];

    public function getAirlines($request = []){

        return DB::select('SELECT taj.*, IFNULL(ter_trans.name, ter.terminal_name) AS terminal_name, IFNULL(air_trans.name, air.name) AS airline_name, air.code as airline_code
                            FROM terminal_airlines_join as taj
                        INNER JOIN terminals as ter ON ter.id = taj.terminal_id
                        INNER JOIN terminals_airlines as air ON air.id = taj.airline_id
                        LEFT JOIN terminals_translate as ter_trans ON ter_trans.terminal_id = ter.id AND ter_trans.lang = 'es'
                        LEFT JOIN terminals_airlines_translate as air_trans ON air_trans.airline_id = air.id AND air_trans.lang = 'es'
                        WHERE ter.iata_code = "CUN"
                        ORDER BY ter.order ASC, air.name ASC', 
                        [
                            'lang' => $request['language'],
                        ]);
                        
        echo "airlines";
        die();
    }
}