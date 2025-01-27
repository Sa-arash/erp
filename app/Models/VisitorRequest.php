<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VisitorRequest extends Model
{

    protected $guarded=['id'];

    protected $casts =[
        'visitors_detail'=>'array',
'driver_vehicle_detail'=>'array',
    ];
public function requested(){
    return $this->belongsTo(Employee::class , 'requested_by');
}

public function approved(){
    return $this->belongsTo(Employee::class , 'approved_by');
}
public function company(){
    return $this->belongsTo(Company::class , 'company_id');
}

}
