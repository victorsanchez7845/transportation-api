<?php

namespace App\Http\Controllers\Api\Authorization;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Api\Authorization\OauthRepository;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Response;
use App\Traits\TokenTrait;

class OauthController extends Controller
{   
    use TokenTrait;

    public function index(Request $request, OauthRepository $oauth){        
        $validator = Validator::make($request->all(), [
            'user' => 'required|min:3|max:35',
            'secret' => 'required|min:3|max:100'
        ]);

        if ($validator->fails()) {
            return response()->json([
                    'error' => [
                        'code' => 'required_params', 
                        'message' =>  $validator->errors()->all() 
                    ]
                ], 422);
        }

        $user = $oauth->token($request);
        if($user == false){                    
            return response()->json([
                'error' => [
                    'code' => 'unauthorized',
                    'message' => 'Invalid user or secret' 
                ]
            ], Response::HTTP_UNAUTHORIZED);
        }
        
        if($oauth->validateStatus($user) == false){
            return response()->json([
                'error' => [
                    'code' => 'unauthorized',
                    'message' => 'User disabled please contact support' 
                ]
            ], Response::HTTP_UNAUTHORIZED);
        }

        if($oauth->validatePassword($user) == false){
            return response()->json([
                'error' => [
                    'code' => 'unauthorized',
                    'message' => 'Invalid username or secret' 
                ]
            ], Response::HTTP_UNAUTHORIZED);
        }

        $item = array(
            "api" => array(
                "id" => $user->id,
            )
        );

        $data = [
            "token" => TokenTrait::set($item, (24 * 7) ), //7 días de expiración del token
            "token_type" => "Bearer",
            "expires_in" => ( 3600 * (24 * 7) )
        ];

        return response()->json( $data, 200);
        
    }
}