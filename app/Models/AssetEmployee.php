<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetEmployee extends Model
{
    protected $guarded=["id"];

    protected $fillable=[
        'employee_id',
        'date',
        'approve_date',
        'type',
        'status',
        'description',
        'note',
        'company_id',
    ];
    public function company(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
    public function employee(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
    public function assetEmployeeItem(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(AssetEmployeeItem::class);
    }


}
