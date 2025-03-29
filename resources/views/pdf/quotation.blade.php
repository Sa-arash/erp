@include('pdf.header',
  ['titles'=>[''],
  'css'=>true
  ])

<style>

        .container {
            width: 210mm;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ccc;
        }
        .header {
            text-align: center;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 13px;
        }
        .header p {
            margin: 0;
            font-size: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid #000;
        }
        th, td {
            padding: 8px;
            text-align: center;
        }
        th {
            background-color: #474646;
        }
        .grand-total {
            text-align: right;
            font-weight: bold;
        }
        .terms {
            margin-top: 20px;
            font-size: 12px;
        }
        .footer {
            margin-top: 20px;
            font-size: 12px;
            display: flex;
            justify-content: space-between;
        }
        .footer div {
            width: 45%;
        }
        .footer p {
            margin: 5px 0;
        }
        @media print {
            body {
                background: none;
            }
            .container {
                border: none;
                padding: 0;
            }
        }
    </style>
<body>
<div style="border: 0!important;">
    <table style="border: 0!important;">
        <tr style="border: 0!important;">
            <td style="border: 0!important;text-align: left!important;">Vendor Name:</td>
            <td style="border: 0!important;text-align: left!important;">Phone:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </td>
            <td style="border: 0!important;text-align: center!important;">Currency: USD [&nbsp; &nbsp;&nbsp; ] &nbsp;&nbsp; AFN [   &nbsp;&nbsp;&nbsp;]</td>
        </tr>
        <tr style="background: transparent!important;">
            <td style=" background: transparent!important;border: 0!important;text-align: left!important;">Address: </td>

        </tr>


    </table>
</div>
<div class="container">
    <h2 style="text-align: center;">RFQ</h2>
    @php
    $i=0;
    @endphp
    <table>
        <thead>
        <tr>
            <th>Nr</th>
            <th>Product</th>
            <th>Item Description</th>
            <th>Unit</th>
            <th>Qty</th>
            <th>Unit rate</th>
            <th>Taxes</th>
            <th>Freights</th>
            <th>Total Cost</th>
        </tr>
        </thead>
        <tbody>

        <!-- Empty rows for filling -->
        @foreach($pr->items->whereIn('ceo_decision',['purchase','approve']) as $item)
            {{$item}}
            <tr>
                <td>{{++$i}}</td>
                <td>{{$item->product->title}}</td>
                <td>{{$item->description}}</td>
                <td>{{$item->unit->title}}</td>
                <td>{{$item->quantity}}</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <p class="grand-total">Grand Total: ___________</p>

    <div class="terms">
        <p><strong>Terms and Conditions:</strong></p>
        <p>1. Afghanistan Tax Law will be applicable (2% tax on registered supplier and 7% on non-registered supplier).</p>
        <p>2. Validity of the Quotation should be clearly indicated.</p>
        <p>3. Quotation should be provided in Supplier letter head signed or stamped.</p>
        <p>4. The Quotation should be prepared in Afghani / USD.</p>
        <p>5. Payment method should be clearly indicated such as Cash, Bank transfer, Cheque.</p>
    </div>

    <div class="footer">
        <div>
            <p>For UNC use only:</p>
            <p>Date: ______/______/__________</p>
        </div>
        <div>
            <p>Prepared by: Logistic (name and signature)</p>
            <p>Approved by: Operation (name and signature)</p>
        </div>
    </div>
</div>
</body>
</html>

