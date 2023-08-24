<?php

namespace App\Repositories\Api\Quotation;
use App\Traits\CodeTrait;
use App\Traits\TokenTrait;
use App\Models\Reservations;
use App\Models\ReservationsServices;
use App\Models\Sales;
use Illuminate\Support\Facades\DB;

class CreationRepository{
    use CodeTrait, TokenTrait;
    private $bearer = '';
    private $request = [];

    public function setData($bearer = "", $request = []){
        $this->bearer = $bearer;
        $this->request = $request->all();
    }

    public function checkServiceToken($request = []){
        $token = TokenTrait::get( $this->request['service_token'] );
        if($token == false){
            return false;
        }else{
            return $token;
        }
    }

    public function create($distance, $search){
        // echo "<pre>";
        // print_r();
        // die();

        $data = [
            'status' => false
        ];

        try {
            DB::beginTransaction();           
            
            $bearer_token = TokenTrait::get( $this->bearer );
            $service_token = TokenTrait::get( $this->request['service_token'] );            
            
            $site = $this->getSite($this->request['site_id']);
            if(!isset($site[0]->id)):
                $data['code'] = "site";
                $data['message'] = "Unknown site";
                return $data;
            endif;

            $is_commissionable = 1;
            if($site[0]->is_commissionable == 0):
                $is_commissionable = 0;
            endif;

            //Obtenemos la distancia en Tiempo (Segundos) y KM [Texto]
            $distance_data = $distance->get( new \Illuminate\Http\Request($service_token['data']['request']) );
            $zones_data = $search->findDestinations( new \Illuminate\Http\Request($service_token['data']['request']) ); //Identificamos a que zona pertenecen los puntos..
            $quantity = $service_token['data']['item']['vehicles']; //Cantidad de reservaciones a crear

            //$counter = 1;
            //while ($counter <= $quantity) {
                $code = CodeTrait::generateCode();
                           
                $rez_db = new Reservations;
                $rez_db->code = $code;
                $rez_db->client_first_name = $this->request['first_name'];
                $rez_db->client_last_name = $this->request['last_name'];
                $rez_db->client_email = trim( strtolower($this->request['email_address']) );
                $rez_db->client_phone = $this->request['phone'];
                $rez_db->currency = $service_token['data']['request']['currency'];
                $rez_db->rate_group = $service_token['data']['request']['rate_group'];
                $rez_db->special_request = $this->request['special_request']; 
                $rez_db->is_cancelled = 0;
                $rez_db->is_commissionable = $is_commissionable;    
                $rez_db->site_id = $this->request['site_id'];
                if($rez_db->save()):
                    
                    //Si es un viaje sencillo y viaje redondo, se ejecuta el siguiente bloque de código.
                    if(in_array($service_token['data']['request']['type'], ['one-way', 'round-trip']) ):
                        $service_db = new ReservationsServices;
                        $service_db->reservation_id = $rez_db->id;
                        $service_db->destination_service_id = $service_token['data']['item']['id'];

                        $service_db->from_name = $service_token['data']['request']['start']['place'];
                        $service_db->from_lat = $service_token['data']['request']['start']['lat'];
                        $service_db->from_lng = $service_token['data']['request']['start']['lng'];
                        $service_db->from_zone = $zones_data['start']['data']['zone']['id'];

                        $service_db->to_name = $service_token['data']['request']['end']['place'];
                        $service_db->to_lat = $service_token['data']['request']['end']['lat'];
                        $service_db->to_lng = $service_token['data']['request']['end']['lng'];
                        $service_db->to_zone = $zones_data['end']['data']['zone']['id'];

                        $service_db->distance_time = ((isset($distance_data['time_seconds']))? $distance_data['time_seconds'] :  0);
                        $service_db->distance_km = ((isset($distance_data['distance']))? $distance_data['distance'] : '');
                        $service_db->status = 'PENDING';
                        $service_db->pickup = $service_token['data']['request']['start']['pickup'];
                        $service_db->flight_number = $this->request['flight_number'];
                        $service_db->flight_data = '';
                        $service_db->passengers = $service_token['data']['request']['passengers'];
                        $service_db->save();
                    endif;

                    //Si es una reserva de tipo redondo, se ejecuta el siguiente bloque de código.
                    if(in_array($service_token['data']['request']['type'], ['round-trip']) ):
                        $service_db = new ReservationsServices;
                        $service_db->reservation_id = $rez_db->id;
                        $service_db->destination_service_id = $service_token['data']['item']['id'];

                        $service_db->from_name = $service_token['data']['request']['end']['place'];
                        $service_db->from_lat = $service_token['data']['request']['end']['lat'];
                        $service_db->from_lng = $service_token['data']['request']['end']['lng'];
                        $service_db->from_zone = $zones_data['end']['data']['zone']['id'];

                        $service_db->to_name = $service_token['data']['request']['start']['place'];
                        $service_db->to_lat = $service_token['data']['request']['start']['lat'];
                        $service_db->to_lng = $service_token['data']['request']['start']['lng'];
                        $service_db->to_zone = $zones_data['start']['data']['zone']['id'];                        

                        $service_db->distance_time = ((isset($distance_data['time_seconds']))? $distance_data['time_seconds'] :  0);
                        $service_db->distance_km = ((isset($distance_data['distance']))? $distance_data['distance'] : '');
                        $service_db->status = 'PENDING';
                        $service_db->pickup = $service_token['data']['request']['end']['pickup'];
                        $service_db->flight_number = '';
                        $service_db->flight_data = '';
                        $service_db->passengers = $service_token['data']['request']['passengers'];
                        $service_db->save();
                    endif;                
                    
                    $label = $service_token['data']['item']['name'].' | '.(($service_token['data']['request']['type'] == "one-way")?'One Way':'Round Trip');
                    if($service_token['data']['request']['language'] == "en"):
                        $label = $service_token['data']['item']['name'].' | '.(($service_token['data']['request']['type'] == "one-way")?'Viaje Sencillo':'Viaje Redondo');
                    endif;

                    $sales_db = new Sales;
                    $sales_db->description = $label;
                    $sales_db->quantity = 1;
                    $sales_db->total = $service_token['data']['item']['price'];
                    $sales_db->call_center_agent_id = ((isset($this->request['call_center_agent']))? $this->request['call_center_agent'] : 0);
                    $sales_db->sale_type_id = 1;
                    $sales_db->save();

                endif;
                
                DB::commit();

                die("Fin");

                //$counter++; 
            //}

            die("FIN...");
            
        } catch (\Exception $e) {
            DB::rollback();
            $data['code'] = "database";
            $data['message'] = $e->getMessage();
            return $data;
        }
    }

    public function getSite($id){
        return DB::select('SELECT id, is_commissionable FROM sites WHERE id = :id ', [ 'id' => $id ]);
    }

}