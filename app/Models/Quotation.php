<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quotation extends Model
{
    protected $guarded = ['id'];

    public function company(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
    public function employee(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
    public function employeeOperation(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Employee::class,'employee_operation_id');
    }
    public function purchaseRequest(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(PurchaseRequest::class);
    }
    public function party(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Parties::class);
    }
    public function quotationItems(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(QuotationItem::class);
    }
    public function currency(): \Illuminate\Database\Eloquent\Relations\belongsTo
    {
        return $this->belongsTo(Currency::class);
    }
}
