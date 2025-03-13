<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="{{ public_path('img/my.png') }}">
    <link rel="icon" type="image/x-icon" href="{!! public_path('images/' . $company?->logo) !!}">

    <title>Report</title>

    @if($css??true)
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            direction: ltr;
            background-color: #ffffff;
            font-size: 12px
        }

        h1 {
            text-align: center;
            margin-bottom: 30px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        th,
        td {
            padding: 12px;
            text-align: left;
        }

        th {
            background-color: #4CAF50;
            color: white;
            border-bottom: 2px solid #ddd;
        }

        tr:nth-child(even) td {
            background-color: #f9f9f9;
        }

        tr:nth-child(odd) td {
            background-color: #ffffff;
        }

        tr:hover td {
            background-color: #f1f1f1;
            cursor: pointer;
        }

        .totals {
            display: flex;
            justify-content: flex-end;
            margin-top: 20px;
        }

        .totals div {
            margin-left: 20px;
            font-weight: bold;
        }



        @media print {
            body {
                background-color: white;
                font-size: 12px;
            }

            table {
                page-break-inside: avoid;
            }

            th,
            td {
                border: 1px solid #000;
            }

            th {
                background-color: #333 !important;
                color: #fff !important;
            }

            tr:nth-child(even) td {
                background-color: #e8e8e8 !important;
            }

            tr:nth-child(odd) td {
                background-color: #ffffff !important;
            }


        }


        h1 {
            text-align: center;
            margin-bottom: 30px;
            font-size: 15px;
            /* کوچک‌تر کردن اندازه فونت تیتر اصلی */
        }

        h2 {

            /* کوچک‌تر کردن اندازه فونت تیترهای شرکت و عناوین */
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            font-size: 12px;
            /* کوچک‌تر کردن فونت جدول */
        }

        th,
        td {
            padding: 10px;
            /* کاهش اندازه padding برای تراکم بیشتر محتوا */
            text-align: left;
            font-size: 12px;
            /* کوچک‌تر کردن فونت سلول‌ها */
        }

        th {
            background-color: #4CAF50;
            color: white;
            border-bottom: 2px solid #ddd;
            font-size: 13px;
            /* کوچک‌تر کردن فونت تیترهای جدول */
        }

        .totals div {
            margin-left: 20px;
            font-weight: bold;
            font-size: 12px;
            /* کوچک‌تر کردن فونت قسمت مجموع‌ها */
        }



        @media print {
            body {
                background-color: white;
                font-size: 12pt;
            }

            table {
                page-break-inside: avoid;
            }

            th,
            td {
                border: 1px solid #000;
                font-size: 10pt;
            }

            th {
                background-color: #333 !important;
                color: #fff !important;
                font-size: 10pt;
            }

            tr:nth-child(even) td {
                background-color: #e8e8e8 !important;
            }

            tr:nth-child(odd) td {
                background-color: #ffffff !important;
            }
        }
    </style>
    @endif
</head>

<body>
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; padding: 0;">



        <table >
            <tr >
                <td style="border: none;width: 33%; text-align: left; padding-left: 10px;">
                    @if ($customImage??false)

                        <img src="{{$customImage}}" style="width: 70px;">
                    @endif
                </td>
                <td style="border: none;text-align: center; vertical-align: middle;">
                    <h2 style="margin: 0; padding: 0; display: inline-block; font-size: 20px">
                        {{ $company->title }}

                        @foreach ($titles as $title)
                            <h2 style="margin: 0; padding: 0; display: inline-block; font-size: 15px">{{ $title }}
                            </h2>
                        @endforeach
                    </h2>
                </td>
                <td style="border: none;width: 33%; text-align: right; padding-right: 10px;">
                    @if($company?->logo)
                        <img src="{!! public_path('images/' . $company?->logo) !!}" style="border-radius: 50px ; width: 70px;">
                    @endif
                </td>
            </tr>
        </table>
    </div>
