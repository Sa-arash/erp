<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    /** @use HasFactory<\Database\Factories\VendorFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'address',
        'company_id',
        'vendor_type_id',
        'country',
        'state',
        'city',
        'description',
        'NIC',
        'gender',
        'website',
        'img',
        'total_amount',
        'employee_id'

    ];


    public function company(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Company::class);
    }



    public function expenses(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function vendorType(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(VendorType::class);
    }
}
