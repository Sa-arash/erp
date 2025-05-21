<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    /** @use HasFactory<\Database\Factories\ExpenceFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'date',
        'amount',
        'reference',
        'description',
        'payment_receipt_image',
        'company_id',
        'vendor_id',
        'category_id',
    ];

    public function getLogAttribute(){
        return $this?->title."#-#".$this?->reference."#-#".$this?->date;
    }

    public function company(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function category(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Bank_category::class);
    }

    public function account(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function vendor(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function data(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'vendor_id', 'id');
    }

    public function transactions(): \Illuminate\Database\Eloquent\Relations\morphMany
    {
        return $this->morphMany(Transaction::class, 'transactionable');
    }

}
