@php    
    $lang = app()->getLocale();
    if( $data['config']['is_cancelled'] == 1 ):
        $data['status'] = "CANCELLED";
    endif;

    $reservation_status_label = $data['status'];
    switch ($lang) {
        case 'es':
                if($reservation_status_label == "CANCELLED"):
                    $reservation_status_label = "CANCELADO";
                elseif($reservation_status_label == "CONFIRMED"):
                    $reservation_status_label = "CONFIRMADO";
                else:
                    $reservation_status_label = "PENDIENTE";
                endif;
            break;        
        default:
            break;
    }
    $provider_name = $data['provider']['name'];
    $destination_name = $data['provider']['destination'];
@endphp
<!DOCTYPE html>
<html lang="{{$lang}}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Bookings</title>
    <style>
        body{
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;            
            font-size: 11pt;
        }
        p{
            font-size: 11pt;
            line-height: 1.5;
            margin: 0px;
        }
        .gray_color{
            color: #6A829E;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            border-radius: 5px;
            margin-top: 15px;
        }

        table.table_init {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            border-radius: 15px;
        }
        .header{
            text-align: center;
        }
        div.orange_content{
            border-radius: 15px 15px 0px 0px;
            background-color: {{$data['site']['color']}};
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }
        div.orange_content table{
            width: 100%;
        }
        div.orange_content table td{
            text-align:left; 
            vertical-align: top; 
            padding: 25px;
        }
        div.orange_content table td h1{
            font-size: 22pt;
            margin: 0px;
            color: white;
            margin-bottom: 8px;
        }
        div.orange_content table td p{
            font-size: 11pt;
            color: white;
            margin: 0px;
        }
        div.orange_content table td p.name{
            font-size: 16pt;
            font-weight: bold;
            color: white;
            margin: 0px;
            margin-bottom: 15px;
        }

        td.white_content{
            background-color: white;
            padding: 25px;
        }
        td.white_content.information > p{
            margin-bottom: 8px;
        }
        p.label{
            font-weight: bold;
            margin-bottom: 8px;
        }
        hr{
            border: 0px;
            border-top: 1px solid #CCD5D8;
            margin: 0px;
        }
        table.destinations_table{
            width: 100%;
            border-collapse: collapse;
        }
        table.destinations_table td{
            width: 50%;
            padding-bottom: 10px;
        }  
        a.pink{
            color: #FF3366;
            text-decoration: none;
        }
        td.important_information:empty{
            padding: 0px !important;
        }
        .important_information p{
            margin-bottom: 8px;
            line-height: 1.5;
        }
        .important_information hr{
            margin-top: 15px;
            margin-bottom: 15px;
        }
        span.payment{
            background-color: #191970;
            color: white;
            padding: 15px 15px;
            border-radius: 8px;
            display: inline-block;
            font-weight: bold;
        }
        span.payment.type-CONFIRMED,
        span.type-CONFIRMED{
            background-color: #198f51;
            color: white;
        }
        span.payment.type-CANCELLED,
        span.type-CANCELLED{
            background-color: #dc3545;
            color: white;
        }
    </style>
</head>
<body style="background-color: #f7fafb;">
    <div class="container">
        <div class="header">
            <img src="{{ $data['site']['logo'] }}" style="max-width:600px;">
        </div>
        <table class="table_init">
            <tbody>                
                <tr>
                    <td>
                        <div class="orange_content">
                            <table>
                                <tbody>
                                    <tr>
                                        <td style="text-align:center;">
                                            <img src="https://ik.imagekit.io/zqiqdytbq/transportation-api/mailing/top-vehicle.png?updatedAt=1693244044317">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding-top:0px;">
                                            <table>
                                                <tbody>
                                                    <tr>
                                                        <td style="padding:0px;">
                                                            <h1>{{ __('mailing/client.hello') }}</h1>
                                                            <p class="name">{{$data['client']['first_name']}} {{$data['client']['last_name']}}</p>
                                                        </td>
                                                        <td style="text-align:right;padding:0px;">
                                                            <h4 style="margin:0px;color:white;margin-bottom:8px;">{{ __('mailing/client.reservation_status') }}</h4>
                                                            <span class="payment type-{{$data['status']}}">{{ $reservation_status_label }}</span>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                            @if($lang == "en")
                                                <p>Thank you very much for booking with us, your service will be operated by {{ $provider_name }} which is our official tourist transportation company in {{ $destination_name }}.</p>
                                            @else
                                                <p>Muchas gracias por reservar con nosotros, Su servicio sera operado por {{ $provider_name }} la cuál es nuestra empresa de transporte turístico oficial en {{ $destination_name }}.</p>
                                            @endif
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </td>                    
                </tr>
                <tr>
                    <td class="white_content">
                        <p class="gray_color" style="margin-bottom:15px;">
                            @if($lang == "en")
                                This email shows in detail the information of your reservation made on {{ $creation_date }}, in which we ask that if it is not correct please contact us to make the corresponding modifications.
                            @else
                                En el presente correo se muestra a detalle la información de su reservación realizada el día {{ $creation_date }}, en el cual le pedimos que si no es correcta ponganse en contacto con nosotros para hacer las modificaciones correspondientes.
                            @endif
                        </p>
                        <p style="margin-bottom:15px;">
                            <strong>
                                @if($lang == "en")
                                    PLEASE PRESENT THIS PRINTED OR DIGITAL (CELL PHONE) RECEIPT TO THE {{ strtoupper($provider_name) }} REPRESENTATIVE TO BOARD YOUR UNIT.
                                @else
                                    POR FAVOR, PRESENTE ESTE RECIBO IMPRESO O DIGITAL (CELULAR) AL REPRESENTANTE DE {{ strtoupper($provider_name) }}, PARA ABORDAR SU UNIDAD.
                                @endif
                            </strong>
                        </p>
                        <p style="margin-bottom:15px;">
                            <strong>
                                @if($lang == "en")
                                    AT {{ strtoupper($provider_name) }} WE TAKE YOUR SAFETY VERY SERIOUSLY. THEREFORE, IT WILL BE NECESSARY TO PRESENT AN OFFICIAL ID AND SIGN THE SERVICE PICK UP FORM AT THE TIME OF BOARDING YOUR UNIT.
                                @else
                                    EN {{ strtoupper($provider_name) }} NOS TOMAMOS MUY EN SERIO SU SEGURIDAD. POR ELLO, SERÁ NECESARIO PRESENTAR UNA IDENTIFICACIÓN OFICIAL Y FIRMAR EL FORMULARIO DE TOMA DE SERVICIO AL MOMENTO DE ABORDAR SU UNIDAD.
                                @endif
                            </strong>
                        </p>
                        <h2>Total: {{ number_format($data['sales']['total'],2) }} {{ $data['config']['currency'] }}</h2>
                        @if(sizeof($data['items']) >= 1)
                            @foreach ($data['items'] as $key => $value)     
                                <div style="background-color:#DDE9FA;padding: 15px;margin-bottom:15px;">
                                    <table style="width:100%;">
                                        <tbody>
                                            <tr>
                                                <td>
                                                    <p class="label">{{ __('mailing/client.booking_id') }}</p>
                                                    <p style="font-size: 14pt;">{{$key}}</p>
                                                </td>
                                                <td rowspan="7" style="text-align:right;">
                                                    @php
                                                        $QR = urlencode('https://api.caribbean-transfers.com/api/v1/mailing/reservation/view?code='.$key.'&email='.trim(strtolower($data['client']['email'])).'&language='.$lang);
                                                    @endphp
                                                    <img src="{{config('app.url')}}/api/v1/reservation/qr?code={{$QR}}" width="250">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <p class="label">{{ __('mailing/client.type') }}</p>
                                                    <p>{{ (($value['is_round_trip'] == 0)? __('mailing/client.one_way') : __('mailing/client.round_trip') ) }}</p>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <p class="label">{{ __('mailing/client.name') }}</p>
                                                    <p>{{$data['client']['first_name']}} {{$data['client']['last_name']}}</p>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <p class="label">{{ __('mailing/client.phone') }}</p>
                                                    <p>{{$data['client']['phone']}}</p>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <p class="label">E-mail</p>
                                                    <p>{{$data['client']['email']}}</p>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <p class="label">Website</p>
                                                    <p>{{$data['site']['name']}}</p>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <p class="label">{{ __('mailing/client.payment_status') }}</p>
                                                    <p>
                                                        @if( $data['payments']['total'] >= $data['sales']['total'] )
                                                            {{ __('mailing/client.paid') }}
                                                        @else
                                                            {{ __('mailing/client.pendiente') }}
                                                        @endif
                                                    </p>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan="2" style="padding-top:10px;"><hr></td>
                                            </tr>
                                            <tr>
                                                <td colspan="2">
                                                    <table class="destinations_table">
                                                        <tbody>
                                                            @php
                                                                $itemCount = 0;
                                                            @endphp
                                                            <tr>
                                                                <td style="vertical-align:baseline;">
                                                                    <p class="label">{{ __('mailing/client.from') }}</p>
                                                                    <p>{{ $value['from']['name'] }}</p>
                                                                </td>
                                                                <td style="vertical-align:baseline;">
                                                                    <p class="label">{{ __('mailing/client.to') }}</p>
                                                                    <p>{{ $value['to']['name'] }}</p>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>
                                                                    <p class="label">{{ __('mailing/client.pickup') }}</p>
                                                                    <p>{{ $value['pickup'] }}</p>
                                                                    @if(!empty($value['departure_pickup']))
                                                                        <p class="label" style="margin-top:8px;">{{ __('mailing/client.departure_pickup') }}</p>
                                                                        <p>{{ $value['departure_pickup'] }}</p>
                                                                    @endif
                                                                </td>
                                                                <td style="vertical-align: baseline;">
                                                                    <p class="label">{{ __('mailing/client.passengers') }}</p>
                                                                    <p>{{ $value['passengers'] }}</p>
                                                                </td>
                                                            </tr>
                                                            
                                                            <tr>
                                                                <td>                                                                    
                                                                    <p class="label">{{ __('mailing/client.service_type') }}</p>
                                                                    <p>{{ $value['service_type_name'] }}</p>                                                                    
                                                                </td>
                                                                @if(!empty($value['flight_number']))
                                                                <td>                                                                    
                                                                    <p class="label">{{ __('mailing/client.flight_number') }}</p>
                                                                    <p>{{ $value['flight_number'] }}</p>                                                                    
                                                                </td>
                                                                @endif
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>                                
                                </div>
                            @endforeach
                        @endif

                        <div>
                            <p style="margin: 15px 0px 5px 0px; font-size:14pt;"><strong>{{ __('mailing/client.indications') }}</strong></p>
                            @if($lang == "en")
                                <p style="margin-bottom: 8px;">In this email you will find a summary of your reservation information, it is important that you can validate that the information is correct, and in case of any change in the information of your flight, doubts or clarifications contact us so we can assist you in the best possible way.</p>
                                <p>If you are at the airport or at your hotel and do not see us, call us at <a class="pink" href="tel:+529983870157">+52 (998) 387 0157</a> or send us a WhatsApp to <a class="pink" href="https://api.whatsapp.com/send?phone=5219982127069&text=Hello!">+52 (998) 212 7069</a>.</p>
                            @else
                                <p style="margin-bottom: 8px;">En este correo electrónico encontrarás un resumen de la información de tu reservación, es importante que puedas validar que la información es correcta, y en caso de algún cambio en la información de tu vuelo, dudas o aclaraciones contáctanos para poder atenderte de la mejor manera posible.</p>
                                <p>Si estás en el Aeropuerto o en tu Hotel y no nos ves, llámanos al <a class="pink" href="tel:+529983870157">+52 (998) 387 0157</a> o envíanos un WhatsApp al <a class="pink" href="https://api.whatsapp.com/send?phone=5219982127069&text=%C2%A1Hola!">+52 (998) 212 7069</a>.</p>
                            @endif                            
                        </div>
                    </td>                    
                </tr>
                <tr>
                    <td class="white_content" style="border-top: 1px solid #CCD5D8; text-align:center;">
                        @if($lang == "en")                            
                            <h3 style="margin-bottom: 0px; color: #191970;">Thank you for your reservation!</h3>
                        @else                            
                            <h4 style="margin-bottom: 0px; color: #191970;">¡Gracias por tu reservación!</h4>
                        @endif
                    </td>
                </tr>
                <tr>
                    <td>
                        <a href="https://www.tripadvisor.com/Attraction_Review-g150807-d25085358-Reviews-Caribbean_Transfers-Cancun_Yucatan_Peninsula.html" target="_blank">
                            <img src="https://ik.imagekit.io/zqiqdytbq/transportation-api/mailing/banner/banner.png" style="width:600px;">
                        </a>
                    </td>
                </tr>
                <tr>
                    <td class="white_content important_information" style="padding-top: 0px;">
                        @if( $mail->isNotEmpty() )
                            @foreach($mail as $key => $value)
                                @php
                                    print_r($value->text);
                                @endphp
                            @endforeach
                        @endif                                                
                    </td>
                </tr>
                <tr>
                    <td class="white_content information" style="text-align:left; padding-top: 0px;">
                        @if($lang == "en")                            
                            <h4 style="color:red; text-align:center;">ITS VERY IMPORTANT TO YOU TO KNOW</h4>
                            <h3>For ARRIVALS</h3>
                            <p><strong>Upon arrival to the Airport please follow these recommendations to ensure an easy and fast access to your vehicle:</strong></p>
                            <p>1.- When you arrive to the Airport, first you are going to go through immigrations, following you will be guided to the baggage claim area.</p>
                            <p>2.- Once you have picked up your luggage you will be directed to Customs.</p>
                            <p>3.- After you have cleared customs, please proceed to walk <strong>OUTSIDE</strong> your arrival terminal - <strong>it is very important to go all the way out since there will be large groups of "tourist advisors" which will try to stop you and claim to give you information about the destination however what they really do is sell time share, offering tours for free or discounted prices. For your own convenience please do not stop anywhere between customs and the airport exit.</strong></p>
                            <p>4.- One of our representatives will be waiting for you and will gladly greet you so you can start enjoying your vacations. He will have a board with the {{ $provider_name }} LOGO. This person will call your pick up transportation.</p>
                            <p>5.- WE ARE ALWAYS AT THE AIRPORT, in case you can`t see us, please dial our phone numbers.</p>
                            <p>6.- Tips for driver are <strong>NOT INCLUDED</strong>.</p>
                            <p>IMPORTANT: <strong>DO NOT</strong> be fooled by others at the airport. Others may say they are with us or tell you they do not know us to steal your business. Our greeters are there all day, so you can ask security for help finding us, but beware of the pirates as they may tell you we are not there. We are <strong>ALWAYS THERE</strong>. </p>
                            <p><strong>REMEMBER THAT WE WILL BE MONITORING YOUR FLIGHT IF IT IS DELAYED OR ARRIVED EARLY</strong>, it is not necessary to contact us, as we will be aware of this information. Only if your flight is canceled or changed, be sure to obtain the new flight information so that you can reschedule your transportation. </p>

                            <h3>For DEPARTURES</h3>
                            <p>The International Airport requests that all passengers departing on international flights must be at the airport at least 2 hours before the flight departure time. Your pick-up time was scheduled in accordance with this request and taking into account information such as flight departure time, estimated travel time between your hotel and airport, day of the week and assumed traffic flow.</p>

                            <h4>Policies</h4>                            
                            <p class="gray_color">In case the service has been paid by credit card, you must present the card and your identification when boarding.</p>
                            <p class="gray_color">The service may only be canceled 24 hours before your arrival or departure. If you purchased the Plus fare, you may receive a partial refund; otherwise, you are not eligible for a refund.</p>


                            <h5>Service Hours</h5> 
                            <p class="gray_color">In case of change of time of service can be made if you contact us 12 hours before the agreed time because you have to reschedule your service. Contact us from 7AM to 11PM from Monday to Sunday at <a class="pink" href="tel:+529983870157">+52 (998) 387 0157</a> or email <a class="pink" href="mailto:bookings@caribbean-transfers.com">bookings@caribbean-transfers.com</a></p>
                        @else
                            <h4 style="color:red; text-align:center;">ES MUY IMPORTANTE QUE SEPAS</h4>
                            <h3>Para LLEGADAS</h3>
                            <p><strong>A su llegada al aeropuerto, siga estas recomendaciones para garantizar un acceso fácil y rápido a su vehículo:</strong></p>
                            <p>1.- Cuando llegue al aeropuerto, primero pasará por el control de inmigración y, a continuación, será conducido a la zona de recogida de equipajes.</p>
                            <p>2.- Una vez que haya recogido su equipaje, se le dirigirá a la aduana.</p>
                            <p>3.- Después de pasar la aduana, diríjase al <strong>EXTERIOR</strong> de la terminal de llegada - <strong>  es muy importante llegar hasta la salida, ya que habrá grandes grupos de "asesores turísticos" que intentarán pararle y afirmarán que le dan información sobre el destino, aunque lo que realmente hacen es vender tiempo compartido, ofreciendo visitas gratuitas o a precios rebajados. Por su propia comodidad, no se detenga entre la aduana y la salida del aeropuerto.</strong></p>
                            <p>4.- Uno de nuestros representantes le estará esperando y gustosamente le dará la bienvenida para que pueda empezar a disfrutar de sus vacaciones. Él tendrá un tablero con el LOGO de {{ $provider_name }}. Esta persona llamará a su transporte de recogida.</p>
                            <p>5.- SIEMPRE ESTAMOS EN EL AEROPUERTO, en caso de que no pueda vernos, marque nuestros números de teléfono.</p>
                            <p>6.- La propina para el conductor <strong>NO ESTÁ INCLUIDA</strong>.</p>
                            <p>IMPORTANTE: <strong>NO</strong> se deje engañar por otras personas en el aeropuerto. Otros pueden decir que están con nosotros o decirle que no nos conocen para robarle su negocio. Nuestros recepcionistas están allí todo el día, por lo que puede pedir ayuda a seguridad para encontrarnos, pero tenga cuidado con los piratas, ya que pueden decirle que no estamos allí. Estamos <strong>SIEMPRE</strong>.</p>
                            <p><strong>RECUERDE QUE ESTAREMOS PENDIENTES DE SU VUELO SI SE RETRASA O LLEGA ANTES DE LO PREVISTO</strong>, no es necesario que se ponga en contacto con nosotros, ya que tendremos conocimiento de esta información. Sólo si su vuelo se cancela o cambia, asegúrese de obtener la información del nuevo vuelo para poder reprogramar su transporte.</p>

                            <h3>Para SALIDAS</h3>
                            <p>El Aeropuerto Internacional solicita que todos los pasajeros que salgan en vuelos internacionales estén en el aeropuerto al menos 2 horas antes de la hora de salida del vuelo. Su hora de recogida se programó de acuerdo con esta petición y teniendo en cuenta información como la hora de salida del vuelo, la duración estimada del trayecto entre su hotel y el aeropuerto, el día de la semana y el flujo de tráfico previsto.</p>

                            <h4>Políticas</h4>
                            <p class="gray_color">En caso de que el servicio haya sido pagado con tarjeta, deberá presentar la tarjeta y su identificación al abordar.</p>                            
                            <p class="gray_color">Unicamente se podrá cancelar el servicio 24 hrs antes de su llegada o salida, si usted adquirió la tarifa plus puede obtener un reembolso parcial, de lo contrario no es candidato a reembolso.</p>

                            <h5>Horario de Servicio</h5> 
                            <p class="gray_color">En caso de cambio de hora de servicio se podra efectuar si se comunica 12 hrs antes de la hora acordada ya que se tiene que reprogramar su servicio. Contáctanos de 7AM a 11PM de Lunes a Domingo al numero <a class="pink" href="tel:+529983870157">+52 (998) 387 0157</a> o al correo <a class="pink" href="mailto:bookings@caribbean-transfers.com">bookings@caribbean-transfers.com</a></p>
                        @endif
                    </td>
                </tr>
                <tr>
                    <td style="padding: 15px; text-align:center;">
                        <div>
                            <a href="https://www.facebook.com/caribbeantransferscun" target="_blank"><img src="https://ik.imagekit.io/zqiqdytbq/transportation-api/mailing/social/facebook.png?updatedAt=1692978703979" style="margin-right: 15px;"></a>
                            <!--<a href="#" target="_blank"><img src="https://ik.imagekit.io/zqiqdytbq/transportation-api/mailing/social/instagram.png?updatedAt=1692978703965"></a>-->
                        </div>
                        <p style="font-size: 11pt; color: #6A829E;">&copy; {{ $provider_name }} | {{ __('mailing/client.rights_reserved') }}</p>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</body>
</html>
