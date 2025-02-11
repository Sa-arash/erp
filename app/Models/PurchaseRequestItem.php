<?php

namespace App\Models;

use App\Enums\ItemStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseRequestItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'description',
        'quantity',
        'estimated_unit_cost',
        'ceo_comment',
        'ceo_decision',
        'status',
        'unit_id',
        'head_comment',
        'head_decision',
        'purchase_request_id',
        'project_id',
        'company_id',
    ];
    protected $casts=[
        'status'=>ItemStatus::class
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function purchaseRequest()
    {
        return $this->belongsTo(PurchaseRequest::class);
    }
    public function project(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Project::class,'project_id');
    }
    public function company(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
    public function quotationItems(): \Illuminate\Database\Eloquent\Relations\hasMany
    {
        return $this->hasMany(QuotationItem::class);
    }
}
