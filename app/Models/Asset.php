<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    protected $guarded = ['id'];
 
    protected $casts = [
        'attributes' => 'array',
    ];
    
    public function getTitleAttribute()
    {
        return  $this->product?->title." (sku:".$this->product?->sku.") ".$this->brand?->title." ".$this->model;
    }
    public function company(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
    public function product(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
    public function warehouse(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }
    public function structure(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Structure::class);
    }
    public function employees(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(AssetEmployeeItem::class);
    }
    public function assetEmployee(): \Illuminate\Database\Eloquent\Relations\hasManyThrough
    {
        return $this->hasManyThrough(AssetEmployee::class,AssetEmployeeItem::class,'id','id','id','asset_employee_id');
    }
    public function brand(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }
    public function finance(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(AssetEmployeeItem::class);
    }
    public function repair(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(AssetEmployeeItem::class);
    }
    public function service(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(AssetEmployeeItem::class);
    }


}
