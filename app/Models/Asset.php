<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    protected $fillable=[
        'number',
        'product_id',
        'status',
        'price',
        'market_price',
        'warehouse_id',
        'structure_id',
        'company_id',
        'serial_number',
        'attributes',
        'brand_id',
        'model',

    ];
    protected $casts = [
        'attributes' => 'array',
    ];
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

    protected $guarded=["id"];

}
