<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Audit Checklist by Location</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 40px;
            background: #fff;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #999;
            margin-bottom: 20px;
        }

        .header .title {
            font-size: 20px;
            color: #2b3c90;
            font-weight: bold;
        }

        .subtitle {
            font-size: 11px;
            color: #444;
            margin-top: 4px;
        }

        .logo img {
            height: 50px;
        }

        h3.section {
            background-color: #d9e1f2;
            color: #000;
            padding: 5px 10px;
            margin-top: 20px;
            font-size: 14px;
            border: 1px solid #bbb;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        th, td {
            border: 1px solid #999;
            padding: 6px;
            text-align: left;
            font-size: 12px;
        }

        th {
            background-color: #eee;
            font-weight: bold;
        }

        .barcode {
            text-align: center;
            font-family: monospace;
        }

        .comment-box {
            width: 16px;
            height: 16px;
            border: 1px solid #000;
            display: inline-block;
        }

        .asset-count {
            font-style: italic;
            font-size: 11px;
            margin-top: 2px;
        }
    </style>
</head>
<body>

<div class="header">
    <div>
        <div class="title">Audit Checklist by Location</div>
        <div class="subtitle">(This report contains assets which have not been audited or were last audited over 365 days ago.)</div>
    </div>

</div>

<!-- Admin Building Section -->
<h3 class="section">Admin Building</h3>
<table>
    <tr>
        <th>Barcode</th>
        <th>Description / Brand / Model</th>
        <th>Serial # / Location</th>
        <th>Status / Audit</th>
        <th>Check / Comments</th>
    </tr>
    <tr>
        <td class="barcode">
{{--            <img src="barcode-sample.png" alt="Barcode" style="height: 40px;"><br>--}}
            AST0000250
        </td>
        <td>Access Point<br>UniFi AP-AC-Pro</td>
        <td>1e:8c:29:ec:13:7a<br>Admin Building</td>
        <td>In Use</td>
        <td>Checked: <span class="comment-box"></span></td>
    </tr>
</table>
<div class="asset-count">Number of assets: 1</div>

<!-- All Locations Section -->
@foreach()

@endforeach
<h3 class="section">All Locations</h3>
@foreach($assets as $warehouse => $items)
    <h3 style="background: #d9e1f2; padding: 5px;">{{ $warehouse->name ?? 'Unknown Location' }}</h3>

    <table>
        <thead>
        <tr>
            <th>Barcode</th>
            <th>Description / Model</th>
            <th>Serial / Location</th>
            <th>Status</th>
            <th>Checked</th>
        </tr>
        </thead>
        <tbody>
        @foreach($items as $asset)
            <tr>
                <td class="barcode">
                    <img src="data:image/png;base64,{{ DNS1D::getBarcodePNG($asset->code, 'C39') }}" height="40"><br>
                    {{ $asset->code }}
                </td>
                <td>{{ $asset->name }}<br>{{ $asset->brand }} {{ $asset->model }}</td>
                <td>{{ $asset->serial }}<br>{{ $asset->warehouse->name ?? 'N/A' }}</td>
                <td>{{ $asset->status }}</td>
                <td>Checked: <span style="border: 1px solid #000; width: 12px; height: 12px; display: inline-block;"></span></td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <p><em>Number of assets: {{ $items->count() }}</em></p>
@endforeach

</body>
</html>
