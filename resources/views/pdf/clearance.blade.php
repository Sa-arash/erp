<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Employee Exit Form</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 40px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            border: 1px solid black;
            padding: 6px;
            vertical-align: top;
        }

        .section-title {
            background-color: #d9d9d9;
            font-weight: bold;
        }

        .font-bold {
            font-weight: bold;
        }

        .font-sm {
            font-size: 12px;
        }

        .header-row {
            background-color: #d9d9d9;
            font-weight: bold;
            text-align: center;
        }

        .no-border {
            border: none;
        }

        .initials-header {
            text-align: center;
            font-size: 11px;
            font-weight: bold;
        }

        .small-text {
            font-size: 10px;
        }

        .footer {

            margin-top: 100px;
        }

        .input-line {
            display: inline-block;
            border-bottom: 1px solid #000;
            min-width: 150px;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 40px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td {
            border: 1px solid black;
            padding: 10px;
            font-size: 15px;
            vertical-align: top;
        }

        .header-black {
            background-color: black;
            color: white;
            text-align: center;
            font-size: 15px;
            font-weight: bold;
            padding: 8px;
        }

        .header-wite {
            text-align: center;
            font-weight: bold;
            text-decoration: underline;
            padding: 8px;
            border-bottom: none !important;
        }

        .header-wite td {
            text-decoration: underline;
            border-bottom: none !important;
        }

        .underline {
            text-decoration: underline !important;
        }

        .section-title {
            font-weight: bold;
            margin-bottom: 10px;
        }

        .red-text {
            color: red;
            margin-top: 10px;
        }

        .signature-row {
            margin-top: 30px;
        }

        .signature-label {
            display: inline-block;
            min-width: 180px;
            border-bottom: 1px solid #000;
        }

        @media print {
            .page-break {
                page-break-before: always;
                break-before: page;
            }
        }

        .tablefirst {
            border-collapse: collapse;
            width: 100%;
        }

        .tablefirst tr {
            border-top: none;
            border-bottom: none;
        }

        .tablefirst td {
            border-top: none;
            border-bottom: none;
            padding: 8px;
           
        }

        .border-bottom-none {
            border-bottom: none !important;
        }

        .border-top-none {
            border-top: none !important;
        }

        .border-bottom-none td {
            border-bottom: none !important;
        }

        .border-top-none td {
            border-top: none !important;
        }
    </style>
</head>

<body>



  
    <table>
        <tr style="text-align:center">
            <td colspan="8" style=" text-align: center;
            font-weight: bold;
            padding: 8px;
            border: none !important;" >
                Employee Name: ____________________________________________Employee ID: _________________
                Job Title: ___________________Department: _______________________Last Day of Worked:_________
                Term Date (return to HOR/Demob/Release): __________________Site Location: ICON Compound- Kabul, AFG
                Reason for Termination:
            
        </tr>
        <tr style="text-align:center">
            <td colspan="8" style="text-align:center;border: 1px solid black; background-color:black;color:white">
                <strong>Employee Name:
                    {{-- <span style="color:black"> ___________________________________________________________________________________________________________________</span> --}}
                </strong> <span class="input-line"></span></td>
        </tr>
        <tr class="section-title ">
            <td colspan="4" rowspan="3" class="font-bold">2. COLLECT COMPANY PROPERTY:</td>
        </tr>
        <tr class="header-row">
            <td colspan="2" class="font-bold ">COMPLETE<br>(Initials)</td>
            <td colspan="2" class="font-bold ">N/A<br>(Initials)</td>
        </tr>

        <tr class="header-row">
            <td class="font-bold font-sm">Employee</td>
            <td class="font-bold font-sm">Manager</td>
            <td class="font-bold font-sm">Employee</td>
            <td class="font-bold font-sm">Manager</td>
        </tr>
 
        <tr class="border-top-none border-bottom-none">
            <td colspan="4">Admin/HR or Security Department must retrieve employee
                issued badge and PPE:
            </td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr class="border-top-none border-bottom-none">
            <td colspan="4">&nbsp;</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr class="border-top-none border-bottom-none">
            <td colspan="4">
                <span class="underline">Status of Badge Hand-Over:</span>
                <br>
                *Company Badge Handed-Over at Site: YES☐ NO☐
                <br>
                If NO, explain: <span class="input-line"></span><br>
            </td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr class="border-top-none border-bottom-none">
            <td colspan="4">&nbsp;</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr class="border-top-none border-bottom-none">
            <td colspan="4">
                *Meal Card Handed-Over at Site: YES☐ NO☐
            </td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr class="border-top-none border-bottom-none">
            <td colspan="4">&nbsp;</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr class="border-top-none border-bottom-none">
            <td colspan="4">
                <span class="underline">*Status of PPE Hand-Over:</span>
                <br>
                PPE Handed-Over at Site: YES☐ NO☐
                <br>
                If NO, explain: <span class="input-line"></span><br>
            </td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr class="border-top-none border-bottom-none">
            <td colspan="4">&nbsp;</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr class="border-top-none border-bottom-none">
            <td colspan="4">
                Admin/HR or Security department must retrieve company
                issued ID/ badges, meal card, PPE from terminated employees.
                PPE and badges are the property of the Company and will be
                turned in upon employee termination.
            </td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <br>

      
        <tr class="border-top-none border-bottom-none">
            <td colspan="4">&nbsp;</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>


        <tr class="border-top-none ">
            <td colspan="4">
                <span class="">Admin/HR or Security</span>
                <br>
                Disposition: <span class="input-line"></span><br>
            </td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr> <td style="border:none" colspan="8">Last Updated: 02/22/2023</td></tr>
    </table>

    <div class="page-break"></div>






















    <table>
        <tr>
            <td colspan="5" style="border: 1px solid black;"><strong>Employee Name:
                    ___________________________________________________________________________________________________________________
                </strong> <span class="input-line"></span></td>
        </tr>
        <tr class="section-title">
            <td colspan="1" rowspan="3" class="font-bold">2. COLLECT COMPANY PROPERTY:</td>
        </tr>
        <tr class="header-row">
            <td colspan="2" class="font-bold">COMPLETE<br>(Initials)</td>
            <td colspan="2" class="font-bold">N/A<br>(Initials)</td>
        </tr>
        <tr class="header-row">
            <td class="font-bold">Employee</td>
            <td class="font-bold">Manager</td>
            <td class="font-bold">Employee</td>
            <td class="font-bold">Manager</td>
        </tr>
        <tr>
            <td><strong>2a.) Keys:</strong><br>
                Admin/HR will retrieve keys from terminating employee. Room & office keys must be returned to Admin/HR
                department.
            </td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td><strong>2b.) Cellular Phone/Sim Card:</strong><br>
                Admin/HR or IT department will retrieve cellular phone from terminating employee and hold for
                reissue.<br><br>
                Cell Phone/Sim card Serial No. <span class="input-line"></span><br>
                Phone No. <span class="input-line"></span>
            </td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td><strong>2c.) Computers/Laptop/USB</strong><br>
                Admin/HR department or IT department will retrieve / account for any computer(s), computer software,
                external drives (original disks and documentation / manuals), USB and any reference material from
                terminating employee and hold for reissue.<br><br>
                Computer Serial #: <span class="input-line"></span><br>
                UNC Property Tag #: <span class="input-line"></span>
            </td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>

        <tr class="section-title">
            <td colspan="1" rowspan="3" class="font-bold">4. ACCOUNTING/FINANCE:</td>
        </tr>
        <tr class="header-row">
            <td colspan="2" class="font-bold">COMPLETE<br>(Initials)</td>
            <td colspan="2" class="font-bold">N/A<br>(Initials)</td>
        </tr>
        <tr class="header-row">
            <td class="font-bold">Employee</td>
            <td class="font-bold">Manager</td>
            <td class="font-bold">Employee</td>
            <td class="font-bold">Manager</td>
        </tr>
        <tr>
            <td><strong>4a.) Final Timesheet:</strong><br>
                Admin/HR department must ensure terminating employee completes and submits a final timesheet.
            </td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td><strong>4d.) Petty Cash:</strong><br>
                All petty cash accounts through Finance Office must be cleared. The employee must liquidate and hand
                over petty cash funds balance with the Finance office and provide associated expense receipts if needed.
                Liquidate and settle all dues and cash advances.
            </td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
    <tr> <td style="border:none" colspan="5">Last Updated: 02/22/2023</td></tr>
    </table>


    <div class="page-break"></div>







    <table>
        <tr>
            <td style="border: 1px solid black;"><strong>Employee Name:
                    ___________________________________________________________________________________________________________________
                </strong> <span class="input-line"></span></td>
        </tr>

        <tr>
            <td class="header-black">PART B: TO BE COMPLETED BY EMPLOYEE</td>
        </tr>
        <br>
        <tr class="header-wite">

            <td class="header-wite">Employee Certification</td>
        </tr>
        <br>
        <tr class="border-top-none">
            <td class="border-top-none">

                <p>
                    I certify that I have returned all company property (e.g., Company ID Badge, keys, cellular
                    phone(s), SIM card computer(s), USB, including all documentation that contains company sensitive
                    information which I have had in my possession (e.g., Manuals, Bulletins, Desk Guides, User’s Guides,
                    Maintenance/Operating Instructions, Handbooks, Computer Runs, external disks, Computer Software, to
                    include original disks and documentation/manuals, ATGT LLC-owned codes and standards, and any
                    reference material, etc.).
                </p>
                <br>
                <p>
                    I certify that I have cleared all financial responsibilities regarding any advances, incomplete
                    expense reports, unsettled allowances, as well as any liability with regard to applicable payment
                    agreements or promissory notes. I understand that if all accounts are not fully paid, the amount
                    owed will be communicated to me and deducted from any earned, but unpaid wages, vacation pay, or
                    final expenses due to me by the Company.
                </p>
                <br>
                <p>
                    ATGT LLC will arrange and purchase your travel back to your Home of Record (International Employees
                    only). In the event, INT employees opt not to travel back to his/her HOR, he/she MUST complete the
                    CTIP Employee’s Traveler’s Waiver form.
                </p>
                <br>
                <p class="red-text">
                    I certify that I do not have any outstanding injuries as of this date and any injury incurred while
                    working at this site for UNC has been identified to Site Management / Safety (date of injury:
                    ______________________) and all medical actions are completed.

                </p>
                <br><br>
                <table style="border: none;">
                    <tr style="border: none;">
                        <td style="border: none;"><strong>Employee Signature</strong> <span
                                class="signature-label"></span></td>
                        <td style="border: none;"><strong>Date</strong> <span class="signature-label"></span></td>
                    </tr>
                    <tr style="border: none;">
                        <td style="border: none;"><strong>Witness Signature</strong> <span
                                class="signature-label"></span></td>
                        <td style="border: none;"><strong>Date</strong> <span class="signature-label"></span></td>
                    </tr>
                    <tr style="border: none;">
                        <td colspan="2" style="border: none;"><br>I CERTIFY THAT THIS EMPLOYEE HAS BEEN CLEARED AS
                            INDICATED ABOVE.</td>
                    </tr>
                    <tr style="border: none;">
                        <td style="border: none;"><strong>Admin/HR Department</strong> <span
                                class="signature-label"></span></td>
                        <td style="border: none;"><strong>Date</strong> <span class="signature-label"></span></td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr> <td style="border:none" colspan="5">Last Updated: 02/22/2023</td></tr>
    </table>

</body>

</html>
