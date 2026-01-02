<?php

namespace App\Http\Controllers\Api\Quotation;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Repositories\Api\Quotation\SearchRepository;
use App\Repositories\Api\Quotation\RatesRepository;
use App\Repositories\Api\Quotation\DistanceRepository;
use Carbon\Carbon;

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
            'passengers' => 'required|integer|min:1|max:150',
            'currency' => 'required|in:USD,MXN',
            'rate_group' => 'required|max:10',
            'service' => 'integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => [
                    'code' => 'required_params',
                    'message' => $validator->errors()->all() 
                ]
            ], 422);
        }

        // -------------- Capa protectora de horario de madrugada
        if ( !isset($request->is_tpv) ) {
            $now = Carbon::now();

            $oneWayDate = Carbon::createFromFormat('Y-m-d H:i', $request['start']['pickup']);
            $roundTripDate = isset($request['end']['pickup'])
                ? Carbon::createFromFormat('Y-m-d H:i', $request['end']['pickup'])
                : null;

            /*
            |---------------------------------------------
            | Configuración del rango de madrugada
            |---------------------------------------------
            */
            $nightStartHour = 22;
            $nightStartMinute = 0;

            $dayEndHour = 8;
            $dayEndMinute = 30;

            /*
            |---------------------------------------------
            | Determinar el "día lógico" de la madrugada
            |---------------------------------------------
            | Si aún no pasamos la hora final, seguimos
            | en la madrugada iniciada el día anterior
            */
            $nightEndToday = $now->copy()->setTime($dayEndHour, $dayEndMinute);

            if ($now->lt($nightEndToday)) {
                $baseDay = $now->copy()->subDay()->startOfDay();
            } else {
                $baseDay = $now->copy()->startOfDay();
            }

            /*
            |---------------------------------------------
            | Construcción de la ventana de riesgo
            |---------------------------------------------
            */
            $dangerStart = $baseDay->copy()->setTime($nightStartHour, $nightStartMinute);
            $dangerEnd   = $baseDay->copy()->addDay()->setTime($dayEndHour, $dayEndMinute);

            $nowInDangerZone = $now->between($dangerStart, $dangerEnd, true);

            $oneWayInDangerZone = $oneWayDate->between($dangerStart, $dangerEnd, true);
            $roundTripInDangerZone = $roundTripDate
                ? $roundTripDate->between($dangerStart, $dangerEnd, true)
                : false;

            if (
                $nowInDangerZone &&
                ($oneWayInDangerZone || $roundTripInDangerZone)
            ) {
                return response()->json([
                    'error' => [
                        'code' => 'availability',
                        'message' => 'Sorry, we have no availability'
                    ]
                ], 404);
            }

            if(false) { // Esta protección, es sólo por si de emergencia se necesitan bloquear los servicios el mero 31 (temporal)
                $start_date = '2025-12-31 00:00';
                $end_date   = '2026-01-01 23:59';
        
                $one_way_date = Carbon::createFromFormat('Y-m-d H:i', $request['start']['pickup']);
                if(isset($request['end']['pickup'])) $round_trip_date = Carbon::createFromFormat('Y-m-d H:i', $request['end']['pickup']);
        
                $start = Carbon::createFromFormat('Y-m-d H:i', $start_date);
                $end   = Carbon::createFromFormat('Y-m-d H:i', $end_date);
        
                if (
                    $one_way_date->between($start, $end, true) ||
                    (isset($round_trip_date) && $round_trip_date->between($start, $end, true))
                ) {
                    return response()->json([
                        'error' => [
                            'code' => 'availability',
                            'message' => 'Sorry, we have no availability'
                        ]
                    ], 404);
                }
            }
        }
        // -------------- Capa protectora de horario de madrugada

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
        $geospacial = $distance->get($request, false);

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
                    "flight_required" => (($availability['start']['data']['zone']['is_primary'] == 1)? true : false),
                    "iata_code" => (( isset($availability['start']['data']['zone']['iata_code']) && !empty($availability['start']['data']['zone']['iata_code']) )? $availability['start']['data']['zone']['iata_code'] : NULL)
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
