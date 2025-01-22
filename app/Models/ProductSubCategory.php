<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductSubCategory extends Model
{
    protected $guarded=["id"];

    public function company(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
    public function products(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Product::class,'product_sub_category_id');
    }
    public function account(): \Illuminate\Database\Eloquent\Relations\belongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
