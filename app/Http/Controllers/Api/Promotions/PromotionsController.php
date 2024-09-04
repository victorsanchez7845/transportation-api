<?php

namespace App\Http\Controllers\Api\Promotions;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Api\Promotions\PromotionsRepository;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\App;

use Barryvdh\DomPDF\Facade\Pdf;

class PromotionsController extends Controller
{
    public function index(Request $request, PromotionsRepository $promotions){
        $validator = Validator::make($request->all(), [
            'language' => 'required|in:en,es',
        ]);

        if ($validator->fails()) {
            return response()->json([
                    'error' => [
                        'code' => 'required_params',
                        'message' =>  $validator->errors()->all() 
                    ]
                ], 404);
        }
        
        $data = $promotions->getPromotions($request);

        return response()->json($data, 200);
    }

    public function download(Request $request, PromotionsRepository $promotions){

        $validator = Validator::make($request->all(), [
            'coupons' => 'required',
            'code' => 'required',
            'language' => 'required|in:en,es',
        ]);

        if ($validator->fails()) {
            return response()->json([
                    'error' => [
                        'code' => 'required_params',
                        'message' =>  $validator->errors()->all() 
                    ]
                ], 404);
        }

        App::setLocale($request->language);

        try {
            $check = $promotions->check($request);
            if($check == false){
                return response()->json([
                    'error' => [
                        'code' => 'invalid',
                        'message' =>  "Your reservation is not valid for the promotion"
                    ]
                ], 404);
            }

            $data = $promotions->getItems($request);
            if(sizeof($data) <= 0):
                return response()->json([
                    'error' => [
                        'code' => 'not_found',
                        'message' =>  "Promotions not found"
                    ]
                ], 404);
            endif;

            $pdf = Pdf::loadView('promotions.download', ['data' => $data]);
            $pdf->setPaper('a4', 'portrait');
            return $pdf->stream('promotions.pdf');

            return response()->json([
                'errors' => [
                    'code' => 'no_content',
                ],
                'message' => 'No content'
            ], Response::HTTP_NO_CONTENT);

        } catch (Exception $e) {
            return response()->json([
                'errors' => [
                    'code' => 'internal_server',
                    'message' => $e->getMessage()
                ],
                'message' => 'Internal Server'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }
}