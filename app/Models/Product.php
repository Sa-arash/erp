<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Product extends Model implements HasMedia
{
    use InteractsWithMedia,SoftDeletes;
    use HasFactory;
    protected $fillable = [
        'title',
        'second_title',
        'image',
        'department_id',
        'account_id',
        'sku',
        'sub_account_id',
        'product_type',
        'stock_alert_threshold',
        'stock_alert_scope',
        'description',
        'company_id',
    ];

    public function getLogAttribute(){
        return $this?->title."#-#".$this?->sku;
    }
    public function getInfoAttribute(){
        return "(SKU#".$this->sku.") ". $this->title  ;
    }

    public function company(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
    public function account(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
    public function subAccount(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Account::class,'sub_account_id');
    }
    public function department(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Department::class,'department_id');
    }
    public function unit(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }
    public function inventories(): \Illuminate\Database\Eloquent\Relations\hasMany
    {
        return $this->hasMany(Inventory::class);
    }
        public function purchaseRequestItem(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PurchaseRequestItem::class);
    }
    public function purchaseOrderItem(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function assets(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Asset::class);
    }
}
