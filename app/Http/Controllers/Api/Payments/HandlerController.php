<?php

namespace App\Http\Controllers\Api\Payments;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\OpenpayClient;
use App\Repositories\Api\Payments\StripeRepository;
use App\Repositories\Api\Payments\StripeElementsRepository;
use App\Repositories\Api\Payments\PaypalRepository;
use App\Repositories\Api\Payments\MifelRepository;
use App\Repositories\Api\Payments\SantanderRepository;
use App\Traits\LoggerTrait;
use Exception;
use Illuminate\Support\Facades\Validator;
use Openpay\Data\Openpay;
use Openpay\Data\OpenpayApiRequestError;

class HandlerController extends Controller
{
    use LoggerTrait;

    public function index(Request $request, StripeRepository $handlerStripe, PaypalRepository $handlerPaypal, MifelRepository $handlerMifel, SantanderRepository $handlerSantander)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:STRIPE,STRIPE-2,PAYPAL,MIFEL,PAYPAL-1,PAYPAL-V2,SANTANDER',
            'id' => 'integer',
            'language' => 'required|in:en,es',
            'success_url' => 'required',
            'cancel_url' => 'required',
            'redirect' => 'integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => [
                    'code' => 'required_params',
                    'message' =>  $validator->errors()->all()
                ]
            ], 404);
        }

        if ($request->type == "STRIPE"):
            $items = $handlerStripe->check($request);
        endif;
        if ($request->type == "STRIPE-2"): //Nueva cuenta de Stripe para probar
            $items = $handlerStripe->check($request);
        endif;
        if ($request->type == "PAYPAL"):
            $items = $handlerPaypal->check($request);
        endif;
        if ($request->type == "PAYPAL-1"):
            $items = $handlerPaypal->check($request, 1);
        endif;
        if ($request->type == "PAYPAL-V2"):
            $items = $handlerPaypal->orders($request, 1);
        endif;
        if ($request->type == "MIFEL"):
            $items = $handlerMifel->check($request);
        endif;
        if ($request->type == "SANTANDER"):
            $items = $handlerSantander->check($request);
        endif;

        if ($items['status'] == false) {
            if ($items['code'] == "cancelled" && $request->language == "es"):
                $items['message'] = "Su reserva ha sido cancelada, si desea reactivarla póngase en contacto con nosotros.";
            endif;

            return response()->json([
                'error' => [
                    'code' => $items['code'],
                    'message' => $items['message']
                ]
            ], 404);
        }

        if (isset($request->redirect) && $request->redirect == 1):
            return redirect()->away($items['data']['url']);
        endif;

        return response()->json($items['data'], 200);
    }

    public function indexStripeElements(Request $request, StripeElementsRepository $handlerStripe)
    {
        $validator = Validator::make($request->all(), [
            'language' => 'required|in:en,es',
            'total' => 'required',
            'currency' => 'required|in:USD,MXN'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => [
                    'code' => 'required_params',
                    'message' =>  $validator->errors()->all()
                ]
            ], 404);
        }

        $items = $handlerStripe->check($request);

        if ($items['status'] == false) {
            return response()->json([
                'error' => [
                    'code' => $items['code'],
                    'message' => $items['message']
                ]
            ], 404);
        }

        return response()->json($items['data'], 200);
    }

    public function mifelValidate(Request $request, MifelRepository $handlerMifel)
    {

        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => [
                    'code' => 'required_params',
                    'message' =>  $validator->errors()->all()
                ]
            ], 404);
        }

        $items = $handlerMifel->validate($request);
        if ($items == false) {
            return response()->json([
                'error' => [
                    'code' => 'declined',
                    'message' => 'The bank had an error in returning the data.'
                ]
            ], 404);
        }

        return response()->json([], 200);
    }

    public function payPalCaptureOrder(Request $request, PaypalRepository $handlerPaypal)
    {

        $this->createLog([
            'type' => 'info',
            'category' => 'paypal_debug',
            'message' => 'API. Entra al controlador de payPalCaptureOrder',
        ]);

        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => [
                    'code' => 'required_params',
                    'message' =>  $validator->errors()->all()
                ]
            ], 404);
        }

        $this->createLog([
            'type' => 'info',
            'category' => 'paypal_debug',
            'message' => 'API. pasa la validación de payPalCaptureOrder',
        ]);

        $items = $handlerPaypal->ordersCapture($request);

        $this->createLog([
            'type' => 'info',
            'category' => 'paypal_debug',
            'message' => 'API. pasa ordersCapture payPalCaptureOrder',
        ]);

        if ($items == false) {
            $this->createLog([
                'type' => 'warning',
                'category' => 'paypal_debug',
                'message' => 'API. items = false en payPalCaptureOrder',
            ]);

            return response()->json([
                'error' => [
                    'code' => 'order_capture',
                    'message' => 'Error capturing the order'
                ]
            ], 404);
        }

        try {
            $this->createLog([
                'type' => 'info',
                'category' => 'paypal_debug',
                'message' => "API. Antes de enviar respuesta en payPalCaptureOrder. Respuesta json: " . json_encode($items),
            ]);
        } catch (\Exception $e) {
            $this->createLog([
                'type' => 'error',
                'message' => "API",
                'exception' => $e,
            ]);
        }

        return response()->json($items, 200);
    }

    public function openpayKeys()
    {
        $keys = $this->getOpenPayKeys();
        return response()->json([
            'publicKey' => $keys['publicKey'],
            'merchantId' => $keys['merchantId']
        ], 200);
    }

    private function getOpenPayKeys()
    {
        return [
            'publicKey' => config('services.openpay.public'),
            'privateKey' => config('services.openpay.private'),
            'merchantId' => config('services.openpay.merchant')
        ];
    }

    /**
     * Create OpenPay payment
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function openpayCreatePayment(Request $request)
    {
        $data = $request->all();

        try {
            $customer = [
                'name' => $data["customer"]["name"],
                'last_name' => $data["customer"]["last_name"],
                'email' => $data["customer"]["email"],
                'phone_number' => $data["customer"]["phone_number"],
            ];
            $chargeRequest = [
                'method' => "card",
                'source_id' => $data["charge"]["source_id"],
                'amount' => $data["charge"]["amount"],
                'currency' => $data["charge"]["currency"],
                'description' => "Pago de servicio de Transporte",
                'customer' => $customer,
                'order_id' => $data["metadata"]["uuid"],
                'device_session_id' => $data["charge"]["device_session_id"],
                'capture' => true,
                'redirect_url' => $data["metadata"]["redirect_url"]
            ];

            Openpay::setProductionMode(true);
            /** @var array{publicKey: string, privateKey: string, merchantId: string} $keys */
            $keys = $this->getOpenPayKeys();

            $openpay = Openpay::getInstance(
                $keys['merchantId'],
                $keys['privateKey'],
                "MX",
                $data["metadata"]["ip"]
            );

            $clientKey = $this->prepareOpenPayCustomer($customer, $openpay);

            if (empty($clientKey)) {
                // Just create the payment, something wrong happened on client creation
                // Directly to the commerce
                try {
                    /** @var object $charge */
                    $charge = $openpay->charges->create($chargeRequest);
                } catch (OpenpayApiRequestError $e) {
                    return response()->json([
                        'success' => false,
                        'error' => [
                            'code' => 'openpay_error',
                            'message' =>  $e->getMessage(),
                            'http_code' => $e->getCode(),
                            'error_code' => $e->getErrorCode(),
                            'description' => $e->getDescription(),
                            'fraud_rules' => $e->getFraudRules(),
                        ]
                    ], 500);
                }
            } else {
               
                // Create the payment with the client key
                /** @var object $openpayClient */
                $openpayClient = $openpay->customers->get($clientKey);
                /** @var object $charge */
                try {
                    $chargeRequestWithoutCustomer = $chargeRequest;
                    unset($chargeRequestWithoutCustomer['customer']);
                    $charge = $openpayClient->charges->create($chargeRequestWithoutCustomer);
                } catch (OpenpayApiRequestError $e) {
                    return response()->json([
                        'success' => false,
                        'error' => [
                            'code' => 'openpay_error',
                            'message' =>  $e->getMessage(),
                            'http_code' => $e->getCode(),
                            'error_code' => $e->getErrorCode(),
                            'description' => $e->getDescription(),
                            'fraud_rules' => $e->getFraudRules(),
                        ]
                    ], 500);
                }
            }

            return response()->json([
                'success' => true,
                'data' => $charge->id
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'openpay_error',
                    'message' =>  $e->getMessage(),
                    'http_code' => $e->getCode(),
                ]
            ], 500);
        }
    }

    /**
     * Prepare or retrieve OpenPay customer ID
     * 
     * @param array $customer Customer data array with keys: name, last_name, email, phone_number
     * @param mixed $openpay OpenPay instance
     * @return string OpenPay customer ID or empty string on failure
     */
    private function prepareOpenPayCustomer(array $customer, $openpay): string
    {
        try {
            $clientExist = OpenpayClient::where('client_email', $customer["email"])->first();
            $clientKey = "";

            if (!isset($clientExist)) {
                $openpayCustomer = $openpay->customers->add([
                    "name" => $customer["name"],
                    "last_name" => $customer["last_name"],
                    "email" => $customer["email"],
                    "phone" => $customer["phone_number"]
                ]);

                $clientKey = $openpayCustomer->id;
                OpenpayClient::create([
                    'client_openpay_id' => $clientKey,
                    'client_email' => $customer["email"],
                    'client_data' => json_encode($customer),
                    'client_name' => $customer["name"] . " " . $customer["last_name"]
                ]);
            } else {
                $clientKey = $clientExist->client_openpay_id;
            }

            return $clientKey;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }
}
