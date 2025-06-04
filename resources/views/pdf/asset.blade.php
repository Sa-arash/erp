@include('pdf.header',
  ['titles'=>[''],
  'css'=>true,'title'=>'Assets'
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
             border: 1px solid #e0e0e0; /* Lighter border for details tables */
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
     </style>
 </head>
 <body>

     <div class="">
         <div class="header">
             <h1>Asset Details</h1>
             <p>Comprehensive Information for: **{{ $asset->product->title ?? 'N/A' }}**</p>
         </div>

         <div class="section-title">General Information</div>
         <table>
             <tbody>
                 <tr>
                     <th>SKU:</th>
                     <td>{{ $asset->product->sku ?? 'N/A' }}</td>
                 </tr>
                 <tr>
                     <th>Product Title:</th>
                     <td>{{ $asset->product->title ?? 'N/A' }}</td>
                 </tr>
                 <tr>
                     <th>Description:</th>
                     <td>{{ $asset->description ?? 'N/A' }}</td>
                 </tr>
                 <tr>
                     <th>Serial Number:</th>
                     <td>{{ $asset->serial_number ?? 'N/A' }}</td>
                 </tr>
                 <tr>
                     <th>PO Number:</th>
                     <td>{{ $asset->po_number ?? 'N/A' }}</td>
                 </tr>
                 <tr>
                     <th>Asset Type:</th>
                     <td>{{ $asset->type ?? 'N/A' }}</td>
                 </tr>
                 <tr>
                     <th>Current Status:</th>
                     <td>
                         @php
                             $statusText = '';
                             $statusClass = '';
                             switch ($asset->status) {
                                 case 'inuse': $statusText = "In Use"; $statusClass = 'badge-inuse'; break;
                                 case 'inStorageUsable': $statusText = "In Storage"; $statusClass = 'badge-instorageusable'; break;
                                 case 'loanedOut': $statusText = "Loaned Out";
                                 $statusClass = 'badge-loanedout'; break;
                                 case 'outForRepair': $statusText = "Out For Repair"; $statusClass = 'badge-outforrepair'; break;
                                 case 'StorageUnUsable': $statusText = "Scrap"; $statusClass = 'badge-storageunusable'; break;
                                 default: $statusText = "Unknown"; break;
                             }
                         @endphp
                         <span class="badge {{ $statusClass }}">{{ $statusText }}</span>
                     </td>
                 </tr>
                 {{-- Added Note Field Here --}}
                 <tr>
                     <th>Note:</th>
                     <td>{{ $asset->note ?? 'N/A' }}</td>
                 </tr>
             </tbody>
         </table>

         <div class="section-title">Financial Details</div>
         <table>
             <tbody>
                 <tr>
                     <th>Purchase Price:</th>
                     <td>{{ number_format($asset->price, 2) }}</td>
                 </tr>
                 <tr>
                     <th>Scrap Value:</th>
                     <td>{{ number_format($asset->scrap_value, 2) }}</td>
                 </tr>
                 <tr>
                     <th>Buy Date:</th>
                     <td>{{ \Carbon\Carbon::parse($asset->buy_date)->format('M j, Y') ?? 'N/A' }}</td>
                 </tr>
                 <tr>
                     <th>Guarantee Due Date:</th>
                     <td>{{ \Carbon\Carbon::parse($asset->guarantee_date)->format('M j, Y') ?? 'N/A' }}</td>
                 </tr>
                 <tr>
                     <th>Warranty End Date:</th>
                     <td>{{ \Carbon\Carbon::parse($asset->warranty_date)->format('M j, Y') ?? 'N/A' }}</td>
                 </tr>
                 <tr>
                     <th>Depreciation Years:</th>
                     <td>{{ $asset->depreciation_years ?? 'N/A' }}</td>
                 </tr>
                 <tr>
                     <th>Depreciation Amount:</th>
                     <td>{{ number_format($asset->depreciation_amount, 2) ?? 'N/A' }}</td>
                 </tr>
             </tbody>
         </table>

         <div class="section-title">Location & Assignment</div>
         <table>
             <tbody>
                 <tr>
                     <th>Warehouse:</th>
                     <td>{{ $asset->warehouse->title ?? 'N/A' }}</td>
                 </tr>
                 <tr>
                     <th>Location:</th>
                     <td>{{ $asset->structure->title ?? 'N/A' }}</td>
                 </tr>
                 <tr>
                     <th>Checked Out To:</th>
                     <td>{{ $asset->check_out_to->fullName ?? 'N/A' }}</td>
                 </tr>
                 <tr>
                     <th>Vendor:</th>
                     <td>{{ $asset->party->name ?? 'N/A' }}</td>
                 </tr>
                 <tr>
                     <th>Assigned Employee:</th>
                     <td>{{ $asset->employees->last()?->assetEmployee?->employee?->fullName ?? 'N/A' }}</td>
                 </tr>
             </tbody>
         </table>

         <div class="footer">
             <p>Generated on: {{ \Carbon\Carbon::now()->format('M j, Y H:i') }}</p>
             <p>&copy; {{$asset->company->title}}. All rights reserved.</p>
         </div>
     </div>

 </body>
 </html>
