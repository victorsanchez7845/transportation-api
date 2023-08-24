<?php

namespace App\Http\Controllers\Api\Quotation;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Repositories\Api\Quotation\CreationRepository;
use App\Repositories\Api\Quotation\DistanceRepository;
use App\Repositories\Api\Quotation\SearchRepository;
use App\Traits\TokenTrait;


class CreationController extends Controller
{
    use TokenTrait;
    
    public function index(Request $request, CreationRepository $creation, DistanceRepository $distance, SearchRepository $search){
        
        // Realizar validaciones
        $validator = Validator::make($request->all(), [
            'service_token' => 'required',
            'first_name' => 'required|max:75',
            'last_name' => 'required|max:75',
            'email_address' => 'required:email|max:125',
            'phone' => 'required',
            'site_id' => 'required|integer',
            'call_center_agent' => 'integer',
            'flight_number' => 'max:10',
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
    
        die("FIN");

        
        return response()->json([], 200);
    }
}
