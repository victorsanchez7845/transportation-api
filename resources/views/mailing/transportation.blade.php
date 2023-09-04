@php
    use App\Traits\FunctionsTrait;
    $lang = app()->getLocale();
    $creation_date = FunctionsTrait::getPrettyDate($data['config']['creation_date'], $lang);
    
    $reservation_status_label = $data['status'];
    switch ($lang) {
        case 'es':
                if($reservation_status_label == "CONFIRMED"):
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
            margin-top: 20px;
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
        span.payment.type-CONFIRMED{
            background-color: #198f51;            
        }
    </style>
</head>
<body style="background-color: #f7fafb;">
    <div class="container">
        <div class="header">
            <img src="{{ $data['site']['logo'] }}">
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
                                                <p>Thank you very much for booking with us, your service will be operated by Caribbean Transfers which is our official tourist transportation company in Cancun and the Riviera Maya.</p>
                                            @else
                                                <p>Muchas gracias por reservar con nosotros, Su servicio sera operado por Caribbean Transfers la cuál es nuestra empresa de transporte turístico oficial en Cancún y la Riviera Maya.</p>
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
                                    PLEASE PRESENT THIS PRINTED OR DIGITAL (CELL PHONE) RECEIPT TO THE CARIBBEAN SEA TRAVEL REPRESENTATIVE TO BOARD YOUR UNIT.
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
                                                <td><p style="font-weight:bold; font-size: 18pt;">{{$key}}</p></td>
                                                <td rowspan="4" style="text-align:right;">
                                                    <img src="data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz4KPHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZlcnNpb249IjEuMSIgd2lkdGg9IjIwMCIgaGVpZ2h0PSIyMDAiIHZpZXdCb3g9IjAgMCAyMDAgMjAwIj48cmVjdCB4PSIwIiB5PSIwIiB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgZmlsbD0iI2ZmZmZmZiIvPjxnIHRyYW5zZm9ybT0ic2NhbGUoOS41MjQpIj48ZyB0cmFuc2Zvcm09InRyYW5zbGF0ZSgwLDApIj48cGF0aCBmaWxsLXJ1bGU9ImV2ZW5vZGQiIGQ9Ik04IDAuMjVMOCAwLjc1QTAuMjUgMC4yNSAwIDAgMCA4LjI1IDFMOC43NSAxQTAuMjUgMC4yNSAwIDAgMSA5IDEuMjVMOSAzLjc1QTAuMjUgMC4yNSAwIDAgMCA5LjI1IDRMOS43NSA0QTAuMjUgMC4yNSAwIDAgMSAxMCA0LjI1TDEwIDQuNzVBMC4yNSAwLjI1IDAgMCAwIDEwLjI1IDVMMTAuNzUgNUEwLjI1IDAuMjUgMCAwIDEgMTEgNS4yNUwxMSA1Ljc1QTAuMjUgMC4yNSAwIDAgMSAxMC43NSA2TDEwLjI1IDZBMC4yNSAwLjI1IDAgMCAwIDEwIDYuMjVMMTAgNi43NUEwLjI1IDAuMjUgMCAwIDEgOS43NSA3TDkuMjUgN0EwLjI1IDAuMjUgMCAwIDEgOSA2Ljc1TDkgNi4yNUEwLjI1IDAuMjUgMCAwIDAgOC43NSA2TDguMjUgNkEwLjI1IDAuMjUgMCAwIDAgOCA2LjI1TDggNy43NUEwLjI1IDAuMjUgMCAwIDAgOC4yNSA4TDguNzUgOEEwLjI1IDAuMjUgMCAwIDEgOSA4LjI1TDkgOC43NUEwLjI1IDAuMjUgMCAwIDAgOS4yNSA5TDEwLjc1IDlBMC4yNSAwLjI1IDAgMCAwIDExIDguNzVMMTEgOC4yNUEwLjI1IDAuMjUgMCAwIDEgMTEuMjUgOEwxMS43NSA4QTAuMjUgMC4yNSAwIDAgMSAxMiA4LjI1TDEyIDkuNzVBMC4yNSAwLjI1IDAgMCAwIDEyLjI1IDEwTDEyLjc1IDEwQTAuMjUgMC4yNSAwIDAgMSAxMyAxMC4yNUwxMyAxMC43NUEwLjI1IDAuMjUgMCAwIDAgMTMuMjUgMTFMMTQuNzUgMTFBMC4yNSAwLjI1IDAgMCAwIDE1IDEwLjc1TDE1IDguMjVBMC4yNSAwLjI1IDAgMCAwIDE0Ljc1IDhMMTQuMjUgOEEwLjI1IDAuMjUgMCAwIDAgMTQgOC4yNUwxNCA5Ljc1QTAuMjUgMC4yNSAwIDAgMSAxMy43NSAxMEwxMy4yNSAxMEEwLjI1IDAuMjUgMCAwIDEgMTMgOS43NUwxMyA0LjI1QTAuMjUgMC4yNSAwIDAgMCAxMi43NSA0TDEyLjI1IDRBMC4yNSAwLjI1IDAgMCAxIDEyIDMuNzVMMTIgMy4yNUEwLjI1IDAuMjUgMCAwIDAgMTEuNzUgM0wxMS4yNSAzQTAuMjUgMC4yNSAwIDAgMSAxMSAyLjc1TDExIDIuMjVBMC4yNSAwLjI1IDAgMCAxIDExLjI1IDJMMTEuNzUgMkEwLjI1IDAuMjUgMCAwIDAgMTIgMS43NUwxMiAxLjI1QTAuMjUgMC4yNSAwIDAgMSAxMi4yNSAxTDEyLjc1IDFBMC4yNSAwLjI1IDAgMCAwIDEzIDAuNzVMMTMgMC4yNUEwLjI1IDAuMjUgMCAwIDAgMTIuNzUgMEw4LjI1IDBBMC4yNSAwLjI1IDAgMCAwIDggMC4yNVpNMTAgMS4yNUwxMCAxLjc1QTAuMjUgMC4yNSAwIDAgMCAxMC4yNSAyTDEwLjc1IDJBMC4yNSAwLjI1IDAgMCAwIDExIDEuNzVMMTEgMS4yNUEwLjI1IDAuMjUgMCAwIDAgMTAuNzUgMUwxMC4yNSAxQTAuMjUgMC4yNSAwIDAgMCAxMCAxLjI1Wk0xMSA0LjI1TDExIDQuNzVBMC4yNSAwLjI1IDAgMCAwIDExLjI1IDVMMTEuNzUgNUEwLjI1IDAuMjUgMCAwIDAgMTIgNC43NUwxMiA0LjI1QTAuMjUgMC4yNSAwIDAgMCAxMS43NSA0TDExLjI1IDRBMC4yNSAwLjI1IDAgMCAwIDExIDQuMjVaTTExIDYuMjVMMTEgNi43NUEwLjI1IDAuMjUgMCAwIDAgMTEuMjUgN0wxMS43NSA3QTAuMjUgMC4yNSAwIDAgMCAxMiA2Ljc1TDEyIDYuMjVBMC4yNSAwLjI1IDAgMCAwIDExLjc1IDZMMTEuMjUgNkEwLjI1IDAuMjUgMCAwIDAgMTEgNi4yNVpNMCA4LjI1TDAgOS43NUEwLjI1IDAuMjUgMCAwIDAgMC4yNSAxMEwxLjc1IDEwQTAuMjUgMC4yNSAwIDAgMSAyIDEwLjI1TDIgMTIuNzVBMC4yNSAwLjI1IDAgMCAwIDIuMjUgMTNMNC43NSAxM0EwLjI1IDAuMjUgMCAwIDAgNSAxMi43NUw1IDEyLjI1QTAuMjUgMC4yNSAwIDAgMSA1LjI1IDEyTDUuNzUgMTJBMC4yNSAwLjI1IDAgMCAxIDYgMTIuMjVMNiAxMi43NUEwLjI1IDAuMjUgMCAwIDAgNi4yNSAxM0w3Ljc1IDEzQTAuMjUgMC4yNSAwIDAgMSA4IDEzLjI1TDggMTMuNzVBMC4yNSAwLjI1IDAgMCAwIDguMjUgMTRMOS43NSAxNEEwLjI1IDAuMjUgMCAwIDEgMTAgMTQuMjVMMTAgMTQuNzVBMC4yNSAwLjI1IDAgMCAwIDEwLjI1IDE1TDEwLjc1IDE1QTAuMjUgMC4yNSAwIDAgMSAxMSAxNS4yNUwxMSAxNS43NUEwLjI1IDAuMjUgMCAwIDEgMTAuNzUgMTZMMTAuMjUgMTZBMC4yNSAwLjI1IDAgMCAwIDEwIDE2LjI1TDEwIDE3Ljc1QTAuMjUgMC4yNSAwIDAgMSA5Ljc1IDE4TDkuMjUgMThBMC4yNSAwLjI1IDAgMCAxIDkgMTcuNzVMOSAxNi4yNUEwLjI1IDAuMjUgMCAwIDAgOC43NSAxNkw4LjI1IDE2QTAuMjUgMC4yNSAwIDAgMCA4IDE2LjI1TDggMTcuNzVBMC4yNSAwLjI1IDAgMCAwIDguMjUgMThMOC43NSAxOEEwLjI1IDAuMjUgMCAwIDEgOSAxOC4yNUw5IDE4Ljc1QTAuMjUgMC4yNSAwIDAgMSA4Ljc1IDE5TDguMjUgMTlBMC4yNSAwLjI1IDAgMCAwIDggMTkuMjVMOCAyMC43NUEwLjI1IDAuMjUgMCAwIDAgOC4yNSAyMUw5Ljc1IDIxQTAuMjUgMC4yNSAwIDAgMCAxMCAyMC43NUwxMCAyMC4yNUEwLjI1IDAuMjUgMCAwIDEgMTAuMjUgMjBMMTAuNzUgMjBBMC4yNSAwLjI1IDAgMCAxIDExIDIwLjI1TDExIDIwLjc1QTAuMjUgMC4yNSAwIDAgMCAxMS4yNSAyMUwxMS43NSAyMUEwLjI1IDAuMjUgMCAwIDAgMTIgMjAuNzVMMTIgMjAuMjVBMC4yNSAwLjI1IDAgMCAwIDExLjc1IDIwTDExLjI1IDIwQTAuMjUgMC4yNSAwIDAgMSAxMSAxOS43NUwxMSAxOC4yNUEwLjI1IDAuMjUgMCAwIDEgMTEuMjUgMThMMTIuNzUgMThBMC4yNSAwLjI1IDAgMCAwIDEzIDE3Ljc1TDEzIDE2LjI1QTAuMjUgMC4yNSAwIDAgMSAxMy4yNSAxNkwxMy43NSAxNkEwLjI1IDAuMjUgMCAwIDEgMTQgMTYuMjVMMTQgMTYuNzVBMC4yNSAwLjI1IDAgMCAwIDE0LjI1IDE3TDE1Ljc1IDE3QTAuMjUgMC4yNSAwIDAgMSAxNiAxNy4yNUwxNiAxNy43NUEwLjI1IDAuMjUgMCAwIDAgMTYuMjUgMThMMTcuNzUgMThBMC4yNSAwLjI1IDAgMCAxIDE4IDE4LjI1TDE4IDE5Ljc1QTAuMjUgMC4yNSAwIDAgMSAxNy43NSAyMEwxNy4yNSAyMEEwLjI1IDAuMjUgMCAwIDEgMTcgMTkuNzVMMTcgMTkuMjVBMC4yNSAwLjI1IDAgMCAwIDE2Ljc1IDE5TDE2LjI1IDE5QTAuMjUgMC4yNSAwIDAgMCAxNiAxOS4yNUwxNiAxOS43NUEwLjI1IDAuMjUgMCAwIDAgMTYuMjUgMjBMMTYuNzUgMjBBMC4yNSAwLjI1IDAgMCAxIDE3IDIwLjI1TDE3IDIwLjc1QTAuMjUgMC4yNSAwIDAgMCAxNy4yNSAyMUwxOS43NSAyMUEwLjI1IDAuMjUgMCAwIDAgMjAgMjAuNzVMMjAgMjAuMjVBMC4yNSAwLjI1IDAgMCAxIDIwLjI1IDIwTDIwLjc1IDIwQTAuMjUgMC4yNSAwIDAgMCAyMSAxOS43NUwyMSAxOC4yNUEwLjI1IDAuMjUgMCAwIDAgMjAuNzUgMThMMTkuMjUgMThBMC4yNSAwLjI1IDAgMCAxIDE5IDE3Ljc1TDE5IDE2LjI1QTAuMjUgMC4yNSAwIDAgMCAxOC43NSAxNkwxOC4yNSAxNkEwLjI1IDAuMjUgMCAwIDEgMTggMTUuNzVMMTggMTUuMjVBMC4yNSAwLjI1IDAgMCAwIDE3Ljc1IDE1TDE3LjI1IDE1QTAuMjUgMC4yNSAwIDAgMSAxNyAxNC43NUwxNyAxNC4yNUEwLjI1IDAuMjUgMCAwIDEgMTcuMjUgMTRMMTcuNzUgMTRBMC4yNSAwLjI1IDAgMCAwIDE4IDEzLjc1TDE4IDEzLjI1QTAuMjUgMC4yNSAwIDAgMSAxOC4yNSAxM0wxOC43NSAxM0EwLjI1IDAuMjUgMCAwIDEgMTkgMTMuMjVMMTkgMTMuNzVBMC4yNSAwLjI1IDAgMCAwIDE5LjI1IDE0TDE5Ljc1IDE0QTAuMjUgMC4yNSAwIDAgMSAyMCAxNC4yNUwyMCAxNi43NUEwLjI1IDAuMjUgMCAwIDAgMjAuMjUgMTdMMjAuNzUgMTdBMC4yNSAwLjI1IDAgMCAwIDIxIDE2Ljc1TDIxIDExLjI1QTAuMjUgMC4yNSAwIDAgMCAyMC43NSAxMUwxOS4yNSAxMUEwLjI1IDAuMjUgMCAwIDEgMTkgMTAuNzVMMTkgMTAuMjVBMC4yNSAwLjI1IDAgMCAxIDE5LjI1IDEwTDE5Ljc1IDEwQTAuMjUgMC4yNSAwIDAgMCAyMCA5Ljc1TDIwIDkuMjVBMC4yNSAwLjI1IDAgMCAxIDIwLjI1IDlMMjAuNzUgOUEwLjI1IDAuMjUgMCAwIDAgMjEgOC43NUwyMSA4LjI1QTAuMjUgMC4yNSAwIDAgMCAyMC43NSA4TDIwLjI1IDhBMC4yNSAwLjI1IDAgMCAwIDIwIDguMjVMMjAgOC43NUEwLjI1IDAuMjUgMCAwIDEgMTkuNzUgOUwxOC4yNSA5QTAuMjUgMC4yNSAwIDAgMCAxOCA5LjI1TDE4IDkuNzVBMC4yNSAwLjI1IDAgMCAxIDE3Ljc1IDEwTDE3LjI1IDEwQTAuMjUgMC4yNSAwIDAgMSAxNyA5Ljc1TDE3IDkuMjVBMC4yNSAwLjI1IDAgMCAwIDE2Ljc1IDlMMTYuMjUgOUEwLjI1IDAuMjUgMCAwIDAgMTYgOS4yNUwxNiAxMC43NUEwLjI1IDAuMjUgMCAwIDAgMTYuMjUgMTFMMTYuNzUgMTFBMC4yNSAwLjI1IDAgMCAxIDE3IDExLjI1TDE3IDEzLjc1QTAuMjUgMC4yNSAwIDAgMSAxNi43NSAxNEwxNi4yNSAxNEEwLjI1IDAuMjUgMCAwIDEgMTYgMTMuNzVMMTYgMTMuMjVBMC4yNSAwLjI1IDAgMCAwIDE1Ljc1IDEzTDE1LjI1IDEzQTAuMjUgMC4yNSAwIDAgMSAxNSAxMi43NUwxNSAxMi4yNUEwLjI1IDAuMjUgMCAwIDAgMTQuNzUgMTJMMTQuMjUgMTJBMC4yNSAwLjI1IDAgMCAwIDE0IDEyLjI1TDE0IDEyLjc1QTAuMjUgMC4yNSAwIDAgMCAxNC4yNSAxM0wxNC43NSAxM0EwLjI1IDAuMjUgMCAwIDEgMTUgMTMuMjVMMTUgMTMuNzVBMC4yNSAwLjI1IDAgMCAxIDE0Ljc1IDE0TDEzLjI1IDE0QTAuMjUgMC4yNSAwIDAgMSAxMyAxMy43NUwxMyAxMy4yNUEwLjI1IDAuMjUgMCAwIDAgMTIuNzUgMTNMMTEuMjUgMTNBMC4yNSAwLjI1IDAgMCAxIDExIDEyLjc1TDExIDEyLjI1QTAuMjUgMC4yNSAwIDAgMSAxMS4yNSAxMkwxMS43NSAxMkEwLjI1IDAuMjUgMCAwIDAgMTIgMTEuNzVMMTIgMTEuMjVBMC4yNSAwLjI1IDAgMCAwIDExLjc1IDExTDEwLjI1IDExQTAuMjUgMC4yNSAwIDAgMCAxMCAxMS4yNUwxMCAxMS43NUEwLjI1IDAuMjUgMCAwIDEgOS43NSAxMkw4LjI1IDEyQTAuMjUgMC4yNSAwIDAgMSA4IDExLjc1TDggMTEuMjVBMC4yNSAwLjI1IDAgMCAxIDguMjUgMTFMOC43NSAxMUEwLjI1IDAuMjUgMCAwIDAgOSAxMC43NUw5IDEwLjI1QTAuMjUgMC4yNSAwIDAgMCA4Ljc1IDEwTDYuMjUgMTBBMC4yNSAwLjI1IDAgMCAxIDYgOS43NUw2IDkuMjVBMC4yNSAwLjI1IDAgMCAxIDYuMjUgOUw2Ljc1IDlBMC4yNSAwLjI1IDAgMCAwIDcgOC43NUw3IDguMjVBMC4yNSAwLjI1IDAgMCAwIDYuNzUgOEw2LjI1IDhBMC4yNSAwLjI1IDAgMCAwIDYgOC4yNUw2IDguNzVBMC4yNSAwLjI1IDAgMCAxIDUuNzUgOUw1LjI1IDlBMC4yNSAwLjI1IDAgMCAxIDUgOC43NUw1IDguMjVBMC4yNSAwLjI1IDAgMCAwIDQuNzUgOEwzLjI1IDhBMC4yNSAwLjI1IDAgMCAwIDMgOC4yNUwzIDguNzVBMC4yNSAwLjI1IDAgMCAxIDIuNzUgOUwyLjI1IDlBMC4yNSAwLjI1IDAgMCAxIDIgOC43NUwyIDguMjVBMC4yNSAwLjI1IDAgMCAwIDEuNzUgOEwwLjI1IDhBMC4yNSAwLjI1IDAgMCAwIDAgOC4yNVpNMyA5LjI1TDMgOS43NUEwLjI1IDAuMjUgMCAwIDAgMy4yNSAxMEwzLjc1IDEwQTAuMjUgMC4yNSAwIDAgMSA0IDEwLjI1TDQgMTAuNzVBMC4yNSAwLjI1IDAgMCAwIDQuMjUgMTFMNC43NSAxMUEwLjI1IDAuMjUgMCAwIDAgNSAxMC43NUw1IDEwLjI1QTAuMjUgMC4yNSAwIDAgMCA0Ljc1IDEwTDQuMjUgMTBBMC4yNSAwLjI1IDAgMCAxIDQgOS43NUw0IDkuMjVBMC4yNSAwLjI1IDAgMCAwIDMuNzUgOUwzLjI1IDlBMC4yNSAwLjI1IDAgMCAwIDMgOS4yNVpNMCAxMS4yNUwwIDExLjc1QTAuMjUgMC4yNSAwIDAgMCAwLjI1IDEyTDAuNzUgMTJBMC4yNSAwLjI1IDAgMCAwIDEgMTEuNzVMMSAxMS4yNUEwLjI1IDAuMjUgMCAwIDAgMC43NSAxMUwwLjI1IDExQTAuMjUgMC4yNSAwIDAgMCAwIDExLjI1Wk02IDExLjI1TDYgMTEuNzVBMC4yNSAwLjI1IDAgMCAwIDYuMjUgMTJMNi43NSAxMkEwLjI1IDAuMjUgMCAwIDAgNyAxMS43NUw3IDExLjI1QTAuMjUgMC4yNSAwIDAgMCA2Ljc1IDExTDYuMjUgMTFBMC4yNSAwLjI1IDAgMCAwIDYgMTEuMjVaTTE5IDEyLjI1TDE5IDEyLjc1QTAuMjUgMC4yNSAwIDAgMCAxOS4yNSAxM0wxOS43NSAxM0EwLjI1IDAuMjUgMCAwIDAgMjAgMTIuNzVMMjAgMTIuMjVBMC4yNSAwLjI1IDAgMCAwIDE5Ljc1IDEyTDE5LjI1IDEyQTAuMjUgMC4yNSAwIDAgMCAxOSAxMi4yNVpNMTEgMTQuMjVMMTEgMTQuNzVBMC4yNSAwLjI1IDAgMCAwIDExLjI1IDE1TDExLjc1IDE1QTAuMjUgMC4yNSAwIDAgMCAxMiAxNC43NUwxMiAxNC4yNUEwLjI1IDAuMjUgMCAwIDAgMTEuNzUgMTRMMTEuMjUgMTRBMC4yNSAwLjI1IDAgMCAwIDExIDE0LjI1Wk0xNCAxNS4yNUwxNCAxNS43NUEwLjI1IDAuMjUgMCAwIDAgMTQuMjUgMTZMMTQuNzUgMTZBMC4yNSAwLjI1IDAgMCAwIDE1IDE1Ljc1TDE1IDE1LjI1QTAuMjUgMC4yNSAwIDAgMCAxNC43NSAxNUwxNC4yNSAxNUEwLjI1IDAuMjUgMCAwIDAgMTQgMTUuMjVaTTE2IDE2LjI1TDE2IDE2Ljc1QTAuMjUgMC4yNSAwIDAgMCAxNi4yNSAxN0wxNi43NSAxN0EwLjI1IDAuMjUgMCAwIDAgMTcgMTYuNzVMMTcgMTYuMjVBMC4yNSAwLjI1IDAgMCAwIDE2Ljc1IDE2TDE2LjI1IDE2QTAuMjUgMC4yNSAwIDAgMCAxNiAxNi4yNVpNMTQgMTguMjVMMTQgMTguNzVBMC4yNSAwLjI1IDAgMCAxIDEzLjc1IDE5TDEzLjI1IDE5QTAuMjUgMC4yNSAwIDAgMCAxMyAxOS4yNUwxMyAxOS43NUEwLjI1IDAuMjUgMCAwIDAgMTMuMjUgMjBMMTQuNzUgMjBBMC4yNSAwLjI1IDAgMCAwIDE1IDE5Ljc1TDE1IDE4LjI1QTAuMjUgMC4yNSAwIDAgMCAxNC43NSAxOEwxNC4yNSAxOEEwLjI1IDAuMjUgMCAwIDAgMTQgMTguMjVaTTkgMTkuMjVMOSAxOS43NUEwLjI1IDAuMjUgMCAwIDAgOS4yNSAyMEw5Ljc1IDIwQTAuMjUgMC4yNSAwIDAgMCAxMCAxOS43NUwxMCAxOS4yNUEwLjI1IDAuMjUgMCAwIDAgOS43NSAxOUw5LjI1IDE5QTAuMjUgMC4yNSAwIDAgMCA5IDE5LjI1Wk0xOSAxOS4yNUwxOSAxOS43NUEwLjI1IDAuMjUgMCAwIDAgMTkuMjUgMjBMMTkuNzUgMjBBMC4yNSAwLjI1IDAgMCAwIDIwIDE5Ljc1TDIwIDE5LjI1QTAuMjUgMC4yNSAwIDAgMCAxOS43NSAxOUwxOS4yNSAxOUEwLjI1IDAuMjUgMCAwIDAgMTkgMTkuMjVaTTAgMC4yNUwwIDYuNzVBMC4yNSAwLjI1IDAgMCAwIDAuMjUgN0w2Ljc1IDdBMC4yNSAwLjI1IDAgMCAwIDcgNi43NUw3IDAuMjVBMC4yNSAwLjI1IDAgMCAwIDYuNzUgMEwwLjI1IDBBMC4yNSAwLjI1IDAgMCAwIDAgMC4yNVpNMSAxLjI1TDEgNS43NUEwLjI1IDAuMjUgMCAwIDAgMS4yNSA2TDUuNzUgNkEwLjI1IDAuMjUgMCAwIDAgNiA1Ljc1TDYgMS4yNUEwLjI1IDAuMjUgMCAwIDAgNS43NSAxTDEuMjUgMUEwLjI1IDAuMjUgMCAwIDAgMSAxLjI1Wk0yIDIuMjVMMiA0Ljc1QTAuMjUgMC4yNSAwIDAgMCAyLjI1IDVMNC43NSA1QTAuMjUgMC4yNSAwIDAgMCA1IDQuNzVMNSAyLjI1QTAuMjUgMC4yNSAwIDAgMCA0Ljc1IDJMMi4yNSAyQTAuMjUgMC4yNSAwIDAgMCAyIDIuMjVaTTE0IDAuMjVMMTQgNi43NUEwLjI1IDAuMjUgMCAwIDAgMTQuMjUgN0wyMC43NSA3QTAuMjUgMC4yNSAwIDAgMCAyMSA2Ljc1TDIxIDAuMjVBMC4yNSAwLjI1IDAgMCAwIDIwLjc1IDBMMTQuMjUgMEEwLjI1IDAuMjUgMCAwIDAgMTQgMC4yNVpNMTUgMS4yNUwxNSA1Ljc1QTAuMjUgMC4yNSAwIDAgMCAxNS4yNSA2TDE5Ljc1IDZBMC4yNSAwLjI1IDAgMCAwIDIwIDUuNzVMMjAgMS4yNUEwLjI1IDAuMjUgMCAwIDAgMTkuNzUgMUwxNS4yNSAxQTAuMjUgMC4yNSAwIDAgMCAxNSAxLjI1Wk0xNiAyLjI1TDE2IDQuNzVBMC4yNSAwLjI1IDAgMCAwIDE2LjI1IDVMMTguNzUgNUEwLjI1IDAuMjUgMCAwIDAgMTkgNC43NUwxOSAyLjI1QTAuMjUgMC4yNSAwIDAgMCAxOC43NSAyTDE2LjI1IDJBMC4yNSAwLjI1IDAgMCAwIDE2IDIuMjVaTTAgMTQuMjVMMCAyMC43NUEwLjI1IDAuMjUgMCAwIDAgMC4yNSAyMUw2Ljc1IDIxQTAuMjUgMC4yNSAwIDAgMCA3IDIwLjc1TDcgMTQuMjVBMC4yNSAwLjI1IDAgMCAwIDYuNzUgMTRMMC4yNSAxNEEwLjI1IDAuMjUgMCAwIDAgMCAxNC4yNVpNMSAxNS4yNUwxIDE5Ljc1QTAuMjUgMC4yNSAwIDAgMCAxLjI1IDIwTDUuNzUgMjBBMC4yNSAwLjI1IDAgMCAwIDYgMTkuNzVMNiAxNS4yNUEwLjI1IDAuMjUgMCAwIDAgNS43NSAxNUwxLjI1IDE1QTAuMjUgMC4yNSAwIDAgMCAxIDE1LjI1Wk0yIDE2LjI1TDIgMTguNzVBMC4yNSAwLjI1IDAgMCAwIDIuMjUgMTlMNC43NSAxOUEwLjI1IDAuMjUgMCAwIDAgNSAxOC43NUw1IDE2LjI1QTAuMjUgMC4yNSAwIDAgMCA0Ljc1IDE2TDIuMjUgMTZBMC4yNSAwLjI1IDAgMCAwIDIgMTYuMjVaIiBmaWxsPSIjMDAwMDAwIi8+PC9nPjwvZz48L3N2Zz4K" alt="SVG">                                                    
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
                                                            <tr>
                                                                <td>
                                                                    <p class="label">{{ __('mailing/client.from') }}</p>
                                                                    <p>{{ $valueItem['from']['name'] }}</p>
                                                                </td>
                                                                <td>
                                                                    <p class="label">{{ __('mailing/client.to') }}</p>
                                                                    <p>{{ $valueItem['to']['name'] }}</p>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>
                                                                    <p class="label">{{ __('mailing/client.pickup') }}</p>
                                                                    <p>{{ $valueItem['pickup'] }}</p>
                                                                </td>
                                                                <td>
                                                                    <p class="label">{{ __('mailing/client.passengers') }}</p>
                                                                    <p>{{ $valueItem['passengers'] }}</p>
                                                                </td>
                                                            </tr>
                                                            
                                                            <tr>
                                                                <td>                                                                    
                                                                    <p class="label">{{ __('mailing/client.service_type') }}</p>
                                                                    <p>{{ $valueItem['service_type_name'] }}</p>                                                                    
                                                                </td>
                                                                @if(!empty(trim($valueItem['flight_number'])))
                                                                <td>                                                                    
                                                                    <p class="label">{{ __('mailing/client.flight_number') }}</p>
                                                                    <p>{{ $valueItem['flight_number'] }}</p>                                                                    
                                                                </td>
                                                                @endif
                                                            </tr>
                                                           
                                                            
                                                                @php
                                                                    $itemCount++;
                                                                    if(sizeof($value) > 1 && $itemCount == 1):
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
                            <p style="margin: 15px 0px 5px 0px;"><strong>{{ __('mailing/client.indications') }}</strong></p>
                            @if($lang == "en")
                                <p style="margin-bottom: 8px;">In this email you will find a summary of your reservation information, it is important that you can validate that the information is correct, and in case of any change in the information of your flight, doubts or clarifications contact us so we can assist you in the best possible way.</p>
                                <p>If you are at the airport or at your hotel and do not see us, <span class="orange">call us at +52 (998) 294 2389</span> or send us a WhatsApp to the same number.</p>
                            @else
                                <p style="margin-bottom: 8px;">En este correo electrónico encontrarás un resumen de la información de tu reservación, es importante que puedas validar que la información es correcta, y en caso de algún cambio en la información de tu vuelo, dudas o aclaraciones contáctanos para poder atenderte de la mejor manera posible.</p>
                                <p>Si estás en el Aeropuerto o en tu Hotel y no nos ves, <span class="orange">llámanos al +52 (998) 294 2389</span> o envíanos un WhatsApp al mismo número.</p>
                            @endif                            
                        </div>
                    </td>                    
                </tr>
                <tr>
                    <td class="white_content" style="border-top: 1px solid #CCD5D8; text-align:center;">
                        @if($lang == "en")
                            <p style="width: 70%; margin: 0 auto;">More information on how to find us here. Cancellation terms and conditions</p>
                            <h3 style="margin-bottom: 0px; color: #191970;">Thank you for your reservation!</h3>
                        @else
                            <p style="width: 70%; margin: 0 auto;">Más información de cómo encontranos aquí. Términos y condiciones de cancelación</p>
                            <h4 style="margin-bottom: 0px; color: #191970;">¡Gracias por tu reservación!</h4>
                        @endif
                    </td>
                </tr>
                <tr>
                    <td>
                        <img src="https://ik.imagekit.io/zqiqdytbq/transportation-api/mailing/banner/banner.png" style="width:600px;">
                    </td>
                </tr>
                <tr>
                    <td class="white_content important_information">
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
                    <td class="white_content" style="text-align:center;">
                        @if($lang == "en")
                            <p>We wish you a pleasant and unforgettable stay.</p>
                            <h4>Policies</h4>
                            <h5>Cancellation Policy</h5> 
                            <p class="gray_color">Cancellations can only be made 24 hours prior to arrival or departure.</p>
                            <h5>Service Hours</h5> 
                            <p class="gray_color">In case of change of time of service can be made if you contact us 12 hours before the agreed time because you have to reschedule your service. Contact us 24 hours a day at +52 9982942389 or email sales@taxirivieramaya.com</p>
                        @else
                            <p>Les deseamos que pasen unos dias gratamente inolvidables.</p>
                            <h4>Políticas</h4>
                            <h5>Politicas Para Cancelacion</h5> 
                            <p class="gray_color">Unicamente se podra cancelar el servicio 24 hrs antes de su servicio sea llegada o salida.</p>
                            <h5>Horario de Servicio</h5> 
                            <p class="gray_color">En caso de cambio de hora de servicio se podra efectuar si se comunica 12 hrs antes de la hora acordada ya que se tiene que reprogramar su servicio. Contáctanos las 24 hrs al numero +52 9982942389 o al correo sales@taxirivieramaya.com</p>
                        @endif
                    </td>
                </tr>
                <tr>
                    <td style="padding: 15px; text-align:center;">
                        <div>
                            <a href="#"><img src="https://ik.imagekit.io/zqiqdytbq/transportation-api/mailing/social/facebook.png?updatedAt=1692978703979" style="margin-right: 15px;"></a>
                            <a href="#"><img src="https://ik.imagekit.io/zqiqdytbq/transportation-api/mailing/social/instagram.png?updatedAt=1692978703965"></a>
                        </div>
                        <p style="font-size: 11pt; color: #6A829E;">Caribbean Transfers | {{ __('mailing/client.rights_reserved') }}</p>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</body>
</html>