<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FactorItem extends Model
{
    protected $guarded=['id'];

    public function getLogAttribute(){
        return $this?->title."#-#".$this?->quantity."#-#".$this?->factor?->title."#-#".$this?->factor?->invoice?->number;
    }

    public function factor(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Factor::class);
    }
    public function unit(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }
}
