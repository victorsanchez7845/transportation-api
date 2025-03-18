<?php

namespace App\Http\Controllers\Api\Flights;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Repositories\Api\Flights\AirlinesRepository;

class AirlinesController extends Controller
{
    /**
     * Display the specified resource.
     *     
     * @return \Illuminate\Http\Response
     */

    public function index(Request $request, AirlinesRepository $airlines){

        $validator = Validator::make($request->all(), [            
            'language' => 'required|max:5',
            'iata' => 'required|max:5',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => [
                    'code' => 'required_params',
                    'message' => $validator->errors()->all() 
                ]
            ], 422);
        }        

        $send = $airlines->getAirlines($request);
        
        if($send == false){
            return response()->json([
                'error' => [
                    'code' => 'not_found',
                    'message' => 'No airlines found'
                ]
            ], 404);            
        }

        return response()->json($send, 200);

    }
}