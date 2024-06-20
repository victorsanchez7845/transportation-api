<?php

namespace App\Repositories\Api\Quotation;
use App\Traits\CodeTrait;
use App\Traits\TokenTrait;
use App\Traits\FunctionsTrait;
use App\Models\Reservations;
use App\Models\ReservationsItems;
use App\Models\ReservationsServices;
use App\Models\Sales;
use App\Models\Payments;
use App\Models\ReservationsFollowUp;
use Illuminate\Support\Facades\DB;

class CreationRepository{
    use CodeTrait, TokenTrait, FunctionsTrait;
    private $bearer = '';
    private $request = [];

    public function setData($bearer = "", $request = []){
        $this->bearer = $bearer;
        $this->request = $request->all();
    }

    public function checkServiceToken($request = []){
        $token = $this->get( $this->request['service_token'] );
        if($token == false){
            return false;
        }else{
            return $token;
        }
    }

    public function create($distance, $search){
  
        $data = [
            'status' => false
        ];

        try {            

            DB::beginTransaction();           
            
            $bearer_token = $this->get( $this->bearer );
            $service_token = $this->get( $this->request['service_token'] );

            $site = $this->getSite($this->request['site_id']);
            if(!isset($site[0]->id)):
                $data['code'] = "site";
                $data['message'] = "Unknown site";
                return $data;
            endif;

            $pay_at_arrival = false;
            $accept_messages = false;
            if(isset($this->request['pay_at_arrival'])) $pay_at_arrival = true;
            if(isset($this->request['accept_messages'])) $accept_messages = true;

            $is_commissionable = 1;
            if($site[0]->is_commissionable == 0):
                $is_commissionable = 0;
            endif;

            //Obtenemos la distancia en Tiempo (Segundos) y KM [Texto]
            $distance_data = $distance->get( new \Illuminate\Http\Request($service_token['data']['request']) );
            $zones_data = $search->findDestinations( new \Illuminate\Http\Request($service_token['data']['request']) ); //Identificamos a que zona pertenecen los puntos..
            $quantity = $service_token['data']['item']['vehicles']; //Cantidad de reservaciones a crear
            $data_rez = [];

            $reference = '';
            if( isset( $this->request['data']['callcenter']['reference'] ) ):
                $reference = $this->request['data']['callcenter']['reference'];
            endif;

            $affiliate_id = 0;
            if( isset( $this->request['affiliate_id'] ) ):
                $affiliate_id = $this->request['affiliate_id'];
            endif;
                           
                $rez_db = new Reservations;                
                $rez_db->client_first_name = $this->request['first_name'];
                $rez_db->client_last_name = $this->request['last_name'];
                $rez_db->client_email = trim( strtolower($this->request['email_address']) );
                $rez_db->client_phone = $this->request['phone'];
                $rez_db->currency = $service_token['data']['request']['currency'];
                $rez_db->language = $service_token['data']['request']['language'];
                $rez_db->rate_group = $service_token['data']['request']['rate_group'];
                $rez_db->is_cancelled = 0;
                $rez_db->is_commissionable = $is_commissionable;    
                $rez_db->site_id = $this->request['site_id'];
                $rez_db->destination_id = $zones_data['start']['data']['destination']['id'];
                $rez_db->reference = $reference;
                $rez_db->affiliate_id = $affiliate_id;
                $rez_db->accept_messages = (( $accept_messages )? 1 : 0 );
                
                if($pay_at_arrival):
                    $rez_db->pay_at_arrival = 1;
                endif;

                if($rez_db->save()):
                    
                    //Con este loop agregamos otro código de reservación en caso de que sobrepase el limite de la unidad (ASUR así lo pide).
                    $counter = 1;
                    while ($counter <= $quantity) {
                        
                        //Insertamos los códigos de reservación, esto se aplico porque un cliente puede tener multiples reservaciones en caso de que haya superado el limite de capacidad dela uto.
                        $rez_item_db = new ReservationsItems;
                        $rez_item_db->reservation_id = $rez_db->id;
                        $rez_item_db->code = $this->generateCode();

                        $rez_item_db->destination_service_id = $service_token['data']['item']['id'];

                        $rez_item_db->from_name = $service_token['data']['request']['start']['place'];
                        $rez_item_db->from_lat = $service_token['data']['request']['start']['lat'];
                        $rez_item_db->from_lng = $service_token['data']['request']['start']['lng'];
                        $rez_item_db->from_zone = $zones_data['start']['data']['zone']['id'];

                        $rez_item_db->to_name = $service_token['data']['request']['end']['place'];
                        $rez_item_db->to_lat = $service_token['data']['request']['end']['lat'];
                        $rez_item_db->to_lng = $service_token['data']['request']['end']['lng'];
                        $rez_item_db->to_zone = $zones_data['end']['data']['zone']['id'];

                        $rez_item_db->distance_time = ((isset($distance_data['time_seconds']))? $distance_data['time_seconds'] :  0);
                        $rez_item_db->distance_km = ((isset($distance_data['distance']))? $distance_data['distance'] : '');
                        
                        $rez_item_db->is_round_trip = 0;
                        if(in_array($service_token['data']['request']['type'], ['round-trip']) ):
                            $rez_item_db->is_round_trip = 1;
                        endif;
                        
                        $rez_item_db->flight_number = $this->request['flight_number'];
                        $rez_item_db->flight_data = '';
                        $rez_item_db->passengers = ( $service_token['data']['request']['passengers'] / $quantity);


                        $rez_item_db->op_one_status = 'PENDING';                        
                        $rez_item_db->op_one_pickup = $service_token['data']['request']['start']['pickup'];
                        
                        
                        if(in_array($service_token['data']['request']['type'], ['round-trip']) ):
                            $rez_item_db->op_two_status = 'PENDING';
                            $rez_item_db->op_two_pickup = $service_token['data']['request']['end']['pickup'];
                        endif;
                        
                        if($rez_item_db->save()):
                            
                            $data_rez['code'] = $rez_item_db->code;
                            $data_rez['email'] = $rez_db->client_email;
                            $data_rez['language'] = $service_token['data']['request']['language'];
                            $data_rez['type'] = 'new';
                            $data_rez['provider'] = '1';
                           
                            /*
                            //Si es un viaje sencillo y viaje redondo, se ejecuta el siguiente bloque de código.
                            if(in_array($service_token['data']['request']['type'], ['one-way', 'round-trip']) ):
                                $service_db = new ReservationsServices;
                                $service_db->reservation_item_id = $rez_item_db->id;
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
                                $service_db->passengers = ( $service_token['data']['request']['passengers'] / $quantity);
                                $service_db->save();
                            endif;

                            //Si es una reserva de tipo redondo, se ejecuta el siguiente bloque de código.
                            if(in_array($service_token['data']['request']['type'], ['round-trip']) ):
                                $service_db = new ReservationsServices;
                                $service_db->reservation_item_id = $rez_item_db->id;
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
                                $service_db->passengers = ( $service_token['data']['request']['passengers'] / $quantity);
                                $service_db->save();
                            endif;*/

                        endif;

                    $counter++;
                    }

                    $label = $service_token['data']['item']['name'].' | '.(($service_token['data']['request']['type'] == "one-way")?'One Way':'Round Trip');
                    if($service_token['data']['request']['language'] == "en"):
                        $label = $service_token['data']['item']['name'].' | '.(($service_token['data']['request']['type'] == "one-way")?'Viaje Sencillo':'Viaje Redondo');
                    endif;

                    $total = $service_token['data']['item']['price'];
                    if( isset( $this->request['data']['callcenter']['total'] ) ):
                        $total = $this->request['data']['callcenter']['total'];
                    endif;

                    $sales_db = new Sales;
                    $sales_db->description = $label;
                    $sales_db->quantity = 1;
                    $sales_db->total = $total;
                    $sales_db->call_center_agent_id = ((isset($this->request['call_center_agent']))? $this->request['call_center_agent'] : 0);
                    $sales_db->sale_type_id = 1;
                    $sales_db->reservation_id = $rez_db->id;
                    $sales_db->save();

                    if(isset( $this->request['special_request'] )):
                        $follow_up_db = new ReservationsFollowUp;
                        $follow_up_db->name = 'Cliente';
                        $follow_up_db->text = $this->request['special_request'];
                        $follow_up_db->type = 'CLIENT';
                        $follow_up_db->reservation_id = $rez_db->id;
                        $follow_up_db->save();
                    endif;

                endif;
                
                DB::commit();
                
                //Enviar correo de reservación
                $this->sendEmail(config('app.url')."/api/v1/reservation/send", $data_rez);        

                $data['status'] = true;
                $data['data'] = $data_rez;

                return $data;

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