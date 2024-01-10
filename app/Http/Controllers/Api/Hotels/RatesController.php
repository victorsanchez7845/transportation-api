<?php

namespace App\Http\Controllers\Api\Hotels;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Repositories\Api\Quotation\SearchRepository;
use App\Repositories\Api\Hotels\RatesRepository;
use App\Repositories\Api\Webhook\PaymentRepository;

class RatesController extends Controller
{
    public function getRates(Request $request, RatesRepository $rates, PaymentRepository $payment){
        $data = $rates->getRates($payment);
        return response()->json($data, 200);
    }
}