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
}
