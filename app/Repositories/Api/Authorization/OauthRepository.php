<?php

namespace App\Repositories\Api\Authorization;
use Illuminate\Support\Facades\DB;
use App\Models\Api;
use App\Models\ApiIntent;

class OauthRepository{
    private $request = [];
    
    public function token($request = []){
        $this->request = $request->all();

        $user = Api::where(['user' => $request->user])->first();
        if(empty($user)){
            return false;
        }

        return $user;
    }

    public function validateStatus($user){
        if(in_array($user->status, ['inactive', 'blocked'])){
            return false;
        }else{
            return true;
        }
    }

    public function validatePassword($user){
        if($user->secret != $this->request['secret']){            
            $add_intents = new ApiIntent;
            $add_intents->api_id = $user->id;
            if($add_intents->save()){

                //Si ya hay más de 3 intentos previos, bloqueamos el usuario por seguridad.
                $get_intents = Api::findOrFail($user->id)->intents->toArray();
                if(sizeof($get_intents) > 3){
                    $api_update = Api::find($user->id); 
                    $api_update->status = 'blocked';
                    $api_update->save();
                }

                return false;
            }else{
                return true;
            }
        }else{
            return true;
        }
    }


}