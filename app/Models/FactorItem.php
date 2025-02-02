<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FactorItem extends Model
{
    protected $guarded=['id'];

    public function factor(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Factor::class);
    }
    public function unit(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }
}
