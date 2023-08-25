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
            'flight_number' => 'max:10',
            'pay_at_arrival' => 'integer|min:0|max:1',
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
}
