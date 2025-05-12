
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

            td,
            th {
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

        <table>
            <tr class="section-title">
                <td colspan="6">Requestor’s Details</td>
            </tr>

            <tr>
                <td>Requestor’s Name</td>
                <td>Title</td>
                <td>UN Agency</td>
                <td>ICON</td>
                <td>Cell Phone</td>
                <td>Email</td>
            </tr>

            <tr>
                <td> {{ $requestVisit->employee->fullName }}</td>

                <td> {{$requestVisit->ICON ? $requestVisit->employee->position?->title:'' }}</td>
                <td> {{ $requestVisit->agency }}</td>
                <td> {{ $requestVisit->ICON ? '■': '□' }}</td>
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
                <td>Date of Visit</td>
                <td>Time of Arrival</td>
                <td>Time of Departure</td>
            </tr>
            <tr>
                <td>{{ \Illuminate\Support\Carbon::create($requestVisit->visit_date)->format('Y/m/d') }}</td>
                <td>{{ \Carbon\Carbon::parse($requestVisit->arrival_time)->format('h:i A') }}</td>
                <td>{{ \Carbon\Carbon::parse($requestVisit->departure_time)->format('h:i A') }}</td>

            </tr>
            <tr>
                <td>Purpose of visit (Be specific)</td>
                <td colspan="2">{{ $requestVisit->purpose }}</td>
            </tr>
            <tr>
                <td colspan="3" class="no-border" style="text-align: right;"> &nbsp;</td>
            </tr>
        </table>

        <table>
            <tr class="section-title">
                <td colspan="7">Visitor(s) Details</td>
            </tr>
            <tr>
                <td></td>
                <td>Name</td>
                <td>ID/Passport</td>
                <td>Cell Phone</td>
                <td>Type</td>
                <td>Organization</td>
                <td>Remarks</td>
            </tr>
            @php
            $i=1;
            @endphp
            @foreach ($requestVisit->visitors_detail as $visitor)
                <tr>
                    <td>{{ $i++ }}</td>
                    <td>{{ $visitor['name'] ?? '---' }}</td>
                    <td>{{ $visitor['id' ?? '---'] }}</td>
                    <td>{{ $visitor['phone'] ?? '---' }}</td>
                    <td>{{ $visitor['type'] ?? '---' }}</td>
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
                <td colspan="6">Armed Close Protection Officers (If Applicable)</td>
            </tr>
            <tr>
                <td><strong>Type</strong></td>
                <td>
                    {!! $requestVisit->employee->card_status === 'National Staff' ? '■ National' : '□ National' !!}
                </td>
                <td>
                    {!! $requestVisit->employee->card_status === 'International Staff' ? '■ International' : '□ International' !!}
                </td>
                <td colspan="3">
                    {!! $requestVisit->employee->card_status === 'International Resident' ? '■ De-facto Security Forces' : '□ De-facto Security Forces' !!}
                </td>

            </tr>
            <tr>
                <td><strong>Total</strong></td>

                <td colspan="5">&nbsp;</td>
            </tr>
            <tr>
                <td colspan="6" class="no-border" style="text-align: right;"> &nbsp;</td>
            </tr>
        </table>

        <table>
            <tr class="section-title">
                <td class="text-center" colspan="6">Driver(s)/Vehicle(s) Details</td>
            </tr>
            <tr>
                <td class="text-center" colspan="3"><strong>Driver’s Details</strong></td>
                <td class="text-center" colspan="3"><strong>Vehicle Details</strong></td>
            </tr>




            <tr>
                <td>Full Name</td>
                <td>ID Type & No.</td>
                <td>Cell Phone</td>
                <td>Type/Model</td>
                <td>Color</td>
                <td>Registration Plate</td>
            </tr>
            {{-- @dd($requestVisit) --}}
            @foreach ($requestVisit->driver_vehicle_detail as $driver)
                <tr>
                    <td>{{ $driver['name'] ?? '---' }}</td>
                    <td>{{ $driver['id'] ?? '---' }}</td>
                    <td>{{ $driver['phone'] ?? '---' }}</td>
                    <td>{{ $vehicle['model'] ?? '---' }}</td>
                    <td>{{ $vehicle['color'] ?? '---' }}</td>
                    <td>{{ $vehicle['Registration_Plate'] ?? '---' }}</td>
                </tr>
            @endforeach

            <tr>
                <td colspan="6" class="no-border" style="text-align: right;"> &nbsp;</td>
            </tr>
        </table>

        <table>
            <tr>
                <td class="no-border">Requestor’s Signature:
                </td>

                <td class="no-border">
                    @if(file_exists($requestVisit->employee->media->where('collection_name', 'signature')->first()?->getPath()))
                    <img src="{{ $requestVisit->employee->media->where('collection_name', 'signature')->first()->getPath() }}"
                        style="width: 120px;height: 50px" alt="">
                    @endif
                </td>

                <td class="no-border">Date:</td>
                <td class="no-border">
                    {{ \Illuminate\Support\Carbon::create($requestVisit->visit_date)->format('Y/m/d') }}</td>
            </tr>

        </table>



        @if (isset($requestVisit->approvals[0]))
            <table class="equal-table ">
                <tr class="section-title bg-lightgreen">
                    <td colspan="4" class="equal-cell bg-lightgreen">Endorsement and Approval</td>
                </tr>
                <tr>
                    <td class="equal-cell">FSU UNHCR</td>
                    <td class="equal-cell"></td>
                    <td class="equal-cell"></td>
                    <td class="equal-cell">Date:</td>
                </tr>
                @foreach ($requestVisit->approvals as $approve)
                <tr>
                    <td class="equal-cell">FSA FAO</td>
                    <td class="equal-cell"></td>
                    <td class="equal-cell" rowspan="2">
                        @if ($approve->employee->media->where('collection_name', 'signature')->first()?->original_url and $approve->status->name === 'Approve')
                        <img src="{{ $approve->employee->media->where('collection_name','signature')->first()->getPath() }}" style="width: 120px;height: 70px" alt="">
                    @endif</td>
                    <td class="equal-cell">Date:

                    </td>
                </tr>
                    <tr>
                        <td class="equal-cell">ICON SFP</td>
                        <td class="equal-cell">{{$approve->employee->fullName}}</td>

                        <td class="equal-cell">Date:
                            {{ $approve->approve_date ? \Carbon\Carbon::make($approve->approve_date)->format('Y.m.d H:i:s') : "" }} +04'30
                        </td>
                    </tr>
                @endforeach
                <tr>
                    <td class="equal-cell">Remarks to CSM</td>
                    <td class="equal-cell"></td>
                    <td class="equal-cell">{{ $requestVisit->status == "approved" ? '■': '□' }}  Approved</td>
                    <td class="equal-cell">{{ $requestVisit->status == "notApproved" ? '■': '□' }} Not Approved</td>

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
