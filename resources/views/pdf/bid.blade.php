{{--<!DOCTYPE html>--}}
{{--<html lang="en">--}}
{{--<head>--}}
{{--    <meta charset="UTF-8">--}}
{{--    <meta name="viewport" content="width=device-width, initial-scale=1.0">--}}
{{--    <title>Services Team - UN Compound</title>--}}
{{--    <style>--}}
{{--        body {--}}
{{--            font-family: Arial, sans-serif;--}}
{{--            direction: ltr;--}}
{{--            text-align: left;--}}
{{--        }--}}
{{--        table {--}}
{{--            width: 100%;--}}
{{--            border-collapse: collapse;--}}
{{--            margin-bottom: 20px;--}}
{{--        }--}}
{{--        th, td {--}}
{{--            border: 1px solid #ddd;--}}
{{--            padding: 8px;--}}
{{--            text-align: center;--}}
{{--        }--}}
{{--        th {--}}
{{--            background-color: #f2f2f2;--}}
{{--        }--}}
{{--        .summary {--}}
{{--            margin-bottom: 20px;--}}
{{--        }--}}
{{--        .summary h2 {--}}
{{--            margin-bottom: 10px;--}}
{{--        }--}}
{{--        .supplier-info {--}}
{{--            margin-bottom: 20px;--}}
{{--        }--}}
{{--        .committee {--}}
{{--            margin-bottom: 20px;--}}
{{--        }--}}
{{--        .committee h2 {--}}
{{--            margin-bottom: 10px;--}}
{{--        }--}}
{{--    </style>--}}
{{--</head>--}}
{{--<body>--}}
{{--@php--}}
{{--    $trs = "";--}}
{{--                                    $totalTrs = "--}}
{{--                                    <tr>--}}
{{--                                            <td style='border: 1px solid black;padding: 8px;text-align: center'> </td>--}}
{{--                                            <td style='border: 1px solid black;padding: 8px;text-align: center'> </td>--}}
{{--                                            <td style='border: 1px solid black;padding: 8px;text-align: center'> </td>--}}
{{--                                            <td style='border: 1px solid black;padding: 8px;text-align: center'> </td>--}}
{{--                                    ";--}}
{{--                                    $vendors = '';--}}
{{--                                    $ths = '';--}}
{{--                                    foreach ($record->quotations as $quotation) {--}}
{{--                                        $vendor = $quotation->party->name;--}}
{{--                                        $vendors .= "<th style='border: 1px solid black;padding: 8px;text-align: center;background-color: #f2f2f2'>{$vendor}</th>";--}}
{{--                                        $ths .= "<th style='border: 1px solid black;padding: 8px;text-align: center;background-color: #f2f2f2'>Unit Cost | Total Cost</th>";--}}
{{--                                        $totalSum = 0;--}}
{{--                                        foreach ($quotation->quotationItems as $quotationItem) {--}}
{{--                                            $totalSum += $quotationItem->item->quantity * $quotationItem->unit_rate;--}}
{{--                                        }--}}
{{--                                        $totalSum = number_format($totalSum);--}}
{{--                                        $totalTrs .= "<td style='border: 1px solid black;padding: 8px;text-align: center'> {$totalSum}</td>";--}}
{{--                                    }--}}
{{--                                    $totalTrs .= "<td style='border: 1px solid black;padding: 8px;text-align: center'> </td></tr>";--}}
{{--                                    foreach ($record->items->whereIn('ceo_decision', ['purchase', 'approve']) as $item) {--}}
{{--                                        $product = $item->product->title . " (" . $item->product->sku . ")";--}}
{{--                                        $description = $item->description;--}}
{{--                                        $quantity = $item->quantity;--}}
{{--                                        $tr = "<tr>--}}
{{--                                                     <td style='border: 1px solid black;padding: 8px;text-align: center'>$product</td>--}}
{{--                                                     <td style='border: 1px solid black;padding: 8px;text-align: center'>$description</td>--}}
{{--                                                     <td style='border: 1px solid black;padding: 8px;text-align: center'>{$item->unit->title}</td>--}}
{{--                                                     <td style='border: 1px solid black;padding: 8px;text-align: center'>$quantity</td>--}}

{{--                                                 ";--}}
{{--                                        foreach ($item->quotationItems as $quotationItem) {--}}
{{--                                            $total = number_format($quotationItem->item->quantity * $quotationItem->unit_rate);--}}
{{--                                            $rate = number_format($quotationItem->unit_rate);--}}
{{--                                            $tr .= "<td style='border: 1px solid black;padding: 8px;text-align: center'>{$rate} | {$total}</td>";--}}
{{--                                        }--}}
{{--                                        $tr .= "<td style='border: 1px solid black;padding: 8px;text-align: center'>AFS</td>";--}}
{{--                                        $tr .= "</tr>";--}}
{{--                                        $trs .= $tr;--}}
{{--                                    }--}}

{{--@endphp--}}
{{--<div class="summary">--}}
{{--    <h2>Cost Summary</h2>--}}
{{--    <table>--}}
{{--        <thead>--}}
{{--        <tr>--}}
{{--            <th>Unit</th>--}}
{{--            <th>Item Description</th>--}}
{{--            <th>Supplier - 1</th>--}}
{{--            <th>Supplier - 2</th>--}}
{{--            <th>Supplier - 3</th>--}}
{{--            <th>Remarks</th>--}}
{{--        </tr>--}}
{{--        </thead>--}}
{{--        <tbody>--}}
{{--        <tr>--}}
{{--            <td>1</td>--}}
{{--            <td>16 Valve Engine</td>--}}
{{--            <td>1300</td>--}}
{{--            <td>14500</td>--}}
{{--            <td>14700</td>--}}
{{--            <td>As</td>--}}
{{--        </tr>--}}
{{--        <tr>--}}
{{--            <td colspan="2">Total Cost</td>--}}
{{--            <td>000</td>--}}
{{--            <td>TA500</td>--}}
{{--            <td>74700</td>--}}
{{--            <td></td>--}}
{{--        </tr>--}}
{{--        </tbody>--}}
{{--    </table>--}}
{{--</div>--}}

{{--<div class="supplier-info">--}}
{{--    <h2>Supplier Information</h2>--}}
{{--    <p><strong>Supplier Name:</strong> Sid Casem Parts Soler</p>--}}
{{--    <p><strong>Address:</strong> kot-e Sang Asia Market</p>--}}
{{--    <p><strong>Contact:</strong> o7aai22883</p>--}}
{{--</div>--}}

{{--<div class="committee">--}}
{{--    <h2>Procurement Committee Members</h2>--}}
{{--    <p><strong>Name:</strong> Mirza Mohammad</p>--}}
{{--    <p><strong>Position:</strong> Procurement Controller</p>--}}
{{--    <p><strong>Name:</strong> afiullah Omar</p>--}}
{{--    <p><strong>Position:</strong> Admin Officer</p>--}}
{{--    <p><strong>Name:</strong> Najebullal Azizi</p>--}}
{{--    <p><strong>Position:</strong> Admin Assistant</p>--}}
{{--</div>--}}

{{--</body>--}}
{{--</html>--}}
