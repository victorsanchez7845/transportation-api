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

        $searchDB = $this->searcDB($request);
        if($searchDB != false):
            return $searchDB;
        endif;

        return false;
        
        $data = $this->sendAws($request->keyword);

        if($data == false){
            return false;
        }
                
        return $data;
    }
    
    public function legacySend($keyword = ''){
        
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

    function sendGoogle($query) {        
                
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
                                "address" => $address,
                                "type" => "GCP",
                                "geo" => [
                                    "lat" => $responseDataDetails['result']['geometry']['location']['lat'],
                                    "lng" => $responseDataDetails['result']['geometry']['location']['lng'],
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
    
    public function sendAws($keyword = ''){
                
        $headers = array(
            'Content-Type: application/json',
        );
        
        $URL = "https://places.geo.us-east-1.amazonaws.com/places/v0/indexes/caribbean-transfers/search/text?key=v1.public.eyJqdGkiOiI0YmIxNWQ1NC1lYTc3LTQwMmQtYTMwNi0yNzc4YTU2ZWNjZWUifWHU8xowy-yNSmwa3JJvIomxTwiewFnXSoT9v6RgBjopSXfMd4mJgKNcG2EhBeTJarJzagTIop--qWWW50SiS1MOcD9XciGmjI8sOwhIu6RrQJNfqpyvOgbatMvVPalN9GT1NYFS4zNBtsuSlVfnsQTPdi9lv-kL6f5qBMWmsKnt9YYD3br2LZIo3s6Xkvk7SRUPNY4dB4lQcUnIfS50Skd6N3KhBYa1ONekm0b9LJtZfUHPGP0r6_cEdsqP-FJ8MLaG8o9PRe7Oqi-m_J1zmA5iR-Lo6TGp840sJ0u0rj9yOXlc8ezQ_FZG7zqZtasydgfr4sE7oIRO4go4AGnAfAI.ZWU0ZWIzMTktMWRhNi00Mzg0LTllMzYtNzlmMDU3MjRmYTkx";

        $data = [
            "Text" => $keyword,
            "FilterCountries" => ["MEX"],
            "MaxResults" => 15
        ];

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));

        curl_setopt($curl, CURLOPT_URL, $URL);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);        
        $response = curl_exec($curl);        
        if ($response === false) {
            curl_close($curl);
            return false;
        }
        
        $data = json_decode($response, true);
        
        if( isset( $data['Results'] ) && sizeof($data['Results']) <= 0 ):
            return false;
        endif;
        
        $items = [];
        foreach($data['Results'] as $key => $value):
            
            $place = $this->awsSplitData($value['Place']['Label']);
            
            $items[] = [
                "name" => $place['name'],
                "address" => $place['address'],
                "type" => "GCP",
                "geo" => [
                    "lat" => $value['Place']['Geometry']['Point'][1],
                    "lng" => $value['Place']['Geometry']['Point'][0],
                ]
            ];        
        endforeach;

        return $items;
    }

    private function awsSplitData($string) {
        // Buscamos la primera coma en la cadena
        $posicion = strpos($string, ',');
        
        // Si no se encuentra una coma, devolvemos un arreglo vacío
        if ($posicion === false) {
            return [];
        }
        
        // Dividimos la cadena en dos partes
        $name = substr($string, 0, $posicion);
        $address = substr($string, $posicion + 1);
        
        // Eliminamos espacios en blanco adicionales
        $name = trim($name);
        $address = trim($address);
        
        return [
            "name" => $name,
            "address" => $address
        ];        
    }
}
