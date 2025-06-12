@include('pdf.header',
  ['titles'=>[''],
  'css'=>false,'title'=>'Assets'
  ])
     <style>
         body {
             font-family: 'Arial', sans-serif;
             margin: 0;
             padding: 0;
             box-sizing: border-box;
             color: #333;
             font-size: 10px; /* Base font size */
         }
         .container {
             width: 210mm; /* A4 width */
             margin: 10mm auto; /* Reduced top/bottom margin */
             padding: 15px 25px; /* Reduced padding */
             border: 1px solid #eee;
             box-shadow: 0 0 8px rgba(0, 0, 0, 0.03);
             background-color: #fff;
         }
         .header {
             text-align: center;
             margin-bottom: 20px;
             padding-bottom: 10px;
             border-bottom: 1px solid #0056b3;
         }
         .header h1 {
             margin: 0;
             font-size: 20px;
             color: #0056b3;
             text-transform: uppercase;
         }
         .header p {
             margin: 3px 0 0;
             font-size: 12px;
             color: #555;
         }
         .section-title {
             font-size: 15px;
             color: #0056b3;
             margin-top: 20px;
             margin-bottom: 10px;
             border-bottom: 1px solid #eee;
             padding-bottom: 3px;
         }
         table {
             width: 100%;
             border-collapse: collapse;
             margin-bottom: 15px; /* Reduced margin */
         }
         table, th, td {
             border: 0; /* Lighter border for details tables */
         }
         th, td {
             padding: 5px 10px; /* Reduced padding */
             vertical-align: top;
             font-size: 10px; /* Reduced font size */
             text-align: left; /* Align text to left in cells */
         }
         th {
             background-color: #f9f9f9; /* Light background for labels */
             width: 30%; /* Allocate width for label column */
             font-weight: bold;
             color: #444;
         }
         /* Specific styling for the detail value cells (td) */
         td {
             color: #666;
         }

         .badge {
             display: inline-block;
             padding: 2px 7px;
             border-radius: 12px;
             font-size: 9px;
             font-weight: bold;
             color: #fff;
             background-color: #007bff;
             margin-right: 3px;
             margin-bottom: 3px;
         }

         /* Status Badges */
         .badge-inuse { background-color: #28a745; }
         .badge-instorageusable { background-color: #17a2b8; }
         .badge-loanedout { background-color: #ffc107; color: #333; }
         .badge-outforrepair { background-color: #dc3545; }
         .badge-storageunusable { background-color: #6c757d; }

         .footer {
             text-align: center;
             margin-top: 25px;
             padding-top: 10px;
             border-top: 1px solid #eee;
             font-size: 10px;
             color: #777;
         }
         .footer p {
             margin: 3px 0;
         }
         @page  {
             margin: 10px;
         }

     </style>
 </head>
 <body>

<table>
    <tr>
        <td style="background-color: #b3bbea" colspan="2"><strong>Description:</strong> {{$asset->description}}
        </td>
    </tr>

    <tr>

        <td style="width: 80%;">
            <table style="width: 100%;">

                <tr>
                    <td><strong>Asset Number:</strong></td>
                    <td>{{$asset->number}}</td>
                    <td><strong>Serial Number:</strong></td>
                    <td>{{$asset->serial_number}}</td>
                </tr>
                <tr>
                    <td><strong>Asset Type:</strong></td>
                    <td>{{$asset->type}}</td>
                    <td><strong>Status:</strong></td>
                    <td>@switch($asset->status)
                            @case('inuse')
                            In Use
                            @break

                            @case('inStorageUsable')
                            In Storage
                            @break

                            @case('loanedOut')
                            Loaned Out
                            @break

                            @case('outForRepair')
                            Out For Repair
                            @break

                            @case('StorageUnUsable')
                            Scrap
                            @break

                            @default
                            Unknown
                        @endswitch
                    </td>
                </tr>
                <tr>
                    <td><strong>Location:</strong></td>
                    <td>{{$asset->warehouse?->title.' '.getParents($asset->structure)}}</td>
                    <td><strong>Condition:</strong></td>
                    <td>{{$asset->quality}}</td>
                </tr>
                <tr>
                    <td><strong>Manufacturer:</strong></td>
                    <td>{{$asset->manufacturer}}</td>
                    <td><strong>Due Date:</strong></td>
                    <td>{{$asset->employees?->last()?->due_date}}</td>
                </tr>
                <tr>
                    <td><strong>Brand:</strong></td>
                    <td>{{$asset->brand->title}}</td>
                    <td><strong>Warranty Expires:</strong></td>
                    <td>{{$asset->warranty_date}}</td>
                </tr>
                <tr>
                    <td><strong>Model:</strong></td>
                    <td>{{$asset->model}}</td>
                    <td><strong>In Service:</strong></td>
                    <td></td>
                </tr>
                <tr>
                    <td><strong>PO Number:</strong></td>
                    <td>{{$asset->po_number}}</td>
                    <td><strong>Purchase Price:</strong></td>
                    <td>{{$asset->price}}</td>
                </tr>
                <tr>
                    <td><strong>Vendor:</strong></td>
                    <td>{{$asset->party?->name}}</td>
                    <td><strong>Market Value:</strong></td>
                    <td>{{number_format($asset->depreciation_amount)}}</td>
                </tr>
                <tr>
                    <td><strong>Purchased:</strong></td>
                    <td>{{$asset->buy_date}}</td>
                    <td><strong>Recovery Period:</strong></td>
                    <td>{{$asset->depreciation_years}}</td>
                </tr>
                <tr>
                    <td><strong>Note</strong></td>

                    <td colspan="3" class="notes">{{$asset->note}}</td>
                </tr>
            </table>
        </td>
        <td style="width: 10%;">
            <table>
                <tr>
                    <td class="barcode" style="text-align: center">
                        {!! '<img src="data:image/png;base64,' . \Milon\Barcode\Facades\DNS1DFacade::getBarcodePNG($asset->number, 'C39',1   ,20) .'" style="width:100px" alt="barcode"/>' !!}
                        <br>

                        {{$asset->number}}<br><br>
                        @if($asset->media->where('collection_name','images')->first())
                            <img class="asset-image" width="100"
                                 src="{{$asset->media->where('collection_name','images')->first()?->getPath()}}"
                                 alt="">
                        @endif
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>


 </body>
 </html>
