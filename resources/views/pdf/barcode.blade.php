<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<style>
    @page {
        margin: 10px; !important;


    }
</style>
<body>

    {!! '<img src="data:image/png;base64,' . \Milon\Barcode\Facades\DNS1DFacade::getBarcodePNG($code, 'C39',1   ,20) .'" style="width:400px" alt="barcode"/>' !!}
    <br>
    <p style="text-align: center" >{{$code}}</p>

</body>
</html>
