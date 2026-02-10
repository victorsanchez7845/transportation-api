@php    
    $lang = app()->getLocale();
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
            background-color: orange;
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
        .main_text_content p {
            margin-bottom: 10px;
        }

        .main-title-btn {
            background-color: #333367;
            font-weight: bold;
            color: white;
            padding: 10px 15px;
            border-radius: 10px;
            text-decoration: none;
            font-size: 17px;
        }
        .link-btn {
            background-color: #f15523;
            font-weight: bold;
            color: white;
            padding: 10px 15px;
            border-radius: 10px;
            text-decoration: none;
        }
    </style>
</head>
<body style="background-color: #f7fafb;">
    <div class="container">
        <div class="header">
            <img src="{{ $site['logo'] }}" style="max-width:600px;">
        </div>
        <table class="table_init">
            <tbody>
                <tr>
                    <td class="white_content main_text_content">
                        <div style="background-color:#DDE9FA;padding: 15px;margin-bottom:15px; padding-top: 26px;">
                            <table style="width:100%;">
                                <tbody>
                                    <tr>
                                        <td>
                                            @if($lang == "en")
                                                <a class="main-title-btn" style="color: white;" href="{{ $paypal_URL }}">Complete Your Reservation</a>
                                                <p style="margin-top: 20px; margin-bottom: 10px;">Secure your transfer now at our special online rate:</p>
                                                
                                                <table>
                                                    <tbody>
                                                        <tr style="height: 62px;">
                                                            <td style="vertical-align: center;">
                                                                <a class="link-btn" style="color: white;" href="{{ $paypal_URL }}" title="Pay with PayPal">Pay with PayPal</a>
                                                            </td>
                                                            <td style="vertical-align: center;">
                                                                <img style="max-width: 160px;" width="159.95" height="57.36" src="https://api.caribbean-transfers.com/img/paypal-credit-card-logo.png" alt="PAYPAL">
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                                <table>
                                                    <tbody>
                                                        <tr style="height: 30px;">
                                                            <td style="vertical-align: center;">
                                                                <a class="link-btn" style="color: white;" href="{{ $stripe_URL }}" title="Pay with Stripe">Pay with Stripe</a>
                                                            </td>
                                                            <td style="vertical-align: center;">
                                                                <img style="max-width: 160px; margin-left: 12px; margin-top: 2px;" width="160" height="41" src="https://api.caribbean-transfers.com/img/powered_by_stripe.png" alt="STRIPE">
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                                
                                                <p style="margin-top: 10px">Hi again!</p>
                                                <p>We noticed your booking is still pending. Prepay now to</p>
                                                <p>confirm your transfer and avoid delays or last-minute fees.</p>

                                                <p>Once payment is received, you'll get a confirmation by email.</p>

                                                <p>Questions? Message us on WhatsApp: {{ $data->transactional_phone }} </p>
                                                <p><strong>Hours:</strong> 7:00 am to 11:00 pm.</p>
                                                <p>We look forward to welcoming you soon!</p>
                                            @else
                                                <a class="main-title-btn" style="color: white;" href="{{ $paypal_URL }}">Complete su reserva</a>
                                                <p style="margin-top: 20px; margin-bottom: 10px;">Asegura tu traslado ahora con nuestra tarifa especial online:</p>
                                            
                                                <table>
                                                    <tbody>
                                                        <tr style="height: 62px;">
                                                            <td style="vertical-align: center;">
                                                                <a class="link-btn" style="color: white;" href="{{ $paypal_URL }}" title="Pagar con PayPal">Pagar con PayPal</a>
                                                            </td>
                                                            <td style="vertical-align: center;">
                                                                <img style="max-width: 160px;" width="159.95" height="57.36" src="https://api.caribbean-transfers.com/img/paypal-credit-card-logo.png" alt="PAYPAL">
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                                <table>
                                                    <tbody>
                                                        <tr style="height: 30px;">
                                                            <td style="vertical-align: center;">
                                                                <a class="link-btn" style="color: white;" href="{{ $stripe_URL }}" title="Pagar con Stripe">Pagar con Stripe</a>
                                                            </td>
                                                            <td style="vertical-align: center;">
                                                                <img style="max-width: 160px; margin-left: 12px; margin-top: 2px;" width="160" height="41" src="https://api.caribbean-transfers.com/img/powered_by_stripe.png" alt="STRIPE">
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>

                                                <p style="margin-top: 10px">¡Hola de nuevo!</p>
                                                <p>Nos hemos dado cuenta de que tu reserva sigue pendiente. Paga por adelantado ahora para confirmar tu traslado y evitar retrasos o cargos de última hora.</p>
                                            
                                                <p>Una vez recibido el pago, recibirás una confirmación por correo electrónico.</p>
                                            
                                                <p>¿Tienes preguntas? Envíanos un mensaje por WhatsApp: {{ $data->transactional_phone }} </p>
                                                <p><strong>Horario:</strong> 7:00 am - 11:00 pm</p>
                                                <p>¡Esperamos darte la bienvenida pronto! </p>
                                            @endif
                                        </td>
                                    </tr>
                                </tbody>
                            </table>                                
                        </div>

                        <div>
                            <p style="margin: 15px 0px 5px 0px; font-size:14pt;"><strong>{{ __('mailing/client.indications') }}</strong></p>
                            @if($lang == "en")
                                <p>If you are at the airport or at your hotel and do not see us, call us at <a class="pink" href="tel:+529983870157">+52 (998) 387 0157</a> or send us a WhatsApp to <a class="pink" href="https://api.whatsapp.com/send?phone=5219982127069&text=Hello!">+52 (998) 212 7069</a>.</p>
                            @else
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
                    <td class="white_content information" style="text-align:left; padding-top: 0px;">
                        @if($lang == "en")                            
                            <h4 style="color:red; text-align:center;">ITS VERY IMPORTANT TO YOU TO KNOW</h4>
                            <h3>For ARRIVALS</h3>
                            <p><strong>Upon arrival to the Airport please follow these recommendations to ensure an easy and fast access to your vehicle:</strong></p>
                            <p>1.- When you arrive to the Airport, first you are going to go through immigrations, following you will be guided to the baggage claim area.</p>
                            <p>2.- Once you have picked up your luggage you will be directed to Customs.</p>
                            <p>3.- After you have cleared customs, please proceed to walk <strong>OUTSIDE</strong> your arrival terminal - <strong>it is very important to go all the way out since there will be large groups of "tourist advisors" which will try to stop you and claim to give you information about the destination however what they really do is sell time share, offering tours for free or discounted prices. For your own convenience please do not stop anywhere between customs and the airport exit.</strong></p>
                            <p>4.- One of our representatives will be waiting for you and will gladly greet you so you can start enjoying your vacations. He will have a board with the {{ $provider->name }} LOGO. This person will call your pick up transportation.</p>
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
                            <p>4.- Uno de nuestros representantes le estará esperando y gustosamente le dará la bienvenida para que pueda empezar a disfrutar de sus vacaciones. Él tendrá un tablero con el LOGO de {{ $provider->name }}. Esta persona llamará a su transporte de recogida.</p>
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
                        <p style="font-size: 11pt; color: #6A829E;">&copy; {{ $provider->name }} | {{ __('mailing/client.rights_reserved') }}</p>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</body>
</html>
