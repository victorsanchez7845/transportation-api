<?php

namespace App\Repositories\Api\Integrations;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Location\Coordinate;
use Location\Polygon;

use App\Traits\CodeTrait;
use App\Models\Reservations;
use App\Models\ReservationsItems;
use App\Models\ReservationsServices;
use App\Models\ReservationsFollowUp;
use App\Models\Sales;

use App\Traits\MailjetTrait;
use App\Traits\MasterToursTrait as MT;

class MasterToursRepository{
    private $data = [];
    private $zones = [];
    private $vehicles = [
        "AUTO 3 PAX" => 4,
        "VAN 10 PAX" => 6,
        "MIINIBUS 17 PAX" => 3,
        "CAMIONETA 4 PAX" => 1,
        "SUBURBAN 6 PAX" => 2,
    ];
    use MT, CodeTrait, MailjetTrait;

    public function listing($request){
        $data = MT::getListing();        
        if(sizeof($data) > 0):
            $this->getZones();
            $this->parseData($data);
            return $this->saveNewReservations();
        endif;
    }

    public function parseData($data){
        
        foreach($data as $key => $item):
            //dd($item);
            $date = Carbon::parse($item['FechaReserva'])->format('Y-m-d H:i:s');
            if(date("Y-m-d", strtotime($date)) >= "2025-01-30"):
                
                $item['TipoVehiculo'] = trim( $item['TipoVehiculo'] );

                $this->data[] = [
                    "id" => $item['IdReserva'],
                    "reference" => $item['legId'],
                    "client" => [
                        "name" => $item['Nombres'],
                        "phone" => $item['NroTelefono'],
                        "flight" => $item['NroVuelo']
                    ],
                    "from" => [
                        "id" => $this->check($item['Recojo_Latitud'], $item['Recojo_Longitud']),
                        "name" => $item['Recojo'],
                        "date" => $date,
                        "lat" => $item['Recojo_Latitud'],
                        "lng" => $item['Recojo_Longitud'],
                    ],
                    "to" => [
                        "id" => $this->check($item['Destino_Latitud'], $item['Destino_Longitud']),
                        "name" => $item['Destino'],
                        "lat" => $item['Destino_Latitud'],
                        "lng" => $item['Destino_Longitud'],
                    ],
                    "vehicle" => [
                        "id" => (( isset( $this->vehicles[$item['TipoVehiculo']] ) )? $this->vehicles[$item['TipoVehiculo']] : 1),
                        "pax" => $item['CantidadPasajeros'],
                        "name" => $item['TipoVehiculo']
                    ],
                    "rate" =>  $this->Rate( (( isset( $this->vehicles[$item['TipoVehiculo']] ) )? $this->vehicles[$item['TipoVehiculo']] : 1), $item['CantidadPasajeros'], $this->check($item['Recojo_Latitud'], $item['Recojo_Longitud']),  $this->check($item['Destino_Latitud'], $item['Destino_Longitud']) )
                ];

            endif;

        endforeach;
    }

    public function getZones(){
        $this->zones = [];
        $zones = DB::select('SELECT dest.id as destination_id, IFNULL(zon.cut_off, dest.cut_off) AS cut_off, dest.time_zone, zon.id as zone_id, zon.is_primary, zon.iata_code, dest.name as destination_name, zon.name as zone_name, zonp.latitude, zonp.longitude
                            FROM zones as zon 
                                INNER JOIN zones_points as zonp ON zonp.zone_id = zon.id
                                INNER JOIN destinations as dest ON dest.id = zon.destination_id
                            WHERE zon.status = 1 AND dest.status = 1');

        if($zones){
            foreach($zones as $key => $value):
                if(!isset( $this->zones[ $value->zone_id ] )){
                    $this->zones[ $value->zone_id ] = [
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
                $this->zones[ $value->zone_id ]['items'][] = [
                    "lat" => $value->latitude,
                    "lng" => $value->longitude
                ];
            endforeach;
            
            return $this->zones;
        }
        
        return false;
    }

    public function check($lat, $lng){

        foreach($this->zones as $key => $value):

            $geofence = new Polygon();
            foreach($value['items'] as $keyI => $valueI):
                $geofence->addPoint(new Coordinate((float) $valueI['lat'], (float) $valueI['lng']));
            endforeach;
            
            $start = new Coordinate((float) $lat, (float) $lng);
            if($geofence->contains($start)){
                return $value['zone']['id'];
            }

            if (!is_numeric($lat) || !is_numeric($lng)) {
                return 1;
            }

        endforeach;

        return 1;        
    }

    public function Rate(int $vehicle = 1, int $pax = 8, int $zone_one = 1, int $zone_two = 1):array
    {
        //return
        $data = [
            "amount" => 0,
            "operating_cost" => 0
        ];

        // params query
        $params = [
            "destination_id" => 1,
            "destination_service_id" => $vehicle,
            "zone_one" => $zone_one,
            "zone_two" => $zone_two,
            "zone_three" => $zone_two,
            "zone_four" => $zone_one,
            "enterprise_id" => 11,
        ];        

        $rates = DB::select("SELECT 
                                    ds.name as service_name, ds.price_type,
                                    rt.*,
                                    zoneOne.name as from_name,
	                                zoneTwo.name as to_name
                             FROM rates_enterprises as rt
                                    LEFT JOIN destination_services as ds ON ds.id = rt.destination_service_id
                                    LEFT JOIN enterprises as e ON e.id = rt.enterprise_id
                                    LEFT JOIN zones as zoneOne ON zoneOne.id = rt.zone_one
                                    LEFT JOIN zones as zoneTwo ON zoneTwo.id = rt.zone_one
                             WHERE rt.destination_id = :destination_id
                                AND rt.destination_service_id = :destination_service_id
                                AND ( (rt.zone_one = :zone_one AND rt.zone_two = :zone_two) OR ( rt.zone_one = :zone_three AND rt.zone_two = :zone_four )  ) 
                                AND e.id = :enterprise_id", $params);

        if( $rates ){
            if( $vehicle == 1 || $vehicle == 3 || $vehicle == 6 ){
                $data['amount'] = ( $pax >= 8 ? ( isset($rates[0]->ow_12) ? $rates[0]->ow_12 : 0 ) : ( $pax >= 3 ? ( isset($rates[0]->ow_37) ? $rates[0]->ow_37 : 0 ) : ( isset($rates[0]->up_8_ow) ? $rates[0]->up_8_ow : 0 ) ) );
            }else{
                $data['amount'] = ( isset($rates[0]->one_way) ? $rates[0]->one_way : 0 );
            }
            $data['operating_cost'] = ( isset($rates[0]->operating_cost) ? $rates[0]->operating_cost : 0 );
        }

        return $data;
    }

    public function searchByReference($id){
        $result = Reservations::where('reference_two', $id)->first();
        if($result):
            return true;
        else:
            return false;
        endif;
    }

    public function saveNewReservations(){
        
        if(sizeof($this->data) <= 0):
            return response()->json([], 200);
        endif;
        
        DB::beginTransaction();

        try {
            foreach($this->data as $key => $item):

                $search = $this->searchByReference($item['id']);
                if($search == false){

                    $rez_db = new Reservations;
                    $rez_db->client_first_name = $item['client']['name'];                 
                    $rez_db->client_last_name = '';
                    $rez_db->client_email = "bookings@caribbean-transfers.com";
                    $rez_db->client_phone = $item['client']['phone'];
                    $rez_db->currency = "USD";
                    $rez_db->language = "en";
                    $rez_db->rate_group = 'xLjDl18';
                    $rez_db->is_cancelled = 0;
                    $rez_db->is_commissionable = 0;
                    $rez_db->site_id = 30;
                    $rez_db->destination_id = 1;
                    $rez_db->reference = $item['reference'];
                    $rez_db->reference_two = $item['id'];                    
                    $rez_db->is_advanced = 0;
                    $rez_db->origin_sale_id = 11;
                    if($rez_db->save()):

                        $rez_item_db = new ReservationsItems;
                        $rez_item_db->reservation_id = $rez_db->id;
                        $rez_item_db->code = $this->generateCode();

                        $rez_item_db->destination_service_id = $item['vehicle']['id'];

                        $rez_item_db->from_name = $item['from']['name'];
                        $rez_item_db->from_lat = $item['from']['lat'];
                        $rez_item_db->from_lng = $item['from']['lng'];
                        $rez_item_db->from_zone = $item['from']['id'];

                        $rez_item_db->to_name = $item['to']['name'];
                        $rez_item_db->to_lat = $item['to']['lat'];
                        $rez_item_db->to_lng = $item['to']['lng'];
                        $rez_item_db->to_zone = $item['to']['id'];

                        $rez_item_db->distance_time = 0;
                        $rez_item_db->distance_km = '';                            
                        $rez_item_db->is_round_trip = 0;
                        
                        $rez_item_db->flight_number = $item['client']['flight'];
                        $rez_item_db->flight_data = '';
                        $rez_item_db->passengers =  $item['vehicle']['pax'];
                        $rez_item_db->op_one_status = 'PENDING';
                        $rez_item_db->op_one_operating_cost = round( ($item['rate']['operating_cost'] * 19), 2 );
                        $rez_item_db->op_one_pickup = $item['from']['date'];
                        $rez_item_db->save();

                        $sales_db = new Sales;
                        $sales_db->description = "Transportation";
                        $sales_db->quantity = 1;
                        $sales_db->total = $item['rate']['amount'];
                        $sales_db->call_center_agent_id = 0;
                        $sales_db->sale_type_id = 1;
                        $sales_db->reservation_id = $rez_db->id;
                        $sales_db->save();

                        $follow_up_db = new ReservationsFollowUp;
                        $follow_up_db->name = 'Automated message';
                        $follow_up_db->text = $item['vehicle']['name'];
                        $follow_up_db->type = 'OPERATION';
                        $follow_up_db->reservation_id = $rez_db->id;
                        $follow_up_db->save();
                        
                        DB::commit();

                        MT::acceptReservation($item['id']);
                        
                    endif;
                }else{
                    MT::acceptReservation($item['id']);
                }

            endforeach;

            $html = $this->HTML();
            
            $email_data = array(
                "Messages" => array(
                    array(
                        "From" => array(
                            "Email" => "bookings@caribbean-transfers.com",
                            "Name" => "Bookings"
                        ),
                        "To" => array(
                            array(
                                "Email" => "bookings@caribbean-transfers.com",
                                "Name" => "Reservaciones"
                            )
                        ),
                        "Subject" => 'NEW - Master Tours API | '.date("Y-m-d H:i"),
                        "HTMLPart" => $html
                    )
                )
            );

            $this->sendMailjet($email_data);

            return response()->json($this->data, 200);
            
        } catch (\Exception $e) {
            DB::rollback();

            $email_data = array(
                "Messages" => array(
                    array(
                        "From" => array(
                            "Email" => "bookings@caribbean-transfers.com",
                            "Name" => "Bookings"
                        ),
                        "To" => array(
                            array(
                                "Email" => "development@caribbean-transfers.com",
                                "Name" => "Development"
                            )
                        ),
                        "Subject" => 'ERROR | Master Tours API',
                        "TextPart" => "Error al procesar el api de master tours: ".$e->getMessage()
                    )
                )
            );

            $this->sendMailjet($email_data);

            return response()->json([ 'error' => $e->getMessage() ], 500);
        }        
    }

    public function HTML(){

        $emailContent = '<h1>Resumen de Reservas '.date("Y-m-d H:i").' se recibieron '. sizeof($this->data) .' reservaciones.</h1>';
        $emailContent .= '<table border="1" cellspacing="0" cellpadding="5" style="border-collapse: collapse; width: 100%;">';
        $emailContent .= '
            <thead>
                <tr>
                    <th style="font-size:10pt;">ID</th>
                    <th style="font-size:10pt;">Referencia</th>
                    <th style="font-size:10pt;">Cliente</th>
                    <th style="font-size:10pt;">Teléfono</th>
                    <th style="font-size:10pt;">Vuelo</th>
                    <th style="font-size:10pt;">Origen</th>
                    <th style="font-size:10pt;">Destino</th>
                    <th style="font-size:10pt;">Vehículo</th>
                    <th style="font-size:10pt;">Pasajeros</th>
                    <th style="font-size:10pt;">Fecha y Hora</th>
                </tr>
            </thead>
            <tbody>';

            foreach ($this->data as $booking) {
                $emailContent .= '<tr>';
                $emailContent .= '<td style="font-size:10pt;">' . $booking['id'] . '</td>';
                $emailContent .= '<td style="font-size:10pt;">' . $booking['reference'] . '</td>';
                $emailContent .= '<td style="font-size:10pt;">' . $booking['client']['name'] . '</td>';
                $emailContent .= '<td style="font-size:10pt;">' . $booking['client']['phone'] . '</td>';
                $emailContent .= '<td style="font-size:10pt;">' . $booking['client']['flight'] . '</td>';
                $emailContent .= '<td style="font-size:10pt;">' . $booking['from']['name'] . '</td>';
                $emailContent .= '<td style="font-size:10pt;">' . $booking['to']['name'] . '</td>';
                $emailContent .= '<td style="font-size:10pt;">' . $booking['vehicle']['name'] . '</td>';
                $emailContent .= '<td style="text-align:center;style="font-size:10pt;"">' . $booking['vehicle']['pax'] . '</td>';
                $emailContent .= '<td style="font-size:10pt;">' . $booking['from']['date'] . '</td>';
                $emailContent .= '</tr>';
            }

        $emailContent .= '</tbody></table>';
        return $emailContent;
    }
}