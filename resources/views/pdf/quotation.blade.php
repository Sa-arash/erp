@include('pdf.header',
   ['titles'=>['']])
    <!DOCTYPE html>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title></title>
    <style>
        body {
            font-family: Vazir, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
        }
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
            font-size: 20px;
        }
        .header p {
            margin: 0;
            font-size: 14px;
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
            font-size: 14px;
        }
        .footer {
            margin-top: 20px;
            font-size: 14px;
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
</head>
<body>
<div style="border: 0!important;">
    <table style="border: 0!important;">
        <tr style="border: 0!important;">
            <td style="border: 0!important;">Vendor Name:________________</td>
            <td style="border: 0!important;">Phone:________________ </td>
            <td style="border: 0!important;">Address:________________ </td>
            <td style="border: 0!important;">Currency:USD/FSD</td>
        </tr>


    </table>
</div>
<div class="container">
    <h2 style="text-align: center;">Request for Quotation</h2>
    @php
    $i=0;
    @endphp
    <table>
        <thead>
        <tr>
            <th>Nr</th>
            <th>Item Description</th>
            <th>Unit</th>
            <th>Qty</th>
            <th>Unit rate</th>
            <th>Total Cost</th>
        </tr>
        </thead>
        <tbody>

        <!-- Empty rows for filling -->
        @foreach($pr->items->whereIn('ceo_decision',['purchase','approve']) as $item)
            {{$item}}
            <tr>
                <td>{{++$i}}</td>
                <td>{{$item->description}}</td>
                <td>{{$item->unit->title}}</td>
                <td>{{$item->quantity}}</td>
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

