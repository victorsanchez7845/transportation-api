<?php

namespace App\Http\Controllers\Api\Quotation;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Repositories\Api\Quotation\CreationRepository;
use App\Repositories\Api\Quotation\DistanceRepository;
use App\Repositories\Api\Quotation\SearchRepository;
use App\Repositories\Api\Reservation\SearchRepository as ReservationSearch;
use App\Traits\TokenTrait;
use DateTime;

class CreationController extends Controller
{
    use TokenTrait;
    
    public function index(Request $request, CreationRepository $creation, DistanceRepository $distance, SearchRepository $search, ReservationSearch $res_search){
        
        // Realizar validaciones
        $validator = Validator::make($request->all(), [
            'service_token' => 'required',
            'first_name' => 'required|max:75',
            'last_name' => 'required|max:75',
            'email_address' => 'required|email|max:85',
            'phone' => 'required',
            'site_id' => 'required|integer',
            'call_center_agent' => 'integer',
            'flight_number' => 'max:35',
            'pay_at_arrival' => 'integer|min:0|max:1',
            'affiliate_id' => 'integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => [
                    'code' => 'required_params',
                    'message' =>  $validator->errors()->all() 
                ]
            ], 404);
        }

        $bearer = request()->bearerToken();
        $creation->setData($bearer, $request);

        $service_token = $creation->checkServiceToken($request);
        if($service_token == false){
            return response()->json([
                'error' => [
                    'code' => 'service_token_invalid',
                    'message' => 'The service token is invalid' 
                ]
            ], 404);
        }
        
        $data = $creation->create($distance, $search);

        if($data['status'] == false){
            return response()->json([
                'error' => [
                    'code' => $data['code'],
                    'message' => $data['message'],
                ]
            ], 404);
        }

        $dateTime = new DateTime($request['data']['request']['start']['pickup']);
        $departure_date = $dateTime->format('Y-m-d');
        $departure_date_today = ( $departure_date == date('Y-m-d') ? true : false );
        $this->sendToSocketIoContent(array(
            'success' => $data['status'],
            'date' => $departure_date,
            'today' => $departure_date_today,
            'message' => 'Se agrego servicio correctamente, para '.(  $departure_date_today ? " el día de hoy, es necesario recargar la pagina para actualizar la información " : " la fecha ".$departure_date ),
        ));
        
        //Buscamos la reservación creada        
        $res_search->setData( new \Illuminate\Http\Request( $data['data'] ) );
        $data = $res_search->search();
        
        if($data == false){
            return response()->json([
                'error' => [
                    'code' => 'not_found',
                    'message' => 'Reservation not found'
                ]
            ], 404);
        }

        return response()->json($data, 200);
    }

    private function sendToSocketIoCurl($data)
    {
        // URL del servidor Express
        $url = "http://localhost:4000/createBooking";

        // Configurar cURL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

        // Ejecutar y obtener la respuesta
        $response = curl_exec($ch);
        curl_close($ch);

        // Retornar respuesta (opcional)
        // dump($response);
        return response()->json(['response' => $response]);    
    }

    private function sendToSocketIoContent($data)
    {
        // URL del servidor Express
        $url = "https://socket-caribbean-transfers.up.railway.app/createBooking";

        // Convertir datos a JSON
        $dataJson = json_encode($data);

        // Configurar encabezados
        $options = [
            'http' => [
                'header'  => "Content-Type: application/json\r\n" .
                             "Content-Length: " . strlen($dataJson) . "\r\n",
                'method'  => 'POST',
                'content' => $dataJson,
            ],
        ];

        // Crear contexto de la solicitud
        $context  = stream_context_create($options);

        // Realizar la solicitud
        $result = @file_get_contents($url, false, $context);

        if ($result === FALSE) {
            // Manejar el error
            return response()->json(['error' => 'Error al consultar la URL'], 500);
        }

        // Retornar la respuesta del servidor
        return response()->json(['response' => json_decode($result)], 200);
    }    
}
