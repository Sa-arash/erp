@include('pdf.header', ['title' => 'Leave Request – R&R/Home', 'titles' => [], 'css' => false])
<style>
    body {
        font-family: Calibri, sans-serif;
        font-size: 13px;
        margin: 20px;
        color: #000;
    }

    .font-bold {
        font-weight: bold;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 0px;
        border: 1px solid black;
    }

    .not-center {
        text-align: left;
    }

    td,
    th {
        border: 1px solid #000;
        padding: 2px;
        text-align: center;
        vertical-align: top;
    }

    .header {
        text-align: center;
        font-weight: bold;
        font-size: 16px;
        border: none;
    }

    .checkbox {
        display: inline-block;
        width: 12px;
        height: 12px;
        border: 1px solid #000;
        margin-right: 4px;
    }

    .filled {
        background: #000;
    }

    .section-title {
        font-weight: bold !important;
        background: #ddd;
        text-align: center;
    }

    .section-title td {
        font-weight: bold !important;
        background: #ddd;
        text-align: center;
    }

    .calendar td {
        text-align: center;
        height: 24px;
    }

    .no-border {
        border: none !important;
    }

    .signature-space {
        height: 50px;
    }

    .small-text {
        font-size: 11px;
    }

    .nowrap {
        white-space: nowrap;
    }

    .hilite {
        background-color: yellow;
        /* یا هر استایل دیگری که می‌خواهید */
    }

      
    .phone-field {
        display: flex;
        justify-content: space-between;
        margin-top: 20px;
        font-size: 12px;
    }

    .phone-section {
        text-align: center;
        flex: 1;
        margin: 0 5px;
    }

    .line {
        border-top: 1px solid black;
        height: 20px;
        margin-bottom: 2px;
    }

    .label {
        padding: 40px;
    }
</style>

<body style="border: 2px solid black !important;">

    <table>
        <tr>
            <td style="border: none" colspan="2">Check the type of Leave the being requested:</td>
            <td style="border: none">
                <div class="checkbox"></div>
                @if (!$leave->type)
                    ☒
                @endif
                R&amp;R
            </td>
            <td style="border: none">
                <div class="checkbox filled"></div>
                @if ($leave->type)
                    ☒
                @endif
                Home Leave
            </td>
        </tr>
    </table>

    <table>
        <tr class="section-title">
            <td>Employee Information</td>
        </tr>
        <tr>
            <td>
                <table style="border-collapse: collapse; width: 100%;">
                    @php
                        $fullNameParts = explode(' ', trim($leave->employee->fullName));
                        $nameCount = count($fullNameParts);
                    @endphp
                    <tr>
                        @if ($nameCount === 3)
                            @foreach ($fullNameParts as $part)
                                <td colspan="4" style="border: none; padding: 0 10px;">
                                    {{ $part }}<br>
                                    <hr style="border: none; border-top: 2px solid black; margin: 5px 0;">
                                </td>
                            @endforeach
                        @elseif ($nameCount === 2)
                            <td colspan="4" style="border: none; padding: 0 10px;">
                                {{ $fullNameParts[0] }}<br>
                                <hr style="border: none; border-top: 2px solid black; margin: 5px 0;">
                            </td>
                            <td colspan="4" style="border: none; padding: 0 10px;">
                                &nbsp;<br>
                                <hr style="border: none; border-top: 2px solid black; margin: 5px 0;">
                            </td>
                            <td colspan="4" style="border: none; padding: 0 10px;">
                                {{ $fullNameParts[1] }}<br>
                                <hr style="border: none; border-top: 2px solid black; margin: 5px 0;">
                            </td>
                        @endif
                    </tr>
                    <tr>
                        <td colspan="4" class="font-bold" style="width: 50%;border: none;padding:0 ">First Name</td>
                        <td colspan="4" class="font-bold" style="width: 50%;border: none;padding:0 ">Mid Name</td>
                        <td colspan="4" class="font-bold" style="width: 50%;border: none;padding:0 ">Last Name</td>
                    </tr>
                    <tr>
                        <td colspan="6" style="border: none;padding:0 10px">
                            {{ $leave->employee?->warehouse?->title . ' - ' . $leave->employee?->structure?->title }}<br>
                            <hr style="border: none; border-top: 2px solid black; margin: 5px 0;">
                        </td>
                        <td colspan="6" style="border: none;padding:0 10px">
                            {{ $leave->employee?->manager?->fullName }}
                            <br>
                            <hr style="border: none; border-top: 2px solid black; margin: 5px 0;">
                        </td>
                    </tr>
                    <tr>
                        <td colspan="6" class="font-bold" style="width: 50%;border: none;padding:0 ">Work Location
                        </td>
                        <td colspan="6" class="font-bold" style="width: 50%;border: none;padding:0 ">Name of
                            Immediate Supervisor /
                            Manager</td>
                    </tr>
                </table>

            </td>
        </tr>
    </table>

    <table style="border: 1px solid black">
        <tr class="section-title ">
            <td colspan="8">Leave Information</td>
        </tr>
        <tr class="no-border font-bold">
            <td class="font-bold" style="border: none; padding: 5px ;">&nbsp;</td>
            <td class="font-bold" colspan="3" style="border: none; padding: 5px 10px;">Departure</td>
            <td class="font-bold" colspan="3" style="border: none; padding: 5px 10px;">Return</td>
            <td class="font-bold" style="border: none; padding: 5px 10px;">Total # </td>
        </tr>
        <tr style="border: none; padding: 0 10px;">
            <td class="font-bold" style="border: none; padding: 0 10px;">&nbsp;</td>
            <td class="font-bold" style="border: none; padding: 0 10px;">Day</td>
            <td class="font-bold" style="border: none; padding: 0 10px;">Month</td>
            <td class="font-bold" style="border: none; padding: 0 10px;">Year</td>


            <td class="font-bold" style="border: none; padding: 0 10px;">Day</td>
            <td class="font-bold" style="border: none; padding: 0 10px;">Month</td>
            <td class="font-bold" style="border: none; padding: 0 10px;">Year</td>
            <td class="font-bold" style="border: none; padding: 0 10px;">of Days</td>
        </tr>
        @if ($lastleave)
            <tr style=" padding: 0 10px;">

                <td class="font-bold" style="border: none; padding: 0 10px;">Last Leave</td>
                <td class="font-bold" style="border: none; padding: 0 10px;">
                    {{ \Carbon\Carbon::parse($lastleave->start_leave)->format('d') }}/<br>
                    <hr style="border: none; border-top: 2px solid black; margin: 5px 0;">
                </td>
                <td class="font-bold" style="border: none; padding: 0 10px;">
                    {{ \Carbon\Carbon::parse($lastleave->start_leave)->format('M') }}/<br>
                    <hr style="border: none; border-top: 2px solid black; margin: 5px 0;">
                </td>
                <td class="font-bold" style="border: none; padding: 0 10px;">
                    {{ \Carbon\Carbon::parse($lastleave->start_leave)->format('Y') }}<br>
                    <hr style="border: none; border-top: 2px solid black; margin: 5px 0;">
                </td>


                <td class="font-bold" style="border: none; padding: 0 10px;">
                    {{ \Carbon\Carbon::parse($lastleave->end_leave)->format('d') }}/<br>
                    <hr style="border: none; border-top: 2px solid black; margin: 5px 0;">
                </td>
                <td class="font-bold" style="border: none; padding: 0 10px;">
                    {{ \Carbon\Carbon::parse($lastleave->end_leave)->format('M') }}/<br>
                    <hr style="border: none; border-top: 2px solid black; margin: 5px 0;">
                </td>
                <td class="font-bold" style="border: none; padding: 0 10px;">
                    {{ \Carbon\Carbon::parse($lastleave->end_leave)->format('Y') }}<br>
                    <hr style="border: none; border-top: 2px solid black; margin: 5px 0;">
                </td>
                <td class="font-bold" style="border: none; padding: 0 10px;">
                  
                    @php
                        $startLast = \Carbon\Carbon::make($lastleave->start_leave)->startOfDay();
                        $endLast = \Carbon\Carbon::make($lastleave->end_leave)->startOfDay();
                        $CompanyHoliday = count(
                            getDaysBetweenDates($startLast, $endLast, $lastleave->company->weekend_days),
                        );
                        $holidaysCount = \App\Models\Holiday::query()
                            ->where('company_id', $lastleave->company->id)
                            ->whereBetween('date', [$startLast, $endLast])
                            ->count();

                    @endphp

                    @if ($startLast && $endLast)
                        {{ round($startLast->diffInDays($endLast) + 1, 0) - $CompanyHoliday - $holidaysCount }}
                    @endif
                    <hr style="border: none; border-top: 2px solid black; margin: 5px 0;">
                </td>
            </tr>
        @else
            <tr style=" padding: 0 10px;">

                <td class="font-bold" style="border: none; padding: 0 10px;">Last Leave</td>
                <td class="font-bold" style="border: none; padding: 0 10px;">
                    <br>
                    ---
                    <hr style="border: none; border-top: 2px solid black; margin: 5px 0;">
                </td>
                <td class="font-bold" style="border: none; padding: 0 10px;">
                    <br>
                    ---
                    <hr style="border: none; border-top: 2px solid black; margin: 5px 0;">
                </td>
                <td class="font-bold" style="border: none; padding: 0 10px;">
                    <br>
                    ---
                    <hr style="border: none; border-top: 2px solid black; margin: 5px 0;">
                </td>


                <td class="font-bold" style="border: none; padding: 0 10px;">
                    <br>
                    ---
                    <hr style="border: none; border-top: 2px solid black; margin: 5px 0;">
                </td>
                <td class="font-bold" style="border: none; padding: 0 10px;">
                    <br>
                    ---
                    <hr style="border: none; border-top: 2px solid black; margin: 5px 0;">
                </td>
                <td class="font-bold" style="border: none; padding: 0 10px;">
                    <br>
                    ---
                    <hr style="border: none; border-top: 2px solid black; margin: 5px 0;">
                </td>
                <td class="font-bold" style="border: none; padding: 0 10px;">
                    <br>
                    ---
                    <hr style="border: none; border-top: 2px solid black; margin: 5px 0;">
                </td>
            </tr>
        @endif

        <tr style=" padding: 0 10px;">
            <td class="font-bold" style="border: none; padding: 0 10px;">Current Leave Request </td>
            <td class="font-bold" style="border: none; padding: 0 10px;">
                {{ \Carbon\Carbon::parse($leave->start_leave)->format('d') }}/<br>
                <hr style="border: none; border-top: 2px solid black; margin: 5px 0;">
            </td>
            <td class="font-bold" style="border: none; padding: 0 10px;">
                {{ \Carbon\Carbon::parse($leave->start_leave)->format('M') }}/<br>
                <hr style="border: none; border-top: 2px solid black; margin: 5px 0;">
            </td>
            <td class="font-bold" style="border: none; padding: 0 10px;">
                {{ \Carbon\Carbon::parse($leave->start_leave)->format('Y') }}<br>
                <hr style="border: none; border-top: 2px solid black; margin: 5px 0;">
            </td>


            <td class="font-bold" style="border: none; padding: 0 10px;">
                {{ \Carbon\Carbon::parse($leave->end_leave)->format('d') }}/<br>
                <hr style="border: none; border-top: 2px solid black; margin: 5px 0;">
            </td>
            <td class="font-bold" style="border: none; padding: 0 10px;">
                {{ \Carbon\Carbon::parse($leave->end_leave)->format('M') }}/<br>
                <hr style="border: none; border-top: 2px solid black; margin: 5px 0;">
            </td>
            <td class="font-bold" style="border: none; padding: 0 10px;">
                {{ \Carbon\Carbon::parse($leave->end_leave)->format('Y') }}<br>
                <hr style="border: none; border-top: 2px solid black; margin: 5px 0;">
            </td>
            <td class="font-bold" style="border: none; padding: 0 10px;">

                @php
                    $start = \Carbon\Carbon::make($leave->start_leave)->startOfDay();
                    $end = \Carbon\Carbon::make($leave->end_leave)->startOfDay();
                    $CompanyHoliday = count(getDaysBetweenDates($start, $end, $leave->company->weekend_days));

                @endphp

                @if ($start && $end)
                    {{ round($start->diffInDays($end) + 1, 0) - $CompanyHoliday }}
                @else
                    تاریخ‌ها نامعتبر هستند
                @endif
                <hr style="border: none; border-top: 2px solid black; margin: 5px 0;">
            </td>
        </tr>


        <tr>

            <td style="border: none; padding: 0 10px;" colspan="8">&nbsp;</td>
        </tr>
        <tr>
            <td not-center colspan="8">Are you aware of any circumstances that will delay or prevent your return to
                the site
                from leave?
                {{ $leave->is_circumstances ? 'Yes ■ No□' : 'Yes □ No■' }}
            </td>
        </tr>
        <tr>
            <td class="not-center" colspan="8" style="height:30px;">If yes, please explain: @if ($leave->is_circumstances)
                    {{ $leave->explain_leave }}
                @endif
            </td>
        </tr>
    </table>

    <table>
        <tr class="section-title">
            <td colspan="2">Leave Details</td>
        </tr>
        <tr>
            <td colspan="2" class="small-text"><i>Use the following legend to annotate the leave pay status in the
                    calendar below for all days off site:</i></td>
        </tr>
        <tr>
            <td colspan="2" class="small-text">

                @php
                    $j = 1;
                @endphp
                <table>
                    <tr>
                        @foreach ($types as $type)
                            <th style="font-size: 10px ;"> {{ $type?->abbreviation }}=
                                <span>{{ $type?->title }}</span></th>
                            @php
                                $j++;
                            @endphp
                            @if ($j == 7)
                                @php
                                    $j = 1;
                                @endphp
                    </tr>
                    <tr>
                        @endif
                        @endforeach
                    </tr>
                </table>

            </td>
        </tr>
        <tr>
            <td>
                @php
                    // dd($company->weekend_days); //=>    ->options([
                    // 'saturday' => 'Saturday',
                    // 'sunday' => 'Sunday',
                    // 'monday' => 'Monday',
                    // 'tuesday' => 'Tuesday',
                    // 'wednesday' => 'Wednesday',
                    // 'thursday' => 'Thursday',
                    // 'friday' => 'Friday',

                    //                     dd($holidays);
                    // //                     => 0 => array:6 [▼
                    // // "id" => 1
                    // // "name" => "test"
                    // // "date" => "2025-05-11 00:00:00"
                    // // "company_id" => 1
                    $startDate = \Carbon\Carbon::parse($leave->start_leave);
                    $endDate = \Carbon\Carbon::parse($leave->end_leave);
                    $daysInMonth = $startDate->daysInMonth; // تعداد روزهای ماه جاری
                    $nextMonthStartDate = $startDate->copy()->addMonth(); // تاریخ شروع ماه آینده
                    $nextMonthDaysInMonth = $nextMonthStartDate->daysInMonth; // تعداد روزهای ماه آینده
                @endphp
                <strong>Month: {{ $startDate->format('F') }}</strong>
                <table class="calendar">
                    @for ($i = 1; $i <= $daysInMonth; $i++)
                        @if ($i % 7 == 1)
                            <tr> <!-- شروع یک ردیف جدید -->
                        @endif

                        @php
                            $currentDate = $startDate->copy()->day($i);

                            $isInLeavePeriod =
                                $currentDate->between($startDate, $endDate) ||
                                $currentDate->isSameDay($startDate) ||
                                $currentDate->isSameDay($endDate); //
                            // بررسی اینکه آیا تاریخ در بازه مرخصی است
                            // dd($currentDate->format('l'),$company->weekend_days,);
                            $isWeekend = in_array(strtolower($currentDate->format('l')), $company->weekend_days);
                            $holidayName = '';
                            foreach ($holidays as $holiday) {
                                if ($currentDate->isSameDay($holiday['date'])) {
                                    $holidayName = $holiday['name'];
                                    break; // اگر تعطیلی پیدا شد، از حلقه خارج می‌شویم
                                }
                            }
                        @endphp

                        <td class="{{ $isInLeavePeriod ? 'hilite' : '' }}">
                            {{ $i }}
                            <br>
                            @if ($isInLeavePeriod)
                                {{ $leave->typeLeave?->abbreviation }}
                            @endif
                            @if ($isWeekend)
                                B
                            @endif
                            @if ($holidayName)
                                {{-- {{ $holidayName }}  --}}
                                H
                            @endif <!-- نمایش نام تعطیلی رسمی -->
                        </td>

                        @if ($i % 7 == 0 || $i == $daysInMonth)
        </tr> <!-- پایان ردیف -->
        @endif
        @endfor

    </table>
    </td>
    <td>
        @php
            $startDate = \Carbon\Carbon::parse($leave->start_leave);
            $endDate = \Carbon\Carbon::parse($leave->end_leave);
            $daysInMonth = $startDate->daysInMonth; // تعداد روزهای ماه جاری
            $nextMonthStartDate = $startDate->copy()->addMonth(); // تاریخ شروع ماه آینده
            $nextMonthDaysInMonth = $nextMonthStartDate->daysInMonth; // تعداد روزهای ماه آینده
        @endphp
        <strong>Month: {{ $startDate->copy()->addMonth()->format('F') }}</strong>
        <table class="calendar">
            @for ($i = 1; $i <= $nextMonthDaysInMonth; $i++)
                @if ($i % 7 == 1)
                    <tr> <!-- شروع یک ردیف جدید -->
                @endif

                @php
                    $currentDate = $startDate->copy()->addMonth()->day($i);
                    $isInLeavePeriod =
                        $currentDate->between($startDate, $endDate) ||
                        $currentDate->isSameDay($startDate) ||
                        $currentDate->isSameDay($endDate); // بررسی اینکه آیا تاریخ در بازه مرخصی است
                    $isWeekend = in_array(strtolower($currentDate->format('l')), $company->weekend_days);
                    $holidayName = '';
                    foreach ($holidays as $holiday) {
                        if ($currentDate->isSameDay($holiday['date'])) {
                            $holidayName = $holiday['name'];
                            break; // اگر تعطیلی پیدا شد، از حلقه خارج می‌شویم
                        }
                    }
                @endphp

                <td class="{{ $isInLeavePeriod ? 'hilite' : '' }}">
                    {{ $i }}
                    @if ($isInLeavePeriod)
                        {{ $leave->typeLeave?->abbreviation }}
                    @endif
                    @if ($isWeekend)
                        B
                    @endif
                    @if ($holidayName)
                        H
                    @endif <!-- نمایش نام تعطیلی رسمی -->
                </td>

                @if ($i % 7 == 0 || $i == $nextMonthDaysInMonth)
                    </tr> <!-- پایان ردیف -->
                @endif
            @endfor
        </table>
    </td>
    </tr>
    </table>

    <table style="border: 1px solid black">
        <tr class="section-title">
            <td style="border: none" colspan="2">Emergency Contact Information</td>
        </tr>
        <tr>
            <td style="border: none" colspan="2"><b><i style="font-size: 12px">Please provide a point of contact
                        who can be reached in the event of an emergency during your leave</i></b></td>
        </tr>
        @if ($leave->employee->emergency_contact)
            <tr>
                <td style="border: none"><b>Email: {{ $leave->employee->emergency_contact[0]['email'] ?? '' }}</b>
                </td>
                @php
                    $number = $leave->employee->emergency_contact[0]['number'];

                    $part1 = substr($number, 0, 3); // سه رقم اول
                    $part2 = substr($number, 3, 3); // سه رقم بعدی
                    $rest = substr($number, 6); // باقی‌مانده
                @endphp

                <p>Part 1: {{ $part1 }}</p>
                <p>Part 2: {{ $part2 }}</p>
                <p>Rest: {{ $rest }}</p>

                <td style="border: none"><b>Contact Number:
                    </b><span>{{ $part1 }}__</span><span>{{ $part2 }}__</span><span>{{ $rest }}</span>
                </td>
            </tr>
        @else
            <tr>
                <td><b>Email:</b> </td>
                <td><b>Contact Number: </b>
                    _______ _________ ________
                </td>
            </tr>
        @endif

    </table>

    <table style="border: 1px solid black; border-collapse: collapse; width: 100%;">
        <tr class="section-title">
            <td colspan="4">
                <b>Signatures</b>

            </td>
        </tr>
        <tr>
            <td style="border: none">
                <hr><b>Employee Signature</b><br>

                <div class="signature-space">
                    @if ($leave->employee->media->where('collection_name', 'signature')->first())
                        <img width="60" height="60"
                            src="{{ $leave->employee->media->where('collection_name', 'signature')->first()?->getPath() }}">
                    @endif
                </div>
            </td>
            <td style="border: none">
                <hr>
                <b>Date</b><br>
                {{ \Carbon\Carbon::parse($leave->created_at)->format('d / M / Y') }}

            </td>
            <td style="border: none">
                <hr><b>Supervisor's Signature</b><br>
                <div class="signature-space">
                    @if ($leave->approvals->first()?->employee?->media->where('collection_name', 'signature')?->first())
                        <img width="60" height="60"
                            src="{{ $leave->approvals->first()->employee->media->where('collection_name', 'signature')->first()?->getPath() }}">
                    @endif
                </div>
            </td>
            <td style="border: none">
                <hr><b>Date</b><br>
                {{ $leave->approvals?->first()?->approve_date ? \Carbon\Carbon::parse($leave->approvals->first()->approve_date)->format('d / M / Y') : ' ' }}
            </td>
        </tr>

        <tr>


            <td style="border: none" colspan="2">
                <hr><b>Admin/HR Dept Signature</b><br>
                <div class="signature-space"></div>
                @if ($leave->admin?->media->where('collection_name', 'signature')?->first())
                    <img width="60" height="60"
                        src="{{ $leave->admin?->media->where('collection_name', 'signature')?->first()?->getPath() }}">
                @endif
            </td>
            <td style="border: none">
                <hr><b>Date</b>

            </td>

            <td style="border: 20px">
                <table>
                    <tr>
                        <th colspan="2"
                            style="border: 1px solid black; padding: 5px; text-align: center; font-weight: bold; text-decoration: underline;">
                            Request Approved
                        </th>
                    </tr>
                    <tr>
                        <td style="border: 1px solid black; padding: 5px;">
                            <span
                                style="display: inline-block; width: 14px; height: 14px; text-align: center; line-height: 14px; vertical-align: middle; font-size: 12px; margin-right: 5px;">
                                @if ($leave->approvals->first()?->status->value !== 'NotApprove')
                                    ☒
                                @endif
                            </span>
                            <b>Approved</b>
                        </td>
                        <td style="border: 1px solid black; padding: 5px;">
                            <span
                                style="display: inline-block; width: 14px; height: 14px; text-align: center; line-height: 14px; vertical-align: middle; font-size: 12px; margin-right: 5px;">
                                @if ($leave->approvals->first()?->status->value !== 'NotApprove')
                                    ☐
                                @endif
                            </span>
                            <b>Denied</b>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" style="border: 1px solid black; padding: 0px 20px 20px;">
                            If denied, rationale:
                            @if ($leave->approvals->first()?->status->value === 'NotApprove')
                                {{ $leave->approvals->first()?->comment }}
                            @else
                                __________
                            @endif
                        </td>
                    </tr>
                </table>

            </td>
        </tr>
    </table>
    <br><br>
    <span style="font-size: 12px;color: gray; margin-left:40% !important; margin-top: auto ">Print Date: {{now()->format('Y/F/d H:iA')}}</span>

</body>

</html>
