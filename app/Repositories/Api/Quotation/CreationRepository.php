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
use App\Models\PaymentsError;
use App\Models\ReservationsFollowUp;
use Illuminate\Support\Facades\DB;

use App\Services\AirbrakeService;
use Exception;
use Carbon\Carbon;

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
            $is_quotation = false;
            $accept_messages = false;
            if(isset($this->request['pay_at_arrival'])) $pay_at_arrival = true;
            if(isset($this->request['is_quotation'])) $is_quotation = true;
            if(isset($this->request['accept_messages'])) $accept_messages = true;

            //NOS AYUDA A SABER SI LA RESERVA SERA COMISIONADA
            $is_commissionable = 1;
            if( !isset($this->request['is_commissionable']) && $site[0]->is_commissionable == 0):
                $is_commissionable = 0;
            endif;

            $is_advanced = 0;
            if(in_array($service_token['data']['item']['id'], [5])):
                $is_advanced = 1;
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

            $cash_fee = 0;
            if(isset( $service_token['data']['item']['cash_fee'] )):
                $cash_fee = $service_token['data']['item']['cash_fee'];
            endif;

            $campaign = '';
            if( isset( $this->request['campaign'] ) ):
                $campaign = $this->request['campaign'];
            endif;

            $payment = [
                "status" => false,
                "data" => []
            ];            

            if( isset( $this->request['data']['payment']['id'] ) ):
                $payment['status'] = true;
                $payment['data']['id'] = $this->request['data']['payment']['id'];
                $payment['data']['brand'] = $this->request['data']['payment']['brand'];
                $payment['data']['amount'] = $this->request['data']['payment']['amount'];
                $payment['data']['currency'] = $this->request['data']['payment']['currency'];
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
                $rez_db->destination_id = 1;
                $rez_db->reference = $reference;
                $rez_db->affiliate_id = $affiliate_id;
                $rez_db->accept_messages = (( $accept_messages )? 1 : 0 );
                $rez_db->is_advanced = $is_advanced;
                $rez_db->call_center_agent_id = ((isset($this->request['call_center_agent']))? $this->request['call_center_agent'] : 0);
                $rez_db->campaign = $campaign;

                if( isset( $this->request['origin_sale_id'] ) ):
                    $rez_db->origin_sale_id = $this->request['origin_sale_id'];
                endif;
                
                if( $pay_at_arrival && $site[0]->is_cxc == 0 ):
                    $rez_db->pay_at_arrival = 1;
                endif;

                if( $is_quotation && $site[0]->is_cxc == 0 && !$pay_at_arrival ):
                    $rez_db->is_quotation = 1;
                    $rez_db->was_is_quotation = 1;
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
                            // APLICA SOLO SI ES COTIZACION
                            if($is_quotation):
                                $newDate = $this->getNewDate(date('Y-m-d H:m:s'), $service_token['data']['request']['start']['pickup']);
                                if( $newDate != null ){
                                    $booking = Reservations::find($rez_db->id);
                                    $booking->expires_at = $newDate;
                                    $booking->save();
                                }
                            endif;
                            
                            $data_rez['code'] = $rez_item_db->code;
                            $data_rez['email'] = $rez_db->client_email;
                            $data_rez['language'] = $service_token['data']['request']['language'];
                            $data_rez['type'] = 'new';
                            $data_rez['provider'] = '1';
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

                    if($payment['status'] == true):
                        $exchange = $this->getExchange("MXN", $service_token['data']['request']['currency']);

                        $payment_db = new Payments;
                        if($payment['data']['brand'] == "STRIPE"):
                            $payment_db->description = "Stripe";
                        else:
                            $payment_db->description = "PayPal";
                        endif;                        
                        $payment_db->total = $payment['data']['amount'];
                        $payment_db->exchange_rate = $exchange->exchange_rate;
                        $payment_db->operation = $exchange->operation;
                        $payment_db->payment_method = $payment['data']['brand'];
                        $payment_db->currency = strtoupper($payment['data']['currency']);
                        $payment_db->reservation_id = $rez_db->id;
                        $payment_db->reference = $payment['data']['id'];
                        $payment_db->save();
                    endif;

                    $sales_db = new Sales;
                    $sales_db->description = $label;
                    $sales_db->quantity = 1;
                    $sales_db->total = $total;
                    $sales_db->call_center_agent_id = ((isset($this->request['call_center_agent']))? $this->request['call_center_agent'] : 0);
                    $sales_db->sale_type_id = 1;
                    $sales_db->reservation_id = $rez_db->id;
                    $sales_db->save();

                    if( $rez_db->pay_at_arrival ):
                        $sales_db = new Sales;
                        $sales_db->description = (( $service_token['data']['request']['language'] == "en" ) ? 'Tax service':'Tarifa de servicio');
                        $sales_db->quantity = 1;
                        $sales_db->total = $cash_fee;
                        $sales_db->call_center_agent_id = 0;
                        $sales_db->sale_type_id = 3;
                        $sales_db->reservation_id = $rez_db->id;
                        $sales_db->save();
                    endif;
                    

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

            //Si existe un error y hay pago realizado, guardamos un respaldo de toda la data...
            $this->paymentErrorHistory();
            
            $airbrake = app(AirbrakeService::class);
            $airbrake->report($e);

            $data['code'] = "database";
            $data['message'] = $e->getMessage();
            return $data;
        }
    }

    public function getSite($id){
        return DB::select('SELECT id, is_commissionable, is_cxc FROM sites WHERE id = :id ', [ 'id' => $id ]);
    }

    public function getNewDate($fecha1, $fecha2){
        $fecha1 = Carbon::parse($fecha1);
        $fecha2 = Carbon::parse($fecha2);

        $diasDiferencia = $fecha1->diffInDays($fecha2);
        $horasDiferencia = $fecha1->diffInHours($fecha2);
    
        if ($diasDiferencia === 0 || $diasDiferencia === 1) {
            // Si la diferencia es de 0 o 1 días, restar 3 horas a la segunda fecha
            return $fecha2->copy()->subHours(3);
        } elseif ($diasDiferencia >= 2 && $diasDiferencia <= 4) {
            // Si son 2, 3 o 4 días, la nueva fecha es un día antes de la segunda fecha
            return $fecha2->copy()->subDay();
        } elseif ($diasDiferencia >= 5) {
            // Si es 5 días o más, la nueva fecha es dos días antes de la segunda fecha
            return $fecha2->copy()->subDays(2);
        }

        return null; // En caso de que no se cumpla ninguna condición (casi imposible)
    }

    public function getExchange($origin, $destination = "MXN"){
        $items = DB::select('SELECT operation, exchange_rate
                                FROM payments_exchange_rate
                            WHERE origin = :origin AND destination = :destination
                            LIMIT 1', 
                        [
                            'origin' => $origin,
                            'destination' => $destination
                        ]);

        return $items[0];
    }

    public function paymentErrorHistory(){
        if( isset($this->request['data']['payment']['id']) ):
            $payment_db = new PaymentsError;
            $payment_db->data = serialize($this->request);
            $payment_db->created_at = date("Y-m-d H:i:s");
            $payment_db->save();            
        endif;
    }

}