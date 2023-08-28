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

    public function send(Request $request){

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

        $template = $this->getTemplate($request);

        $subject 'Thank you for booking with us';
        if(isset( $request->type )):
            switch ($request->type) {
                case 'update':
                    $subject 'Thank you for booking with us';
                    break;
                case 'cancel':
                    $subject 'Thank you for booking with us';
                    break;
                default:
                    $subject 'Thank you for booking with us';
                    break;
            }
        endif;
        
        

        $data = array(
            "Messages" => array(
                array(
                    "From" => array(
                        "Email" => "bookings@caribbean-transfers.com",
                        "Name" => "Bookings"
                    ),
                    "To" => array(
                        array(
                            "Email" => $request->email,
                            "Name" => "Dear client"
                        )
                    ),
                    "Subject" => "Your email flight plan!",
                    "TextPart" => ""
                    "HTMLPart" => $template
                )
            )
        );


        MailjetTrait::send();
        
        die();

    }


   

    public function getTemplate(Request $request){
        return '
            <!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta http-equiv="X-UA-Compatible" content="ie=edge"><title>Bookings</title><style>body{margin:0;padding:0;font-family:Arial,sans-serif;background-color:#fff}p{font-size:11pt;line-height:1.5;margin:0}.gray_color{color:#6a829e}.container{max-width:600px;margin:0 auto;border-radius:5px;margin-top:15px}table.table_init{width:100%;border-collapse:collapse;margin-top:20px;box-shadow:0 0 32px 0 rgba(145,161,180,.25);border-radius:15px}.header{text-align:center}div.orange_content{border-radius:15px 15px 0 0;background-color:#ce8506;background-size:cover;background-position:center;background-repeat:no-repeat}div.orange_content table{width:100%}div.orange_content table td{text-align:left;vertical-align:top;padding:25px}div.orange_content table td h1{font-size:22pt;margin:0;color:#fff;margin-bottom:8px}div.orange_content table td p{font-size:11pt;color:#fff;margin:0}div.orange_content table td p.name{font-size:16pt;font-weight:700;color:#fff;margin:0;margin-bottom:15px}td.white_content{background-color:#fff;padding:25px}p.label{font-weight:700;margin-bottom:8px}hr{border:0;border-top:1px solid #ccd5d8;margin:0}table.destinations_table{width:100%;border-collapse:collapse}table.destinations_table td{width:50%;padding-bottom:10px}.orange{color:#ff7903}.important_information p{margin-bottom:8px;line-height:1.5}.important_information hr{margin-top:15px;margin-bottom:15px}span.payment{background-color:#191970;color:#fff;padding:15px 15px;border-radius:8px;display:inline-block;font-weight:700}span.payment.type-CONFIRMED{background-color:#198f51}</style></head><body><div class="container"><div class="header"><img src="https://ik.imagekit.io/zqiqdytbq/transportation-api/mailing/logo.png"></div><table class="table_init"><tbody><tr><td><div class="orange_content"><table><tbody><tr><td style="text-align:center"><img src="https://ik.imagekit.io/zqiqdytbq/transportation-api/mailing/top-vehicle.png?updatedAt=1693244044317"></td></tr><tr><td style="padding-top:0"><table><tbody><tr><td style="padding:0"><h1>Hello</h1><p class="name">Omar Alejandro</p></td><td style="text-align:right;padding:0"><h4 style="margin:0;color:#fff;margin-bottom:8px">Reservation status</h4><span class="payment type-CONFIRMED">CONFIRMED</span></td></tr></tbody></table><p>Thank you very much for booking with us, your service will be operated by Caribbean Transfers which is our official tourist transportation company in Cancun and the Riviera Maya.</p></td></tr></tbody></table></div></td></tr><tr><td class="white_content"><p class="gray_color" style="margin-bottom:15px">This email shows in detail the information of your reservation made on Monday, August 28, 2023, in which we ask that if it is not correct please contact us to make the corresponding modifications.</p><p style="margin-bottom:15px"><strong>PLEASE PRESENT THIS PRINTED OR DIGITAL (CELL PHONE) RECEIPT TO THE CARIBBEAN SEA TRAVEL REPRESENTATIVE TO BOARD YOUR UNIT.</strong></p><h2>Total: 612.00 MXN</h2><div style="background-color:#dde9fa;padding:15px;margin-bottom:15px"><table style="width:100%"><tbody><tr><td><p style="font-weight:700;font-size:18pt">8GpWI2YrQ</p></td><td rowspan="4" style="text-align:right"> <?xml version="1.0" encoding="UTF-8"?> <svg xmlns="http://www.w3.org/2000/svg" version="1.1" width="200" height="200" viewBox="0 0 200 200"><rect x="0" y="0" width="200" height="200" fill="#ffffff"/><g transform="scale(9.524)"><g transform="translate(0,0)"><path fill-rule="evenodd" d="M9 0L9 1L8 1L8 3L9 3L9 4L8 4L8 8L6 8L6 9L8 9L8 10L9 10L9 12L10 12L10 13L11 13L11 14L12 14L12 13L15 13L15 11L16 11L16 10L17 10L17 13L16 13L16 14L15 14L15 16L14 16L14 14L13 14L13 15L10 15L10 14L9 14L9 13L8 13L8 15L9 15L9 17L8 17L8 18L9 18L9 17L10 17L10 19L8 19L8 21L9 21L9 20L10 20L10 21L13 21L13 20L15 20L15 19L13 19L13 18L12 18L12 16L13 16L13 17L15 17L15 16L17 16L17 17L16 17L16 20L17 20L17 21L20 21L20 20L17 20L17 19L18 19L18 17L19 17L19 18L20 18L20 19L21 19L21 17L20 17L20 16L17 16L17 15L19 15L19 14L21 14L21 13L19 13L19 14L18 14L18 10L17 10L17 8L14 8L14 9L13 9L13 10L12 10L12 11L10 11L10 10L11 10L11 8L13 8L13 4L12 4L12 3L13 3L13 2L12 2L12 1L13 1L13 0L11 0L11 2L12 2L12 3L11 3L11 4L12 4L12 5L11 5L11 6L10 6L10 7L9 7L9 4L10 4L10 0ZM11 6L11 7L12 7L12 6ZM0 8L0 9L2 9L2 10L0 10L0 12L1 12L1 13L3 13L3 11L4 11L4 12L5 12L5 13L7 13L7 12L5 12L5 11L7 11L7 10L5 10L5 9L4 9L4 8L3 8L3 9L2 9L2 8ZM8 8L8 9L9 9L9 8ZM18 8L18 9L19 9L19 10L20 10L20 11L19 11L19 12L21 12L21 10L20 10L20 8ZM3 9L3 10L4 10L4 9ZM14 9L14 11L12 11L12 12L11 12L11 13L12 13L12 12L14 12L14 11L15 11L15 10L16 10L16 9ZM10 16L10 17L11 17L11 16ZM11 18L11 20L13 20L13 19L12 19L12 18ZM0 0L0 7L7 7L7 0ZM1 1L1 6L6 6L6 1ZM2 2L2 5L5 5L5 2ZM14 0L14 7L21 7L21 0ZM15 1L15 6L20 6L20 1ZM16 2L16 5L19 5L19 2ZM0 14L0 21L7 21L7 14ZM1 15L1 20L6 20L6 15ZM2 16L2 19L5 19L5 16Z" fill="#000000"/></g></g></svg></td></tr><tr><td><p class="label">Name</p><p>Omar Alejandro Trujillo Flores</p></td></tr><tr><td><p class="label">Phone</p><p>+529981710512</p></td></tr><tr><td><p class="label">E-mail</p><p>omar.trujillo.91@gmail.com</p></td></tr><tr><td colspan="2" style="padding-top:10px"><hr></td></tr><tr><td colspan="2"><table class="destinations_table"><tbody><tr><td><p class="label">From</p><p>Aeropuerto Internacional de Cancún</p></td><td><p class="label">To</p><p>Cancun Downtown</p></td></tr><tr><td><p class="label">Pickup</p><p>2023-10-01 11:00:00</p></td><td><p class="label">Passengers</p><p>1</p></td></tr><tr><td><p class="label">Service type</p><p>Taxi</p></td><td><p class="label">Flight number</p><p>1234567890</p></td></tr></tbody></table></td></tr></tbody></table></div><div><p style="margin:15px 0 5px 0"><strong>You&#039;re almost done!</strong></p><p style="margin-bottom:8px">In this email you will find a summary of your reservation information, it is important that you can validate that the information is correct, and in case of any change in the information of your flight, doubts or clarifications contact us so we can assist you in the best possible way.</p><p>If you are at the airport or at your hotel and do not see us,<span class="orange">call us at +52 (998) 294 2389</span>or send us a WhatsApp to the same number.</p></div></td></tr><tr><td class="white_content" style="border-top:1px solid #ccd5d8;text-align:center"><p style="width:70%;margin:0 auto">More information on how to find us here. Cancellation terms and conditions</p><h3 style="margin-bottom:0;color:#191970">Thank you for your reservation!</h3></td></tr><tr><td><img src="https://ik.imagekit.io/zqiqdytbq/transportation-api/mailing/banner/banner.png" style="width:600px"></td></tr><tr><td class="white_content important_information"></td></tr><tr><td class="white_content" style="text-align:center"><p>We wish you a pleasant and unforgettable stay.</p><h4>Policies</h4><h5>Cancellation Policy</h5><p class="gray_color">Cancellations can only be made 24 hours prior to arrival or departure.</p><h5>Service Hours</h5><p class="gray_color">In case of change of time of service can be made if you contact us 12 hours before the agreed time because you have to reschedule your service. Contact us 24 hours a day at +52 9982942389 or email sales@taxirivieramaya.com</p></td></tr><tr><td style="padding:15px;text-align:center"><div><a href="#"><img src="https://ik.imagekit.io/zqiqdytbq/transportation-api/mailing/social/facebook.png?updatedAt=1692978703979" style="margin-right:15px"></a><a href="#"><img src="https://ik.imagekit.io/zqiqdytbq/transportation-api/mailing/social/instagram.png?updatedAt=1692978703965"></a></div><p style="font-size:11pt;color:#6a829e">Caribbean Transfers | All Rights Reserved</p></td></tr></tbody></table></div></body></html>
                ';
        /*$this->request = $request->all();

        $url = "http://localhost:1000/api/v1/mailing/reservation/view";

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
        echo "<pre>";
        print_r($response);
        die();*/


    }

}