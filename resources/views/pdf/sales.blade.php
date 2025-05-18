@include('pdf.header',['title'=>'Invoice-'.$invoice->from,'titles'=>[''],'css'=>true])
<style>
    body {
        font-family: Arial, sans-serif;
        margin: 0;
        padding: 0px;
        font-size: 12px;
        color: #000000;
        background-color: #ffffff;
    }

    .header {
        text-align: center;
        margin-bottom: 20px;
    }

    .header h1 {
        margin: 0;
        color: #000000;
        font-size: 15px;
        font-weight: normal;
    }

    .info-table {
        width: 100%;
        margin-bottom: 10px;
        border: none;
    }

    .info-table td {
        padding: 5px 0;
        vertical-align: top;
        border: none;
    }

    .info-table .title {
        font-weight: bold;
        width: 15%;
        color: #000000;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
    }

    th {
        background-color: #ffffff;
        color: #000000;
        padding: 8px;
        text-align: left;
        border: 1px solid #000000;
        font-weight: normal;
    }

    td {
        padding: 8px;
        border: 1px solid #000000;
        text-align: left;
    }

    .totals {
        margin-top: 20px;
        text-align: right;
    }

    .grand-total {
        font-size: 16px;
        margin-top: 10px;
        color: #000000;
    }

    .payment-details {
        margin-top: 30px;
        padding-top: 20px;
    }

    .note {
        margin-top: 20px;
        color: #000000;
    }

    .signature {
        margin-top: 50px;
        text-align: right;
    }

    .empty-row td {
        height: 20px;
        border: none;
    }
</style>
</head>
<body>
<div class="invoice-container">
    {{--    <div class="header">--}}
    {{--        <h1>INVOICE - Deyar Restaurant Sales</h1>--}}
    {{--    </div>--}}

    <table class="info-table">
        <tr>
            <td class="title">From:</td>
            <td colspan="3">
                {{$invoice->from}}
                <br>
                Email: finance@uncompound.com<br>
                P.: +971 56 152 7710
            </td>
        </tr>
        <tr>
            <td colspan="6" style="background: white!important;">
                <pre>_____________________________________________________________________________________________</pre>
            </td>
        </tr>
        <tr>

            <td style="background: white!important;" class="title">To:</td>
            <td style="background: white!important;" colspan="4">
                Name of Customer<br>
                {{$invoice->to}}
            </td>
            <td style="background: white!important;">
                <pre style="background: #c4e7e7">Invoice Date:<span style="background: #ffffff;" > {{ \Carbon\Carbon::parse($invoice->created_at)->format('l, F d, Y') }}</span></pre>
                <pre style="background: #c4e7e7">Invoice No:<span style="background: #ffffff;">   2025-{{$invoice->invoice?->number}}</span></pre>
            </td>
        </tr>
    </table>

    <table>
        <thead>
        <tr>
            <th style="background:#ababa7 !important; ">No</th>
            <th style="background:#ababa7 !important; ">Date</th>
            <th style="background:#ababa7 !important; ">Description</th>
{{--            <th style="background:#ffffaa !important; ">Facility</th>--}}
            <th style="background:#ababa7 !important; ">Quantity</th>
            <th style="background:#ababa7 !important; ">Unit</th>
            <th style="background:#ababa7 !important; ">Unit Rate</th>
            <th style="background:#ababa7 !important; ">Total</th>
        </tr>
        </thead>
        <tbody>
        @php
        $i=1;
        $total=0;
        $currency=$invoice->currency;
        @endphp
        @foreach($invoice->items as $item)
            <tr>
                <td style="border: none;border-bottom: 2px solid black">{{$i++}}</td>
                <td style="border: none;border-bottom: 2px solid black!important">{{\Illuminate\Support\Carbon::make($invoice->created_at)->format('d-F-Y')}}</td>
                <td style="border: none;border-bottom: 2px solid black!important">{{$item->title}}</td>
{{--                <td style="border: none;border-bottom: 2px solid black!important">Deyar</td>--}}
                <td style="border: none;border-bottom: 2px solid black!important"> {{$item->quantity}}</td>
                <td style="border: none;border-bottom: 2px solid black!important">{{$item->unit->title}} </td>
                <td style="border: none;border-bottom: 2px solid black!important"> {{$currency?->name}} {{number_format($item->unit_price,2)}}</td>
                <td style="border: none;border-bottom: 2px solid black!important">{{number_format($item->unit_price*$item->quantity,2)}} </td>
            </tr>
            @php
            $total+=$item->unit_price*$item->quantity;
            @endphp$item->unit_price*$item->quantity
        @endforeach
        </tbody>
        <tfooter>
            <tr>
                <td colspan="3" style="text-align: right;border: none;border-bottom: 2px solid black;">Grand Total Amount </td>
                <td colspan="3" style="text-align: right;border: none;border-bottom: 2px solid black;"> {{numberToWords($total,' '.$currency?->name)}}</td>
                <td style="border: none;border-bottom: 2px solid black;" >{{number_format($total,2)}}</td>
            </tr>
        </tfooter>
    </table>



    <table style="border: none">
        <tbody>
        <tr>
            <td style="width: 50%" >
                    @if($invoice->type)
                        @php
                            $str='';
                                    foreach ($invoice->invoice->transactions->where('debtor','!=','0') as $tra){
                                       $str.= "<p>Account Name:    {$tra->account->name}  </p>
                                       <p> Account Number: {$tra->account->code}  </p>
                                       ";
                                    }
                        @endphp
                    @else
                        @php
                            $str='';
                                foreach ($invoice->invoice->transactions->where('creditor','!=','0') as $tra){
                                   $str.= "<p>Account Name:    {$tra->account->name}  </p>
                                   <p> Account Number: {$tra->account->code}  </p>
                                   ";
                                }

                        @endphp
                    @endif
                    <div style="font-weight: bold; margin-bottom: 10px;">Payments to:
                        {!! $str !!}
                    </div>


            </td>
            <td  style="border: none;font-size: 13px">
                <b>Note:</b>
                <div>We kindly request that you provide the wire transfer voucher or any other relevant evidence of payment to
                    the ICON Finance office. This is necessary for banking compliance purposes and to ensure proper
                    documentation and processing of your payment.
                </div>
            </td>
        </tr>
        </tbody>
    </table>
{{--    <div class="payment-details" style=" width: 100%;padding: 5px;display: inline">--}}
{{--        <div class="note" >--}}
{{--            <div style="font-weight: bold;">Note:</div>--}}
{{--           --}}
{{--        </div>--}}
{{--    </div>--}}




    <div class="mt-3">
          <p  >Signed and stamped:</p> <div style="border-bottom: 1px solid #000 ; width: 150px;margin-left: 120px" ></div>
    </div>
    <br>
    <br>
    <br>
    <div>Thank you for doing business with us!</div>


</div>
</body>
</html>
