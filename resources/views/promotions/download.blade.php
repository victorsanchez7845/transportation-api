<!doctype html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Invoice</title>
    <style>
        @page {
            margin: 0px;
        }
        body { 
            -webkit-text-size-adjust: none; 
            height: 100%; 
            line-height: 1.4; 
            margin: 0;
            padding: 0; 
            width: 100%; 
            font-size: 14px; 
            background: url('https://ik.imagekit.io/zqiqdytbq/coupons/background-coupons.jpg');
            background-size: 100% 100%;
            padding-top: 230px;
            padding-bottom: 80px;
            font-family: Arial, sans-serif;
        }

        
        .one {
            width: 100%;
            border-collapse: collapse;
        }
        
        .one tr td {
            width: 50%;
            padding: 10px;
            vertical-align: top;
            border: 1px dotted #CCD6EB;
        }
        div.container{
            margin-left: 25px;
            margin-right: 25px;
        }
        table.two tbody tr td{
            width: 100%
        }
    </style>
</head>
<body>
    <div class="container">
        <table class="one">
            @foreach ($data as $key => $value)
                @if( $key % 2 == 0 )
                        @if( $key > 0)
                            </tr>
                        @endif
                    <tr>
                @endif                    
                    <td>
                        <table class="two">
                            <tbody>
                                <tr>
                                    <td style="width: 80px; vertical-align: middle; border: 0px; padding: 0px; text-align: center;">
                                        <img src="{{ $value->logo }}" width="80px">
                                    </td>
                                    <td style="width: 220px; padding: 0px; border: 0px; padding-left: 15px;">
                                        <p style="font-size: 14pt; margin: 0px; color: #425466;">{{ $value->name }}</p>
                                        <p style="font-size: 10pt; margin: 0px; color: #425466;">{{ $value->hidden_instructions }}</p>
                                        <p style="font-size: 18pt; margin: 0px; color: #16161D; font-weight:bold;">
                                            @if(app()->getLocale() == "en")
                                                Up to {{ $value->promo }}
                                            @else
                                                Hasta {{ $value->promo }}
                                            @endif
                                        </p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
            @endforeach
                </tr>
        </table>
    </div>
</body>
</html>