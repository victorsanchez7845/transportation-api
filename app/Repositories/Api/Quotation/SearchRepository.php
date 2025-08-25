<?php

namespace App\Repositories\Api\Quotation;
use Illuminate\Support\Facades\DB;
use Location\Coordinate;
use Location\Polygon;

class SearchRepository{
    private $data = [];

    /**
     * Nos proporciona las reservaciones que son cotizaciones, y que cuenta con una fecha de vigencia
     */
    public function getBookingQuotation(){
        return DB::select('SELECT   rez.id, 
                                    rez.client_email, 
                                    rez.language, 
                                    rez.is_cancelled, 
                                    rez.is_quotation, 
                                    rez.expires_at, 
                                    item.code
                    FROM reservations as rez 
                    INNER JOIN reservations_items AS item ON item.reservation_id = rez.id
                WHERE rez.is_cancelled = 0 AND rez.is_quotation = 1 AND rez.expires_at IS NOT NULL');
    }

    public function findDestinations($request){        
        $this->data = $request->all();

        $zones = $this->getZones();
        if($zones == false) return false;

        $availability = $this->checkAvailability($zones);
        if($availability == false) return false;
        
        // dd($this->data, $availability);
        //Seteamos la zona horaría del destino...
        date_default_timezone_set($availability['start']['data']['destination']['time_zone']);        
        if(!isset( $this->data['lastminute'] )):
            // Obtener la hora actual del servidor
            $dates = [];
            $current_hour = date("H"); //Hora actual
            $today = date("Y-m-d"); //HOY
            $tomorrow =  date("Y-m-d", strtotime( date("Y-m-d") . ' +1 day') ); //Mañana
            $pickup_time = date("Y-m-d", strtotime( $this->data['start']['pickup'] )); //Formato de hora de recogida completo
            $pickup_time_hour = date("H", strtotime( $this->data['start']['pickup'] )); //Hora de recogida
                        
            $dates[$today][] = [ 'start' => 0, 'end' => 9];
            $dates[$today][] = [ 'start' => 20, 'end' => 23];
            $dates[$tomorrow][] = [ 'start' => 0, 'end' => 9];
            $dates[$tomorrow][] = [ 'start' => 20, 'end' => 23];

            // dd($current_hour, $today, $tomorrow, $pickup_time, $pickup_time_hour, $dates);
    
            /*
            if(isset( $dates[ $pickup_time ] )){    

                // Verificar si $hola está dentro de algún rango para la fecha $today
                $found_one = isset($dates[$today]) && array_reduce(
                    $dates[$today],
                    function ($carry, $range) use ($current_hour) {
                        return $carry || ($current_hour >= $range['start'] && $current_hour <= $range['end']);
                    },
                    false
                );

                $found_two = isset($dates[$today]) && array_reduce(
                    $dates[$today],
                    function ($carry, $range) use ($pickup_time_hour) {
                        return $carry || ($pickup_time_hour >= $range['start'] && $pickup_time_hour <= $range['end']);
                    },
                    false
                );

                if($found_one == true && $found_two):
                    return false;
                endif;
            }*/

            /*$current_date_time = strtotime( date("Y-m-d H:i") );
            $block_init_night = strtotime( date("Y-m-d")." 20:00" );
		    $block_end_night  = strtotime( date("Y-m-d")." 20:00" ) + (13 * 60 * 60); //Se suman 13 horas
            $block_init_night_tomorrow = strtotime( date("Y-m-d", strtotime(date("Y-m-d") . ' +1 day')) ." 20:00" );
		    $block_end_night_tomorrow  = strtotime( date("Y-m-d", strtotime(date("Y-m-d") . ' +1 day'))." 20:00" ) + (13 * 60 * 60); //Se suman 13 horas

            //Necesitamos validar si la fehca del cliente es para hoy o para mañana, esto lo necesitamos hacer para validar las fechas de atención a clientes
            if(date("Y-m-d") == date("Y-m-d", strtotime($this->data['start']['pickup'])) ){
                //Es hoy                
                if( $current_date_time >= $block_init_night && $current_date_time <= $block_end_night ){                    
                    return false;
                }
            }elseif( date("Y-m-d", strtotime(date("Y-m-d") . ' +1 day')) == date("Y-m-d", strtotime($this->data['start']['pickup'])) ){                
                //Es mañana                
                if( $current_date_time >= $block_init_night_tomorrow && $current_date_time <= $block_end_night_tomorrow ){
                    return false;
                }                
            }*/


            //Sumamos a la fecha y hora actual, la cantidad de horas de CUT_OFF, para saber si nos dará tiempo pasar por el cliente...
            $time = date('Y-m-d H:i', strtotime(date("Y-m-d H:i")) + ( $availability['start']['data']['destination']['cut_off']  * 3600) );
            if($this->data['start']['pickup'] < $time){
                return false;
            }

            //Si es round-trip, validamos que no sea menor a la fecha de pickup y también validamos que esté dentro del CUT_OFF
            if($this->data['type'] == "round-trip"){
                if($this->data['end']['pickup'] <= $this->data['start']['pickup']){
                    return false;
                }
                $time = date('Y-m-d H:i', strtotime(date("Y-m-d H:i")) + ( $availability['start']['data']['destination']['cut_off']  * 3600) );
                if($this->data['end']['pickup'] < $time){
                    return false;
                }
            }
        endif;
        
        return $availability;        
    }

    public function getZones(){
        $items = [];
        $zones = DB::select('SELECT 
                                    dest.id as destination_id, 
                                    IFNULL(zon.cut_off, dest.cut_off) AS cut_off, 
                                    dest.time_zone, 
                                    zon.id as zone_id, 
                                    zon.name as zone_name, 
                                    zon.is_primary, 
                                    zon.iata_code,                        
                                    zonp.latitude, 
                                    zonp.longitude, 
                                    dest.name as destination_name 
                            FROM zones as zon 
                                INNER JOIN zones_points as zonp ON zonp.zone_id = zon.id
                                INNER JOIN destinations as dest ON dest.id = zon.destination_id
                            WHERE zon.status = 1 AND dest.status = 1');

        if($zones){
            foreach($zones as $key => $value):
                if(!isset( $items[ $value->zone_id ] )){
                    $items[ $value->zone_id ] = [
                        "destination" => [
                            "id" => $value->destination_id,
                            "name" => $value->destination_name,
                            "cut_off" => $value->cut_off,
                            "time_zone" => $value->time_zone,
                        ],
                        "zone" => [
                            "id" => $value->zone_id,
                            "name" => $value->zone_name,
                            "is_primary" => $value->is_primary,
                            "iata_code" => $value->iata_code,
                        ],
                        "items" => []
                    ];                    
                }
                $items[ $value->zone_id ]['items'][] = [
                    "lat" => $value->latitude,
                    "lng" => $value->longitude
                ];
            endforeach;
            
            return $items;
        }
        
        return false;
    }

    /**
     * Se verifica si el punto de inicio y fin pertenecen a una geocerca, si existe validamos que pertenezcan al mismo destino.
     */
    public function checkAvailability($zones){

        $validation = [
            "start" => [
                "status" => 0,
                "data" => [],
            ],
            "end" => [
                "status" => 0,
                "data" => [],
            ],
            "geospacial" => []
        ];

        //Verificamos si el punto de partida pertenece a algúna zona disponible (en general)
        foreach($zones as $key => $value):
            $geofence = new Polygon();
            foreach($value['items'] as $keyI => $valueI):
                $geofence->addPoint(new Coordinate($valueI['lat'], $valueI['lng']));
            endforeach;

            $start = new Coordinate($this->data['start']['lat'], $this->data['start']['lng']);
            // dd($geofence, $start);
            if($geofence->contains($start)){
                $validation['start']['status'] = 1;
                $validation['start']['data'] = $value;
            }
        endforeach;

        //Si no se encontró una zona de disponibilidad, se retorna false
        if($validation['start']['status'] == 0){
            return false;
        }

        //Filtramos sólo las zonas dónde el Destino sea igual al destino de partida...
        $filtered_zones = $this->filterArray($zones, $validation['start']['data']['destination']['id']);
        if(sizeof($filtered_zones) == 0){
            return false;
        }

        foreach($filtered_zones as $key => $value):
            $geofence = new Polygon();
            foreach($value['items'] as $keyI => $valueI):
                $geofence->addPoint(new Coordinate($valueI['lat'], $valueI['lng']));
            endforeach;

            $end = new Coordinate($this->data['end']['lat'], $this->data['end']['lng']);
            if($geofence->contains($end)){
                $validation['end']['status'] = 1;
                $validation['end']['data'] = $value;
            }
        endforeach;

        if($validation['start']['status'] == 0 || $validation['end']['status'] == 0) return false;

        return $validation;
    }

    public function filterArray($array, $destination_id) {
        $data = array_filter($array, function($item) use ($destination_id) {
            return isset($item['destination']['id']) && $item['destination']['id'] === $destination_id;
        });    
        return $data;
    }

    public function setAtributes($request){
        $this->data = $request->all();
    }

    public function checkHotelZone(){
        $zones = $this->getZones();

        $validation = [
            "start" => [
                "status" => 0,
                "data" => [],
            ],
            "end" => [
                "status" => 0,
                "data" => [],
            ],
            "geospacial" => []
        ];
        
        foreach($zones as $key => $value):

            $geofence = new Polygon();
            foreach($value['items'] as $keyI => $valueI):
                $geofence->addPoint(new Coordinate($valueI['lat'], $valueI['lng']));
            endforeach;
            
            $start = new Coordinate($this->data['start']['lat'], $this->data['start']['lng']);
            if($geofence->contains($start)){
                $validation['start']['status'] = 1;
                $validation['start']['data'] = $value;
                return $validation;
            }

        endforeach;

        return false;        
    }

}