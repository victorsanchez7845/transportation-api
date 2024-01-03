<?php

namespace App\Http\Controllers\Api\Hotels;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Repositories\Api\Quotation\SearchRepository;
use App\Repositories\Api\Hotels\HotelsRepository;

class HotelsController extends Controller
{
    /**
     * Display the specified resource.
     *     
     * @return \Illuminate\Http\Response
     */

     public function index(Request $request, SearchRepository $search, HotelsRepository $hotels){
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:125',
            'address' => 'required|max:250',
            'start.lat' => 'required',
            'start.lng' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => [
                    'code' => 'required_params',
                    'message' =>  $validator->errors()->all() 
                ]
            ], 404);
        }

        $search->setAtributes($request);        
        $data = $search->checkHotelZone();
        if($data == false):
            return response()->json([
                'error' => [
                    'code' => 'not_found',
                    'message' => 'No availability'
                ]
            ], 422);
        endif;

        $insert = $hotels->save($request, $data);


        if($insert == false):
            return response()->json([
                'error' => [
                    'code' => 'insert_error',
                    'message' => 'Error adding hotel'
                ]
            ], 422);
        endif;

        return response()->json(['status' => "success"], 200);
    }
}