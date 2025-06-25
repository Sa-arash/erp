@php
$title="";
switch ($type){
    case 'warehouse_id';
      $title='Location';
    break;
    case 'brand_id';
      $title='Brand';
    break;
    case 'type';
      $title='Type';
    break;
    case 'po_number';
      $title='PO';
    break;
    case 'party_id';
      $title='Vendor';
    break;
    case 'check_out_to';
      $title='Employee';
    break;
    case 'check_out_person';
      $title='Personnel';
    break;
}

@endphp

@include('pdf.header',
  ['titles'=>[''],
  'css'=>true,'title'=>'Asset Details by '.$title
  ])


<style>

    .title {
        font-size: 18px;
        font-weight: bold;
        color: #2b3990;
    }


    .section-title {
        background-color: #ead09c;
        font-weight: bold;
        padding: 5px;
        color: #2b3990;
        border: 1px solid #aaa;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 10px;
        page-break-inside: auto;

    }


    td, th {
        font-size: 20px !important;

        border: 0;
        background-color: #ffffff !important;

        padding: 6px;
        vertical-align: top;
    }

    .no-border {
        border: none !important;
    }

    .notes {
        white-space: pre-line;
        font-style: italic;
    }

    .barcode {
        text-align: center;
    }

    .barcode img, .asset-image {
        width: 250px;
        margin-bottom: 10px;
    }

    .label {
        font-weight: bold;
        width: 140px;
        display: inline-block;
    }


</style>
@foreach( $assets  as $grupe)

    <div class="section-title">
        @switch($type)
            @case('warehouse_id')
            {{$grupe[0]->warehouse?->title??'None Location'}}
            @break
            @case('type')
            {{$grupe[0]->type??'None Type'}}
            @break
            @case('brand_id')
            {{$group[0]->brand?->title??'None Brand'}}
            @break
            @case('PO')
            {{$grupe[0]->po_number??'None PO'}}
            @break
            @case('check_out_person')
            {{$grupe[0]?->person?->name?$grupe[0]?->person?->name.' ('.$grupe[0]?->person?->number.')':'None Person'}}
            @break
            @case('check_out_to')
            {{$grupe[0]?->checkOutTo?->fullName??'None Employee'}}
            @break
            @case('party_id')
            {{$grupe[0]->party->name??'None Vendor'}}
            @break
        @endswitch
    </div>

    <table >


        @foreach($grupe as $asset)
            <tr>
                <td style="background-color: #b3bbea" colspan="2"><strong>Description:</strong> {{$asset->description}}
                </td>
            </tr>

            <tr>

                <td style="width: 200%;">
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
                            <td><strong>Note : </strong></td>
                            <td colspan="3" class="notes">{{$asset->note}}</td>
                        </tr>
                        <tr>
                            <td><strong>Check Out to :</strong></td>

                            <td colspan="3" class="notes">{{$asset->check_out_to ? $asset?->checkOutTo?->fullName : $asset?->person?->name . ' (' . $asset?->person?->number . ')'}}</td>
                        </tr>
                    </table>
                </td>
                <td style="width: 50%;">
                    <table>
                        <tr>
                            <td class="barcode">
                                {!! '<img src="data:image/png;base64,' . \Milon\Barcode\Facades\DNS1DFacade::getBarcodePNG($asset->number, 'C39',1   ,20) .'" style="width:400px" alt="barcode"/>' !!}
                                {{$asset->number}}<br><br>
                                @if(file_exists($asset->media->where('collection_name','images')->first()?->getPath()))
                                    <img class="asset-image"
                                         src="{{$asset->media->where('collection_name','images')->first()?->getPath()}}"
                                         alt="">
                                @endif
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        @endforeach
    </table>
@endforeach



