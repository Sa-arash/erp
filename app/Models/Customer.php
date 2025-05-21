<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    /** @use HasFactory<\Database\Factories\CustomerFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'gender',
        'country',
        'state',
        'city',
        'description',
        'company_id',
        'NIC',
        'website',
        'img',
        'total_amount',
        'vendor_type_id'
    ];

    public function getLogAttribute(){
        return $this?->name."#-#".$this?->email."#-#".$this?->NIC;
    }

    public function company(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function customerType(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(VendorType::class,'vendor_type_id','id');
    }

    public function incomes(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Income::class);
    }

    public function transactions(): \Illuminate\Database\Eloquent\Relations\HasManyThrough
    {
        return $this->hasManyThrough(Transaction::class,Income::class,'customer_id','transactionable_id','id','id')->where('transactionable_type','App\Models\Income');
    }

}
