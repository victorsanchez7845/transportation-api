<?php

namespace App\Http\Controllers\Api\Terms;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class TermsController extends Controller
{
    public function index(Request $request){

        // Realizar validaciones
        $validator = Validator::make($request->all(), [
            'brand' => 'required|max:50',
            'email' => 'required|email|max:75',
            'language' => 'required|in:en,es',
            'phone_US' => 'required',
            'phone_MX' => 'required',
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
            "phone_US" => $request->phone_US,
            "phone_MX" => $request->phone_MX        
        ];

        return view('terms-and-conditions.general', [ 'lang' =>'en', 'data' => $data ]);
    }
}