<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Income extends Model
{
    /** @use HasFactory<\Database\Factories\IncomeFactory> */
    use HasFactory;
    protected $fillable = [
        'title',
        'date',
        'amount',
        'reference',
        'description',
        'payment_receipt_image',
        'company_id',
        'customer_id',
        'category_id',
    ];
    public function company(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
    public function category(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Bank_category::class);
    }

    public function customer(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
    public function data(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Customer::class,'customer_id','id');
    }

    public function transactions(): \Illuminate\Database\Eloquent\Relations\morphMany
    {
        return $this->morphMany(Transaction::class,'transactionable');
    }
}
