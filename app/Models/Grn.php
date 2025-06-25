<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Grn extends Model
{
    protected $guarded = ['id'];

    public function approvals(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(Approval::class, 'approvable', 'approvable_type', 'approvable_id');
    }

    public function purchaseOrder(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function manager(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function company(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
    public function employee(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Employee::class,'manager_id');
    }

    public function items(): \Illuminate\Database\Eloquent\Relations\hasMany
    {
        return $this->hasMany(PurchaseOrderItem::class, 'purchase_order_id', 'purchase_order_id');
    }
}
