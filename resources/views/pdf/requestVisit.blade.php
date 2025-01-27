


   @include('pdf.header', ['titles' => ['Visitor Request Details'], 'css'=>false] )
   
  
    

    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f9f9f9;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        table {
            width: 100%;
            border-collapse: collapse;
        
        }
        table, th, td {
            border: 1px solid #ccc;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .section-title {
            font-size: 1.2em;
            margin-top: 20px;
            margin-bottom: 10px;
            color: #007bff;
            border-bottom: 2px solid #007bff;
            padding-bottom: 5px;
        }
    </style>
</head>
<body>
    <div class="">
        <div class="section">
            <div class="section-title">Requestor’s Details</div>
            <table>
                <tr>
                    <th>Full Name</th>
              
              
        
            
                    <th>Cell Phone</th>
                
           
                    <th>Email</th>
                
                </tr>
                {{-- @dd($requestVisit); --}}
                <tr> 
                    <td>{{$requestVisit->requested->fullName}}</td>
                 
                    <td>+1234567890</td>
                    <td>johndoe@unagency.org</td>    
                </tr>
                    
            </table>
        </div>

        <div class="section">
            <div class="section-title">Specific Visit Details</div>
            <table>
                <tr>
            
                
           
                    <th>Date of Visit</th>
                
               
                    <th>Time of Arrival</th>
             
                    <th>Time of Departure</th>
               
             
                    <th>Purpose of Visit</th>
               
                </tr>
                <tr>
                    <td>{{\Illuminate\Support\Carbon::create($requestVisit->visit_date)->format('Y/m/d')}}</td>
                    <td>{{$requestVisit->arrival_time}}</td> 
                     <td>{{$requestVisit->departure_time}}</td> 
                     <td>{{$requestVisit->purpose}}</td>
             </tr>
            </table>
        </div>

        <div class="section">
            <div class="section-title">Visitor(s) Details</div>
            <table>
                
                
                <tr>
                    <th>Full Name</th>
                    <th>ID/Passport</th>
                    <th>Cell Phone</th>
                    <th>Type</th>
                    <th>Organization</th>
                    <th>Remarks</th>
                </tr>
                @foreach ($requestVisit->visitors_detail as $visitor)
                    {{-- @dd($visitor) --}}
                <tr>
                    <td>{{$visitor['name']??'---'}}</td>
                    <td>{{$visitor['id'??'---']}}</td>
                    <td>{{$visitor['phone']??'---'}}</td>
                    <td>{{$visitor['type']??'---'}}</td>
                    <td>{{$visitor['organization']??'---'}}</td>
                    <td>{{$visitor['remarks']??'---'}}</td>
                </tr>
                @endforeach
            </table>
        </div>

        <div class="section">
            <div class="section-title">Driver and Vehicle Details</div>
            <table style="">
                <tr>
                    <th>Driver</th>
                    <th>Vehicle</th>
                </tr>
                <tr style="padding: 0px 0px ;margin:0px">
                    <td style="padding: 0px 0px ;margin:0px">
                           <table>  
                            <tr>
                                <th>Full Name</th>
                                <th>ID/Passport</th>
                                <th>Cell Phone</th>
                            </tr>
                            @foreach ($requestVisit->driver_vehicle_detail as $driver)
                            <tr>
                                <td>{{$driver['name']??'---'}}</td>
                                <td>{{$driver['id']??'---'}}</td>
                                <td>{{$driver['phone']??'---'}}</td>
                            
                            </tr>
                            @endforeach
                             </table>
                    </td>
                    <td style="padding: 0px 0px;margin :0px">
                           <table> 
                            <tr>
                                <th>Model</th>
                                <th>Color</th>
                                <th>Registration Plate</th>
                            </tr>
                            @foreach ($requestVisit->driver_vehicle_detail as $vehicle)
                            <tr>
                                <td>{{$vehicle['model']??'---'}}</td>
                                <td>{{$vehicle['color']??'---'}}</td>
                                <td>{{$vehicle['Registration_Plate']??'---'}}</td>

                            </tr>
                            @endforeach
                             </table>
                    </td>
                </tr>
             

           
            </table>
        </div>

        <div class="section">
            <div class="section-title">Approval</div>
            <table style="border: none">
                <tr>
                    <td style="border: none;padding-bottom: 20px">
                        <p>Requestor’s Signature: </p>
                    </td>
                    <td style="border: none;padding-bottom: 20px">
                        <p>Date: </p>
                    </td>
                </tr>
            </table>
            <table>
                <tr>
                   
                   
                    <th>Status : </th>
                    <th>Approval Date: </th>
                </tr>
                <tr>
                    <td>Approve</td>
                    <td>2024-10-19</td>
                </tr>
            </table>
        </div>
    </div>
    
  
    
@include('pdf.footer') 