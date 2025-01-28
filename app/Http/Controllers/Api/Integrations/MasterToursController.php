<?php

namespace App\Http\Controllers\Api\Integrations;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Api\Integrations\MasterToursRepository as MT;
use Illuminate\Support\Facades\Validator;

class MasterToursController extends Controller
{
    public function listing(Request $request, MT $master){
        return $master->listing($request);
    }
}