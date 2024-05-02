<?php

namespace App\Http\Controllers\Api\Reservation;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Repositories\Api\Reservation\SearchRepository;
use App\Traits\TokenTrait;
use App\Traits\MailjetTrait;
use App\Models\ReservationsFollowUp;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\DB;

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
            'type' => 'required|in:new,update,cancel',
            'provider' => 'in:1',
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
                    'code' => 'mailing_disabled',
                    'message' => 'Mailing disabled'
                ]
            ], 404);
        endif;
        
        $provider = $this->getProvider($data['config']['destination_id']);
        $template = $this->getTemplate($request);
        
        if($template['status'] == false):
            return response()->json($template['data'], 404);
        endif;

        if($request['language'] == "en"):
            switch ($request['type']) {
                case 'new':
                    $subject = '🎟 Thank you for booking with us | '.$data['site']['name'];
                    break;
                case 'update':
                    $subject = '🎟 Your reservation data updated | '.$data['site']['name'];
                    break;
                case 'confirmed':
                        $subject = '🎟 Your reservation has been confirmed | '.$data['site']['name'];
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
                case 'confirmed':
                        $subject = '🎟 Tu reservación ha sido confirmada | '.$data['site']['name'];
                        break;
                case 'cancel':
                    $subject = '🎟 Reservación cancelada | '.$data['site']['name'];
                    break;
                default:
                    $subject = '🎟 Reservación | '.$data['site']['name'];
                    break;
            }
        endif;

        $provider_email = [];
        if(isset( $provider->id ) && !empty($provider->transactional_emails) && isset($request->provider)):
            $emails = explode(",", trim($provider->transactional_emails));
            foreach($emails as $key => $value):
                $provider_email[] = array(
                    "Email" => trim($value),
                    "Name" => $provider->name,
                );
            endforeach;
        endif;

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
                    "Bcc" => $provider_email,
                    "Subject" => $subject,
                    "TextPart" => "Dear client",
                    "HTMLPart" => $template['data']
                )
            )
        );
    
        if(config('app.env') == "production"):
            $email_response = $this->sendMailjet($email_data);
        else:
            $email_response['Messages'][0]['Status'] = 'success';
        endif;
        
        if(isset($email_response['Messages'][0]['Status']) && $email_response['Messages'][0]['Status'] == "success"):
            $follow_up_db = new ReservationsFollowUp;
            $follow_up_db->name = 'Sistema';
            $follow_up_db->text = 'E-mail enviado ('.$request['type'].')';
            $follow_up_db->type = 'INTERN';
            $follow_up_db->reservation_id = $data['config']['id'];
            $follow_up_db->save();

            return response()->json(['status' => "success"], 200);
        else:
            $follow_up_db = new ReservationsFollowUp;
            $follow_up_db->name = 'Sistema';
            $follow_up_db->text = 'No fue posible enviar el e-mail del cliente, por favor contactar a Desarrollo';
            $follow_up_db->type = 'INTERN';
            $follow_up_db->reservation_id = $data['config']['id'];
            $follow_up_db->save();
            
            return response()->json([
                'error' => [
                    'code' => 'mailing_system',
                    'message' => 'The mailing platform has a problem, please report to development'
                ]
            ], 404);
        endif;
    }

    public function makeQr(Request $request){
        // Realizar validaciones
        $validator = Validator::make($request->all(), [
            'code' => 'max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => [
                    'code' => 'required_params',
                    'message' =>  $validator->errors()->all() 
                ]
            ], 404);
        }

        $qr = QrCode::create($request->code);
        $writer = new PngWriter();
        $result = $writer->write($qr);
        header("Content-Type: " . $result->getMimeType());
        echo $result->getString();
        exit;
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
            $data['status'] = false;
            $data['data'] = [
                'error' => [
                    'code' => 'curl_error',
                    'message' => 'Error en la solicitud cURL: '.curl_error($ch)
                ]
            ];
            return $data;
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

    public function getProvider($id){

        $data = DB::select('SELECT id, name, transactional_phone, transactional_emails, is_default FROM providers WHERE destination_id = :id AND is_default = 1', [ 'id' => $id ]);
        if(isset( $data[0] )):
            return $data[0];
        else:
            return [];
        endif;
    }

    public function getTypesCancellations(Request $request, SearchRepository $search){
        return $search->getTypesCancellations($request);
    }
}