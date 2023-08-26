@php
    use App\Traits\FunctionsTrait;
    $lang = app()->getLocale();

    $creation_date = FunctionsTrait::getPrettyDate($data['config']['creation_date'], $lang);
    // echo "<pre>";
    // print_r($data);
    // die();
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
            background-color: #CCD5D8;
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
            margin-top: 20px;
        }
        .header{
            background-color:white; 
            padding: 25px;
            text-align: center;
        }
        td.orange_content{
            background-color: #FD9941;
            background-image: url('https://ik.imagekit.io/zqiqdytbq/transportation-api/mailing/banner.png?updatedAt=1692914906714');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            height: 400px;
        }
        td.orange_content table{
            width: 100%;
            height: 400px;
        }
        td.orange_content table td{
            text-align:left; 
            vertical-align: bottom; 
            padding: 25px;
        }
        td.orange_content table td h1{
            font-size: 22pt;
            margin: 0px;
            color: white;
            margin-bottom: 8px;
        }
        td.orange_content table td p{
            font-size: 11pt;
            color: white;
            margin: 0px;
        }
        td.orange_content table td p.name{
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
        .orange{
            color: #FF7903;
        }
        .important_information p{
            margin-bottom: 8px;
            line-height: 1.5;
        }
    </style>
</head>
<body>
    <div class="container">
        <table class="table_init">
            <thead>
                <tr>
                    <th class="header"><img src="https://ik.imagekit.io/zqiqdytbq/transportation-api/mailing/logo.png"></th>
                </tr>                
            </thead>
            <tbody>                
                <tr>
                    <td class="orange_content">
                        <table>
                            <tbody>
                                <tr>
                                    <td>
                                        <h1>{{ __('mailing/client.hello') }}</h1>
                                        <p class="name">{{ $data['client']['first_name'] }}</p>
                                        @if($lang == "en")
                                            <p>Thank you very much for booking with us, your service will be operated by Caribbean Transfers which is our official tourist transportation company in Cancun and the Riviera Maya.</p>
                                        @else
                                            <p>Muchas gracias por reservar con nosotros, Su servicio sera operado por Caribbean Transfers la cuál es nuestra empresa de transporte turístico oficial en Cancún y la Riviera Maya.</p>
                                        @endif
                                    </td>
                                </tr>
                            </tbody>
                        </table>
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
                                    PLEASE PRESENT THIS PRINTED OR DIGITAL (CELL PHONE) RECEIPT TO THE CARIBBEAN SEA TRAVEL REPRESENTATIVE TO BOARD YOUR UNIT.
                                @else
                                    POR FAVOR, PRESENTE ESTE RECIBO IMPRESO O DIGITAL (CELULAR) AL REPRESENTANTE DE CARIBBEAN TRANSFERS, PARA ABORDAR SU UNIDAD.
                                @endif
                            </strong>
                        </p>
                        @if(sizeof($data['items']) >= 1)
                            @foreach ($data['items'] as $key => $value)     
                                <div style="background-color:#DDE9FA;padding: 15px;margin-bottom:15px;">
                                    <table style="width:100%;">
                                        <tbody>
                                            <tr>
                                                <td><p style="font-weight:bold; font-size: 18pt;">{{$key}}</p></td>
                                                <td rowspan="4" style="text-align:right;">
                                                    <img src="https://ik.imagekit.io/zqiqdytbq/transportation-api/mailing/qr.png">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <p class="label">Nombre</p>
                                                    <p>{{$data['client']['first_name']}} {{$data['client']['last_name']}}</p>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <p class="label">Teléfono</p>
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
                                                <td colspan="2" style="padding-top:10px;"><hr></td>
                                            </tr>
                                            <tr>
                                                <td colspan="2">
                                                    <table class="destinations_table">
                                                        <tbody>
                                                            @php
                                                                $itemCount = 0;
                                                            @endphp
                                                            @foreach($value as $keyItem => $valueItem)
                                                            @php
                                                                // echo "<pre>";
                                                                // print_r($valueItem);
                                                                // die();
                                                            @endphp
                                                            <tr>
                                                                <td>
                                                                    <p class="label">Desde</p>
                                                                    <p>{{ $valueItem['from']['name'] }}</p>
                                                                </td>
                                                                <td>
                                                                    <p class="label">Hacia</p>
                                                                    <p>{{ $valueItem['to']['name'] }}</p>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>
                                                                    <p class="label">Pickup</p>
                                                                    <p>{{ $valueItem['pickup'] }}</p>
                                                                </td>
                                                                <td>
                                                                    <p class="label">Passengers</p>
                                                                    <p>{{ $valueItem['passengers'] }}</p>
                                                                </td>
                                                            </tr>
                                                            
                                                            <tr>
                                                                <td>                                                                    
                                                                    <p class="label">Tipo de servicio</p>
                                                                    <p>{{ $valueItem['service_type_name'] }}</p>                                                                    
                                                                </td>
                                                                @if(!empty(trim($valueItem['flight_number'])))
                                                                <td>                                                                    
                                                                    <p class="label">Número de vuelo</p>
                                                                    <p>{{ $valueItem['flight_number'] }}</p>                                                                    
                                                                </td>
                                                                @endif
                                                            </tr>
                                                           
                                                            
                                                                @php
                                                                    $itemCount++;
                                                                    if($itemCount == 1):
                                                                        echo '<tr><td colspan="2"><hr></td></tr>';
                                                                    endif;
                                                                @endphp
                                                            @endforeach
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
                            <p style="margin: 15px 0px 5px 0px;"><strong>Indicaciones</strong></p>
                            <p style="margin-bottom: 8px;">En este correo electrónico encontrarás un resumen de la información de tu reservación, es importante que puedas validar que la información es correcta, y en caso de algún cambio en la información de tu vuelo, dudas o aclaraciones contáctanos para poder atenderte de la mejor manera posible.</p>
                            <p>Si estás en el Aeropuerto o en tu Hotel y no nos ves, <span class="orange">llámanos al +52 (998) 294 2389</span> o envíanos un WhatsApp al mismo número.</p>
                        </div>
                    </td>                    
                </tr>
                <tr>
                    <td class="white_content" style="border-top: 1px solid #CCD5D8; text-align:center;">
                        <p style="width: 70%; margin: 0 auto;">Más información de cómo encontranos aquí. Términos y condiciones de cancelación</p>
                        <h4 style="margin-bottom: 0px;">¡Gracias por tu reservación!</h4>
                    </td>
                </tr>
                <tr>
                    <td>
                        <img src="https://ik.imagekit.io/zqiqdytbq/transportation-api/mailing/banner/banner.png" style="width:600px;">
                    </td>
                </tr>
                <tr>
                    <td class="white_content important_information">
                        <p><strong>Indicaciones importantes</strong></p>

                        <p>Lorem ipsum, dolor sit amet consectetur adipisicing elit. Est at asperiores voluptatibus dicta sunt? Laboriosam explicabo exercitationem ut doloribus odio blanditiis, molestias minima esse nesciunt iste ipsa mollitia iusto error!</p>
                        <p>Lorem ipsum, dolor sit amet consectetur adipisicing elit. Est at asperiores voluptatibus dicta sunt? Laboriosam explicabo exercitationem ut doloribus odio blanditiis, molestias minima esse nesciunt iste ipsa mollitia iusto error!</p>
                        <p>Lorem ipsum, dolor sit amet consectetur adipisicing elit. Est at asperiores voluptatibus dicta sunt? Laboriosam explicabo exercitationem ut doloribus odio blanditiis, molestias minima esse nesciunt iste ipsa mollitia iusto error!</p>
                        <p>Lorem ipsum, dolor sit amet consectetur adipisicing elit. Est at asperiores voluptatibus dicta sunt? Laboriosam explicabo exercitationem ut doloribus odio blanditiis, molestias minima esse nesciunt iste ipsa mollitia iusto error!</p>
                    </td>
                </tr>
                <tr>
                    <td class="white_content important_information" style="border-top: 1px solid #CCD5D8;">
                        <p><strong>A su salida</strong></p>

                        <p>Lorem ipsum, dolor sit amet consectetur adipisicing elit. Est at asperiores voluptatibus dicta sunt? Laboriosam explicabo exercitationem ut doloribus odio blanditiis, molestias minima esse nesciunt iste ipsa mollitia iusto error!</p>
                        <p>Lorem ipsum, dolor sit amet consectetur adipisicing elit. Est at asperiores voluptatibus dicta sunt? Laboriosam explicabo exercitationem ut doloribus odio blanditiis, molestias minima esse nesciunt iste ipsa mollitia iusto error!</p>
                        <p>Lorem ipsum, dolor sit amet consectetur adipisicing elit. Est at asperiores voluptatibus dicta sunt? Laboriosam explicabo exercitationem ut doloribus odio blanditiis, molestias minima esse nesciunt iste ipsa mollitia iusto error!</p>
                        <p>Lorem ipsum, dolor sit amet consectetur adipisicing elit. Est at asperiores voluptatibus dicta sunt? Laboriosam explicabo exercitationem ut doloribus odio blanditiis, molestias minima esse nesciunt iste ipsa mollitia iusto error!</p>
                    </td>
                </tr>
                <tr>
                    <td class="white_content" style="text-align:center;">
                        <p>Les deseamos que pasen unos dias gratamente inolvidables.</p>
                        <h4>Políticas</h4>
                        <h5>Politicas Para Cancelacion</h5> 
                        <p class="gray_color">Unicamente se podra cancelar el servicio 24 hrs antes de su servicio sea llegada o salida.</p>
                        <h5>Horario de Servicio</h5> 
                        <p class="gray_color">En caso de cambio de hora de servicio se podra efectuar si se comunica 12 hrs antes de la hora acordada ya que se tiene que reprogramar su servicio. Contáctanos las 24 hrs al numero +52 9982942389 o al correo sales@taxirivieramaya.com</p>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 15px; text-align:center;">
                        <div>
                            <a href="#"><img src="https://ik.imagekit.io/zqiqdytbq/transportation-api/mailing/social/facebook.png?updatedAt=1692978703979" style="margin-right: 15px;"></a>
                            <a href="#"><img src="https://ik.imagekit.io/zqiqdytbq/transportation-api/mailing/social/instagram.png?updatedAt=1692978703965"></a>
                        </div>
                        <p style="font-size: 11pt; color: #6A829E;">Caribbean Sea Travel | All RIghts Reserved</p>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</body>
</html>