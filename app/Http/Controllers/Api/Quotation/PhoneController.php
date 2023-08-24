<?php

namespace App\Http\Controllers\Api\Quotation;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Api\Quotation\PhoneRepository;

class PhoneController extends Controller
{
    public function index(Request $request, PhoneRepository $phone){

        $data = $phone->get($request);
        return response()->json($data, 200);
        
    }
}
