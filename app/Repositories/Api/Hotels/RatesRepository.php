<?php

namespace App\Repositories\Api\Hotels;
use Illuminate\Support\Facades\DB;
use App\Traits\FunctionsTrait;
use App\Repositories\Api\Webhook\PaymentRepository;

class RatesRepository{
    private $data = [];

    public function getRates($payment){

        $exchange = $payment->getExchange("USD", "MXN");
        $zones = [
            2 => [
                "name" => "Cancún",
                "rates" => []
            ],
            3 => [
                "name" => "Puerto Juarez",
                "rates" => []
            ],
            4 => [
                "name" => "Costa Mujeres",
                "rates" => []
            ],
            5 => [
                "name" => "Puerto Morelos",
                "rates" => []
            ],
            6 => [
                "name" => "Playa del Carmen",
                "rates" => []
            ],
            8 => [
                "name" => "Puerto Aventuras",
                "rates" => []
            ],
            9 => [
                "name" => "Akumal",
                "rates" => []
            ],
            10 => [
                "name" => "Tulum",
                "rates" => []
            ],
            12 => [
                "name" => "Bahía Principe",
                "rates" => []
            ],
            13 => [
                "name" => "Playa Paraíso",
                "rates" => []
            ],
            14 => [
                "name" => "Ruta de los Cenotes",
                "rates" => []
            ],
            15 => [
                "name" => "Boca Paila",
                "rates" => []
            ],
            16 => [
                "name" => "Valladolid",
                "rates" => []
            ],
            17 => [
                "name" => "Chiquilá",
                "rates" => []
            ],
            18 => [
                "name" => "Chichén Itzá",
                "rates" => []
            ],
            19 => [
                "name" => "Mérida",
                "rates" => []
            ],
            20 => [
                "name" => "Chetumal",
                "rates" => []
            ],
            21 => [
                "name" => "Playa Mujeres",
                "rates" => []
            ]            
        ];

        foreach($zones as $key => $value):
            $rates = DB::select("SELECT 
                        r.zone_one, r.zone_two, r.one_way, r.round_trip, r.ow_12, r.rt_12, 
                        ds.id as ds_id, ds.name, ds.price_type, z.name as zone_name, z.distance, z.time
                    FROM rates_transfers as r
                        INNER JOIN destination_services as ds ON ds.id = r.destination_service_id
                        INNER JOIN zones as z ON z.id = r.zone_two
                    WHERE r.rate_group_id = 1 AND ds.status = 1 AND r.zone_one = 1 AND r.zone_two = :code 
                    ORDER BY ds.name ASC",
            [
                'code' => $key
            ]);

            if( sizeof($rates) >= 1){
                foreach($rates as $keyR => $valueR):

                    $price_OW = (( $valueR->price_type == 'passenger' )? $valueR->ow_12 : $valueR->one_way );
                    $price_RT = (( $valueR->price_type == 'passenger' )? $valueR->rt_12 : $valueR->round_trip );
                    
                    $zones[ $key ]['rates'][] = [
                        "id" => $valueR->ds_id,
                        "name" => $valueR->name,
                        "USD" => [
                            "OW" => number_format($price_OW, 2, '.', ''),
                            "RT" => number_format($price_RT, 2, '.', ''),
                        ],
                        "MXN" => [
                            "OW" => number_format($price_OW * $exchange->exchange_rate, 2, '.', ''),
                            "RT" => number_format($price_RT * $exchange->exchange_rate, 2, '.', ''),
                        ]
                    ];

                endforeach;               
            }

        endforeach;

        return $zones;
    }

}