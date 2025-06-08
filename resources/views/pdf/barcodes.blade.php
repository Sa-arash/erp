<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Assets Barcode</title>
    <style>
        @page {
            margin: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        td {
            width: 25%;
            text-align: center;
            padding: 10px;
        }
        img {
            max-width: 100%;
        }
    </style>
</head>
<body>

<table>
    @foreach(array_chunk(explode('-', $codes), 4) as $row)
        <tr>
            @foreach($row as $code)
                <td>
                    {!! '<img src="data:image/png;base64,' . \Milon\Barcode\Facades\DNS1DFacade::getBarcodePNG($code, 'C39', 1, 30) . '" alt="barcode"/>' !!}
                    <div style="margin-top: 5px;">{{ $code }}</div>
                </td>
            @endforeach

            @for($i = count($row); $i < 4; $i++)
                <td></td>
            @endfor
        </tr>
    @endforeach
</table>

</body>
</html>
