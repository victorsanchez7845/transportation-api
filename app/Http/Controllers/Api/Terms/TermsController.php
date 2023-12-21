<?php

namespace App\Http\Controllers\Api\Terms;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class TermsController extends Controller
{
    public function terms(Request $request){

        // Realizar validaciones
        $validator = Validator::make($request->all(), [
            'brand' => 'required|max:50',
            'email' => 'required|email|max:75',
            'language' => 'required|in:en,es',
            'phoneUS' => 'required',
            'phoneMX' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => [
                    'code' => 'required_params',
                    'message' =>  $validator->errors()->all() 
                ]
            ], 404);
        }

        $data = [
            "lang" => $request->language,
            "brand" =>  $request->brand,
            "email" => $request->email,
            "phone_US" => $request->phoneUS,
            "phone_MX" => $request->phoneMX        
        ];

        return view('terms-and-conditions.general', [ 'data' => $data ]);
    }

    public function privacy(Request $request){

        // Realizar validaciones
        $validator = Validator::make($request->all(), [
            'brand' => 'required|max:50',
            'email' => 'required|email|max:75',
            'language' => 'required|in:en,es',
            'phoneUS' => 'required',
            'phoneMX' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => [
                    'code' => 'required_params',
                    'message' =>  $validator->errors()->all() 
                ]
            ], 404);
        }

        $data = [
            "lang" => $request->language,
            "brand" =>  $request->brand,
            "email" => $request->email      
        ];

        return view('privacy-policy.general', [ 'data' => $data ]);
    }
}