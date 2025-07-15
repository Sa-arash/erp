@include('pdf.header', ['title'=>'Audit Checklist','titles' => [''],'css'=>false])
<style>
    body {
        font-family: Arial, sans-serif;
        font-size: 12px;
        margin: 30px;
    }

    h2 {
        color: #2f3c9e;
        margin-bottom: 5px;
    }

    .location-title {
        background-color: #dbe6f4;
        padding: 8px;
        font-weight: bold;
        border: 1px solid #999;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 10px;
        background-color: white!important;
    }

    th, td {
        border: 1px solid #aaa;
        color: black;
        background-color: white;
        padding: 6px;
        vertical-align: middle;
    }

    th {
        background-color: #efefef;
        text-transform: uppercase!important;
    }
    table tr {
        background-color: #ffffff !important;
    }

    table tr:nth-child(even),
    table tr:nth-child(odd) {
        background-color: #ffffff !important;
    }


    .barcode {
        font-size: 18px;
        text-align: center;
    }


    .check-cell {
        text-align: center;
    }


    @page  {
        margin-left: 30px;
        margin-right: 30px;
    }
</style>


<p><small>(This report contains assets which have not been audited or were last audited over 365 days ago.)</small></p>

@foreach($groups as $group)
    <div class="location-title" style="background-color: #10184d;font-size: 12px;color: white;text-transform: uppercase">

        @switch($type)
            @case('warehouse_id')
            {{$group[0]->warehouse?->title??'None Location'}}
            @break
            @case('type')
            {{$group[0]->type??'None Type'}}
            @break
            @case('brand_id')
            {{$group[0]->brand?->title??'None Brand'}}
            @break
            @case('PO')
            {{$group[0]->po_number??'None PO'}}
            @break
            @case('check_out_person')
            {{$grupe[0]?->person?->name?$grupe[0]?->person?->name.' ('.$grupe[0]?->person?->number.')':'None Person'}}
            @break
            @case('check_out_to')
            {{$grupe[0]?->checkOutTo?->fullName??'None Employee'}}
            @break
            @case('party_id')
            {{$group[0]->party->name??'None Vendor'}}
            @break
        @endswitch</div>

    <table>
        <thead>
        <tr>
            <th>Barcode</th>
            <th>Product/Description / Brand / Model</th>
            <th>Serial # / Location</th>
            <th>Status</th>
            <th>Check / Comments</th>
        </tr>
        </thead>
        <tbody>
        @foreach($group as $asset)
            <tr>
                <td class="check-cell" style="text-align: center !important;">
                    <div class="barcode">
                        {!! '<img src="data:image/png;base64,' . \Milon\Barcode\Facades\DNS1DFacade::getBarcodePNG($asset->number, 'C39',1   ,20) .'" style="width:200px" alt="barcode"/>' !!}
                    </div>
                    <pre class="" style="text-align: center !important;margin-top: 5px">           {{$asset->number}}</pre>
                </td>
                <td>{{$asset->product->title}}<br>{{$asset->description??"N/A"}}<br>{{$asset->brand?->title??"N/A"}}<br>{{$asset->model??"N/A"}}</td>
                <td>{{$asset->serial_number ??"N/A"}}<br>{{$asset->warehouse?->title}}</td>
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
                <td class="check-cell"><input type="checkbox"> Checked</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <p>Number Of Assets {{count($group)}}</p>
@endforeach




<htmlpagefooter name="MyFooter">
    <div style="text-align: center; font-size: 10px;margin-top: 5px">
        Page {PAGENO} of {nbpg}  | &nbsp; Report Generated &nbsp; | &nbsp; {{   now()->format('d/F/Y')}}
    </div>
</htmlpagefooter>
