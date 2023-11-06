<?php

namespace App\Http\Controllers\Api\Quotation;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Api\Quotation\AutocompleteRepository;
use Illuminate\Support\Facades\Validator;

class AutocompleteController extends Controller
{
    public function index(Request $request, AutocompleteRepository $autocomplete){
        $validator = Validator::make($request->all(), [
            'keyword' => 'required|min:3|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                    'error' => [
                        'code' => 'required_params',
                        'message' =>  $validator->errors()->all() 
                    ]
                ], 404);
        }

        $items = $autocomplete->search($request);        
        if($items == false){
            return response()->json([
                'error' => [
                    'code' => 'results_not_found',
                    'message' => 'Results not found' 
                ]
            ], 404);            
        }
        return response()->json(['items' => $items], 200);
    }

    public function affiliates(Request $request, AutocompleteRepository $autocomplete){
        $validator = Validator::make($request->all(), [
            'keyword' => 'required|min:3|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                    'error' => [
                        'code' => 'required_params',
                        'message' =>  $validator->errors()->all() 
                    ]
                ], 404);
        }

        $headers = $request->header();
        if(!isset( $headers['app-key'] )):
            return response()->json([
                'error' => [
                    'code' => 'app-key',
                    'message' => 'The api-key is needed' 
                ]
            ], 404);
        endif;

        if($headers['app-key'][0] != "bb65be85-82f9-492f-bbd6-4a698509106a"):
            return response()->json([
                'error' => [
                    'code' => 'app-key',
                    'message' => 'The api-key is invalid!' 
                ]
            ], 404);
        endif;

        $items = $autocomplete->search($request);        
        if($items == false){
            return response()->json([
                'error' => [
                    'code' => 'results_not_found',
                    'message' => 'Results not found' 
                ]
            ], 404);            
        }
        return response()->json(['items' => $items], 200);
    }
}
