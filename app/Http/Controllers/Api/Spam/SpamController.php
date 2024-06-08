<?php

namespace App\Http\Controllers\Api\Spam;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

//REPOSITORYS
use App\Repositories\Api\Spam\SpamRepository;

//FACADES
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Exception;

class SpamController extends Controller
{
    private $SpamRepository;

    public function __construct(SpamRepository $SpamRepository)
    {
        $this->SpamRepository = $SpamRepository;
    }

    public function spamChangeStatus(Request $request){
        try {
            DB::beginTransaction();

            $validator = Validator::make($request->all(), [
                'id' => 'required|integer|exists:reservations_items,id',
                'status' => 'required|integer',
            ]);
    
            if ($validator->fails()) {
                return response()->json([
                    'errors' => [
                        'code' => 'required_params',                
                    ],
                    'message' =>  $validator->errors()->all()
                ], Response::HTTP_BAD_REQUEST);
            }
    
            $this->SpamRepository->spamChangeStatus($request);
            $this->SpamRepository->spamCallCount($request);

            DB::commit();
    
            return response()->json([
                'success' => true,
                'message' => "Se actualizo el estatus correctamente."
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json([
                'errors' => [
                    'code' => 'internal_server',
                    'message' => $e->getMessage()
                ],
                'message' => 'Internal Server'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // public function spamCallCount(Request $request){
    //     try {
    //         $validator = Validator::make($request->all(), [
    //             'id' => 'required|integer|exists:reservations_items,id',
    //             'status' => 'required|integer',
    //         ]);
    
    //         if ($validator->fails()) {
    //             return response()->json([
    //                 'errors' => [
    //                     'code' => 'required_params',                
    //                 ],
    //                 'message' =>  $validator->errors()->all()
    //             ], Response::HTTP_BAD_REQUEST);
    //         }
    
    //         $this->SpamRepository->spamCallCount($request);

    //         DB::commit();
    
    //         return response()->json([
    //             'success' => true,
    //             'message' => "Se actualizo el conteo correctamente."
    //         ], Response::HTTP_OK);
    //     } catch (Exception $e) {
    //         DB::rollback();
    //         return response()->json([
    //             'errors' => [
    //                 'code' => 'internal_server',
    //                 'message' => $e->getMessage()
    //             ],
    //             'message' => 'Internal Server'
    //         ], Response::HTTP_INTERNAL_SERVER_ERROR);
    //     }
    // }
}
