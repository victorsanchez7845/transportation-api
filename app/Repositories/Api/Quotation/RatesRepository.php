<?php

namespace App\Repositories\Api\Quotation;
use Illuminate\Support\Facades\DB;
use Location\Coordinate;
use Location\Polygon;
use App\Traits\TokenTrait;

class RatesRepository{
    use TokenTrait;

    private $data = [];
    private $request = [];
    private $exchange = [];

    public function check($availability, $request = []){
        // dd($availability, $request->all());
        $this->data = $availability;
        $this->request = $request->all();
        $this->setExchangeRate();
        return $this->getTransferRates();

        //if( $this->data['start']['data']['zone']['is_primary'] == 1 || $this->data['end']['data']['zone']['is_primary'] == 0):
            //Es un servicio que tiene que ver con la zona principal ( Aeropuerto->Destino | Destino->Aeropuerto )....
            //return $this->getGeneralRates();
        //else:
            //Es un servicio que tiene que ver con transfers ( Destino->Destino | Destino->Destino )....            
        //endif;
    }

    public function getGeneralRates(){
        $items = [];
        $zone = 0;
        if($this->data['start']['data']['zone']['is_primary'] == 0):
            $zone = $this->data['start']['data']['zone']['id'];
        endif;
        if($this->data['end']['data']['zone']['is_primary'] == 0):
            $zone = $this->data['end']['data']['zone']['id'];
        endif;
        
        $rates = DB::select('SELECT rate.*, dest.name as destination_name, dest.status as destination_status, zone.name as zone_name, zone.status as zone_status, IFNULL(dest_trans.translation, serv.name) AS service_name, serv.passengers, serv.luggage, serv.price_type, serv.image_url, serv.id as service_id
                        FROM rates as rate
                        INNER JOIN destinations as dest ON dest.id = rate.destination_id
                        INNER JOIN zones as zone ON zone.id = rate.zone_id
                        INNER JOIN destination_services as serv ON serv.id = rate.destination_service_id
                        LEFT JOIN destination_services_translate as dest_trans ON dest_trans.destination_services_id = serv.id AND dest_trans.lang = :lang
                        INNER JOIN rates_groups as rg ON rg.id = rate.rate_group_id
                        WHERE dest.status = 1 AND zone.status = 1 AND serv.status = 1 AND rate.zone_id = :zone_id AND rg.code = :rate_group
                        ORDER BY serv.order ASC', 
                        [
                            'lang' => $this->request['language'],
                            'zone_id' => $zone,
                            'rate_group' => $this->request['rate_group'],
                        ]);
 
        if(!$rates){
            return false;
        }

        foreach($rates as $key => $value):

            $price = [
                "one_way" => 0,
                "round_trip" => 0,
            ];
 
            if(in_array($value->price_type, ["vehicle","shared"])):
                $price['one_way'] = $value->one_way;
                $price['round_trip'] = $value->round_trip;
            endif;
            
            if($value->price_type == "passenger"):                
                if($this->request['passengers'] >= 1 && $this->request['passengers'] <= 2){
                    $price['one_way'] = $value->ow_12;
                    $price['round_trip'] = $value->rt_12;
                }

                if($this->request['passengers'] >= 3 && $this->request['passengers'] <= 7){
                    $price['one_way'] = $value->ow_37;
                    $price['round_trip'] = $value->rt_37;
                }
                
                if($this->request['passengers'] >= 8){
                    $price['one_way'] = $value->up_8_ow;
                    $price['round_trip'] = $value->up_8_rt;
                }                
            endif;

            $vehicles = $this->calculatePassengersNeeded($value->passengers, $this->request['passengers']);
            $price['one_way'] = number_format($price['one_way'] * $vehicles, 2, '.', '');
            $price['round_trip'] = number_format($price['round_trip'] * $vehicles, 2, '.', '');
            
            $final_price = $price['one_way'];        
            if($this->request['type'] == "round-trip") $final_price = $price['round_trip'];
            
            //Si es un servicio Shared, debemos multiplicar el precio por la cantidad de personas..
            if($value->price_type == "shared"):
                $final_price = $final_price * $this->request['passengers'];
            endif;

            $token_data = [
                "request" => $this->request,
                "item" => [
                    "id" => $value->service_id,
                    "name" => $value->service_name,
                    "passengers" => $value->passengers,
                    "luggage" => $value->luggage,
                    "image" => $value->image_url,
                    "price" => $this->getExchangeConvertion($final_price),
                    "currency" => $this->request['currency'],
                    "vehicles" => $vehicles
                ]
            ];
            $token_data['item']['token'] = $this->set($token_data, 3);
            $items[] = $token_data['item'];

        endforeach;

        $items = $this->validatePrices($items);
        if(sizeof($items) <= 0) return false;
        
        return $items;
    }

    public function getTransferRates(){

        //Validamos si el punto de recogida y dejada están en la misma zona... si es así, se da el precio de la misma zona.
        //if($this->data['start']['data']['zone']['id'] == $this->data['end']['data']['zone']['id']):
            //return $this->getGeneralRates();
        //endif;

        $rates = DB::select('SELECT rate.*, 
                                    dest.name as destination_name, 
                                    dest.status as destination_status, 
                                    IFNULL(dest_trans.translation, serv.name) AS service_name, 
                                    serv.passengers, 
                                    serv.luggage, 
                                    serv.price_type, 
                                    serv.image_url, 
                                    serv.id as service_id, 
                                    serv.cash_fee,
                                    zoneA.name as zone_nameA, 
                                    zoneA.status as zone_statusA, 
                                    zoneB.name as zone_nameB, 
                                    zoneB.status as zone_statusB
                            FROM rates_transfers as rate
                            INNER JOIN destinations as dest ON dest.id = rate.destination_id
                            INNER JOIN zones as zoneA ON zoneA.id = rate.zone_one
                            INNER JOIN zones as zoneB ON zoneB.id = rate.zone_two
                            INNER JOIN destination_services as serv ON serv.id = rate.destination_service_id
                            LEFT JOIN destination_services_translate as dest_trans ON dest_trans.destination_services_id = serv.id AND dest_trans.lang = :lang
                            INNER JOIN rates_groups as rg ON rg.id = rate.rate_group_id
                            WHERE dest.status = 1 AND zoneA.status = 1 AND zoneB.status = 1 AND serv.status = 1 AND rg.code = :rate_group 
                            AND ( (rate.zone_one = :zoneOne AND rate.zone_two = :zoneTwo) OR (rate.zone_one = :zoneThree AND rate.zone_two = :zoneFour) )
                            ORDER BY serv.order ASC', 
                                    [
                                        'lang' => $this->request['language'],
                                        'rate_group' => $this->request['rate_group'],
                                        'zoneOne' => $this->data['start']['data']['zone']['id'],
                                        'zoneTwo' => $this->data['end']['data']['zone']['id'],
                                        'zoneThree' => $this->data['end']['data']['zone']['id'],
                                        'zoneFour' => $this->data['start']['data']['zone']['id'],
                                    ]);
                            
        if(!$rates){
            return false;
        }

        foreach($rates as $key => $value):

            $price = [
                "one_way" => 0,
                "round_trip" => 0,
            ];
 
            if(in_array($value->price_type, ["vehicle","shared"])):
                $price['one_way'] = $value->one_way;
                $price['round_trip'] = $value->round_trip;
            endif;
            
            if($value->price_type == "passenger"):                
                if($this->request['passengers'] >= 1 && $this->request['passengers'] <= 2){
                    $price['one_way'] = $value->ow_12;
                    $price['round_trip'] = $value->rt_12;
                }               

                if($this->request['passengers'] >= 3 && $this->request['passengers'] <= 7){
                    $price['one_way'] = $value->ow_37;
                    $price['round_trip'] = $value->rt_37;
                }
                
                if($this->request['passengers'] >= 8){
                    $price['one_way'] = $value->up_8_ow;
                    $price['round_trip'] = $value->up_8_rt;
                }                
            endif;

            $vehicles = $this->calculatePassengersNeeded($value->passengers, $this->request['passengers']);
            $price['one_way'] = number_format($price['one_way'] * $vehicles, 2, '.', '');
            $price['round_trip'] = number_format($price['round_trip'] * $vehicles, 2, '.', '');
            
            $final_price = $price['one_way'];        
            if($this->request['type'] == "round-trip") $final_price = $price['round_trip'];
            
            //Si es un servicio Shared, debemos multiplicar el precio por la cantidad de personas..
            if($value->price_type == "shared"):
                $final_price = $final_price * $this->request['passengers'];
            endif;
            
            $token_data = [
                "request" => $this->request,
                "item" => [
                    "id" => $value->service_id,
                    "name" => $value->service_name,
                    "passengers" => $value->passengers,
                    "luggage" => $value->luggage,
                    "image" => $value->image_url,
                    "price" => $this->getExchangeConvertion($final_price),
                    "cash_fee" => $this->getExchangeConvertion($value->cash_fee),
                    "currency" => $this->request['currency'],
                    "vehicles" => $vehicles
                ]
            ];
            $token_data['item']['token'] = $this->set($token_data, 3);
            $items[] = $token_data['item'];

        endforeach;

        $items = $this->validatePrices($items);
        if(sizeof($items) <= 0) return false;
        
        return $items;
    }

    public function calculatePassengersNeeded($availablePassengers, $requestedPassengers) {
        if ($availablePassengers >= $requestedPassengers) {
            return 1;
        } else {
            return ceil($requestedPassengers / $availablePassengers);
        }
    }

    public function validatePrices($arr) {
        return array_filter($arr, function ($item) {
            return $item['price'] > 0;
        });
    }

    public function setExchangeRate(){        
        $this->exchange = DB::select('SELECT * FROM exchange_rate WHERE destination = :currency', ['currency' => $this->request['currency']]);
    }

    public function getExchangeConvertion($price){
        if(isset( $this->exchange[0] )){
            if($this->exchange[0]->operation == "division"):
                return number_format( ($price * $this->exchange[0]->exchange_rate) , 2, '.', '');
            endif;
            if($this->exchange[0]->operation == "multiplication"):
                return number_format( ($price / $this->exchange[0]->exchange_rate) , 2, '.', '');
            endif;
        }else{
            return $price;
        }
    }

}