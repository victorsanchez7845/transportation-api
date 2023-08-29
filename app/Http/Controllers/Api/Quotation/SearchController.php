<?php

namespace App\Http\Controllers\Api\Quotation;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Repositories\Api\Quotation\SearchRepository;
use App\Repositories\Api\Quotation\RatesRepository;
use App\Repositories\Api\Quotation\DistanceRepository;

class SearchController extends Controller
{
    /**
     * Display the specified resource.
     *     
     * @return \Illuminate\Http\Response
     */

    public function index(Request $request, SearchRepository $search, RatesRepository $rates, DistanceRepository $distance){    
        
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:one-way,round-trip',
            'start.lat' => 'required',
            'start.lng' => 'required',
            'start.place' => 'required|max:150',            
            'start.pickup' => 'required|date_format:Y-m-d H:i',
            'end.lat' => 'required',
            'end.lng' => 'required',
            'end.place' => 'required|max:150',
            'end.pickup' => [
                'required_if:type,round-trip',
                'date_format:Y-m-d H:i',
            ],
            'language' => 'required|in:en,es',
            'passengers' => 'required|integer|min:1|max:35',
            'currency' => 'required|in:USD,MXN',
            'rate_group' => 'required|max:10',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => [
                    'code' => 'required_params',
                    'message' => $validator->errors()->all() 
                ]
            ], 422);
        }

        //Buscamos dentro de las geocercas existentes...
        $availability = $search->findDestinations($request);
        if($availability == false){            
            return response()->json([
                'error' => [
                    'code' => 'availability',
                    'message' => 'Sorry, we have no availability'
                ]
            ], 404);
        }

        //Buscamos tarifas disponibles por destino...
        $prices = $rates->check($availability, $request);
        if($prices == false){
            return response()->json([
                'error' => [
                    'code' => 'rates',
                    'message' => 'Rates not found'
                ]
            ], 404);
        }

        //Obtenemos el tiempo y distancia de diferencia entre el punto A y B
        $geospacial = $distance->get($request);

        $data = [
            "places" => [
                "one_way" => [
                    "init" => [
                        "name" => $request['start']['place'],
                        "geo" => [
                            "lat" => $request['start']['lat'],
                            "lng" => $request['start']['lng'],
                        ],
                        "time" => $request['start']['pickup'],
                    ],
                    "end" => [
                        "name" => $request['end']['place'],
                        "geo" => [
                            "lat" => $request['end']['lat'],
                            "lng" => $request['end']['lng'],
                        ],
                        "time" => date("Y-m-d H:i", strtotime($request['start']['pickup']) + $geospacial['time_seconds'])
                    ]
                ],
                "round_trip" => [
                    "init" => [                        
                        "name" => $request['end']['place'],
                        "geo" => [
                            "lat" => $request['end']['lat'],
                            "lng" => $request['end']['lng'],
                        ],
                        "time" => ((isset( $request['end']['pickup'] ))? $request['end']['pickup'] : NULL),
                    ],
                    "end" => [
                        "name" => $request['start']['place'],
                        "geo" => [
                            "lat" => $request['start']['lat'],
                            "lng" => $request['start']['lng'],
                        ],
                        "time" => ((isset( $request['end']['pickup'] ))? date("Y-m-d H:i", strtotime($request['end']['pickup']) + $geospacial['time_seconds']) : NULL)                        
                    ]
                ],
                "distance" => $geospacial['distance'],
                "time" => $geospacial['time'],
                "config" => [
                    "flight_required" => (($availability['start']['data']['zone']['is_primary'] == 1)? true : false)
                ]
            ],
            "items" => $prices
        ];
        
        if($request['type'] == "one-way"){
            unset( $data['places']['round_trip'] );
        }
        
        return response()->json($data, 200);
    }
}
