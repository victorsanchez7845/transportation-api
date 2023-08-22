<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {    

        #Destinos
        DB::table('destinations')->insert([
            'name' => 'Cancun',
            'status' => 1,
            'cut_off' => 12,
            'time_zone' => 'America/Cancun',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        #Zonas
        DB::table('zones')->insert([
            'destination_id' => 1,
            'name' => "Cancun Airport",
            'is_primary' => 1,
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('zones')->insert([
            'destination_id' => 1,
            'name' => "Cancun Downtown",
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('zones')->insert([
            'destination_id' => 1,
            'name' => "Cancun Hotel Zone",
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('zones')->insert([
            'destination_id' => 1,
            'name' => "Tulum",
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        #Puntoz de Zonas - Cancun Airport
        DB::table('zones_points')->insert([
            'zone_id' => 1,
            'latitude' => 21.020191,
            'longitude' => -86.854778,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('zones_points')->insert([
            'zone_id' => 1,
            'latitude' => 21.038780,
            'longitude' => -86.851311,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('zones_points')->insert([
            'zone_id' => 1,
            'latitude' => 21.063410,
            'longitude' => -86.890800,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('zones_points')->insert([
            'zone_id' => 1,
            'latitude' => 21.048610,
            'longitude' => -86.901067,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        #Puntoz de Zonas - Cancun Downtown
        DB::table('zones_points')->insert([
            'zone_id' => 2,
            'latitude' => 21.044412,
            'longitude' => -86.845496,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('zones_points')->insert([
            'zone_id' => 2,
            'latitude' => 21.174695,
            'longitude' => -86.816541,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('zones_points')->insert([
            'zone_id' => 2,
            'latitude' => 21.207058,
            'longitude' => -86.853767,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('zones_points')->insert([
            'zone_id' => 2,
            'latitude' => 21.161289,
            'longitude' => -86.946835,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('zones_points')->insert([
            'zone_id' => 2,
            'latitude' => 21.093796,
            'longitude' => -86.907953,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

         #Puntoz de Zonas - Cancun Hotel Zone
         DB::table('zones_points')->insert([
            'zone_id' => 3,
            'latitude' => 21.020191,
            'longitude' => -86.808859,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('zones_points')->insert([
            'zone_id' => 3,
            'latitude' => 21.170483,
            'longitude' => -86.815278,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('zones_points')->insert([
            'zone_id' => 3,
            'latitude' => 21.152723,
            'longitude' => -86.732705,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('zones_points')->insert([
            'zone_id' => 3,
            'latitude' => 21.020080,
            'longitude' => -86.778166,
            'created_at' => now(),
            'updated_at' => now(),
        ]);


        #Servicios
        DB::table('destination_services')->insert([
            'name' => 'Taxi',
            'passengers' => 3,
            'luggage' => 4,
            'order' => 1,
            'destination_id' => 1,
            'status' => 1,
            'image_url' => '',
            'price_type' => 'vehicle',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('destination_services')->insert([
            'name' => 'Private service',
            'passengers' => 8,
            'luggage' => 5,
            'order' => 2,
            'destination_id' => 1,
            'status' => 1,
            'image_url' => '',
            'price_type' => 'passenger',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('destination_services')->insert([
            'name' => 'Shared Shuttle',
            'passengers' => 8,
            'luggage' => 5,
            'order' => 3,
            'destination_id' => 1,
            'status' => 1,
            'image_url' => '',
            'price_type' => 'shared',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        #Servicios - Traducciones
        DB::table('destination_services_translate')->insert([
            'lang' => 'es',
            'translation' => "Taxi ES",
            'destination_services_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        #Servicios - Traducciones
        DB::table('destination_services_translate')->insert([
            'lang' => 'es',
            'translation' => "Servicio privado",
            'destination_services_id' => 2,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('destination_services_translate')->insert([
            'lang' => 'es',
            'translation' => "Transportación compartida",
            'destination_services_id' => 3,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        //Rate Groups
        DB::table('rates_groups')->insert([
            'name' => "DEFAULT",
            'code' => "xLjDl18",
            'created_at' => now(),
            'updated_at' => now(),
        ]);
       
        //Cancun Downtown
        #Precios - Taxi
        DB::table('rates')->insert([
            'destination_service_id' => 1,
            'destination_id' => 1,
            'rate_group_id' => 1,
            'zone_id' => 2,
            'one_way' => 34,
            'round_trip' => 60,
            'ow_12' => 0,
            'rt_12' => 0,
            'ow_37' => 0,
            'rt_37' => 0,
            'up_8_ow' => 0,
            'up_8_rt' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        #Precios - Private
        DB::table('rates')->insert([
            'destination_service_id' => 2,
            'destination_id' => 1,
            'rate_group_id' => 1,
            'zone_id' => 2,
            'one_way' => 0,
            'round_trip' => 0,
            'ow_12' => 30,
            'rt_12' => 55,
            'ow_37' => 40,
            'rt_37' => 75,
            'up_8_ow' => 50,
            'up_8_rt' => 95,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('rates')->insert([
            'destination_service_id' => 3,
            'destination_id' => 1,
            'rate_group_id' => 1,
            'zone_id' => 2,
            'one_way' => 10,
            'round_trip' => 18,
            'ow_12' => 0,
            'rt_12' => 0,
            'ow_37' => 0,
            'rt_37' => 0,
            'up_8_ow' => 0,
            'up_8_rt' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        //Cancun Hotel Zone
        #Precios - Taxi
        DB::table('rates')->insert([
            'destination_service_id' => 1,
            'destination_id' => 1,
            'rate_group_id' => 1,
            'zone_id' => 3,
            'one_way' => 34,
            'round_trip' => 60,
            'ow_12' => 0,
            'rt_12' => 0,
            'ow_37' => 0,
            'rt_37' => 0,
            'up_8_ow' => 0,
            'up_8_rt' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        #Precios - Private
        DB::table('rates')->insert([
            'destination_service_id' => 2,
            'destination_id' => 1,
            'rate_group_id' => 1,
            'zone_id' => 3,
            'one_way' => 0,
            'round_trip' => 0,
            'ow_12' => 30,
            'rt_12' => 55,
            'ow_37' => 40,
            'rt_37' => 75,
            'up_8_ow' => 50,
            'up_8_rt' => 95,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('rates')->insert([
            'destination_service_id' => 3,
            'destination_id' => 1,
            'rate_group_id' => 1,
            'zone_id' => 3,
            'one_way' => 12,
            'round_trip' => 22,
            'ow_12' => 0,
            'rt_12' => 0,
            'ow_37' => 0,
            'rt_37' => 0,
            'up_8_ow' => 0,
            'up_8_rt' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]); 

        //Tarfias de Transfers
        DB::table('rates_transfers')->insert([
            'destination_service_id' => 1,
            'destination_id' => 1,
            'rate_group_id' => 1,
            'zone_one' => 2,
            'zone_two' => 3,
            'one_way' => 34,
            'round_trip' => 60,
            'ow_12' => 0,
            'rt_12' => 0,
            'ow_37' => 0,
            'rt_37' => 0,
            'up_8_ow' => 0,
            'up_8_rt' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);


        //Tipo de Cambio
        DB::table('exchange_rate')->insert([
            'origin' => "USD",
            'destination' => "MXN",
            'exchange_rate' => 18.00,
            'operation' => "division",
            'created_at' => now(),
            'updated_at' => now(),
        ]);


        //API
        DB::table('api')->insert([
            'user' => "api",
            'secret' => "1234567890",
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        
    }

}
