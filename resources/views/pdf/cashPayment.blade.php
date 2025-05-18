<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Cash Payment</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 100%;
            padding: 20px;
        }

        .header {
            text-align: right;
            border: 1px solid black;
            padding: 5px;
            width: 150px;
            float: right;
        }

        .logo {
            float: left;
            margin-bottom: 20px;
        }

        .clearfix {
            clear: both;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        td,
        th {
            padding: 5px;
        }

        .bordered {
            border: 1px solid black;
        }
        .bordered tr{
            border: 1px solid black;
        }
        .bordered-none{
            border: none !important;
        }
        .bordered-none td{
            border: none !important;
        }
        .bordered-none tr{
            border: none !important;
        }

        .highlight- {
            background-color: yellow;
        }

        .bold {
            font-weight: bold;
        }

        .section {
            margin-top: 20px;
        }

        .amount {
            text-align: right;
        }

        .footer {
            margin-top: 40px;
        }

        .inline-block {
            display: inline-block;
        }

        .label {
            width: 150px;
            display: inline-block;
        }
    </style>
</head>

<body>

    <div class="container">
        {{-- <div class="logo">
        <img src="{{ public_path('images/logo.png') }}" alt="Logo" height="60">
    </div> --}}

        {{-- <div class="header">
        <strong>CASH PAYMENT</strong>
    </div> --}}
        <div class="clearfix"></div>


        <table class="bordered">
            <tr >
                <td>
                    @foreach ($invoice->transactions->where('creditor','>', 0) as $transaction)
                    {{-- @dd($transaction) --}}
                    <table class="bordered-none">
                        <tr>
                            <td><span class="bold">PETTY CASH CODE:</span> <span class="highlight">{{ $transaction->account->title }}</span></td>
                            <td><span class="bold">PAYMENT DATE:</span> <span class="highlight">{{\Carbon\Carbon::parse($invoice->date)->format('Y/m/d')}}</span></td>
                        </tr>
                        <tr>
                            <td><span class="bold">T.NO:</span> <span class="highlight">{{ str_pad($invoice->number, 9, '0', STR_PAD_LEFT) }}</span></td>
                            <td><span class="bold">HOUR:</span>{{\Carbon\Carbon::parse($invoice->date)->format('h:i:s A')}}</td>
                        </tr>
                    </table>
                    @endforeach
                </td>
            </tr>
            <tr style="border-bottom:none">
                <td style="border-bottom:none">
                    @foreach ($invoice->transactions->where('debtor','>', 0) as $transaction)
                    <table class="" >
                        <tr>
                            <td><span class="bold">Account Code:</span> <span class="highlight">{{ $transaction->account->title }}</span>
                            </td>
                        </tr>
                        <tr>
                            <td><span class="bold">Payment Description:</span> <span class="highlight">
                                {{ $transaction->description }}
                        </tr>
                        <tr>
                            <td class="amount"><span class="bold">AMOUNT</span>
                            
                               
                           {{ number_format($transaction->debtor) }}
                            
                            </td>
                        </tr>
                        
                    </table>
                    @endforeach
                </td>
            </tr>
            <tr >
                <td><span class="bold">DESCRIPTION:</span> {{$invoice->description}}</td>
            </tr>
            <tr style="border-top:none">
                <td style="border-top:none">
                    <table class="bordered-none">
                        <tr>
                            <td>
                                
                                <strong>Amount in words:</strong>  {{ numberToWords($invoice->transactions->sum('debtor') ,'') }}
                            </td>
                           
                        </tr>
                        <tr>
                            <td>
                                
                                &nbsp;
                            </td>
                           
                        </tr>
                        <tr>
                            <td>
                                <strong>Received By</strong>
                            </td>
                            
                        </tr>
                    </table>
                </td>
            </tr>
        </table>


        
    </div>

</body>

</html>
