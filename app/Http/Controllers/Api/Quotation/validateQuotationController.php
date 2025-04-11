<?php

namespace App\Http\Controllers\Api\Quotation;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Api\Quotation\SearchRepository;
use App\Repositories\Api\Reservation\SearchRepository as ReservationSearch;

use App\Traits\FunctionsTrait;
use Carbon\Carbon;

use App\Models\Reservations;
use App\Models\ReservationsItems;

class validateQuotationController extends Controller
{
    use FunctionsTrait;

    public function validateQuotation(Request $request, SearchRepository $search){
        try {
            //code...
            $data = $search->getBookingQuotation();
            if( empty($data) ){
                return response()->json([
                    'error' => [
                        'code' => 'not_data',
                        'message' => 'There is no data to process'
                    ],
                    'message' => 'There is no data to process'
                ], 404);
            }

            foreach ($data as $key => $booking) {
                $expires_at = Carbon::parse($booking->expires_at);
                if ($expires_at->isPast()) {
                    $resultBooking = Reservations::where('id', $booking->id)->update([ 
                        'is_cancelled' => 1, 
                        'cancellation_type_id' => 17, 
                        'is_quotation' => 0, 
                        'expires_at' => NULL 
                    ]);
                    $resultItemsBooking = ReservationsItems::where('reservation_id', $booking->id)->update([ 
                        'vehicle_id_one' => NULL,
                        'driver_id_one' => NULL,
                        'op_one_status' => 'CANCELLED',
                        'op_one_status_operation' => 'PENDING',
                        'op_one_time_operation' => NULL,
                        'op_one_preassignment' => NULL,
                        'op_one_operating_cost' => 0,
                        'op_one_cancellation_type_id' => 17,
                        'vehicle_id_two' => NULL,
                        'driver_id_two' => NULL,
                        'op_two_status' => 'CANCELLED',
                        'op_two_status_operation' => 'PENDING',
                        'op_two_time_operation' => NULL,
                        'op_two_preassignment' => NULL,
                        'op_two_operating_cost' => 0,
                        'op_two_cancellation_type_id' => 17,
                    ]);

                    if( $resultBooking && $resultItemsBooking ){
                        //Envío de correo al cliente...
                        $email = [];
                        $email['code'] = $booking->code;
                        $email['email'] = $booking->client_email;
                        $email['language'] = $booking->language;
                        $email['type'] = 'cancel';
                        $result = $this->sendEmail(config('app.url')."/api/v1/reservation/send", $email);
                    }
                }
            }

            return response()->json([
                'error' => [
                    'code' => 'success',
                    'message' => 'the quotes were cancelled correctly'
                ],
                'message' => 'the quotes were cancelled correctly'
            ], 200);            
        } catch (Exception $e) {
            return response()->json([
                'error' => [
                    'code' => 'internal_server',
                    'message' => 'Server errror'
                ],
                'message' => $e->getMessage()
            ], 500); 
        }
    }
}
