<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorType extends Model
{
    use HasFactory;
    protected $fillable = [
        'title',
        'description',
        'company_id',
        'type',
        'parent_id'
    ];

    public function company(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
    public function children(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(__CLASS__,'parent_id','id');
    }


    public function vendors(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Vendor::class);
    }
    public function customers(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Customer::class);
    }
}
