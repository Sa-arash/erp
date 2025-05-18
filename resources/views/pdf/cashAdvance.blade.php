@include('pdf.header',['title'=>'Cash Advance Form','titles'=>[],'css'=>true])
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        th, td {
            border: 1px solid #000;
            padding: 8px;
            vertical-align: top;
        }

        th {
            background-color: #f2f2f2;
            text-align: left;
            color: black;
        }



        .side-header {
            transform: rotate(-90deg);
            transform-origin: bottom left;
            white-space: nowrap;
            font-weight: bold;
            background-color: #e6f0ff;
            color: #003399;
            padding: 10px;
            text-align: left;
        }



        .title {
            text-align: center;
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 20px;
        }
    </style>


<div class="title">CASH ADVANCE FORM</div>

<table>
    <tr>
        <th>Date:</th>
        <td>{{$loan->approve_finance_date}}</td>
    </tr>
    <tr>
        <th>Name:</th>
        <td>{{$loan->employee->fullName}}</td>
    </tr>
    <tr>
        <th>Badge Number:</th>
        <td>{{$loan->employee->ID_number}}</td>
    </tr>
    <tr>
        <th>Department:</th>
        <td>{{$loan->employee->department->title}}</td>
    </tr>
    <tr>
        <th>Amount:</th>
        <td>AFS 5,000.00</td>
    </tr>
    <tr>
        <th>Requestor Signature:</th>
        <td> @if($loan->employee?->media->where('collection_name','signature')->first())
                <img width="60" height="60" src="{{$loan->employee->media?->where('collection_name','signature')->first()?->getPath()}}">
            @endif</td>
    </tr>
    <tr>
        <th>Line Manager:</th>
        <td>{{$loan->employee->manager->fullName}}</td>
    </tr>
    <tr>
        <th>Admin/HR Department:</th>
        <td>{{$loan->admin->fullName}}</td>
    </tr>
    <tr>
        <th>Endorsed to Finance department:</th>
        <td>{{$loan->finance->fullName}}</td>
    </tr>
    @if($loan->employee->loan_limit < $loan->amount)
    <tr>
        <th>CEO Approval:</th>
        <td >{{$loan->approvals->where('position','CEO')->first()?->employee?->fullName}}</td>
    </tr>
    @endif
</table>


