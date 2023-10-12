<?php

namespace App\Http\Controllers\Api\Flights;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Repositories\Api\Flights\SearchRepository;

class SearchController extends Controller
{
    /**
     * Display the specified resource.
     *     
     * @return \Illuminate\Http\Response
     */

    public function index(Request $request, SearchRepository $search){

        $validator = Validator::make($request->all(), [
            'flight_number' => 'required',
            'date' => 'required|date_format:Y-m-d',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => [
                    'code' => 'required_params',
                    'message' => $validator->errors()->all() 
                ]
            ], 422);
        }

        $flight = $search->getFlight($request);
        if($flight == false){
            return response()->json([
                'error' => [
                    'code' => 'results_not_found',
                    'message' => 'Results not found' 
                ]
            ], 404);            
        }

        return response()->json($flight, 200);
    }

    public function searchDate(Request $request, SearchRepository $search){
        $validator = Validator::make($request->all(), [
            'iata_code' => 'required|max:6',
            'date' => 'required|date_format:Y-m-d',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => [
                    'code' => 'required_params',
                    'message' => $validator->errors()->all() 
                ]
            ], 422);
        }

        $flights = $search->getFlightsByDate($request);

        return response()->json(['all' => $flights], 200);
    }
}