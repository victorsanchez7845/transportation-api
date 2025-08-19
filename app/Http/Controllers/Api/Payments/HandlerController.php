<?php

namespace App\Http\Controllers\Api\Payments;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Api\Payments\StripeRepository;
use App\Repositories\Api\Payments\StripeElementsRepository;
use App\Repositories\Api\Payments\PaypalRepository;
use App\Repositories\Api\Payments\MifelRepository;
use App\Repositories\Api\Payments\SantanderRepository;
use Illuminate\Support\Facades\Validator;

class HandlerController extends Controller
{
    public function index(Request $request, StripeRepository $handlerStripe, PaypalRepository $handlerPaypal, MifelRepository $handlerMifel, SantanderRepository $handlerSantander){
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:STRIPE,STRIPE-2,PAYPAL,MIFEL,PAYPAL-1,PAYPAL-V2,SANTANDER',
            'id' => 'integer',
            'language' => 'required|in:en,es',
            'success_url' => 'required',
            'cancel_url' => 'required',
            'redirect' => 'integer',
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
        if($request->type == "STRIPE-2"): //Nueva cuenta de Stripe para probar
            $items = $handlerStripe->check($request);
        endif;
        if($request->type == "PAYPAL"):
            $items = $handlerPaypal->check($request);
        endif;
        if($request->type == "PAYPAL-1"):
            $items = $handlerPaypal->check($request, 1);
        endif;
        if($request->type == "PAYPAL-V2"):
            $items = $handlerPaypal->orders($request, 1);
        endif;
        if($request->type == "MIFEL"):
            $items = $handlerMifel->check($request);
        endif;
        if($request->type == "SANTANDER"):
            $items = $handlerSantander->check($request);
        endif;

        if($items['status'] == false){
            if($items['code'] == "cancelled" && $request->language == "es"):
                $items['message'] = "Su reserva ha sido cancelada, si desea reactivarla póngase en contacto con nosotros.";
            endif;
            
            return response()->json([
                'error' => [
                    'code' => $items['code'],
                    'message' => $items['message']
                ]
            ], 404);            
        }

        if(isset( $request->redirect ) && $request->redirect == 1):
            return redirect()->away($items['data']['url']);
        endif;

        return response()->json($items['data'], 200);
       
    }

    public function indexStripeElements(Request $request, StripeElementsRepository $handlerStripe){
        $validator = Validator::make($request->all(), [
            'language' => 'required|in:en,es',
            'total' => 'required',
            'currency' => 'required|in:USD,MXN'
        ]);

        if ($validator->fails()) {
            return response()->json([
                    'error' => [
                        'code' => 'required_params',
                        'message' =>  $validator->errors()->all() 
                    ]
                ], 404);
        }

        $items = $handlerStripe->check($request);

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

    public function mifelValidate(Request $request, MifelRepository $handlerMifel){

        $validator = Validator::make($request->all(), [            
            'id' => 'required',            
        ]);

        if ($validator->fails()) {
            return response()->json([
                    'error' => [
                        'code' => 'required_params',
                        'message' =>  $validator->errors()->all() 
                    ]
                ], 404);
        }

        $items = $handlerMifel->validate($request);
        if($items == false){
            return response()->json([
                'error' => [
                    'code' => 'declined',
                    'message' => 'The bank had an error in returning the data.'
                ]
            ], 404);
        }

        return response()->json([], 200);
    }

    public function payPalCaptureOrder(Request $request, PaypalRepository $handlerPaypal){
        $validator = Validator::make($request->all(), [            
            'id' => 'required',            
        ]);

        if ($validator->fails()) {
            return response()->json([
                    'error' => [
                        'code' => 'required_params',
                        'message' =>  $validator->errors()->all() 
                    ]
                ], 404);
        }

        $items = $handlerPaypal->ordersCapture($request);
        if($items == false){
            return response()->json([
                'error' => [
                    'code' => 'order_capture',
                    'message' => 'Error capturing the order'
                ]
            ], 404);
        }

        return response()->json($items, 200);
    }
}
