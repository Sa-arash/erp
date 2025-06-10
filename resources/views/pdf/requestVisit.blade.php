
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <title>Visitor Access Request Form</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                font-size: 14px;

            }

            table {
                width: 100%;
                border-collapse: collapse;
                text-align: center;
            }

            td {
                border: 1px solid #000;
                padding: 4px;
                vertical-align: top;
                text-align: center;
            }

            th {
                font-weight: bold;
                border: 1px solid #000;
                padding: 4px;
                vertical-align: top;
                text-align: center;
            }

            .header {
                text-align: center;
                font-weight: bold;
            }

            .sub-header {
                text-align: center;
                font-size: 12px;
            }

            .section-title {
                background-color: #dbe5f1;
                font-weight: bold;
                text-align: center;
            }

            .section-title td {
                text-align: center;
                font-weight: bold;
            }

            .text-center {
                text-align: center;
                font-size: 12px;
            }

            .no-border {
                border: none;
            }

            .small-text {
                font-size: 12px;
            }

            .approved-box,
            .not-approved-box {
                width: 12px;
                height: 12px;
                border: 1px solid #000;
                display: inline-block;
                margin-right: 5px;
            }

            .light-blue {
                background-color: #dbe5f1;
            }

            .gray-bg {
                background-color: #f2f2f2;
            }

            .center {
                text-align: center;
            }

            .approved-box,
            .not-approved-box {
                width: 12px;
                height: 12px;
                border: 1px solid #000;
                display: inline-block;
                margin-right: 5px;
            }

            .bg-lightgreen {
                background-color: rgb(197, 224, 179, 255) !important;
            }
            @page  {
                margin-top: 10px;
                margin-right: 50px;
                margin-left: 50px;
                padding: 0;
            }




            .equal-table {
                width: 100%;
                text-align: center;
                /* عرض جدول را به 100% تنظیم کنید */
                border-collapse: collapse;
                /* برای حذف فاصله بین سلول‌ها */
            }

            .equal-cell {
                width: 25%;
                text-align: center;
                /* عرض هر ستون را به 25% تنظیم کنید */
                border: 1px solid #000;
                /* برای نمایش مرز سلول‌ها */
            }
        </style>
    </head>

    <body>

        <table>
            <tr>
                <td colspan="2">
                    @if ($company?->logo_security)
                        <img src="{!! public_path('images/' . $company?->logo_security) !!}" style="padding: 0; border-radius: 50px ; width: 200px;">
                    @endif
                </td>
                <td colspan="4" style="padding: 0px 0px;margin :0px 0px;font-size: 12px!important;">
                        <table>
                            <tr>
                                <td  class="text-center"><b>{{$company->title_security}}</b></td>
                            </tr>
                            <tr>
                                <td class="text-center"><b>{{$company->description_security}}</b></td>
                            </tr>
                            <tr>
                                <td class="text-center"><b>Page 1 of 1</b></td>
                            </tr>
                            <tr>
                                <td class="text-center"><b>{{$company->SOP_number}}</b></td>
                            </tr>
                            <tr>
                                <td class="text-center " style="color:#1c6fb9 "><b>{{$company->supersedes_security}}</b></td>
                            </tr>

                            <tr>
                                <td class="text-center"><b>Effective Date: <span style="color:#1c6fb9 ">{{ $company->effective_date_security }}</span></b></td>
                            </tr>
                        </table>
                </td>
            </tr>
        </table>

        <table>
            <tr>
                <td colspan="6" class="header">ICON COMPOUND - VISITOR ACCESS REQUEST FORM</td>
            </tr>
            <tr>
                <td colspan="6" class="no-border" style="text-align: right;"> &nbsp;</td>
            </tr>
        </table>
        <div style="text-align: right;color: #6b7280;font-size: 10px">S/N: {{$requestVisit->SN_code}}</div>

        <table>
            <tr class="section-title">
                <th colspan="6">Requestor’s Details</th>
            </tr>

            <tr>
                <th>Requestor’s Name</th>
                <th>Title</th>
                <th>UN Agency</th>
                <th>ICON</th>
                <th>Cell Phone</th>
                <th>Email</th>
            </tr>

            <tr>
                <td> {{ $requestVisit->employee->fullName }}</td>

                <td> {{$requestVisit->ICON ? $requestVisit->employee->position?->title:'' }}</td>
                <td> {{ $requestVisit->agency }}</td>
                <td> {{ $requestVisit->ICON ? '✔': '' }}</td>
                <td> {{ $requestVisit->employee->phone_number }}</td>
                <td>{{ $requestVisit->employee->email }}</td>
            </tr>
            <tr>
                <td colspan="6" class="no-border" style="text-align: right;"> &nbsp;</td>
            </tr>
        </table>

        <table>
            <tr class="section-title">
                <td colspan="3">Specific Visit Details</td>
            </tr>
            <tr>
                <td style="width: 33%;font-weight: bold">Date of Visit</td>
                <td style="width: 33%;font-weight: bold">Time of Arrival</td>
                <td style="width: 33%;font-weight: bold">Time of Departure</td>
            </tr>
            @php
                use Illuminate\Support\Carbon;

                $dates = collect($requestVisit->visiting_dates)
                    ->map(fn($date) => Carbon::createFromFormat('d/m/Y', $date) );

                $grouped = $dates->groupBy(fn($date) => $date->format('Y,F')); // group by year and month
            @endphp

            @foreach ($grouped as $monthKey => $dates)
                @php
                    $sortedDates = $dates->sortBy(fn($date) => $date->timestamp)->values();

                    // چک کن آیا تاریخ‌ها پشت سر هم هستند
                    $isConsecutive = true;
                    for ($i = 1; $i < $sortedDates->count(); $i++) {
                        if (!$sortedDates[$i]->isSameDay($sortedDates[$i - 1]->copy()->addDay())) {
                            $isConsecutive = false;
                            break;
                        }
                    }

                    $monthName = ucfirst(strtolower($sortedDates->first()->format('M'))); // Jun
                    $year = $sortedDates->first()->year;

                    if ($isConsecutive && $sortedDates->count() > 1) {
                        $startDay = $sortedDates->first()->day;
                        $endDay = $sortedDates->last()->day;
                        $dayString = "{$startDay}/{$monthName}/{$year} - {$endDay}/{$monthName}/{$year}";
                    } else {
                        $days = $sortedDates->pluck('day')->sort()->values();
                        $last = $days->pop();
                        $secondLast = $days->pop();

                        $dayString = '';

                        if ($secondLast) {
                            if ($days->count()) {
                                $dayString .= $days->implode(',') . ',';
                            }
                            $dayString .= $secondLast . ' & ' . $last;
                        } elseif ($last) {
                            $dayString = $last;
                        }

                        $dayString .= "/{$monthName}/{$year}";
                    }
                @endphp

                <tr>
                    <td style="width: 33%">
                        {{ $dayString }}
                    </td>

                    <td style="width: 33%">
                        {{ \Carbon\Carbon::parse($requestVisit->arrival_time)->format('h:i A') }}
                    </td>

                    <td style="width: 33%">
                        {{ \Carbon\Carbon::parse($requestVisit->departure_time)->format('h:i A') }}
                    </td>
                </tr>
            @endforeach



            <tr>
                <td style="font-weight: bold">Purpose of visit (Be specific)</td>
                <td colspan="2">{{ $requestVisit->purpose }}</td>
            </tr>
            <tr>
                <td colspan="3" class="no-border" style="text-align: right;"> &nbsp;</td>
            </tr>
        </table>

        <table>
            <tr class="section-title">
                <td colspan="6">Visitor(s) Details</td>
            </tr>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>ID/Passport</th>
                <th>Cell Phone</th>
                <th>Organization</th>
                <th>Remarks</th>
            </tr>
            @php
            $i=1;
$tdsArend='';
            @endphp
            @foreach ($requestVisit->visitors_detail as $visitor)
                <tr>
                    <td>{{ $i++ }}</td>
                    <td style="text-align: left">{{ $visitor['name'] ?? '---' }}</td>
                    <td>{{ $visitor['id' ?? '---'] }}</td>
                    <td>{{ $visitor['phone'] ?? '---' }}</td>
                    <td>{{ $visitor['organization'] ?? '---' }}</td>
                    <td>{{ $visitor['remarks'] ?? '---' }}</td>
                </tr>
            @endforeach
            <tr>
                <td colspan="7" class="no-border" style="text-align: right;"> &nbsp;</td>
            </tr>
        </table>

        <table>
            <tr class="section-title">
                <td colspan="4">Armed Close Protection Officers (If Applicable)</td>
            </tr>
            <tr>
                <td><strong>Type</strong></td>
                @foreach($requestVisit->armed as $item)
                    @php
                        $total=$item['total'] !=0? $item['total']:"";
                        $tdsArend.="<td>{$total} </td>";
                    @endphp
                    @if($item['total'] !=0)
                        <td style="font-weight: bold">✔ {{$item['type'] }}</td>

                    @else
                        <td style="font-weight: bold"> {{$item['type'] }}</td>
                    @endif
                @endforeach
            </tr>
            <tr>
                <td><strong>Total</strong></td>
                {!! $tdsArend !!}
            </tr>
            <tr>
                <td colspan="4" class="no-border" style="text-align: right;"> &nbsp;</td>
            </tr>
        </table>

        <table>
            <tr class="section-title">
                <th  style="font-weight: bold" colspan="7">Driver(s)/Vehicle(s) Details</th>
            </tr>
            <tr>
                <th   style="font-weight: bold" colspan="3"><strong>Driver’s Details</strong></th>
                <td  style="font-weight: bold" colspan="4"><strong>Vehicle Details</strong></td>
            </tr>


            <tr>
                <th>#</th>
                <th>Full Name</th>
                <th>ID Type & No.</th>
                <th>Cell Phone</th>
                <th>Type/Model</th>
                <th>Color</th>
                <th>Registration Plate</th>
            </tr>
            @php
            $j=1;
            @endphp

            {{-- @dd($requestVisit) --}}
            @foreach ($requestVisit->driver_vehicle_detail as $driver)
                <tr>
                    <td>{{ $j++ }}</td>
                    <td>{{ $driver['name'] ?? '---' }}</td>
                    <td>{{ $driver['id'] ?? '---' }}</td>
                    <td>{{ $driver['phone'] ?? '---' }}</td>
                    <td>{{ $driver['model'] ?? '---' }}</td>
                    <td>{{ $driver['color'] ?? '---' }}</td>
                    @php
                        $trip=isset($driver['trip']) ? ' ('.$driver['trip'].')Trip' :' ';
                    @endphp
                    <td>{{ $driver['Registration_Plate'] . $trip ?? '---' }}</td>
                </tr>
            @endforeach

            <tr>
                <td colspan="7" class="no-border" style="text-align: right;"> &nbsp;</td>
            </tr>
        </table>

        <table>
            <tr>
                <td style="text-align: left;width:23%;padding: 15px 0 0;font-size: 15px" class="no-border">Requestor’s Signature:
                </td>

                <td class="no-border" style="">
                    @if(file_exists($requestVisit->employee->media->where('collection_name', 'signature')->first()?->getPath()))
                        <img
                            src="{{ $requestVisit->employee->media->where('collection_name', 'signature')->first()->getPath() }}"
                            style="width: 70px;height: 50px;margin-bottom: 5px;padding: 0" alt="">
                    @endif

                </td>

                <td class="no-border" style="padding-top: 15px;">
                    Date: {{ \Illuminate\Support\Carbon::create($requestVisit->created_at)->format('d/M/Y ') }}</td>
                <td class="no-border" style="padding-top: 15px">
                </td>
            </tr>

        </table>


        @if (isset($requestVisit->approvals[0]))
            <table class="equal-table ">
                <tr class="section-title bg-lightgreen">
                    <td colspan="4" style="text-align: left;font-weight: bold" class="equal-cell bg-lightgreen">
                        Endorsement and Approval
                    </td>
                </tr>
                <tr>
                    <td style="text-align: left;font-weight: bold" class="equal-cell">FSU UNHCR</td>
                    <td class="equal-cell"></td>
                    <td class="equal-cell"></td>
                    <td class="equal-cell">Date:</td>
                </tr>
                @if($requestVisit->approvals->where('status','Approve')->first()===null)
                    <tr>
                        <td style="text-align: left;font-weight: bold" class="equal-cell">FSA FAO</td>
                        <td class="equal-cell"></td>
                        <td class="equal-cell" rowspan="2">

                           </td>
                        <td class="equal-cell">Date:

                        </td>
                    </tr>
                    <tr>
                        <td style="text-align: left;font-weight: bold" class="equal-cell">ICON SFP</td>
                        <td class="equal-cell"><span style="border-bottom: 1px solid black;font-size: 12px">Digitally Signed By:</span>
                            <br> </td>

                        <td class="equal-cell">Date:

                            +04:30
                        </td>
                @endif
                @foreach ($requestVisit->approvals->where('status','Approve') as $approve)

                    <tr>
                        <td style="text-align: left;font-weight: bold" class="equal-cell">FSA FAO</td>
                        <td class="equal-cell"></td>
                        <td class="equal-cell" rowspan="2">

                            @if ($approve->employee->media->where('collection_name', 'signature')->first()?->original_url and $approve->status->name === 'Approve')
                                <img
                                    src="{{ $approve->employee->media->where('collection_name','signature')->first()->getPath() }}"
                                    style="width: 120px;height: 70px" alt="">
                            @endif</td>
                        <td class="equal-cell">Date:

                        </td>
                    </tr>
                    <tr>
                        <td style="text-align: left;font-weight: bold" class="equal-cell">ICON SFP</td>
                        <td class="equal-cell"><span style="border-bottom: 1px solid black;font-size: 12px">Digitally Signed By:</span>
                            <br> {{$approve->employee->fullName}}</td>

                        <td class="equal-cell">Date:
                            {{ $approve->approve_date ? \Carbon\Carbon::make($approve->approve_date)->format('d/F/Y H:i:s') : "" }}
                            +04:30
                        </td>
                    </tr>
                @endforeach
                <tr>
                    <td style="text-align: left;" class="equal-cell"><b>Remarks to CSM</b></td>
                    <td class="equal-cell"></td>
                    <td class="equal-cell">{{ $requestVisit->status == "approved" ? '✔': '' }} Approved</td>
                    <td class="equal-cell">{!! $requestVisit->status == "notApproved" ? '&#x2718;': '' !!} Not
                        Approved
                    </td>

                </tr>
                {{-- <tr>
                    <td colspan="4" class="no-border" style="text-align: right;">&nbsp;</td>
                </tr> --}}

            </table>
        @endif














        {{--
            @if (isset($requestVisit->approvals[0]))
            <table>
                @foreach ($requestVisit->approvals as $approve)
                            {{-- <tr>
                                <th>{{$approve->employee->fullName}}</th>
                                <th>{{$approve->position}}</th>
                                <th>{{$approve->status}}</th>
                                <th>{{ $approve->approve_date ? \Carbon\Carbon::make($approve->approve_date)->format('M j, Y / h:iA'):""}}</th>
                                <th>

                                    @if ($approve->employee->media->where('collection_name', 'signature')->first()?->original_url and $approve->status->name === 'Approve')
                                        <img src="{{ $approve->employee->media->where('collection_name','signature')->first()->getPath() }}" style="width: 70px;height: 30px" alt="">
                                    @endif
                                </th>
                            </tr>

                            <tr>
                                <td class="no-border">Approved By:  </td>
                                <td class="no-border"> {{$approve->employee->fullName}}</td>
                                <td class="no-border">Signature: </td>
                                <td class="no-border">     @if ($approve->employee->media->where('collection_name', 'signature')->first()?->original_url and $approve->status->name === 'Approve')
                                    <img src="{{ $approve->employee->media->where('collection_name','signature')->first()->getPath() }}" style="width: 100px;height: 40px" alt="">
                                @endif</td>
                                <td class="no-border">Date: </td>
                                <td class="no-border"> {{ $approve->approve_date ? \Carbon\Carbon::make($approve->approve_date)->format('M j, Y '):""}}</td>
                                <td class="no-border">Time: </td>
                                <td class="no-border"> {{ $approve->approve_date ? \Carbon\Carbon::make($approve->approve_date)->format(' h:iA'):""}}</td>
                            </tr>
                        @endforeach

                <tr>
                    <td colspan="6" class="no-border" style="text-align: right;">   &nbsp;</td>
                </tr>
            </table>
            @endif --}}
    </body>

    </html>
