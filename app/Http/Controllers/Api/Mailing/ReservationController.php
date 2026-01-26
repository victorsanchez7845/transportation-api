<?php

namespace App\Http\Controllers\Api\Mailing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Repositories\Api\Reservation\SearchRepository;
use Illuminate\Support\Facades\App;
use App\Models\DestinationMail;
use App\Models\Provider;
use App\Models\Site;
use App\Traits\FunctionsTrait;
use Illuminate\Support\Facades\DB;

class ReservationController extends Controller
{
    use FunctionsTrait;

    public function view(Request $request, SearchRepository $search){
         
        $validator = Validator::make($request->all(), [
            'code' => 'max:12',
            'email' => 'required|email|max:75',
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

        $search->setData($request);
        $data = $search->search();
        if($data == false){
            return response()->json([
                'error' => [
                    'code' => 'not_found',
                    'message' => 'Reservation not found'
                ]
            ], 404);
        }

        App::setLocale($request->language);
        
        $mail = DestinationMail::where('destination_id', $data['config']['destination_id'])->get();
        $creation_date = $this->getPrettyDate($data['config']['creation_date'], $request->language);

        return view('mailing.transportation', ['data' => $data, 'mail' => $mail, 'creation_date' => $creation_date]);
    }
    
    public function viewQR(Request $request, SearchRepository $search){
         
        $validator = Validator::make($request->all(), [
            'code' => 'max:12',
            'email' => 'required|email|max:75',
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

        $search->setData($request);
        $data = $search->search();
        if($data == false){
            return response()->json([
                'error' => [
                    'code' => 'not_found',
                    'message' => 'Reservation not found'
                ]
            ], 404);
        }

        App::setLocale($request->language);
        
        $mail = DestinationMail::where('destination_id', $data['config']['destination_id'])->get();
        $creation_date = $this->getPrettyDate($data['config']['creation_date'], $request->language);

        return view('mailing.transportationQR', ['data' => $data, 'mail' => $mail, 'creation_date' => $creation_date]);
    }    

    public function paymentRequest(Request $request) {
        $validator = Validator::make($request->all(), [
            'reservation_id' => 'integer',
            'lang' => 'required|in:en,es',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => [
                    'code' => 'required_params',
                    'message' =>  $validator->errors()->all() 
                ]
            ], 404);
        }

        $data = DB::select("SELECT sit.payment_domain, sit.transactional_phone, rez.client_email, rez.client_first_name, rez.id as reservation_id, sit.success_payment_url, sit.cancel_payment_url, rez.destination_id as destination_id
                            FROM reservations as rez 
                                INNER JOIN sites as sit ON sit.id = rez.site_id
                            WHERE rez.id = :id", ['id' => $request['reservation_id'] ]);
        
        $data = $data[0];

        $paypal_URL = $this->makePaymentURL( $request->reservation_id, $request->lang, $data, 'PAYPAL' );
        $stripe_URL = $this->makePaymentURL( $request->reservation_id, $request->lang, $data, 'STRIPE' );

        App::setLocale($request['lang']);

        $site = Site::find($data->site_id ?? 1);
        $provider = Provider::where('destination_id', $data->destination_id)->first();

        return view('mailing.sendPaymentRequest', ['data' => $data, 'site' => $site, 'paypal_URL' => $paypal_URL, 'stripe_URL' => $stripe_URL, 'provider' => $provider]);
    }

    private static function makePaymentURL($reservation_id, $lang, $data, $type = "STRIPE"){

        $data = [
            "type" => $type,
            "id" => $reservation_id,
            "language" => $lang,
            "success_url" => $data->success_payment_url,
            "cancel_url" => $data->cancel_payment_url,
            "redirect" => 1
        ];

        return env('APP_URL') . '/api/v1/reservation/payment/handler?' . http_build_query($data);        
    }

}