<?php

namespace App\Repositories\Api\Quotation;
use Illuminate\Support\Facades\DB;
use App\Models\Autocomplete;
use App\Models\AutocompleteTranslate;

class AutocompleteRepository{

    public function searcDB($request){
   
        $term = preg_replace('/[^a-zA-Z0-9_ ]/', '', $request->keyword);

        $data = DB::select("
                    SELECT
                        aut.id, aut.latitude, aut.longitude,
                    CASE
                        WHEN aut.name LIKE '%{$term}%' THEN aut.name
                        ELSE aut_trans.name
                    END AS name,
                        aut.address
                    FROM autocomplete as aut
                    LEFT JOIN autocomplete_translate as aut_trans ON aut_trans.autocomplete_id = aut.id
                    WHERE aut.name LIKE '%{$term}%' OR aut_trans.name LIKE '%{$term}%' limit 25");        

        if( sizeof($data) <= 0 ){
            return false;
        }        

        $items = [];
        foreach($data as $key => $value):
            $items[] = [
                "name" => $value->name,
                "address" => $value->address,
                "type" => "DEFAULT",
                "geo" => [
                    "lat" => $value->latitude,
                    "lng" => $value->longitude
                ]
            ];
        endforeach;

        return $items;

    }

    public function search($request){

        /*$searchDB = $this->searcDB($request);
        if($searchDB != false):
            return $searchDB;
        endif;*/

        //return false;
        
        $data = $this->sendNew($request->keyword);

        if($data == false){
            return false;
        }

        $items = [];
        foreach($data as $key => $value):
            $items[] = [
                "name" => $value['name'],
                "address" => $value['formatted_address'],
                "type" => "GCP",
                "geo" => [
                    "lat" => $value['geometry']['location']['lat'],
                    "lng" => $value['geometry']['location']['lng'],
                ]
            ];
        endforeach;

        return $items;
    }
    
    public function send($keyword = ''){
        
        $api_url = "https://maps.googleapis.com/maps/api/place/textsearch/json?query=". urlencode($keyword) . "&location=21.0442704,-86.8747223&radius=400&key=".config('services.maps.key');
    
        $curl = curl_init();        
        curl_setopt($curl, CURLOPT_URL, $api_url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);        
        $response = curl_exec($curl);        
        if ($response === false) {
            curl_close($curl);
            return false;
        }

        $data = json_decode($response, true);
        if($data['status'] != "OK"){
            return false;
        }

        return $data['results'];
    }

    function sendNew($query) {        
                
        // Paso 1: Obtener el place_id usando la API de Autocomplete
        $urlAutocomplete = 'https://maps.googleapis.com/maps/api/place/autocomplete/json?input=' . urlencode($query) . '&components=country:mx&location=21.0419282,-86.8769593&radius=400000&strictbounds=true&key=' . config('services.maps.key');
    
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $urlAutocomplete);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $responseAutocomplete = curl_exec($ch);
        curl_close($ch);
    
        $responseDataAutocomplete = json_decode($responseAutocomplete, true);
        $items = [];

        if(isset( $responseDataAutocomplete['status'] ) && $responseDataAutocomplete['status'] == "OK"):
            
            if( isset($responseDataAutocomplete['predictions']) && sizeof($responseDataAutocomplete['predictions']) > 0 ):
                foreach($responseDataAutocomplete['predictions'] as $keyP => $valueP):

                    $id = $valueP['place_id'];
                    $name = $valueP['structured_formatting']['main_text'];
                    $address = $valueP['structured_formatting']['secondary_text'];                    

                    // Paso 2: Obtener los detalles del lugar usando el place_id
                    $urlDetails = 'https://maps.googleapis.com/maps/api/place/details/json?place_id=' . $id . '&fields=geometry&key=' . config('services.maps.key');    
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $urlDetails);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    $responseDetails = curl_exec($ch);
                    curl_close($ch);
            
                    $responseDataDetails = json_decode($responseDetails, true);

                    if(isset( $responseDataDetails['status'] ) && $responseDataDetails['status'] == "OK"):
                        
                        if(isset( $responseDataDetails['result']['geometry']['location'] )):
                            $items[] = [
                                "name" => $name,
                                "formatted_address" => $address,
                                "geometry" => [
                                    "location" => [
                                        "lat" => $responseDataDetails['result']['geometry']['location']['lat'],
                                        "lng" => $responseDataDetails['result']['geometry']['location']['lng'],
                                    ]
                                ]
                            ];
                        else:
                            return false;
                        endif;

                    else:
                        return false;
                    endif;

                endforeach;

                return $items;

            else:
                return false;
            endif;
        else:
            return false;
        endif;
    }
    
}
