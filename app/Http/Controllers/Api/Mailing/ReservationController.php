<?php

namespace App\Http\Controllers\Api\Mailing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Repositories\Api\Reservation\SearchRepository;
use Illuminate\Support\Facades\App;
use App\Models\DestinationMail;
use App\Traits\FunctionsTrait;

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

}