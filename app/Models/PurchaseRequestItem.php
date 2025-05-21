<?php

namespace App\Models;

use App\Enums\ItemStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class PurchaseRequestItem extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    protected $fillable = [
        'product_id',
        'description',
        'quantity',
        'estimated_unit_cost',
        'status',
        'unit_id',
        'purchase_request_id',
        'project_id',
        'company_id',
        'clarification_comment',
        'verification_comment',
        'approval_comment',
        'clarification_decision',
        'verification_decision',
        'approval_decision',
    ];
    protected $casts=[
        'status'=>ItemStatus::class
    ];
    public function getLogAttribute(){
        return $this?->product?->title."#-#".$this?->purchaseRequest?->purchase_number;
    }

    public function getDepartmentAttribute(){
        return $this->product->department_id  ;
    }

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
