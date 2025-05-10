@include('pdf.header',['title'=>'Deyar Restaurant Sales Invoice','titles'=>['INVOICE - Deyar Restaurant Sales'],'css'=>true])
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
                <pre style="background: #81c7d3">Invoice Date:<span style="background: yellow;"> Saturday, January 18, 2025</span></pre>
                <pre style="background: #81c7d3">Invoice No:<span style="background: yellow;">   2025-00XX</span></pre>
            </td>
        </tr>
    </table>

    <table>
        <thead>
        <tr>
            <th style="background:#ffffaa !important; ">No</th>
            <th style="background:#ffffaa !important; ">Date</th>
            <th style="background:#ffffaa !important; ">Description</th>
            <th style="background:#ffffaa !important; ">Facility</th>
            <th style="background:#ffffaa !important; ">Quantity</th>
            <th style="background:#ffffaa !important; ">Unit</th>
            <th style="background:#ffffaa !important; ">Unit Rate</th>
            <th style="background:#ffffaa !important; ">Total</th>
        </tr>
        </thead>
        <tbody>
        @php
        $i=1;
        $total=0;
        @endphp
        @foreach($invoice->items as $item)
            <tr>
                <td style="border: none;border-bottom: 2px solid black">{{$i++}}</td>
                <td style="border: none;border-bottom: 2px solid black!important">{{\Illuminate\Support\Carbon::make($invoice->created_at)->format('d-F-Y')}}</td>
                <td style="border: none;border-bottom: 2px solid black!important">{{$item->title}}</td>
                <td style="border: none;border-bottom: 2px solid black!important">Deyar</td>
                <td style="border: none;border-bottom: 2px solid black!important">{{$item->quantity}}</td>
                <td style="border: none;border-bottom: 2px solid black!important">{{$item->unit->title}}</td>
                <td style="border: none;border-bottom: 2px solid black!important">{{number_format($item->unit_price,2)}}</td>
                <td style="border: none;border-bottom: 2px solid black!important">{{number_format($item->unit_price*$item->quantity,2)}} </td>
            </tr>
            @php
            $total+=$item->unit_price*$item->quantity;
            @endphp$item->unit_price*$item->quantity
        @endforeach
        </tbody>
        <tfooter>
            <tr>
                <td colspan="4" style="text-align: right;border: none;border-bottom: 2px solid black;">Grand Total Amount</td>
                <td colspan="3" style="text-align: right;border: none;border-bottom: 2px solid black;">{{numberToWords($total)}}</td>
                <td style="border: none;border-bottom: 2px solid black;" >{{number_format($total,2)}}</td>
            </tr>
        </tfooter>
    </table>



    <div class="payment-details">
        <div style="font-weight: bold; margin-bottom: 10px;">Payments to:</div>
        <div>Beneficiary Bank USD: EMINATES NBD PJSC</div>
        <div>Account Name: AREA TARGET GENERAL TRADING LLC</div>
        <div>Account Number: IBAN: AE380260001024332222002</div>
        <div>Swift Code: EBILAEAD</div>
        <div>DUBAI- UAE</div>
    </div>

    <div class="note">
        <div style="font-weight: bold; margin-bottom: 10px;">Note:</div>
        <div>We kindly request that you provide the wire transfer voucher or any other relevant evidence of payment to
            the ICON Finance office. This is necessary for banking compliance purposes and to ensure proper
            documentation and processing of your payment.
        </div>
    </div>

    <div class="signature">
        <div>Thank you for doing business with us!</div>
        <br><br>
        <div>Signed and stamped:</div>
        <div style="border-top: 1px solid #000; width: 200px; display: inline-block; margin-top: 30px;"></div>
    </div>
</div>
</body>
</html>
