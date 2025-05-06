<?php

namespace App\Models;

use App\Enums\POStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_date',
        'purchase_number',
        'description',
        'status',
        'comment',
        'company_id',
        'employee_id',
        'is_quotation',
        'currency_id',
        'need_change'
    ];
    protected $casts = ['status' => POStatus::class];

    public function approvals(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(Approval::class, 'approvable', 'approvable_type', 'approvable_id');
    }

    public function department(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function structure(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Structure::class);
    }

    public function employee(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }


    public function items(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PurchaseRequestItem::class, 'purchase_request_id');
    }

    public function bid(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Bid::class);
    }

    public function company(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
    public function currency(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function quotations(): \Illuminate\Database\Eloquent\Relations\hasMany
    {
        return $this->hasMany(Quotation::class);
    }

    public function quotationItems(): \Illuminate\Database\Eloquent\Relations\hasManyThrough
    {
        return $this->hasManyThrough(QuotationItem::class, Quotation::class);
    }
}
