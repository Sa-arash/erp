@include('pdf.header',
  ['titles'=>[''],
  'css'=>true,'title'=>'Assets'
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

<div class="container">
    <h2 style="text-align: center;">Assets</h2>
    @php
        $i=0;
    @endphp
    <table>
        <thead>
        <tr>
            <th>Nr</th>
            <th>SKU</th>
            <th>Asset Name</th>
            <th>Purchase Price</th>
            <th>Warehouse/Building</th>
            <th>Location</th>
            <th>Employee</th>
            <th>Status</th>
        </tr>
        </thead>
        <tbody>

        <!-- Empty rows for filling -->
        @foreach($assets as $asset)

            <tr>
                <td>{{++$i}}</td>
                <td>{{$asset->product->sku}}</td>
                <td>{{$asset->titlen}}</td>
                <td>{{$asset->price}}</td>
                <td>{{$asset->warehouse?->title}}</td>
                <td>{{getParents($asset->structure)}}</td>
                <td>{{$asset->employees->last()?->assetEmployee?->employee?->fullName}}</td>
                <td>{{$asset->status}}</td>
            </tr>
        @endforeach
        </tbody>
    </table>


</div>
</body>
</html>

