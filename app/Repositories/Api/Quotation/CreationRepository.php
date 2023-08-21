<?php

namespace App\Repositories\Api\Quotation;
use App\Traits\CodeTrait;
use App\Traits\TokenTrait;

class CreationRepository{
    use CodeTrait, TokenTrait;
    private $request = [];

    public function checkServiceToken($request = []){

        $this->request = $request->all();
        $token = TokenTrait::get( $this->request['service_token'] );
        if($token == false){
            return false;
        }else{
            return $token;
        }

    }

    public function create($service_token){

        $api_token = TokenTrait::get( request()->bearerToken() );
        echo "<pre>";
        print_r($api_token);
        print_r($service_token);
        die();
        

        echo "<pre>";
        print_r($this->request);
        die();

        echo "<pre>";
        print_r($this->request);

        //CodeTrait::generateCode()

        die("Creando...");
    }

}