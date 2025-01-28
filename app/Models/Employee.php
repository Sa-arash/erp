<?php

namespace App\Models;

use App\Enums\GenderEnum;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use SoftDeletes,HasFactory;


    protected $fillable = ['structure_id','warehouse_id','signature_pic','daily_salary','position_id','branch','contract_id','NIC','post_code','benefit_salary','user_id', 'fullName', 'email', 'phone_number', 'birthday', 'joining_date', 'leave_date', 'country', 'state', 'city', 'address','address2', 'cart', 'bank', 'tin', 'base_salary', 'department_id', 'position_id', 'gender', 'marriage', 'count_of_child', 'emergency_phone_number', 'pic', 'blood_group', 'company_id', 'duty_id','covid_vaccine_certificate','immunization','card_status','type_of_ID','ID_number','emergency_contact'];

    protected $casts=[
        'emergency_contact'=>'array',
        'immunization'=>'array',
    ];

   public function getInfoAttribute(){
       return $this->fullName." (ID:".$this->ID_number." )";
   }

    public function structure(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Structure::class);
    }
    public function separation(): \Illuminate\Database\Eloquent\Relations\hasOne
    {
        return $this->hasOne(Separation::class);
    }

    public function company(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function department(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Department::class);
    }
    public function duty(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Duty::class);
    }
    public function position(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Position::class);
    }
    public function benefits(): \Illuminate\Database\Eloquent\Relations\belongsToMany
    {
        return $this->belongsToMany(Benefit::class,'benefit_employees');
    }
    public function contract(): \Illuminate\Database\Eloquent\Relations\belongsTo
    {
        return $this->belongsTo(Contract::class);
    }
    public function documents(): \Illuminate\Database\Eloquent\Relations\hasMany
    {
        return $this->hasMany(Documentation::class);
    }
    public function AssetEmployee(): \Illuminate\Database\Eloquent\Relations\hasMany
    {
        return $this->hasMany(AssetEmployee::class,'employee_id');
    }
    public function assetEmployeeItems()
    {
        return $this->hasManyThrough(
            AssetEmployeeItem::class,
            AssetEmployee::class,
            'employee_id',
            'asset_employee_id',
            'id',
            'id'
        )->where('asset_employee_items.type',0)->whereHas('assetEmployee',function ($query){
            return $query->where('type','Assigned');
        });
    }
    public function payrolls(): \Illuminate\Database\Eloquent\Relations\hasMany
    {
        return $this->hasMany(Payroll::class);
    }

    public function leaves(): \Illuminate\Database\Eloquent\Relations\hasMany
    {
        return $this->hasMany(Leave::class);
    }
    public function overTimes(): \Illuminate\Database\Eloquent\Relations\hasMany
    {
        return $this->hasMany(Overtime::class);
    }

    public function assetEmployeeRepair(): \Illuminate\Database\Eloquent\Relations\HasManyThrough
    {
        return $this->hasManyThrough(
            AssetEmployeeItem::class,
            AssetEmployee::class,
            'employee_id',
            'asset_employee_id',
            'id',
            'id'
        )->whereHas('assetEmployee',function ($query){
            return $query->where('type','Repair');
        });
    }
    public function assetEmployeeReturn(): \Illuminate\Database\Eloquent\Relations\HasManyThrough
    {
        return $this->hasManyThrough(
            AssetEmployeeItem::class,
            AssetEmployee::class,
            'employee_id',
            'asset_employee_id',
            'id',
            'id'
        )->whereHas('assetEmployee',function ($query){
            return $query->where('type','Return');
        });
    }

    public function approvals(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Approval::class , 'employee_id');
    }

}
