{{-- <!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Leave Request – R&R/Home</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      font-size: 12px;
      margin: 20px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
    }

    td, th {
      border: 1px solid #000;
      padding: 4px;
      vertical-align: top;
    }

    .center {
      text-align: center;
    }

    .no-border {
      border: none !important;
    }

    .section-title {
      font-weight: bold;
      background-color: #f0f0f0;
      text-align: center;
    }

    .checkbox {
      display: inline-block;
      width: 14px;
      height: 14px;
      border: 1px solid #000;
      margin-right: 5px;
    }

    .filled {
      background-color: #000;
    }

    .calendar td {
      height: 24px;
      text-align: center;
    }

    @media print {
      body {
        margin: 0;
      }
    }
  </style>
</head>
<body>

  <h2 class="center">Leave Request – R&R/Home</h2>

  <table>
    <tr>
      <td colspan="2">Check the type of Leave the being requested:</td>
      <td><div class="checkbox"></div> R&R</td>
      <td><div class="checkbox filled"></div> Home Leave</td>
    </tr>
  </table>

  <table>
    <tr class="section-title"><td colspan="4">Employee Information</td></tr>
    <tr>
      <td><strong>First Name</strong><br>DONALD</td>
      <td><strong>Middle In.</strong><br>J.</td>
      <td><strong>Last Name</strong><br>TRUMP</td>
      <td><strong>Name of Immediate Supervisor / Manager</strong><br>WASIM A. WISSAL</td>
    </tr>
    <tr>
      <td colspan="4"><strong>Work Location</strong><br>ICON COMPOUND - KABUL, AFGHANISTAN</td>
    </tr>
  </table>

  <table>
    <tr class="section-title"><td colspan="6">Leave Information</td></tr>
    <tr>
      <td><strong>Last Leave</strong><br>01 / DEC / 2024</td>
      <td colspan="2"><strong>Current Leave Request</strong><br>25 / APR / 2025 – 09 / MAY / 2025</td>
      <td colspan="3"><strong>Total # of Days</strong><br>13</td>
    </tr>
    <tr>
      <td colspan="6">Are you aware of any circumstances that will delay or prevent your return to the site from leave?
        <div style="display:inline-block; margin-left:10px;"><div class="checkbox"></div> Yes</div>
        <div style="display:inline-block; margin-left:10px;"><div class="checkbox filled"></div> No</div>
      </td>
    </tr>
    <tr><td colspan="6" style="height:30px;">If yes, please explain:</td></tr>
  </table>

  <table>
    <tr class="section-title"><td colspan="2">Leave Details</td></tr>
    <tr>
      <td colspan="2">Use the following legend to annotate the leave pay status in the calendar below for all days off site:</td>
    </tr>
    <tr>
      <td colspan="2">
        O = Regular Day Off &nbsp;&nbsp; H = Holiday &nbsp;&nbsp; CL = Casual (LN) &nbsp;&nbsp; ML = Medical Leave &nbsp;&nbsp;
        BL = Bereavement &nbsp;&nbsp; M = Marriage &nbsp;&nbsp; TD = Travel Day &nbsp;&nbsp; S = Sick &nbsp;&nbsp;
        RR = Paid leave &nbsp;&nbsp; LWOP = Leave without Pay &nbsp;&nbsp; ML/PL = Maternity/Paternity
      </td>
    </tr>
    <tr>
      <td>
        <strong>Month: APRIL</strong>
        <table class="calendar">
          <tr><td>1</td><td>2</td><td>3</td><td>4</td><td>5</td><td>6</td><td>7</td></tr>
          <tr><td>8</td><td>9</td><td>10</td><td>11</td><td>12</td><td>13</td><td>14</td></tr>
          <tr><td>15</td><td>16</td><td>17</td><td>18</td><td>19</td><td>20</td><td>21</td></tr>
          <tr><td>22</td><td>23</td><td>24</td><td>25 TD</td><td>26 RR</td><td>27 RR</td><td>28 RR</td></tr>
          <tr><td>29 RR</td><td>30 RR</td><td colspan="5" class="no-border"></td></tr>
        </table>
      </td>
      <td>
        <strong>Month: MAY</strong>
        <table class="calendar">
          <tr><td>1 RR</td><td>2 RR</td><td>3 RR</td><td>4 RR</td><td>5 RR</td><td>6 RR</td><td>7 RR</td></tr>
          <tr><td>8 TD</td><td>9</td><td>10</td><td>11</td><td>12</td><td>13</td><td>14</td></tr>
          <tr><td>15</td><td>16</td><td>17</td><td>18</td><td>19</td><td>20</td><td>21</td></tr>
          <tr><td>22</td><td>23</td><td>24</td><td>25</td><td>26</td><td>27</td><td>28</td></tr>
          <tr><td>29</td><td>30</td><td>31</td><td colspan="4" class="no-border"></td></tr>
        </table>
      </td>
    </tr>
  </table>

  <table>
    <tr class="section-title"><td colspan="2">Emergency Contact Information</td></tr>
    <tr>
      <td>Email: ____________________________</td>
      <td>Contact Number: ____ - ____ - __________</td>
    </tr>
  </table>

  <table>
    <tr class="section-title"><td colspan="4">Signatures</td></tr>
    <tr>
      <td>Employee Signature<br><br>__________________</td>
      <td>Date<br><br>__________</td>
      <td>Supervisor's Signature<br><br>__________________</td>
      <td>Date<br><br>__________</td>
    </tr>
    <tr>
      <td colspan="2">Admin/HR Dept Signature<br><br>__________________</td>
      <td colspan="2">
        <div><div class="checkbox filled"></div> Approved</div>
        <div><div class="checkbox"></div> Denied</div>
        <br>If denied, rationale:<br>______________________________
      </td>
    </tr>
  </table>

</body>
</html> --}}






@include('pdf.header', [
    'titles' => ['Leave Request – R&R/Home'],
    'title' => 'Leave Request – R&R/Home',
    'css' => false,
])


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
        margin-bottom: 10px;
    }

    .not-center {
        text-align: left;
    }

    td,
    th {
        border: 1px solid #000;
        padding: 5px;
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
</style>

<body>



    <table>
        <tr>
            <td colspan="2">Check the type of Leave the being requested:</td>
            <td>
                <div class="checkbox"></div> R&amp;R
            </td>
            <td>
                <div class="checkbox filled"></div> Home Leave
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
                            {{ $leave->employee?->manager?->fullName }}<br>
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

    <table>
        <tr class="section-title">
            <td colspan="6">Leave Information</td>
        </tr>
        <tr>
            <td><strong>Last Leave</strong><br>{{ \Carbon\Carbon::parse($lastleave->start_leave)->format('d / M / Y') }}
                - {{ \Carbon\Carbon::parse($lastleave->end_leave)->format('d / M / Y') }}</td>
            <td colspan="2"><strong>Current Leave
                    Request</strong><br>{{ \Carbon\Carbon::parse($leave->start_leave)->format('d / M / Y') }} -
                {{ \Carbon\Carbon::parse($leave->end_leave)->format('d / M / Y') }}</td>
            <td colspan="3"><strong>Total # of Days</strong><br>

                {{ \Carbon\Carbon::parse($leave->start_leave)->startOfDay()->diffInDays(\Carbon\Carbon::parse($leave->end_leave), $leave->end_leave) }}
            </td>
        </tr>
        <tr>
            <td not-center colspan="6">Are you aware of any circumstances that will delay or prevent your return to
                the site
                from leave?
                Yes □ No □
            </td>
        </tr>
        <tr>
            <td class="not-center" colspan="6" style="height:30px;">If yes, please explain:</td>
        </tr>
    </table>

    <table>
        <tr class="section-title">
            <td colspan="2">Leave Details</td>
        </tr>
        <tr>
            <td colspan="2" class="small-text">Use the following legend to annotate the leave pay status in the
                calendar below for all days off site:</td>
        </tr>
        <tr>
            <td colspan="2" class="small-text">
                O = Regular Day Off, H = Holiday, CL = Casual (LN), ML = Medical Leave, BL = Bereavement,
                M = Marriage, TD = Travel Day, S = Sick, RR = Paid leave, LWOP = Leave without Pay, ML/PL =
                Maternity/Paternity
            </td>
        </tr>
        <tr>
            <td>
                @php
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
                        @endphp

                        <td class="{{ $isInLeavePeriod ? 'hilite' : '' }}">
                            {{ $i }}
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
                @endphp

                <td class="{{ $isInLeavePeriod ? 'hilite' : '' }}">
                    {{ $i }}
                </td>

                @if ($i % 7 == 0 || $i == $nextMonthDaysInMonth)
                    </tr> <!-- پایان ردیف -->
                @endif
            @endfor
        </table>
    </td>
    </tr>
    </table>

    <table>
        <tr class="section-title">
            <td colspan="2">Emergency Contact Information</td>
        </tr>
        @if ($leave->employee->emergency_contact)
            @foreach ($leave->employee->emergency_contact as $emergency)
                <tr>
                    <td>Email: {{ $emergency['email']??'' }}</td>
                    <td>Contact Number: {{ $emergency['number'] }} </td>
                </tr>
            @endforeach
        @endif

    </table>

    <table>
        <tr class="section-title">
            <td colspan="4">Signatures<b>

                  
            </td>
        </tr>
        <tr>
            <td>Employee Signature<br>
             
              <div class="signature-space">
                @if ($leave->employee->media->where('collection_name', 'signature')->first())
                    <img width="60" height="60"
                        src="{{ $leave->employee->media->where('collection_name', 'signature')->first()?->getPath() }}">
                @endif
            </div>
            </td>
            <td>Date<br>
            </td>
            <td>Supervisor's Signature<br>
              <div class="signature-space">
                @if ($leave->employee->manager->media->where('collection_name', 'signature')->first())
                    <img width="60" height="60"
                        src="{{ $leave->employee->manager->media->where('collection_name', 'signature')->first()?->getPath() }}">
                @endif
            </div>
            </td>
            <td>Date<br>
                <div class="signature-space"></div>
            </td>
        </tr>
        <tr>
            <td colspan="2">Admin/HR Dept Signature<br>
                <div class="signature-space"></div>
            </td>
            <td colspan="2">
                <div>
                    <div class="checkbox filled"></div> Approved
                </div>
                <div>
                    <div class="checkbox"></div> Denied
                </div>
                <div>If denied, rationale:<br>_______________________________</div>
            </td>
        </tr>
    </table>

</body>

</html>
