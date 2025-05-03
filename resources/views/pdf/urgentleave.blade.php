<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Urgent Leave Slip</title>
  <style>
    body {
      font-family: Calibri, sans-serif;
      margin: 40px;
      color: #000;
    }
    .header {
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .header img {
      height: 60px;
    }
    .title {
      font-size: 20px;
      font-weight: bold;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
    }
    td {
      border: 1px solid #000;
      padding: 8px;
      vertical-align: top;
    }
    .side-header {
      writing-mode: vertical-rl;
      transform: rotate(180deg);
      text-align: center;
      background-color: #e8f0ff;
      font-weight: bold;
      color: #004aad;
      font-size: 14px;
      border-right: none;
    }
    .content-table td:first-child {
      width: 30%;
      font-weight: bold;
    }
    .content-table td:nth-child(2) {
      width: 70%;
    }
  </style>
</head>
<body>

  <div class="header">
    <img src="logo.png" alt="ATGT Logo">
    <div class="title">Urgent Leave Slip</div>
  </div>

  <table>
    <tr>
      <td class="side-header" rowspan="9">Human Resources Department</td>
      <td class="content-table" colspan="2">
        <table class="content-table">
          <tr><td>Date:</td><td>DD-MM-YYYY</td></tr>
          <tr><td>Name:</td><td>JANE DOE</td></tr>
          <tr><td>Badge Number:</td><td>UNC-INT-1234</td></tr>
          <tr><td>Department:</td><td>FINANCE</td></tr>
          <tr><td>Reason:</td><td>SICK FAMILY MEMBER TO TAKE TO HOSPITAL</td></tr>
          <tr><td>Time Out:</td><td>14:00 PM</td></tr>
          <tr><td>Time In:</td><td>NOT RETURNING TO DUTY</td></tr>
          <tr><td>Staff Signature:</td><td></td></tr>
          <tr><td>Approved by Line Manager:</td><td>JOHN H. DOE IV</td></tr>
          <tr><td>Approved by HR Department:</td><td>JANE E. PALER</td></tr>
        </table>
      </td>
    </tr>
  </table>

</body>
</html>
