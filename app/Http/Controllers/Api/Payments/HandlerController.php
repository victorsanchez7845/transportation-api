<?php

namespace App\Http\Controllers\Api\Payments;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Api\Payments\StripeRepository;
use App\Repositories\Api\Payments\PaypalRepository;
use App\Repositories\Api\Payments\MifelRepository;
use Illuminate\Support\Facades\Validator;

class HandlerController extends Controller
{
    public function index(Request $request, StripeRepository $handlerStripe, PaypalRepository $handlerPaypal, MifelRepository $handlerMifel){
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:STRIPE,PAYPAL,MIFEL,PAYPAL-1',
            'id' => 'integer',
            'language' => 'required|in:en,es',
            'success_url' => 'required',
            'cancel_url' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                    'error' => [
                        'code' => 'required_params',
                        'message' =>  $validator->errors()->all() 
                    ]
                ], 404);
        }

        if($request->type == "STRIPE"):
            $items = $handlerStripe->check($request);
        endif;
        if($request->type == "PAYPAL"):
            $items = $handlerPaypal->check($request);
        endif;
        if($request->type == "PAYPAL-1"):
            $items = $handlerPaypal->check($request, 1);
        endif;
        if($request->type == "MIFEL"):
            $items = $handlerMifel->check($request);
        endif;

        if($items['status'] == false){
            return response()->json([
                'error' => [
                    'code' => $items['code'],
                    'message' => $items['message']
                ]
            ], 404);            
        }

        return response()->json($items['data'], 200);
       
    }
}
