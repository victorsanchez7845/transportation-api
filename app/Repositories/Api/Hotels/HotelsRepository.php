<?php

namespace App\Repositories\Api\Hotels;
use Illuminate\Support\Facades\DB;
use App\Models\Autocomplete;
use App\Traits\FunctionsTrait;

class HotelsRepository{
    private $data = [];
    use FunctionsTrait;

    public function save($request, $data){

        $request['name'] = str_replace("'", "", $request['name']);
        $request['name'] = str_replace("(", "", $request['name']);
        $request['name'] = str_replace(")", "", $request['name']);
        $request['name'] = str_replace("`", "", $request['name']);
        $request['name'] = str_replace("-", "", $request['name']);
        
        $autocomplete = new Autocomplete();        
        $autocomplete->name = $request['name'];
        $autocomplete->address = $request['address'];
        $autocomplete->latitude = $request['start']['lat'];
        $autocomplete->longitude = $request['start']['lng'];
        $autocomplete->zone_id = $data['start']['data']['zone']['id'];        
        if( $autocomplete->save() ){
            return true;
        }else{
            return false;
        }
    }

    public function get($request){                
        $items = [];

        //Inglés
        $rez_en = DB::select("SELECT
                            au.id, au.name, au.address, au.latitude, au.longitude, au.zone_id
                        FROM autocomplete AS au
                        WHERE au.zone_id = :code
                        ORDER BY au.name ASC",
                        [
                            'code' => $request['code']
                        ]);


        //Español
        $rez_es = DB::select("SELECT
                            au.id, aut.name, au.address, au.latitude, au.longitude, au.zone_id
                        FROM autocomplete_translate AS aut
                        INNER JOIN autocomplete as au ON au.id = aut.autocomplete_id
                        WHERE au.zone_id = :code
                        ORDER BY au.name ASC",
                        [
                            'code' => $request['code']
                        ]);
        
        $rez = array_merge($rez_en, $rez_es);

        if( sizeof($rez) <= 0) return [];

        foreach( $rez as $key => $value ):
            $items[] = [
                "id" => $value->id,
                "name" => $value->name,
                "slug" => FunctionsTrait::slug($value->name),
                "address" => $value->address,
                "geo" => [
                    "zone" => $value->zone_id,
                    "lat" => $value->latitude,
                    "lng" => $value->longitude,
                ]
            ];
        endforeach;
        
        return $items;
    }
}