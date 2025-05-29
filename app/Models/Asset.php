<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Asset extends Model implements HasMedia
{
    use InteractsWithMedia;

    
    protected $guarded = ['id'];

    protected $casts = [
        'attributes' => 'array',
    ];

  
    
    public function getLogAttribute()
    {
        return $this->product?->title . "#-#" . $this->product?->sku . "#-#" . $this?->serial_number ;
    }
    public function getTitleAttribute()
    {
        if ($this->serial_number){
            return  $this->product?->title." (SKU:".$this->product?->sku.") ".$this->brand?->title." ".$this->model ." SN:".$this->serial_number;
        }
        return  $this->product?->title." (SKU:".$this->product?->sku.") ".$this->brand?->title." ".$this->model ;

    }
    public function getTitlenAttribute()
    {
        if ($this->serial_number){
            return  $this->product?->title."-".$this->brand?->title." ".$this->model." SN:".$this->serial_number;
        }
        return  $this->product?->title."-".$this->brand?->title." ".$this->model;

    }
    public function company(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
    public function product(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
    public function check_out_to(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Employee::class,'check_out_to');
    }
    public function party(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Parties::class,'party_id');
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
    public function assetEmployee(): \Illuminate\Database\Eloquent\Relations\HasManyThrough
    {
        return $this->hasManyThrough(
            AssetEmployee::class, // مدل مقصد
            AssetEmployeeItem::class, // مدل واسط
            'asset_id', // کلید خارجی در مدل واسط (AssetEmployeeItem)
            'id', // کلید خارجی در مدل مقصد (AssetEmployee)
            'id', // کلید محلی در مدل اصلی (Asset)
            'asset_employee_id' // کلید محلی در مدل واسط (AssetEmployeeItem)
        );
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
        return $this->hasMany(Service::class);
    }


}
