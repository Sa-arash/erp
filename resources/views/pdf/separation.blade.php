<!DOCTYPE html>
<html lang="fa">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Clearance/Separation Form</title>
    <style>
        body {
            font-family: Vazir, sans-serif;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #000;
            padding: 8px;
            text-align: center;
        }
        th {
            background-color: #f2f2f2;
        }
        .form-title {
            text-align: center;
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .section-title {
            font-weight: bold;
            margin-top: 20px;
        }
        .signature {
            margin-top: 40px;
        }
    </style>
</head>
<body>

<div class="form-title">SERVICES TEAM - UN COMPOUND</div>
<div class="form-title">Staff Clearance/Separation Form</div>

<table>
    <tr>
        <th colspan="2">For employee use only:</th>
    </tr>
    <tr>
        <td>Name: {{$employee->fullName}}</td>
        <td>Position: {{$employee?->position?->title}}</td>
    </tr>
    <tr>
        <td>Duty Station: {{$employee->structure?->title}}</td>
        <td>Date: {{\Carbon\Carbon::make($employee->leave_date)->format('Y/m/d')}}</td>
    </tr>
    <tr>
        <td colspan="2">Signature: (employee)</td>
    </tr>
</table>

<div class="section-title">Departments</div>
<table>
    <tr>
        <th>Department</th>
        <th>Comments/Liabilities</th>
    </tr>
    <tr>
        <td>Relevant Department</td>
        <td>Comments, name and signature</td>
    </tr>
    <tr>
        <td>Logistic Department</td>
        <td>Comments, name and signature</td>
    </tr>
    <tr>
        <td>Administration Department</td>
        <td>Comments, name and signature</td>
    </tr>
    <tr>
        <td>HR Department</td>
        <td>Comments, name and signature</td>
    </tr>
    <tr>
        <td>Stock</td>
        <td>Comments, name and signature</td>
    </tr>
    <tr>
        <td>Finance Department</td>
        <td>Comments, name and signature</td>
    </tr>
</table>

<div class="section-title">Final Steps:</div>
<table>
    <tr>
        <td>Attested by: (Head of Department name and signature)</td>
        <td>Approved by: (Operations, name and signature)</td>
    </tr>
</table>

</body>
</html>
