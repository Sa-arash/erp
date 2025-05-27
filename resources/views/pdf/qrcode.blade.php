<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>QRCODE Checkout</title>
</head>
<body>
{!! '<img src="data:image/png;base64,' . \Milon\Barcode\Facades\DNS2DFacade::getBarcodePNG(env('APP_URL').'/admin/'.$companyID.'/asset-employees/create?asset='.$code, 'QRCODE',10,10) .'" alt="barcode"/>' !!}

</body>
</html>
