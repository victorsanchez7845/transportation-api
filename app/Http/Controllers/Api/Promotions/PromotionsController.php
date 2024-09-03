<?php

namespace App\Http\Controllers\Api\Promotions;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Api\Promotions\PromotionsRepository;
use Illuminate\Support\Facades\Validator;
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

    public function download(Request $request){

        $validator = Validator::make($request->all(), [
            'coupons' => 'required',
            'code' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                    'error' => [
                        'code' => 'required_params',
                        'message' =>  $validator->errors()->all() 
                    ]
                ], 404);
        }

        try {
            
            $pdf = Pdf::loadView('promotions.download', []);
            return $pdf->stream();

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