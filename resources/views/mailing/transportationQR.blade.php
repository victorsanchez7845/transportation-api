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
        <table class="table_init">
            <tbody>
                <tr>
                    <td class="white_content">
                        <p style="margin-bottom:15px;">
                            <strong>
                                @if($lang == "en")
                                    PLEASE PRESENT THIS PRINTED OR DIGITAL (CELL PHONE) RECEIPT TO THE CARIBBEAN TRANSFERS REPRESENTATIVE TO BOARD YOUR UNIT.
                                @else
                                    POR FAVOR, PRESENTE ESTE RECIBO IMPRESO O DIGITAL (CELULAR) AL REPRESENTANTE DE CARIBBEAN TRANSFERS, PARA ABORDAR SU UNIDAD.
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
                    </td>                    
                </tr>
            </tbody>
        </table>
    </div>
</body>
</html>