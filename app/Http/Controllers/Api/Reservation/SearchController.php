<?php

namespace App\Http\Controllers\Api\Reservation;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Repositories\Api\Reservation\SearchRepository;
use App\Traits\TokenTrait;
use App\Traits\MailjetTrait;


class SearchController extends Controller
{
    use MailjetTrait;

    public function index(Request $request, SearchRepository $search){
        // Realizar validaciones
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

        return response()->json($data, 200);
    }

    public function send(Request $request, SearchRepository $search){
        
        $validator = Validator::make($request->all(), [
            'code' => 'max:12',
            'email' => 'required|email|max:75',
            'language' => 'required|in:en,es',
            'type' => 'in:new,update,cancel',
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

        if($data['site']['email'] == 0):
            return response()->json([
                'error' => [
                    'code' => 'mailing',
                    'message' => 'Mailing disabled'
                ]
            ], 404);
        endif;
        
        $template = $this->getTemplate($request);

        if($template['status'] == false):
            return response()->json($template['data'], 404);
        endif;

        if($request['type'] == "en"):
            switch ($request['type']) {
                case 'new':
                    $subject = '🎟 Thank you for booking with us | '.$data['site']['name'];
                    break;
                case 'update':
                    $subject = '🎟 Your reservation data updated | '.$data['site']['name'];
                    break;
                case 'cancel':
                    $subject = '🎟 Reservation cancelled | '.$data['site']['name'];
                    break;
                default:
                    $subject = '🎟 Reservation | '.$data['site']['name'];
                    break;
            }
        else:
            switch ($request['type']) {
                case 'new':
                    $subject = '🎟 Gracias por reservar con nosotros | '.$data['site']['name'];
                    break;
                case 'update':
                    $subject = '🎟 Datos de reservación actualizados | '.$data['site']['name'];
                    break;
                case 'cancel':
                    $subject = '🎟 Reservación cancelada | '.$data['site']['name'];
                    break;
                default:
                    $subject = '🎟 Reservación | '.$data['site']['name'];
                    break;
            }
        endif;

        //$data['config']['id']

        $email_data = array(
            "Messages" => array(
                array(
                    "From" => array(
                        "Email" => $data['site']['email'],
                        "Name" => "Bookings"
                    ),
                    "To" => array(
                        array(
                            "Email" => $request->email,
                            "Name" => $data['client']['first_name'],
                        )
                    ),
                    "Subject" => $subject,
                    "TextPart" => "Dear client",
                    "HTMLPart" => $template['data']
                )
            )
        );

        $email_response = MailjetTrait::send($email_data);
        echo "<pre>";
        print_r($email_response);
        die();
    }


   

    public function getTemplate(Request $request){
        $data = [
            "status" => false,
            "data" => NULL
        ];

        $this->request = $request->all();

        $url = config('app.url')."/api/v1/mailing/reservation/view";        

        $params = array(
            'code' => $this->request['code'],
            'email' => $this->request['email'],
            'language' => $this->request['language'],
        );
        
        $ch = curl_init();
        $urlWithParams = $url . '?' . http_build_query($params);
        curl_setopt($ch, CURLOPT_URL, $urlWithParams);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
    
        if (curl_errno($ch)) {
            echo "Error en la solicitud cURL: " . curl_error($ch);
        }
        curl_close($ch);
        
        $jsonData = json_decode($response);
        if ($jsonData === null && json_last_error() !== JSON_ERROR_NONE) {     
            //Es HTML, esto indica que todo va bien...
            $data['status'] = true;
            $data['data'] = $response;
            return $data;
        }else{
            //Es un JSON por lo que algo salió mal...
            $data['status'] = false;
            $data['data'] = json_decode($response, true);
            return $data;     
        }

    }

}