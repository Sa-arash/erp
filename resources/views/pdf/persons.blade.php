@include('pdf.header',
   ['titles'=>[''],'title'=>'Personnel Details','css'=>true])
    <!DOCTYPE html>

    <style>
    .group-title {
        background-color: #ece9d9;
        padding: 8px;
        font-size: 16px;
        margin-top: 20px;
        color: #003399 !important;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 25px;

        border: 1px solid #ccc !important;
    }
     tr ,td, th {
        border: 0;
        padding: 6px 8px;
        background: #cccccc !important;
        vertical-align: top;
    }
    .barcode {
        font-family: "Free 3 of 9", monospace;
        font-size: 28px;
    }
    .photo-box {
        border: 0;
        width: 120px;
        height: 140px;
        text-align: center;
    }
    .section-header {
        background-color: #ffffff;
        font-weight: bold;
    }

    p{
         padding: 4px 0 !important;
     }

    </style>


@foreach($groups as $groupName => $personnelList)
    <div class="group-title">{{ $groupName }} </div>

    @foreach($personnelList as $person)
        <table>
            <tr class="section-header">
                <td style="background: #a7bdec" colspan="2 ">Personnel Name: {{ $person->name }}</td>
            </tr>
            <tr>
                <td style="width: 75%;background: #ffffff" >
                    <div class="barcode">*{{ $person->number }}*</div>
                    <p><strong>Personnel Number:</strong> {{ $person->number }}</p>
                    <p><strong>Personnel Group:</strong> {{ $person->group }}</p>
                    <p><strong>Job Title:</strong> {{ $person->job_title }}</p>
                    <p><strong>Work Phone:</strong> {{ $person->work_phone }}</p>
                    <p><strong>Home Phone:</strong> {{ $person->home_phone }}</p>
                    <p><strong>Mobile Phone:</strong> {{ $person->mobile_phone }}</p>
                    <p><strong>Pager:</strong> {{ $person->pager }}</p>
                    <p><strong>Email:</strong> {{ $person->email }}</p>
                    <p><strong>Notes:</strong> {{ $person->note }}</p>
                </td>

                <td style="width: 25%;background: #ffffff">
                    <div class="photo-box">
                        @if(file_exists($person->media->where('collection_name','image')?->first()?->getPath()))
                            <img width="140"  src="{{$person->media->where('collection_name','image')->first()?->getPath()}}" alt="{{ $person->number }}">

                        @endif
                    </div>
                </td>
            </tr>
        </table>
    @endforeach
@endforeach
