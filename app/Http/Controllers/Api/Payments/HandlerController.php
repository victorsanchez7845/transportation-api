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
            'type' => 'required|in:STRIPE,STRIPE-2,PAYPAL,MIFEL,PAYPAL-1,PAYPAL-V2,PAYPAL-V3,SANTANDER',
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
        if($request->type == "PAYPAL-V3"):
            $items = $handlerPaypal->ordersV2($request);
        endif;
        if($request->type == "MIFEL"):
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
            'merchantId' => $keys['merchantId'],
            'productionMode' => config('services.openpay.production_mode'),
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

            Openpay::setProductionMode( config('services.openpay.production_mode') );
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
                            'message' =>  $this->getOpenPayMessageError($e->getErrorCode(), $data["metadata"]["language"], $e->getMessage()),
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
                            'message' =>  $this->getOpenPayMessageError($e->getErrorCode(), $data["metadata"]["language"], $e->getMessage()),
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

    /**
     * Get OpenPay error message translated to Spanish
     * 
     * @param int $code OpenPay error code
     * @param string $lang Language code (en or es)
     * @param string $message Default error message in English
     * @return string Translated error message or original message
     */
    private function getOpenPayMessageError($code, $lang, $message) {
        if($lang == "en") return $message;

        switch($code) {
            case 1001: return "Ocurrió un error interno en el servidor de Openpay";
            case 1001: return "El formato de la petición no es JSON, los campos no tienen el formato correcto, o la petición no tiene campos que son requeridos.";
            case 1002: return "La llamada no esta autenticada o la autenticación es incorrecta.";
            case 1003: return "La operación no se pudo completar por que el valor de uno o más de los parámetros no es correcto.";
            case 1004: return "Un servicio necesario para el procesamiento de la transacción no se encuentra disponible.";
            case 1004: return "Uno de los recursos requeridos no existe.";
            case 1006: return "Ya existe una transacción con el mismo ID de orden.";
            case 1007: return "La transferencia de fondos entre una cuenta de banco o tarjeta y la cuenta de Openpay no fue aceptada.";
            case 1008: return "Una de las cuentas requeridas en la petición se encuentra desactivada.";
            case 1009: return "El cuerpo de la petición es demasiado grande.";
            case 1010: return "Se esta utilizando la llave pública para hacer una llamada que requiere la llave privada, o bien, se esta usando la llave privada desde JavaScript.";
            case 1011: return "Se solicita un recurso que esta marcado como eliminado.";
            case 1012: return "El monto transacción esta fuera de los limites permitidos.";
            case 1013: return "La operación no esta permitida para el recurso.";
            case 1014: return "La cuenta esta inactiva.";
            case 1015: return "No se ha obtenido respuesta de la solicitud realizada al servicio.";
            case 1016: return "El mail del comercio ya ha sido procesada.";
            case 1017: return "El gateway no se encuentra disponible en ese momento.";
            case 1018: return "El número de intentos de cargo es mayor al permitido.";
            case 1020: return "El número de dígitos decimales es inválido para esta moneda.";
            case 1023: return "Se han terminado las transacciones incluidas en tu paquete. Para contratar otro paquete contacta a soporte@openpay.mx.";
            case 1024: return "El monto de la transacción excede su límite de transacciones permitido por TPV";
            case 1025: return "Se han bloqueado las transacciones CoDi contratadas en tu plan";
            case 2001: return "La cuenta de banco con esta CLABE ya se encuentra registrada en el cliente.";
            case 2003: return "El cliente con este identificador externo (External ID) ya existe.";
            case 2004: return "El número de tarjeta es invalido.";
            case 2005: return "La fecha de expiración de la tarjeta es anterior a la fecha actual.";
            case 2006: return "El código de seguridad de la tarjeta (CVV2) no fue proporcionado.";
            case 2007: return "El número de tarjeta es de prueba, solamente puede usarse en Sandbox.";
            case 2008: return "La tarjeta no es valida para pago con puntos.";
            case 2009: return "El código de seguridad de la tarjeta (CVV2) es inválido.";
            case 2010: return "Autenticación 3D Secure fallida.";
            case 2011: return "Tipo de tarjeta no soportada.";
            case 3001: return "La tarjeta fue declinada por el banco.";
            case 3002: return "La tarjeta ha expirado.";
            case 3003: return "La tarjeta no tiene fondos suficientes.";
            case 3004: return "La tarjeta ha sido identificada como una tarjeta robada.";
            case 3005: return "La tarjeta ha sido identificada como una tarjeta fraudulenta.";
            case 3006: return "La operación no esta permitida para este cliente o esta transacción.";
            case 3009: return "La tarjeta fue reportada como perdida.";
            case 3010: return "El banco ha restringido la tarjeta.";
            case 3011: return "El banco ha solicitado que la tarjeta sea retenida. Contacte al banco.";
            case 3012: return "Se requiere solicitar al banco autorización para realizar este pago.";
            case 3201: return "Comercio no autorizado para procesar pago a meses sin intereses.";
            case 3203: return "Promoción no valida para este tipo de tarjetas.";
            case 3204: return "El monto de la transacción es menor al mínimo permitido para la promoción.";
            case 3205: return "Promoción no permitida.";
            case 4001: return "La cuenta de Openpay no tiene fondos suficientes.";
            case 4002: return "La operación no puede ser completada hasta que sean pagadas las comisiones pendientes.";
            case 6001: return "El webhook ya ha sido procesado.";
            case 6002: return "No se ha podido conectar con el servicio de webhook.";
            case 6003: return "El servicio respondió con errores.";
            default: return $message;
        }
    }
}
