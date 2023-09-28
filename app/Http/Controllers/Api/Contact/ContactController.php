<?php

namespace App\Http\Controllers\Api\Contact;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Repositories\Api\Contact\ContactRepository;

class ContactController extends Controller
{
    /**
     * Display the specified resource.
     *     
     * @return \Illuminate\Http\Response
     */

    public function index(Request $request, ContactRepository $contact){

        $validator = Validator::make($request->all(), [
            'company_email' => 'email|max:85',
            'client_full_name' => 'required|max:75',
            'client_subject' => 'required|max:100',
            'client_email' => 'required|email|max:85',
            'client_phone' => 'required|max:35',
            'client_message' => 'required|max:200',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => [
                    'code' => 'required_params',
                    'message' => $validator->errors()->all() 
                ]
            ], 422);
        }
        
        $send = $contact->send($request);
        if($send == false){
            return response()->json([
                'error' => [
                    'code' => 'mailing_system',
                    'message' => 'The mailing platform has a problem, please report to development'
                ]
            ], 404);            
        }

        return response()->json(['status' => "success"], 200);

    }
}