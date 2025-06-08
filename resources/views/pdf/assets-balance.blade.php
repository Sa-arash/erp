@include('pdf.header', ['title'=>'Assets   Report','titles' => [''],'css'=>true])


    <style>
        body {
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f9f9f9;
            color: #333;
            margin: 40px;
        }






        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        th  {
            text-align: center;
            padding: 12px;
            border-bottom: 1px solid #ddd;
        }
       tr td{
            text-align: start;
        }

        th {
            background-color: #2c3e50;
            color: #fff;
        }

        tfoot td {
            font-style: italic;
            text-align: right;
            border: none;
        }
    </style>



    <table>
        <thead>
        <tr>
            <th>NO</th>
            <th>All Location</th>
            <th>Purchase Price</th>
            <th>Market Value</th>
            <th>Asset Count</th>
        </tr>
        </thead>
        <tbody>
        @php
        $totalPO=0;
        $totalAmount=0;
        $totalCount=0;
        @endphp
        @foreach($groups as $key=> $warehouse)
            @php
                $totalPO+=$warehouse->assets()->sum('price');
                $totalAmount+=$warehouse->assets()->sum('depreciation_amount');
                $totalCount+=$warehouse->assets()->count();
            @endphp
            <tr>
                <td>{{$key}}</td>
                <td>{{$warehouse->title}}</td>
                <td>{{number_format($warehouse->assets()->sum('price'),2).' '.PDFdefaultCurrency($company)}} </td>
                <td>{{number_format($warehouse->assets()->sum('depreciation_amount'),2).' '.PDFdefaultCurrency($company)}}</td>
                <td>{{$warehouse->assets()->count()}}</td>
            </tr>

        @endforeach
        </tbody>
        <tfoot>
        <tr>
            <td style="text-align: start" colspan="2"><b>Totals:</b></td>
            <td>{{number_format($totalPO,2).' '.PDFdefaultCurrency($company)}}</td>
            <td>{{number_format($totalAmount,2).' '.PDFdefaultCurrency($company)}}</td>
            <td>{{number_format($totalCount)}}</td>

        </tr>
        <tr><td style="text-align: center" colspan="5">Generated on Thursday, June 5, 2025</td></tr>
        </tfoot>
    </table>
