@include('pdf.header',
  ['titles'=>[$accountTitle??'Account Report'],'css'=>false])

<br>
    <style>
        body {
            font-family: Arial, sans-serif;
            direction: ltr;
            text-align: left;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }

        th {
            background-color: #f2f2f2;
        }

        .summary {
            margin-bottom: 20px;
        }

        .summary h2 {
            margin-bottom: 10px;
        }

        .supplier-info {
            margin-bottom: 20px;
        }

        .committee {
            margin-bottom: 20px;
        }

        .committee h2 {
            margin-bottom: 10px;
        }
    </style>

<body>
@php
    $trs = "";
                                    $totalTrs = "
                                    <tr>
                                            <td style='border: 1px solid black;padding: 8px;text-align: center'> </td>
                                            <td style='border: 1px solid black;padding: 8px;text-align: center'> </td>
                                            <td style='border: 1px solid black;padding: 8px;text-align: center'> </td>
                                            <td style='border: 1px solid black;padding: 8px;text-align: center'> </td>
                                    ";
                                    $vendors = '';
                                    $ths = '';
                                    foreach ($PR->quotations as $quotation) {
                                        $vendor = $quotation->party->name;
                                        $vendors .= "<th style='border: 1px solid black;padding: 8px;text-align: center;background-color: #f2f2f2'>{$vendor}</th>";
                                        $ths .= "<th style='border: 1px solid black;padding: 8px;text-align: center;background-color: #f2f2f2'>Unit Cost | Total Cost</th>";
                                        $totalSum = 0;
                                        foreach ($quotation->quotationItems as $quotationItem) {
                                            $totalSum += $quotationItem->item->quantity * $quotationItem->unit_rate;
                                        }
                                        $totalSum = number_format($totalSum);
                                        $totalTrs .= "<td style='border: 1px solid black;padding: 8px;text-align: center'> {$totalSum}</td>";
                                    }
                                    $totalTrs .= "<td style='border: 1px solid black;padding: 8px;text-align: center'> </td></tr>";
                                    foreach ($PR->items->whereIn('ceo_decision', ['purchase', 'approve']) as $item) {
                                        $product = $item->product->title . " (" . $item->product->sku . ")";
                                        $description = $item->description;
                                        $quantity = $item->quantity;
                                        $tr = "<tr>
                                                     <td style='border: 1px solid black;padding: 8px;text-align: center'>$product</td>
                                                     <td style='border: 1px solid black;padding: 8px;text-align: center'>$description</td>
                                                     <td style='border: 1px solid black;padding: 8px;text-align: center'>{$item->unit->title}</td>
                                                     <td style='border: 1px solid black;padding: 8px;text-align: center'>$quantity</td>

                                                 ";
                                        foreach ($item->quotationItems as $quotationItem) {
                                            $total = number_format($quotationItem->item->quantity * $quotationItem->unit_rate);
                                            $rate = number_format($quotationItem->unit_rate);
                                            $tr .= "<td style='border: 1px solid black;padding: 8px;text-align: center'>{$rate} | {$total}</td>";
                                        }
                                        $tr .= "<td style='border: 1px solid black;padding: 8px;text-align: center'>AFS</td>";
                                        $tr .= "</tr>";
                                        $trs .= $tr;
                                    }

@endphp
<table style='border-collapse: collapse;width: 100%'>
    <thead>
    <tr>
        <th style='border: 1px solid black;padding: 8px;text-align: center;background-color: #f2f2f2'>Item</th>
        <th style='border: 1px solid black;padding: 8px;text-align: center;background-color: #f2f2f2'>Item Description
        </th>
        <th style='border: 1px solid black;padding: 8px;text-align: center;background-color: #f2f2f2'>Unit</th>
        <th style='border: 1px solid black;padding: 8px;text-align: center;background-color: #f2f2f2'>Qty</th>
        {{$vendors}}
        <th style='border: 1px solid black;padding: 8px;text-align: center;background-color: #f2f2f2'>Remarks</th>
    </tr>
    <tr>
        <th style='border: 1px solid black;padding: 8px;text-align: center;background-color: #f2f2f2'></th>
        <th style='border: 1px solid black;padding: 8px;text-align: center;background-color: #f2f2f2'></th>
        <th style='border: 1px solid black;padding: 8px;text-align: center;background-color: #f2f2f2'></th>
        <th style='border: 1px solid black;padding: 8px;text-align: center;background-color: #f2f2f2'></th>
        {{$ths}}
        <th style='border: 1px solid black;padding: 8px;text-align: center;background-color: #f2f2f2'></th>
    </tr>
    </thead>
    <tbody>
    {{$trs}}
    {{$totalTrs}}
    </tbody>
</table>
</div>
<table>
    <tr>
        <th colspan="3"><strong>Supplier Information</strong></th>
    </tr>
    <tr>
        <th>Supplier Name</th>
        <th>Address</th>
        <th>Phone</th>
    </tr>
    <tr>
        <td>{{$bid->party->name}} </td>
        <td>{{$bid->party->address}}</td>
        <td>{{$bid->party->phone}}</td>

    </tr>
</table>


{{--<div class="committee">--}}
{{--    <h2>Procurement Committee Members</h2>--}}
{{--    <p><strong>Name:</strong> Mirza Mohammad</p>--}}
{{--    <p><strong>Position:</strong> Procurement Controller</p>--}}
{{--    <p><strong>Name:</strong> afiullah Omar</p>--}}
{{--    <p><strong>Position:</strong> Admin Officer</p>--}}
{{--    <p><strong>Name:</strong> Najebullal Azizi</p>--}}
{{--    <p><strong>Position:</strong> Admin Assistant</p>--}}
{{--</div>--}}

</body>
</html>
