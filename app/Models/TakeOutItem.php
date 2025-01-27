<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TakeOutItem extends Model
{
    protected $guarded=['id'];

    public function asset(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }
}
